<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 * @var array $messages
 */
?>
<article>
    <header>
        <h1>Login</h1>
    </header>

    <?php if (isset($errors["login"])): ?>
        <div style="text-align: center; margin-bottom: 2rem;">
            <p style="color: var(--pico-color-red-550); font-size: 1.1rem; font-weight: 500; margin: 0;">
                <?php echo htmlspecialchars($errors["login"]); ?>
            </p>
        </div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="/login">
        <label for="username">
            Username
            <input
                type="text"
                id="username"
                name="username"
                value="<?php echo htmlspecialchars($old["username"] ?? ""); ?>"
                required
                pattern="^[a-zA-Z0-9]+$"
                title="Only alphanumeric characters allowed"
                <?php if (isset($errors["username"])): ?>
                aria-invalid="true"
                <?php endif; ?>
                aria-describedby="<?php echo isset($errors["username"])
                    ? "username-error"
                    : ""; ?>">
            <?php if (isset($errors["username"])): ?>
                <small id="username-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["username"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <label for="password">
            Password
            <input
                type="password"
                id="password"
                name="password"
                required
                minlength="12"
                <?php if (isset($errors["password"])): ?>
                aria-invalid="true"
                <?php endif; ?>
                aria-describedby="<?php echo isset($errors["password"])
                    ? "password-error"
                    : ""; ?>">
            <?php if (isset($errors["password"])): ?>
                <small id="password-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["password"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
            $csrf_token,
        ); ?>">

        <button type="submit">Login</button>
    </form>

    <footer>
        <p>Don't have an account? <a href="/register">Register here</a></p>
    </footer>
</article>
