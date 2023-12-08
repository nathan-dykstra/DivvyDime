<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts & Styling-->
        @vite(['resources/css/app.css'])

        @if (isset($styles)) 
            {{ $styles }}
        @endif

        @if (isset($scripts))
            {{ $scripts }}
        @endif
    </head>

    <body>
        <!-- Set app theme before the content loads -->
        <script>
            const availableThemes = ["system", "light", "dark"];
            const theme = window.localStorage.getItem('theme');
            if (availableThemes.includes(theme)) {
                availableThemes.forEach((availableTheme) => {
                    document.body.classList.remove(availableTheme);
                });
                document.body.classList.add(theme);
            }
        </script>
        
        <div class="guest-app">
            <main class="guest-main-content" >
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
