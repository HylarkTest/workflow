<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        {{ $title ?? __('errors.error_pages.title') }}
    </title>
    <link rel="shortcut icon" type="image/png" href="/favicon_small.ico" sizes="16x16">
    <link rel="shortcut icon" type="image/png" href="/favicon_medium.ico" sizes="32x32">
    <link rel="shortcut icon" type="image/png" href="/favicon_large.ico" sizes="96x96">
    <script>
        @php
            try {
                $frontendEnvFile = file_get_contents('frontend/.env');
                $sentryEnvVars = [];

                foreach ([
                    'VITE_SENTRY_DSN',
                    'VITE_REPORT_ERRORS',
                ] as $envKey) {
                    preg_match('/' . $envKey . '=(.*)/', $frontendEnvFile, $matches);
                    $sentryEnvVars[$envKey] = trim($matches[1]);
                }

                foreach($sentryEnvVars as $key => $value) {
                    echo "const $key = '$value';" . PHP_EOL;
                }
            } catch (\Exception) {
                throw new Error('Cannot read environment variables');
            }
        @endphp

        window.sentryOnLoad = () => {
            Sentry.init({
                dsn: VITE_SENTRY_DSN,
                environment: '{{ config('app.env') }}',
                enabled: VITE_REPORT_ERRORS === 'true',
            });
        };
    </script>

    @if ($sentryEnvVars['VITE_REPORT_ERRORS'] === 'true')
    <script
        src="https://js.sentry-cdn.com/b1a4ff6e07ba42c89d0e8ee77b7353a0.min.js"
        crossorigin="anonymous"
    >
    </script>
    @endif

    <style>
        @import "https://fonts.googleapis.com/css?family=Figtree:400,500,600,700&display=swap";
        * {
            font-family: 'Figtree', sans-serif;
            margin: 0;
            overflow: hidden;
            padding: 0;
        }
        body {
            font-size: 100%;
            --hl-cm-color-00: #ffffff;
            --hl-cm-color-600: #516176;
            --hl-cm-color-900: #333c47;
            --hl-color-azure-500: #615cff;
            --hl-color-azure-600: #4229ff;
            --hl-color-azure-800: #2b1aa8;
        }
        body.darkmode {
            --hl-cm-color-00: #000000;
            --hl-cm-color-600: #8d9cb0;
            --hl-cm-color-900: #f3f5f7;
            --hl-color-azure-500: #615cff;
            --hl-color-azure-600: #8084ff;
            --hl-color-azure-800: #ccd5ff;
        }
        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        main {
            background: var(--hl-cm-color-00);
            min-height: 100vh;
        }
        #error-container {
            flex-direction: column;
            max-width: 500px;
        }
        img {
            display: block;
            height: 10rem;
            margin-bottom: 1rem;
            max-width: 100%;
            vertical-align: middle;
        }
        h1 {
            color: var(--hl-color-azure-800);
            font-size: 120px;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        #error-text {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        #error-text h3 {
            color: var(--hl-cm-color-900);
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.75rem;
            margin-bottom: 0.25rem;
        }
        #error-text p {
            color: var(--hl-cm-color-600);
            font-size: 0.92rem;
            line-height: 1.5;
        }
        a {
            background: var(--hl-color-azure-600);
            border-radius: 0.5rem;
            color: var(--hl-cm-color-00);
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: 0.2s ease-in-out;
        }
        a:hover {
            background: var(--hl-color-azure-500);
        }
    </style>
</head>
<body>
    <main class="flex-center">
        <div id="error-container" class="flex-center">
            <img
                src="{{ "/branding/$image" }}"
                alt="{{ $imgAltText }}"
            />
            <h1>
                @yield('code')
            </h1>
            <div id="error-text">
                <h3>
                    @yield('message')
                </h3>
                <p>
                    @yield('explanation')
                </p>
            </div>
            <a href="/home">
                {{ __('*.errors.backHome') }}
            </a>
        </div>
    </main>
    <script>
        if (localStorage['user-preferences'] ?? null) {
            const colorMode = JSON.parse(JSON.parse(localStorage['user-preferences']).value).colorMode;
            if (colorMode === 'DARK') {
                document.body.classList.add('darkmode');
            }
        }
    </script>
</body>
</html>
