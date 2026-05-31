<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS & Alpine.js from CDN to avoid Vite/npm runtime dependency -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --bg-main: #0b1022;
            --card-surface: rgba(255, 255, 255, 0.03);
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(255, 255, 255, 0.06);
            --text-main: #e6eefb;
            --text-dim: #9ca7cb;
        }

        body {
            background: radial-gradient(circle at 20% 0%, #1b2450 0%, var(--bg-main) 45%);
            color: var(--text-main);
        }

        .guest-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }

        .guest-card {
            width: 100%;
            max-width: 480px;
            padding: 2.25rem;
            background: var(--card-surface);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(3, 7, 18, 0.6);
        }

        .guest-card label {
            color: var(--text-dim);
        }

        .guest-card input[type="text"],
        .guest-card input[type="email"],
        .guest-card input[type="password"] {
            background: var(--input-bg);
            color: var(--text-main);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: .75rem 1rem;
        }

        .guest-card input::placeholder {
            color: rgba(230, 238, 251, 0.45);
        }

        .guest-card .underline {
            text-decoration: underline;
            color: var(--text-dim);
        }

        .guest-card .text-gray-600 {
            color: var(--text-dim) !important;
        }

        .guest-card .primary-btn {
            background: var(--text-main);
            color: var(--bg-main);
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="guest-wrapper">
        <div>

        </div>

        <div class="guest-card">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
