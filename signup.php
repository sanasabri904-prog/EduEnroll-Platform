<?php
// signup.php — create new user accounts

require_once 'includes/db.php';
require_once 'includes/auth.php';

startSession();

// if already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'username' => '', 'email' => ''];

  // collect form inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    
    // basic validation
    if ($old['full_name'] === '')
        $errors['full_name'] = 'Full name is required.';
    if ($old['username'] === '')
        $errors['username'] = 'Username is required.';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $old['username']))
        $errors['username'] = '3–30 characters: letters, numbers, underscores only.';
    if ($old['email'] === '')
        $errors['email'] = 'Email is required.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Enter a valid email address.';
    if ($pass === '')
        $errors['password'] = 'Password is required.';
    elseif (strlen($pass) < 6)
        $errors['password'] = 'Password must be at least 6 characters.';
    if ($pass !== $pass2)
        $errors['password2'] = 'Passwords do not match.';

      // if no errors, check duplicates and insert
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$old['email'], $old['username']]);
        if ($stmt->fetch()) {
            $errors['general'] = 'Email or username is already taken.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare('INSERT INTO users (full_name, email, username, password_hash, role) VALUES (?,?,?,?,?)')
                    ->execute([$old['full_name'], $old['email'], $old['username'], $hash, 'user']);
            setFlash('success', 'Account created! You can now log in.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Sign Up — EduEnroll</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: 'Outfit', sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #bfdbfe 0%, #dbeafe 50%, #e0f2fe 100%);
                padding: 2rem;
            }
            .signup-card {
                background: #fff;
                border-radius: 16px;
                padding: 2.5rem;
                width: 100%;
                max-width: 440px;
                box-shadow: 0 8px 40px rgba(59,130,246,.15), 0 2px 8px rgba(59,130,246,.08);
            }
            .logo-area {
                text-align: center;
                margin-bottom: 2rem;
            }
            .logo-icon {
                font-size: 2.5rem;
                display: block;
                margin-bottom: .5rem;
            }
            .logo-area h1 {
                font-size: 1.5rem;
                font-weight: 700;
                color: #2563eb;
            }
            .logo-area p  {
                font-size: .85rem;
                color: #64748b;
                margin-top: .2rem;
            }
            .card-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin-bottom: 1.5rem;
                border-bottom: 1px solid #dbeafe;
                padding-bottom: .75rem;
                color: #1e40af;
            }
            .flash {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .75rem 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                font-size: .88rem;
                font-weight: 500;
            }
            .flash-error {
                background: #fef2f2;
                color: #b91c1c;
                border: 1px solid #fecaca;
            }
            .flash-close {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 1rem;
                color: inherit;
            }
            .field {
                margin-bottom: 1rem;
            }
            .field label {
                display: block;
                font-size: .8rem;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .04em;
                margin-bottom: .35rem;
            }
            .field input {
                width: 100%;
                background: #f0f7ff;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                color: #1e3a5f;
                font-family: 'Outfit', sans-serif;
                font-size: .9rem;
                padding: .6rem .85rem;
                outline: none;
                transition: border-color .2s, box-shadow .2s;
            }
            .field input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59,130,246,.15);
                background: #fff;
            }
            .field-error {
                font-size: .78rem;
                color: #b91c1c;
                margin-top: .3rem;
            }
            .btn-signup {
                width: 100%;
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: #fff;
                border: none;
                border-radius: 8px;
                font-family: 'Outfit', sans-serif;
                font-size: .95rem;
                font-weight: 600;
                padding: .65rem;
                cursor: pointer;
                margin-top: .75rem;
                transition: opacity .2s, transform .1s;
            }
            .btn-signup:hover {
                opacity: .92;
                transform: translateY(-1px);
            }
            .bottom-link {
                text-align: center;
                margin-top: 1.25rem;
                font-size: .85rem;
                color: #64748b;
            }
            .bottom-link a {
                color: #2563eb;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="signup-card">
            <div class="logo-area">
                <span class="logo-icon">◈</span>
                <h1>EduEnroll</h1>
                <p>Course Enrollment System</p>
            </div>
            <div class="card-title">Create Account</div>

<?php if (!empty($errors['general'])): ?>
                <div class="flash flash-error">
    <?= htmlspecialchars($errors['general']) ?>
                    <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
                </div>
                    <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name']) ?>" placeholder="Your full name" required />
<?php if (!empty($errors['full_name'])): ?><div class="field-error"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($old['username']) ?>" placeholder="e.g. john_doe" required />
<?php if (!empty($errors['username'])): ?><div class="field-error"><?= htmlspecialchars($errors['username']) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" placeholder="you@example.com" required />
<?php if (!empty($errors['email'])): ?><div class="field-error"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Minimum 6 characters" required />
<?php if (!empty($errors['password'])): ?><div class="field-error"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" name="password2" placeholder="Repeat your password" required />
<?php if (!empty($errors['password2'])): ?><div class="field-error"><?= htmlspecialchars($errors['password2']) ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn-signup">Create Account →</button>
            </form>

            <p class="bottom-link">Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </body>
</html>