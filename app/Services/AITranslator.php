<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Traducción de texto via AI. Si hay API key de OpenAI configurada, usa la
 * API real; si no, devuelve una simulación marcada con el idioma destino
 * (útil en dev / tests sin credenciales).
 *
 * Shared service: lo consumen tanto AIController (API pública con sanctum)
 * como ContentController (admin, con session auth) para evitar duplicar la
 * lógica.
 */
class AITranslator
{
    public const LANGUAGE_NAMES = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ja' => 'Japanese',
        'zh' => 'Chinese',
        'ru' => 'Russian',
        'ar' => 'Arabic',
    ];

    protected string $provider;
    protected ?string $apiKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'openai');
        $this->apiKey   = config('services.ai.api_key');
        $this->endpoint = config('services.ai.endpoint', 'https://api.openai.com/v1');
    }

    public function isConfigured(): bool
    {
        return $this->provider === 'openai' && ! empty($this->apiKey);
    }

    public function translate(string $content, string $targetLanguage, string $sourceLanguage = 'auto'): string
    {
        if ($this->isConfigured()) {
            return $this->translateWithOpenAI($content, $targetLanguage, $sourceLanguage);
        }
        return $this->simulate($content, $targetLanguage);
    }

    protected function translateWithOpenAI(string $content, string $targetLanguage, string $sourceLanguage): string
    {
        $targetName = self::LANGUAGE_NAMES[$targetLanguage] ?? $targetLanguage;
        $systemPrompt = "You are a professional translator. Translate the given text to {$targetName}. "
            . "Preserve formatting and tone. Return only the translation, no preface.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post("{$this->endpoint}/chat/completions", [
            'model'    => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $content],
            ],
            'max_tokens'  => 1000,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI API request failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    protected function simulate(string $content, string $targetLanguage): string
    {
        $targetName = self::LANGUAGE_NAMES[$targetLanguage] ?? $targetLanguage;
        return "[Translated to {$targetName}] " . $content;
    }
}
