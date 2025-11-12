<?php
declare(strict_types=1);

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Include UserManager class (which extends UserAuth)
require_once __DIR__ . '/../lib/auth/UserManager.php';

// ========================================
// INITIALIZE DATABASE AND USER MANAGER
// ========================================

$message = '';
$messageType = '';
$users = [];
$dbConnected = false;
$userManager = null;
$stats = [];
$dbPath = __DIR__ . '/../data/users_debug.db';

try {
    $userManager = new UserManager($dbPath);
    $dbConnected = true;
    $users = $userManager->getAllUsers();
    $stats = $userManager->getStats();
    
    // Debug output
    error_log("Debug: Database connected successfully");
    error_log("Debug: Number of users: " . count($users));
    error_log("Debug: Users data: " . json_encode($users));
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
    error_log("General error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $dbConnected) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($userManager->register($username, $password)) {
                $message = "User registered successfully";
                $messageType = 'success';
            } else {
                $message = "Registration failed (check username/password requirements)";
                $messageType = 'error';
            }
            
            $users = $userManager->getAllUsers();
            $stats = $userManager->getStats();
            break;

        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($userManager->login($username, $password)) {
                $_SESSION['debug_username'] = $username;
                $message = "Login successful - Welcome, {$username}!";
                $messageType = 'success';
            } else {
                $message = "Login failed (invalid username or password)";
                $messageType = 'error';
            }
            
            $users = $userManager->getAllUsers();
            break;

        case 'delete_user':
            $userId = (int)($_POST['user_id'] ?? 0);
            
            if ($userManager->deleteUser($userId)) {
                $message = "User ID {$userId} deleted successfully";
                $messageType = 'success';
            } else {
                $message = "Failed to delete user ID {$userId}";
                $messageType = 'error';
            }
            
            $users = $userManager->getAllUsers();
            $stats = $userManager->getStats();
            break;

        case 'delete_all':
            if ($userManager->deleteAllUsers()) {
                $message = "All users deleted successfully";
                $messageType = 'success';
                unset($_SESSION['debug_username']);
            } else {
                $message = "Failed to delete all users";
                $messageType = 'error';
            }
            
            $users = $userManager->getAllUsers();
            $stats = $userManager->getStats();
            break;

        case 'logout':
            unset($_SESSION['debug_username']);
            $message = "Logged out successfully";
            $messageType = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Interface - User Management Testing</title>
    <link rel="stylesheet" href="https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css">
    <style>
        .password-hash-cell {
            word-break: break-all;
            white-space: pre-wrap;
        }
        .debug-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid var(--font-color);
            border-radius: 4px;
        }
        .debug-section h2 {
            margin-top: 0;
            border-bottom: 2px solid var(--font-color);
            padding-bottom: 10px;
        }
        .user-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        .user-table th, .user-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid var(--font-color);
        }
        .user-table th {
            background-color: rgba(255, 255, 255, 0.1);
            font-weight: bold;
        }
        .user-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .inline-form {
            display: inline-block;
            margin: 0;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-online {
            background-color: #4caf50;
            color: white;
        }
        .status-offline {
            background-color: #666;
            color: white;
        }
        .quick-action-btns {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            max-width: 600px;
        }
        .info-grid dt {
            font-weight: bold;
        }
        .info-grid dd {
            margin: 0;
        }
        pre {
            background-color: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
        .btn-small {
            padding: 4px 8px;
            font-size: 0.85em;
        }
        .warning-box {
            background-color: rgba(255, 193, 7, 0.1);
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="terminal-nav">
            <div class="terminal-logo">
                <div class="logo terminal-prompt"><a href="/index" class="no-style">Roary Debug</a></div>
            </div>
            <nav class="terminal-menu">
                <ul>
                    <li><a class="menu-item" href="/index">Home</a></li>
                    <li><a class="menu-item" href="/login">Login</a></li>
                    <li><a class="menu-item" href="/register">Register</a></li>
                    <li><a class="menu-item active" href="/admin/debug">Debug</a></li>
                </ul>
            </nav>
        </div>

        <main>
            <h1>🔧 User Management Debug Interface</h1>
            
            <?php if ($message): ?>
                <div class="terminal-alert terminal-alert-<?php echo $messageType === 'success' ? 'primary' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Database Status -->
            <div class="debug-section">
                <h2>📊 Database Status</h2>
                <dl class="info-grid">
                    <dt>🗄️ Database File:</dt>
                    <dd><code><?php echo htmlspecialchars($dbPath); ?></code></dd>

                    <dt>📁 Database Exists:</dt>
                    <dd><?php echo file_exists($dbPath) ? '✅ Yes' : '❌ No'; ?></dd>

                    <dt>🔌 Connection Status:</dt>
                    <dd><?php echo $dbConnected ? '<span class="status-badge status-online">Connected</span>' : '<span class="status-badge status-offline">Disconnected</span>'; ?></dd>

                    <?php if ($dbConnected): ?>
                        <dt>📦 Database Size:</dt>
                        <dd><?php echo file_exists($dbPath) ? number_format(filesize($dbPath) / 1024, 2) . ' KB' : 'N/A'; ?></dd>

                        <dt>📝 Last Modified:</dt>
                        <dd><?php echo file_exists($dbPath) ? date('Y-m-d H:i:s', filemtime($dbPath)) : 'N/A'; ?></dd>

                        <dt>👥 Total Users:</dt>
                        <dd><?php echo $stats['total_users']; ?></dd>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Session Status -->
            <div class="debug-section">
                <h2>👤 Session Status</h2>
                <?php if (isset($_SESSION['debug_username'])): ?>
                    <p>✅ Logged in as: <strong><?php echo htmlspecialchars($_SESSION['debug_username']); ?></strong></p>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-error btn-small">Logout</button>
                    </form>
                <?php else: ?>
                    <p>❌ Not logged in</p>
                <?php endif; ?>
            </div>

            <?php if ($dbConnected): ?>
                <!-- Quick Actions -->
                <div class="debug-section">
                    <h2>⚡ Quick Test Actions</h2>
                    <p>Use these buttons to quickly fill forms with test data:</p>
                    <div class="quick-action-btns">
                        <button class="btn btn-primary" onclick="fillTestRegister()">Fill Test Registration</button>
                        <button class="btn btn-primary" onclick="fillTestLogin()">Fill Test Login</button>
                        <button class="btn btn-default" onclick="clearForms()">Clear All Forms</button>
                    </div>
                </div>

                <!-- Register User -->
                <div class="debug-section">
                    <h2>📝 Register New User</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="register">
                        <fieldset>
                            <legend>Test user registration</legend>
                            
                            <label for="reg_username">Username (alphanumeric, 3-50 chars)</label>
                            <input type="text" 
                                   id="reg_username" 
                                   name="username" 
                                   required 
                                   pattern="^[a-zA-Z0-9]+$" 
                                   minlength="3"
                                   maxlength="50"
                                   placeholder="testuser123">
                            
                            <label for="reg_password">Password (minimum 12 characters)</label>
                            <input type="text" 
                                   id="reg_password" 
                                   name="password" 
                                   required 
                                   minlength="12" 
                                   placeholder="testpassword123">
                            <small>💡 Using text input for easier debugging (normally would be password type)</small>
                            
                            <button type="submit" class="btn btn-primary">Register User</button>
                        </fieldset>
                    </form>
                </div>

                <!-- Login User -->
                <div class="debug-section">
                    <h2>🔐 Test Login</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="login">
                        <fieldset>
                            <legend>Test user authentication</legend>
                            
                            <label for="login_username">Username</label>
                            <input type="text" 
                                   id="login_username" 
                                   name="username" 
                                   required 
                                   placeholder="testuser123">
                            
                            <label for="login_password">Password</label>
                            <input type="text" 
                                   id="login_password" 
                                   name="password" 
                                   required 
                                   placeholder="testpassword123">
                            <small>💡 Using text input for easier debugging</small>
                            
                            <button type="submit" class="btn btn-primary">Test Login</button>
                        </fieldset>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="debug-section">
                    <h2>👥 All Users in Database!</h2>
                    <?php if (count($users) > 0): ?>
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Password Hash</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo (int)$user['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars((string)$user['username']); ?></strong></td>
                                        <td class="password-hash-cell"><?php echo htmlspecialchars((string)($user['password_hash'] ?? 'N/A')); ?></td>
                                        <td>
                                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete user <?php echo htmlspecialchars((string)$user['username']); ?>?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                <button type="submit" class="btn btn-error btn-small">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="margin-top: 15px;">
                            <form method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete ALL users? This cannot be undone!');">
                                <input type="hidden" name="action" value="delete_all">
                                <button type="submit" class="btn btn-error">Delete All Users</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p>No users in database yet. Register a user above to get started!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function fillTestRegister() {
            const timestamp = Date.now();
            document.getElementById('reg_username').value = 'testuser' + timestamp;
            document.getElementById('reg_password').value = 'testpassword123';
            
            // Scroll to registration form
            document.getElementById('reg_username').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function fillTestLogin() {
            // Try to fill with the first user from the table if available
            const firstUsername = document.querySelector('.user-table tbody tr td:nth-child(2)');
            if (firstUsername) {
                document.getElementById('login_username').value = firstUsername.textContent.trim();
                document.getElementById('login_password').value = 'testpassword123';
            } else {
                // Fallback to generic test credentials
                document.getElementById('login_username').value = 'testuser123';
                document.getElementById('login_password').value = 'testpassword123';
                alert('No users found in database. The form has been filled with default test credentials, but you need to register a user first.');
            }
            
            // Scroll to login form
            document.getElementById('login_username').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function clearForms() {
            document.getElementById('reg_username').value = '';
            document.getElementById('reg_password').value = '';
            document.getElementById('login_username').value = '';
            document.getElementById('login_password').value = '';
        }

        // Highlight newly added users
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.user-table tbody tr');
            if (rows.length > 0 && window.location.search.includes('registered')) {
                rows[rows.length - 1].style.backgroundColor = 'rgba(76, 175, 80, 0.2)';
            }
        });
    </script>
</body>
</html>
