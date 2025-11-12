<?php
require_once __DIR__ . '/../auth/UserAuth.php';

$isLoggedIn = UserAuth::isLoggedIn();
$username = $isLoggedIn ? $_SESSION['username'] : null;
?>
<div class="terminal-nav">
    <div class="terminal-logo">
        <div class="logo terminal-prompt"><a href="/index" class="no-style">Roary</a></div>
    </div>
    <nav class="terminal-menu">
        <ul>
            <li><a class="menu-item" href="/index">Home</a></li>
            <?php if (!$isLoggedIn): ?>
                <li><a class="menu-item" href="/login">Login</a></li>
                <li><a class="menu-item" href="/register">Register</a></li>
            <?php else: ?>
                <li>
                    <a class="menu-item user-logout-btn" href="/login?action=logout">
                        <span class="username"><?php echo htmlspecialchars($username); ?></span>
                        <span class="logout-text">Logout</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<style>
.user-logout-btn {
    position: relative;
    display: inline-block;
    width: 100px;
    text-align: center;
    overflow: hidden;
    box-sizing: border-box;
    padding: 0 !important;
    margin: 0 !important;
    border: 1px solid transparent !important;
    vertical-align: middle;
}
.user-logout-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #ff6b6b, #ee5a6f);
    transition: left 0.3s ease;
    z-index: 0;
}
.user-logout-btn:hover::before {
    left: 0;
}
.user-logout-btn .username,
.user-logout-btn .logout-text {
    position: relative;
    z-index: 1;
}
.user-logout-btn .username {
    display: inline-block;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: opacity 0.3s ease;
    vertical-align: middle;
}
.user-logout-btn .logout-text {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    white-space: nowrap;
    transition: opacity 0.3s ease;
}
.user-logout-btn:hover {
    border-color: #ff6b6b !important;
}
.user-logout-btn:hover .username {
    opacity: 0;
}
.user-logout-btn:hover .logout-text {
    opacity: 1;
    color: white !important;
}
</style>
