<?php
// header.php - shared header/menu

// Determine current script to mark active menu item
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

function menuItem($href, $label, $current) {
    $class = 'menu-item';
    if ($current === basename($href)) {
        $class .= ' active';
    }
    return "<li><a class=\"$class\" href=\"$href\">$label</a></li>";
}
?>
<div class="terminal-nav">
    <div class="terminal-logo">
        <div class="logo terminal-prompt"><a href="index.php" class="no-style">Roary</a></div>
    </div>
    <nav class="terminal-menu">
        <ul>
            <?php
            echo menuItem('index.php', 'Home', $current);
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                // When logged in, show Admin and Logout; hide Login/Register
                echo menuItem('admin.php', 'Admin', $current);
                echo menuItem('logout.php', 'Logout', $current);
            } else {
                // Not logged in: show Login and Register
                echo menuItem('login.php', 'Login', $current);
                echo menuItem('register.php', 'Register', $current);
            }
            ?>
        </ul>
    </nav>
</div>
