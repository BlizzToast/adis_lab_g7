<?php
/**
 * @var bool $isLoggedIn
 * @var string|null $username
 * @var array $posts
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 */
?>

<?php if ($isLoggedIn): ?>
    <!-- Post Form -->
    <article>
        <header>Share your thoughts</header>
        <form method="POST" action="/create-post">
            <?php if (isset($errors["content"])): ?>
                <small class="pico-color-red-500">
                    <?php echo htmlspecialchars($errors["content"]); ?>
                </small>
            <?php endif; ?>

            <textarea
                name="content"
                id="postInput"
                placeholder="Something to roar about?"
                rows="4"
                <?php if (isset($errors["content"])): ?>
                aria-invalid="true"
                <?php endif; ?>
                required><?php echo htmlspecialchars(
                    $old["content"] ?? "",
                ); ?></textarea>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                $csrf_token,
            ); ?>">

            <button type="submit">ROAR IT!</button>
        </form>
    </article>

<?php else: ?>
    <article>
        <p><a href="/login">Login</a> or <a href="/register">register</a> to share your thoughts!</p>
    </article>
<?php endif; ?>

<section>
    <h2>Feed</h2>

    <?php if (empty($posts)): ?>
        <article>
            <p>No roars yet. Be the first to roar!</p>
        </article>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article>
                <header style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <hgroup >
                        <p >
                            <span aria-label="Avatar"><?php echo htmlspecialchars(
                                $post["avatar"] ?? "🐧",
                            ); ?></span>
                            <strong><?php echo htmlspecialchars(
                                $post["username"],
                            ); ?></strong>
                        </p>
                        <p ><small><?php echo htmlspecialchars(
                            $post["created_at_relative"] ?? "",
                        ); ?></small></p>
                    </hgroup>
                    <?php if (
                        $isLoggedIn &&
                        $post["username"] === $username
                    ): ?>
                        <form method="POST" action="/post/<?php echo $post[
                            "id"
                        ]; ?>/delete" >
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                                $csrf_token,
                            ); ?>">
                            <button type="submit" class="outline" onclick="return confirm('Are you sure you want to delete this roar?');" aria-label="Delete post">🗑️</button>
                        </form>
                    <?php endif; ?>
                </header>

                <p><?php echo htmlspecialchars($post["content"]); ?></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
