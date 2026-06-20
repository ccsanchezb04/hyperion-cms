<x-mail::message>
# Nuevo mensaje de contacto

Has recibido un nuevo mensaje desde el formulario de **Contacto** del sitio.

**Nombre:** {{ $name }}
**Email:** {{ $email }}
**Asunto:** {{ $subjectLabel }}
**Recibido:** {{ $receivedAt?->format('Y-m-d H:i') }}
@if ($ip)
**IP:** {{ $ip }}
@endif

---

{{ $body }}

<x-mail::button :url="config('app.url').'/admin/contact-messages'">
Ver en el panel
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
