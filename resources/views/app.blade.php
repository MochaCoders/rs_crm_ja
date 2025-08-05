<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
      href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
      rel="stylesheet"
    />

    <!-- Quill CSS (must come before your app CSS) -->
    <link
      href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css"
      rel="stylesheet"
    />

    <!-- Scripts you need before your Vue app -->
    @routes

    <!-- Quill JS (load here so window.Quill exists) -->
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <!-- Vite-compiled scripts/CSS -->
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
  </head>

  <body class="font-sans antialiased">
    @inertia
  </body>
</html>
