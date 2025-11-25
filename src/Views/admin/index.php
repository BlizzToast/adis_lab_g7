<?php
/**
 * @var int $userCount
 * @var int $postCount
 * @var string $dbSize
 * @var string $dbPath
 * @var array $users
 * @var bool $isLoggedIn
 * @var string|null $username
 * @var string $csrf_token
 * @var array $messages
 */
?>
<h1>Admin Panel</h1>

<div class="grid">
    <article>
        <header>Database Stats</header>
        <p><strong>Total Users:</strong> <?php echo $userCount; ?></p>
        <p><strong>Total Posts:</strong> <?php echo $postCount; ?></p>
        <p><strong>Database Size:</strong> <?php echo $dbSize; ?></p>
    </article>

    <article>
        <header>Admin Actions</header>
        <form method="POST" action="/admin/reset" style="margin-bottom: 1rem;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                $csrf_token,
            ); ?>">
            <button type="submit" class="contrast" style="width: 100%;" onclick="return confirm('Warning: This will delete ALL data and reset with test data. Are you sure?');">
                Reset Database
            </button>
        </form>
        <a href="/info" role="button" class="secondary" style="width: 100%; display: block; text-align: center;">PHP Info</a>
    </article>
</div>

<section>
    <h2>User Management</h2>

    <figure style="overflow-x: auto;">
        <table class="striped">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Avatar</th>
                    <th scope="col">Role</th>
                    <th scope="col">Created</th>
                    <th scope="col" style="min-width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6">No users yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <th scope="row"><?php echo $user["id"]; ?></th>
                            <td><?php echo htmlspecialchars(
                                $user["username"],
                            ); ?></td>
                            <td><?php echo $user["avatar"] ?? "🐧"; ?></td>
                            <td>
                                <?php if ($user["is_admin"] ?? false): ?>
                                    <mark>Admin</mark>
                                <?php else: ?>
                                    <small>User</small>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo $user["created_at"] ??
                                "N/A"; ?></small></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; white-space: nowrap;">
                                    <?php if (
                                        $user["username"] !== $username
                                    ): ?>
                                        <form method="POST" action="/admin/impersonate" >
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                                                $csrf_token,
                                            ); ?>">
                                            <input type="hidden" name="username" value="<?php echo htmlspecialchars(
                                                $user["username"],
                                            ); ?>">
                                            <button type="submit"
                                                    class="outline"
                                                    style="margin: 0; padding: 0.25rem 0.5rem; font-size: 0.875rem;"
                                                    onclick="return confirm('Impersonate user <?php echo htmlspecialchars(
                                                        $user["username"],
                                                    ); ?>?');">
                                                Login as
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($user["username"] === "admin"): ?>
                                        <button disabled class="secondary" style="margin: 0; padding: 0.25rem 0.5rem; font-size: 0.875rem;">Protected</button>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/delete-user" >
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                                                $csrf_token,
                                            ); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user[
                                                "id"
                                            ]; ?>">
                                            <button type="submit"
                                                    class="outline contrast"
                                                    style="margin: 0; padding: 0.25rem 0.5rem; font-size: 0.875rem;"
                                                    onclick="return confirm('Delete user <?php echo htmlspecialchars(
                                                        $user["username"],
                                                    ); ?> and all their posts?');">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </figure>
</section>

<article>
    <header>Admin Information</header>
    <p>Default admin credentials:</p>
    <ul>
        <li>Username: <kbd>admin</kbd></li>
        <li>Password: Set via <kbd>ADMIN_PASSWORD</kbd> environment variable</li>
    </ul>
    <p><small>To change the admin password, set the <code>ADMIN_PASSWORD</code> environment variable.</small></p>
</article>
