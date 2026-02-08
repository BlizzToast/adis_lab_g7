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
    <link rel="stylesheet" href="/public/css/penguin-animation.css">
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
    <script src="/public/js/penguin-animation.js"></script>
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

    <!-- Ice Track -->
    <div id="iceTrack" class="ice-track">
        <svg viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <ellipse cx="50" cy="10" rx="50" ry="10" fill="#B3E5FC" opacity="0.7"/>
            <ellipse cx="50" cy="8" rx="48" ry="8" fill="#E1F5FE"/>
        </svg>
    </div>

    <!-- Pengu Animation Container -->
    <template id="penguTemplate">
        <div class="pengu-container">
            <!-- Sliding Pengu Frame -->
            <svg class="pengu-sliding" viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
                <g class="body">
                    <ellipse cx="100" cy="90" rx="55" ry="35" fill="#2C3E50"/>
                    <ellipse cx="100" cy="90" rx="40" ry="25" fill="#ECF0F1"/>
                </g>

                <g class="flippers">
                    <ellipse cx="50" cy="85" rx="25" ry="10" fill="#2C3E50" transform="rotate(-15 50 85)"/>
                    <ellipse cx="145" cy="95" rx="25" ry="10" fill="#2C3E50" transform="rotate(15 145 95)"/>
                </g>

                <g class="head">
                    <ellipse cx="130" cy="70" rx="28" ry="25" fill="#2C3E50" transform="rotate(20 130 70)"/>
                </g>

                <g class="eyes">
                    <ellipse cx="135" cy="65" rx="8" ry="10" fill="white"/>
                    <circle cx="137" cy="67" r="4" fill="#2C3E50"/>

                    <ellipse cx="150" cy="68" rx="8" ry="10" fill="white"/>
                    <circle cx="152" cy="70" r="4" fill="#2C3E50"/>
                </g>

                <g class="beak">
                <ellipse cx="145" cy="75" rx="8" ry="5" fill="#FF6B35" transform="rotate(20 145 75)"/>
                </g>

                <g class="feet">
                <ellipse cx="85" cy="105" rx="10" ry="5" fill="#FF6B35"/>
                <ellipse cx="105" cy="108" rx="10" ry="5" fill="#FF6B35"/>
                </g>
            </svg>

            <!-- Standing Pengu Frame -->
            <svg class="pengu-standing" viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">

                <g class="body">
                    <ellipse cx="100" cy="80" rx="45" ry="50" fill="#2C3E50"/>
                    <ellipse cx="100" cy="85" rx="30" ry="38" fill="#ECF0F1"/>
                </g>

                <g class="flippers">
                    <ellipse cx="65" cy="75" rx="12" ry="30" fill="#2C3E50" transform="rotate(-20 65 75)"/>
                    <ellipse cx="135" cy="75" rx="12" ry="30" fill="#2C3E50" transform="rotate(20 135 75)"/>
                </g>

                <g class="head">
                    <circle cx="100" cy="45" r="28" fill="#2C3E50"/>
                </g>

                <g class="eyes">
                    <ellipse cx="90" cy="42" rx="8" ry="11" fill="white"/>
                    <circle cx="90" cy="44" r="4" fill="#2C3E50"/>

                    <ellipse cx="110" cy="42" rx="8" ry="11" fill="white"/>
                    <circle cx="110" cy="44" r="4" fill="#2C3E50"/>
                </g>

                <g class="beak">
                    <ellipse cx="100" cy="52" rx="8" ry="6" fill="#FF6B35"/>
                    <ellipse cx="100" cy="57" rx="6" ry="4" fill="#FF6B35"/>
                </g>

                <g class="feet">
                    <ellipse cx="90" cy="125" rx="12" ry="6" fill="#FF6B35"/>
                    <ellipse cx="110" cy="125" rx="12" ry="6" fill="#FF6B35"/>
                </g>
            </svg>
        </div>
    </template>

    <div id="penguStage"></div>

</body>
</html>
