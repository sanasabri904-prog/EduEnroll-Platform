<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

startSession(); // start session for login tracking

// if already logged in, go straight to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$old_username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($old_username === '' || $pass === '') {
        $error = 'Please enter your username and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$old_username, $old_username]);
        $user = $stmt->fetch();

         // verify password against hash
        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Login — EduEnroll</title>
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
            .login-card {
                background: #fff;
                border-radius: 16px;
                padding: 2.5rem;
                width: 100%;
                max-width: 420px;
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
            .flash-success {
                background: #f0fdf4;
                color: #15803d;
                border: 1px solid #bbf7d0;
            }
            .flash-error   {
                background: #fef2f2;
                color: #b91c1c;
                border: 1px solid #fecaca;
            }
            .flash-close   {
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
            .btn-login {
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
            .btn-login:hover {
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
        <div class="login-card">
            <div class="logo-area">
                <span class="logo-icon">◈</span>
                <h1>EduEnroll</h1>
                <p>Course Enrollment System</p>
            </div>
            <div class="card-title">Sign In</div>

            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['msg']) ?>
                    <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="flash flash-error">
                    <?= htmlspecialchars($error) ?>
                    <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Username or Email</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($old_username) ?>" placeholder="Enter your username or email" required autofocus />
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Your password" required />
                </div>
                <button type="submit" class="btn-login">Log In →</button>
            </form>

            <p class="bottom-link">No account yet? <a href="signup.php">Sign up</a></p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var flash = document.querySelector('.flash');
                if (flash) {
                    setTimeout(function () {
                        flash.style.transition = 'opacity .4s';
                        flash.style.opacity = '0';
                        setTimeout(function () {
                            flash.remove();
                        }, 400);
                    }, 4000);
                }
            });
        </script>
    </body>
</html>