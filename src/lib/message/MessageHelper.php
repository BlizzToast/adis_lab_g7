<?php
declare(strict_types=1);

/**
 * MessageHelper - Display utility for messages
 */
class MessageHelper
{
    public static function renderMessage(array $message): string
    {
        $avatar = htmlspecialchars($message['avatar'] ?? '🐧');
        $username = htmlspecialchars($message['username']);
        $content = htmlspecialchars($message['content']);

        return <<<HTML
            <article class="terminal-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
                <header style="display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--secondary-color); border-radius: 0.3rem; padding: 0.4rem 0.75rem; margin-bottom: 1rem; font-size: 0.95rem; font-weight: normal; background: none;">
                    <span style="display: flex; align-items: center; gap: 0.5em;">
                        <span style="font-size: 1.2em;">{$avatar}</span>
                        <span style="font-weight: bold; color: var(--primary-color);">{$username}</span>
                    </span>
                </header>
                <div style="line-height: 1.6;">{$content}</div>
            </article>
        HTML;
    }
}
