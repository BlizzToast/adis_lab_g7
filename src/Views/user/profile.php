<?php
/**
 * @var array $user
 * @var array $stats
 * @var int $postCount
 * @var string $csrf_token
 * @var array $errors
 * @var array $messages
 */
?>
<h1>Profile</h1>

<div class="grid">
    <!-- User Info Card -->
    <article>
        <header>User Information</header>
        <p><strong>Username:</strong> <?php echo htmlspecialchars(
            $user["username"],
        ); ?></p>
        <p><strong>Avatar:</strong> <?php echo $user["avatar"] ?? "🐧"; ?></p>
        <p><strong>Total Posts:</strong> <?php echo $postCount; ?></p>
        <p><strong>Member Since:</strong> <?php echo date(
            "Y-m-d",
            strtotime($user["created_at"] ?? "now"),
        ); ?></p>
        <?php if ($user["is_admin"] ?? false): ?>
            <p><strong>Role:</strong> <mark>Admin</mark></p>
        <?php endif; ?>
    </article>

    <!-- Quick Actions Card -->
    <article>
        <header>Actions</header>
        <form method="POST" action="/logout" id="logout-form" style="margin-bottom: 1rem;">
            <button type="submit" class="secondary" style="width: 100%;">Logout</button>
        </form>
        <a href="/" role="button" style="width: 100%; display: block; text-align: center;">Back to Feed</a>
    </article>
</div>

<script>
document.getElementById('logout-form')?.addEventListener('submit', () => {
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_API_CACHE' });
    }
});
</script>

<!-- Change Username -->
<?php if ($user["username"] !== "admin"): ?>
<article>
    <header>Change Username</header>
    <form method="POST" action="/profile/update-username">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
            $csrf_token,
        ); ?>">

        <label for="username">
            New Username
            <input type="text"
                   id="username"
                   name="username"
                   value="<?php echo htmlspecialchars($user["username"]); ?>"
                   pattern="^[a-zA-Z0-9]+$"
                   minlength="3"
                   maxlength="50"
                   required
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

        <button type="submit">Update Username</button>
    </form>
</article>
<?php endif; ?>

<!-- Change Password -->
<?php if ($user["username"] !== "admin"): ?>
<article>
    <header>Change Password</header>
    <form method="POST" action="/profile/update-password">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
            $csrf_token,
        ); ?>">

        <label for="current_password">
            Current Password
            <input type="password"
                   id="current_password"
                   name="current_password"
                   required
                   <?php if (isset($errors["current_password"])): ?>
                   aria-invalid="true"
                   <?php endif; ?>
                   aria-describedby="<?php echo isset(
                       $errors["current_password"],
                   )
                       ? "current_password-error"
                       : ""; ?>">
            <?php if (isset($errors["current_password"])): ?>
                <small id="current_password-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["current_password"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <label for="new_password">
            New Password
            <input type="password"
                   id="new_password"
                   name="new_password"
                   minlength="12"
                   required
                   <?php if (isset($errors["new_password"])): ?>
                   aria-invalid="true"
                   <?php endif; ?>
                   aria-describedby="<?php echo isset($errors["new_password"])
                       ? "new_password-error"
                       : ""; ?>">
            <?php if (isset($errors["new_password"])): ?>
                <small id="new_password-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["new_password"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <label for="confirm_password">
            Confirm New Password
            <input type="password"
                   id="confirm_password"
                   name="confirm_password"
                   minlength="12"
                   required
                   <?php if (isset($errors["confirm_password"])): ?>
                   aria-invalid="true"
                   <?php endif; ?>
                   aria-describedby="<?php echo isset(
                       $errors["confirm_password"],
                   )
                       ? "confirm_password-error"
                       : ""; ?>">
            <?php if (isset($errors["confirm_password"])): ?>
                <small id="confirm_password-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["confirm_password"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <button type="submit">Update Password</button>
    </form>
</article>
<?php else: ?>
<article>
    <header>Admin Account</header>
    <p>The admin account password can only be changed via the ADMIN_PASSWORD environment variable.</p>
</article>
<?php endif; ?>

<!-- Delete Account -->
<?php if ($user["username"] !== "admin"): ?>
<article class="pico-color-red-500">
    <header>Danger Zone</header>
    <h3>Delete Account</h3>
    <p>Once you delete your account, there is no going back. All your posts will be deleted as well.</p>

    <form method="POST" action="/profile/delete" onsubmit="return confirmDelete()">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
            $csrf_token,
        ); ?>">

        <label for="delete_password">
            Confirm your password to delete account
            <input type="password"
                   id="delete_password"
                   name="password"
                   placeholder="Enter your password"
                   required>
        </label>

        <button type="submit" class="contrast">Delete My Account Permanently</button>
    </form>
</article>
<?php endif; ?>

<script>
function confirmDelete() {
    return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone and all your posts will be deleted.');
}
</script>
