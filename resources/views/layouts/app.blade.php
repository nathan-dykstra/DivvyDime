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
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if (isset($styles)) 
            {{ $styles }}
        @endif

        @if (isset($scripts))
            {{ $scripts }}
        @endif
    </head>

    <body>
        <div class="app">
            @include('layouts.navbar')

            @include('layouts.sidebar')

            <div class="app-wrapper" id="app-wrapper">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="page-header" id="page-header">
                        <div class="header-content-wrapper" id="header-content-wrapper">
                            <div class="header-content" id="header-content">
                                {{ $header }}
                            </div>
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="main-content" >
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
