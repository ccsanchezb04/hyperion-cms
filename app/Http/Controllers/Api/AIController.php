<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateContentRequest;
use App\Http\Requests\AI\GenerateSeoRequest;
use App\Http\Requests\AI\TranslateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected string $aiProvider;
    protected ?string $apiKey;
    protected string $apiEndpoint;

    public function __construct()
    {
        $this->aiProvider = config('services.ai.provider', 'openai');
        $this->apiKey = config('services.ai.api_key');
        $this->apiEndpoint = config('services.ai.endpoint', 'https://api.openai.com/v1');
    }

    /**
     * Generate content using AI.
     */
    public function generateContent(GenerateContentRequest $request): JsonResponse
    {
        $prompt = $request->input('prompt');
        $type = $request->input('type', 'blog_post');
        $tone = $request->input('tone', 'professional');
        $length = $request->input('length', 'medium');

        try {
            if ($this->aiProvider === 'openai' && $this->apiKey) {
                $generatedContent = $this->generateWithOpenAI($prompt, $type, $tone, $length);
            } else {
                $generatedContent = $this->simulateContentGeneration($prompt, $type, $tone, $length);
            }

            return response()->json([
                'data' => [
                    'content' => $generatedContent,
                    'type' => $type,
                    'tone' => $tone,
                    'length' => $length,
                    'word_count' => str_word_count($generatedContent),
                ],
                'message' => 'Content generated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AI content generation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate content: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate SEO metadata using AI.
     */
    public function generateSeo(GenerateSeoRequest $request): JsonResponse
    {
        $title = $request->input('title');
        $content = $request->input('content');
        $keywords = $request->input('keywords', []);

        try {
            if ($this->aiProvider === 'openai' && $this->apiKey) {
                $seoData = $this->generateSeoWithOpenAI($title, $content, $keywords);
            } else {
                $seoData = $this->simulateSeoGeneration($title, $content, $keywords);
            }

            return response()->json([
                'data' => $seoData,
                'message' => 'SEO metadata generated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AI SEO generation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate SEO: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Translate content using AI.
     */
    public function translate(TranslateRequest $request): JsonResponse
    {
        $content = $request->input('content');
        $targetLanguage = $request->input('target_language');
        $sourceLanguage = $request->input('source_language', 'auto');

        try {
            if ($this->aiProvider === 'openai' && $this->apiKey) {
                $translatedContent = $this->translateWithOpenAI($content, $targetLanguage, $sourceLanguage);
            } else {
                $translatedContent = $this->simulateTranslation($content, $targetLanguage, $sourceLanguage);
            }

            return response()->json([
                'data' => [
                    'original_content' => $content,
                    'translated_content' => $translatedContent,
                    'source_language' => $sourceLanguage,
                    'target_language' => $targetLanguage,
                    'character_count' => mb_strlen($translatedContent),
                ],
                'message' => 'Content translated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AI translation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to translate content: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate content using OpenAI API.
     */
    protected function generateWithOpenAI(string $prompt, string $type, string $tone, string $length): string
    {
        $lengths = [
            'short' => 150,
            'medium' => 300,
            'long' => 600,
        ];

        $maxTokens = $lengths[$length] ?? 300;

        $systemPrompt = "You are a content writer. Write content in a {$tone} tone. The content type is {$type}.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiEndpoint}/chat/completions", [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Generate SEO metadata using OpenAI API.
     */
    protected function generateSeoWithOpenAI(string $title, string $content, array $keywords): array
    {
        $systemPrompt = "You are an SEO expert. Generate SEO metadata for the given content.";
        $userPrompt = "Generate SEO metadata for:\nTitle: {$title}\nContent: {$content}\nKeywords: " . implode(', ', $keywords);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiEndpoint}/chat/completions", [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 500,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        $generatedText = $data['choices'][0]['message']['content'] ?? '';

        // Parse the generated text to extract SEO components
        return $this->parseSeoResponse($generatedText, $title, $content, $keywords);
    }

    /**
     * Translate content using OpenAI API.
     */
    protected function translateWithOpenAI(string $content, string $targetLanguage, string $sourceLanguage): string
    {
        $languageNames = [
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

        $targetLangName = $languageNames[$targetLanguage] ?? $targetLanguage;

        $systemPrompt = "You are a professional translator. Translate the given text to {$targetLangName}.";
        $userPrompt = $content;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiEndpoint}/chat/completions", [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 1000,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Parse SEO response from AI.
     */
    protected function parseSeoResponse(string $generatedText, string $title, string $content, array $keywords): array
    {
        // Default fallback values
        $metaTitle = strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;
        $metaDescription = substr(strip_tags($content), 0, 157) . '...';

        // Try to parse structured response (simple implementation)
        if (preg_match('/Meta Title: (.+)/i', $generatedText, $matches)) {
            $metaTitle = trim($matches[1]);
        }
        if (preg_match('/Meta Description: (.+)/i', $generatedText, $matches)) {
            $metaDescription = trim($matches[1]);
        }

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => array_merge($keywords, ['generated', 'keyword', 'suggestion', 'seo']),
            'og_title' => $metaTitle,
            'og_description' => $metaDescription,
            'twitter_title' => $metaTitle,
            'twitter_description' => $metaDescription,
        ];
    }

    /**
     * Simulate content generation (placeholder for real AI integration).
     */
    protected function simulateContentGeneration(string $prompt, string $type, string $tone, string $length): string
    {
        $lengths = [
            'short' => 150,
            'medium' => 300,
            'long' => 600,
        ];

        $wordCount = $lengths[$length] ?? 300;

        $templates = [
            'blog_post' => "Here is a blog post about {$prompt} written in a {$tone} tone. ",
            'article' => "This article explores {$prompt} with a {$tone} perspective. ",
            'description' => "A {$tone} description of {$prompt}: ",
            'summary' => "Summary of {$prompt} in a {$tone} style: ",
        ];

        $template = $templates[$type] ?? $templates['blog_post'];

        $content = $template;
        $content .= "This is a placeholder for AI-generated content. ";
        $content .= "In a real implementation, this would connect to an AI service like OpenAI's GPT. ";
        $content .= "The content would be tailored to the {$type} type and {$tone} tone requested. ";
        $content .= str_repeat("Additional generated content would appear here. ", ceil($wordCount / 10));

        return $content;
    }

    /**
     * Simulate SEO generation (placeholder for real AI integration).
     */
    protected function simulateSeoGeneration(string $title, string $content, array $keywords): array
    {
        $metaTitle = $title;
        if (strlen($title) > 60) {
            $metaTitle = substr($title, 0, 57) . '...';
        }

        $metaDescription = substr(strip_tags($content), 0, 160);
        if (strlen($metaDescription) >= 160) {
            $metaDescription = substr($metaDescription, 0, 157) . '...';
        }

        $suggestedKeywords = array_merge($keywords, [
            'generated',
            'keyword',
            'suggestion',
            'seo',
        ]);

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => $suggestedKeywords,
            'og_title' => $metaTitle,
            'og_description' => $metaDescription,
            'twitter_title' => $metaTitle,
            'twitter_description' => $metaDescription,
        ];
    }

    /**
     * Simulate translation (placeholder for real AI integration).
     */
    protected function simulateTranslation(string $content, string $targetLanguage, string $sourceLanguage): string
    {
        $languageNames = [
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

        $targetLangName = $languageNames[$targetLanguage] ?? $targetLanguage;

        return "[Translated to {$targetLangName}] " . $content;
    }

    /**
     * Get AI service status.
     */
    public function status(): JsonResponse
    {
        $isConfigured = !empty($this->apiKey) && $this->aiProvider === 'openai';

        return response()->json([
            'data' => [
                'service' => 'ai_service',
                'provider' => $this->aiProvider,
                'status' => $isConfigured ? 'connected' : 'simulated',
                'features' => [
                    'content_generation' => true,
                    'seo_generation' => true,
                    'translation' => true,
                ],
                'message' => $isConfigured
                    ? 'AI service is connected and ready.'
                    : 'AI service is currently in simulation mode. Configure API credentials for production use.',
            ],
        ]);
    }
}
