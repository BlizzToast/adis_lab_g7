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
        <form id="post-form">
            <textarea
                name="content"
                id="postInput"
                placeholder="Something to roar about?"
                rows="4"
                maxlength="280"
                required></textarea>

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

    <!-- Posts will be dynamically loaded here via API -->
    <div id="feed-container" data-current-user="<?php echo $isLoggedIn ? htmlspecialchars(
        $username,
    ) : ""; ?>">
        <article aria-busy="true">
            <p>Loading roars...</p>
        </article>
    </div>
</section>
