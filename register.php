<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // 1. Basic Validation (Email removed from check)
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // 2. Insert directly (Removed the 'username already exists' check)
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Removed email from the INSERT statement
        $stmt = $conn->prepare("INSERT INTO Users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed);
        
        if ($stmt->execute()) {
            $success = "Account created! You can now log in.";
        } else {
            // If this triggers, your database likely has a UNIQUE rule on the username column
            $error = "Database error. This username might already be in use.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinLog | Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { height: 100%; background: #0b0f1e; }

        body {
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f1e;
            background-image:
                radial-gradient(ellipse 100% 60% at 50% 0%, rgba(30,70,160,0.55) 0%, transparent 65%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(20,50,120,0.25) 0%, transparent 60%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            color: #e8edf8;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(1px 1px at 10% 15%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(1px 1px at 25% 35%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 45% 10%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 70% 25%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 85% 55%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 15% 65%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 55% 75%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 90% 15%, rgba(255,255,255,0.35) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 35% 85%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 60% 45%, rgba(255,255,255,0.25) 0%, transparent 100%);
            pointer-events: none;
            z-index: 0;
        }

        .register-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(17, 24, 39, 0.88);
            border: 1px solid rgba(79,142,247,0.25);
            border-radius: 20px;
            padding: 44px 40px 38px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow:
                0 0 0 1px rgba(79,142,247,0.08),
                0 20px 60px rgba(0,0,0,0.6),
                0 0 80px rgba(30,80,200,0.12);
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(79,142,247,0.7), transparent);
            border-radius: 50%;
        }

        .logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .logo-star { font-size: 26px; line-height: 1; }

        .logo-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #e8edf8;
            letter-spacing: -0.5px;
        }

        .card-sub {
            font-size: 0.85rem;
            color: #7a8db3;
            margin-bottom: 30px;
        }

        .field-block { margin-bottom: 22px; }

        .field-label {
            display: block;
            font-size: 0.78rem;
            color: #7a8db3;
            margin-bottom: 9px;
            letter-spacing: 0.3px;
        }

        .field-input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(79,142,247,0.3);
            padding: 8px 0 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            color: #e8edf8;
            outline: none;
            transition: border-color 0.2s;
        }

        .field-input::placeholder { color: rgba(122,141,179,0.5); }
        .field-input:focus { border-bottom-color: #4f8ef7; }

        .register-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #2d5abf 0%, #4f8ef7 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 6px;
            box-shadow: 0 4px 20px rgba(79,142,247,0.38);
            transition: opacity 0.2s, transform 0.15s;
        }

        .register-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        .error-msg {
            margin-top: 16px;
            padding: 10px 14px;
            background: rgba(255,90,90,0.1);
            border: 1px solid rgba(255,90,90,0.25);
            border-radius: 8px;
            color: #ff7070;
            font-size: 0.82rem;
        }

        .success-msg {
            margin-top: 16px;
            padding: 10px 14px;
            background: rgba(74,222,128,0.1);
            border: 1px solid rgba(74,222,128,0.25);
            border-radius: 8px;
            color: #4ade80;
            font-size: 0.82rem;
        }

        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 0.83rem;
            color: #7a8db3;
        }

        .login-link a {
            color: #4f8ef7;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .login-link a:hover { opacity: 0.8; }

        .strength-wrap { margin-top: 8px; height: 3px; background: rgba(79,142,247,0.1); border-radius: 2px; overflow: hidden; }
        .strength-bar  { height: 100%; border-radius: 2px; width: 0; transition: width 0.3s, background 0.3s; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo-row">
            <span class="logo-star">💳</span>
            <span class="logo-name">FinLog</span>
        </div>
        <p class="card-sub">Create your account to get started</p>

        <?php if($success): ?>
            <div class="success-msg">✓ <?= htmlspecialchars($success) ?></div>
            <p class="login-link" style="margin-top:18px;">
                <a href="login.php">← Back to Login</a>
            </p>
        <?php else: ?>
        <form method="POST" autocomplete="off" id="regForm">
            <div class="field-block">
                <label class="field-label">Username</label>
                <input class="field-input" type="text" name="username"
                       placeholder="Choose a username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="field-block">
                <label class="field-label">Password</label>
                <input class="field-input" type="password" name="password"
                       placeholder="Min. 6 characters" required id="pwdInput">
                <div class="strength-wrap"><div class="strength-bar" id="strengthBar"></div></div>
            </div>

            <div class="field-block">
                <label class="field-label">Confirm Password</label>
                <input class="field-input" type="password" name="confirm"
                       placeholder="Repeat your password" required>
            </div>

            <button class="register-btn" type="submit">Create Account ✦</button>
        </form>

        <?php if($error): ?>
            <div class="error-msg">✕ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
        <?php endif; ?>
    </div>

    <script>
        const pwd = document.getElementById('pwdInput');
        const bar = document.getElementById('strengthBar');
        if (pwd) {
            pwd.addEventListener('input', () => {
                const v = pwd.value;
                let score = 0;
                if (v.length >= 6)  score++;
                if (v.length >= 10) score++;
                if (/[A-Z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                const colors = ['#ff5a5a','#ff9f43','#ffd32a','#4ade80','#4f8ef7'];
                const widths  = ['20%','40%','60%','80%','100%'];
                bar.style.width  = v.length ? widths[score - 1] || '20%' : '0';
                bar.style.background = v.length ? colors[score - 1] || '#ff5a5a' : 'transparent';
            });
        }
    </script>
</body>
</html>