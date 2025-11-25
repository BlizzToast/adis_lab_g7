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
        <h1>Register</h1>
    </header>

    <form id="registerForm" method="POST" action="/register">
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

        <label for="confirmPassword">
            Confirm Password
            <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                required
                minlength="12"
                <?php if (isset($errors["confirmPassword"])): ?>
                aria-invalid="true"
                <?php endif; ?>
                aria-describedby="<?php echo isset($errors["confirmPassword"])
                    ? "confirmPassword-error"
                    : ""; ?>">
            <?php if (isset($errors["confirmPassword"])): ?>
                <small id="confirmPassword-error" class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["confirmPassword"]); ?>
                </small>
            <?php endif; ?>
        </label>

        <label for="avatar">
            Choose your avatar (optional)
            <select name="avatar" id="avatar">
                <option value="🐧" <?php echo ($old["avatar"] ?? "🐧") === "🐧"
                    ? "selected"
                    : ""; ?>>🐧 Penguin (default)</option>
                <option value="🐸" <?php echo ($old["avatar"] ?? "") === "🐸"
                    ? "selected"
                    : ""; ?>>🐸 Frog</option>
                <option value="🐢" <?php echo ($old["avatar"] ?? "") === "🐢"
                    ? "selected"
                    : ""; ?>>🐢 Turtle</option>
                <option value="🐍" <?php echo ($old["avatar"] ?? "") === "🐍"
                    ? "selected"
                    : ""; ?>>🐍 Snake</option>
                <option value="🦖" <?php echo ($old["avatar"] ?? "") === "🦖"
                    ? "selected"
                    : ""; ?>>🦖 T-Rex</option>
                <option value="🦕" <?php echo ($old["avatar"] ?? "") === "🦕"
                    ? "selected"
                    : ""; ?>>🦕 Dinosaur</option>
                <option value="🐶" <?php echo ($old["avatar"] ?? "") === "🐶"
                    ? "selected"
                    : ""; ?>>🐶 Dog</option>
                <option value="🐱" <?php echo ($old["avatar"] ?? "") === "🐱"
                    ? "selected"
                    : ""; ?>>🐱 Cat</option>
                <option value="🦋" <?php echo ($old["avatar"] ?? "") === "🦋"
                    ? "selected"
                    : ""; ?>>🦋 Butterfly</option>
                <option value="🦁" <?php echo ($old["avatar"] ?? "") === "🦁"
                    ? "selected"
                    : ""; ?>>🦁 Lion</option>
                <option value="🐯" <?php echo ($old["avatar"] ?? "") === "🐯"
                    ? "selected"
                    : ""; ?>>🐯 Tiger</option>
                <option value="🐻" <?php echo ($old["avatar"] ?? "") === "🐻"
                    ? "selected"
                    : ""; ?>>🐻 Bear</option>
                <option value="🦊" <?php echo ($old["avatar"] ?? "") === "🦊"
                    ? "selected"
                    : ""; ?>>🦊 Fox</option>
                <option value="🦝" <?php echo ($old["avatar"] ?? "") === "🦝"
                    ? "selected"
                    : ""; ?>>🦝 Raccoon</option>
                <option value="🐨" <?php echo ($old["avatar"] ?? "") === "🐨"
                    ? "selected"
                    : ""; ?>>🐨 Koala</option>
                <option value="🐼" <?php echo ($old["avatar"] ?? "") === "🐼"
                    ? "selected"
                    : ""; ?>>🐼 Panda</option>
                <option value="🦘" <?php echo ($old["avatar"] ?? "") === "🦘"
                    ? "selected"
                    : ""; ?>>🦘 Kangaroo</option>
                <option value="🦜" <?php echo ($old["avatar"] ?? "") === "🦜"
                    ? "selected"
                    : ""; ?>>🦜 Parrot</option>
                <option value="🦅" <?php echo ($old["avatar"] ?? "") === "🦅"
                    ? "selected"
                    : ""; ?>>🦅 Eagle</option>
                <option value="🦉" <?php echo ($old["avatar"] ?? "") === "🦉"
                    ? "selected"
                    : ""; ?>>🦉 Owl</option>
            </select>
        </label>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
            $csrf_token,
        ); ?>">

        <button type="submit">Register</button>
    </form>

    <footer>
        <p>Already have an account? <a href="/login">Login here</a></p>
    </footer>
</article>
