<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @foreach (app(\App\Services\ThemeManager::class)->faviconLinks() as $link)
            <link
                rel="{{ $link['rel'] }}"
                @isset($link['type']) type="{{ $link['type'] }}" @endisset
                @isset($link['sizes']) sizes="{{ $link['sizes'] }}" @endisset
                href="{{ $link['href'] }}"
            >
        @endforeach

        @routes
        @vite([app(\App\Services\ThemeManager::class)->viteEntry()])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
