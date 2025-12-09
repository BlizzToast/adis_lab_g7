<?php
/**
 * @var string $title
 * @var string $content
 * @var \App\Core\Session $session
 * @var array $config
 * @var \App\Core\Request $request
 * @var array $messages
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#222225">
    <title><?php echo htmlspecialchars($title ?? "Roary"); ?></title>
    <link rel="stylesheet" href="/public/css/pico.min.css">
    <style>
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('/public/fonts/JetBrainsMono-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('/public/fonts/JetBrainsMono-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('/public/fonts/JetBrainsMono-Medium.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
        }
        :root {
            --pico-font-family: 'JetBrains Mono', monospace;
        }
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        article header {
            background: transparent;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="container">
        <nav>
            <ul>
                <li><strong><a href="/" class="contrast logo-text">🐧 Roary</a></strong></li>
            </ul>
            <ul>
                <?php if (!$session->has("username")): ?>
                    <li><a href="/login" role="button">Login</a></li>
                    <li><a href="/register" role="button" class="contrast">Register</a></li>
                <?php else: ?>
                    <li>
                        <a href="/profile">
                            <?php echo htmlspecialchars(
                                $session->get("username"),
                            ); ?> - Profile
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Flash Messages -->
        <?php if ($messages ?? false): ?>
            <?php foreach ($messages as $type => $message): ?>
                <?php if ($message): ?>
                    <article class="<?php echo match ($type) {
                        "success" => "pico-color-green-500",
                        "error" => "pico-color-red-500",
                        "warning" => "pico-color-amber-500",
                        "info" => "pico-color-blue-500",
                        default => "",
                    }; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="container">
        <hr>
        <p><small>
            Roary - Where Penguins Roar and Vibes Soar! | ADIS Lab Project | <a href="/admin" class="secondary">Admin</a>
        </small></p>
    </footer>

    <!-- JavaScript -->
    <script src="/public/js/user.js"></script>
    <script src="/public/js/roary.js"></script>
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/worker.js')
                    .then((registration) => {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
