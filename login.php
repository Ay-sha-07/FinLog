<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    // Query to find the user
    $result = mysqli_query($conn, "SELECT * FROM Users WHERE username='$user'");
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($pass, $row['password'])) {
        // --- THE FIX IS HERE ---
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $row['id'];       // Store the Unique ID from the database
        $_SESSION['username']  = $row['username']; // Store the name for the dashboard
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid credentials. Access denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinLog | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            height: 100%;
            background: #0b0f1e;
        }

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

        /* Stars */
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

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(17, 24, 39, 0.85);
            border: 1px solid rgba(79,142,247,0.25);
            border-radius: 20px;
            padding: 48px 40px 40px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow:
                0 0 0 1px rgba(79,142,247,0.08),
                0 20px 60px rgba(0,0,0,0.6),
                0 0 80px rgba(30,80,200,0.12);
        }

        /* top shimmer line */
        .login-card::before {
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
            margin-bottom: 10px;
        }

        .logo-star {
            font-size: 28px;
            line-height: 1;
        }

        .logo-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #e8edf8;
            letter-spacing: -0.5px;
        }

        .login-sub {
            font-size: 0.88rem;
            color: #7a8db3;
            margin-bottom: 36px;
        }

        .field-block {
            margin-bottom: 28px;
        }

        .field-label {
            display: block;
            font-size: 0.82rem;
            color: #7a8db3;
            margin-bottom: 10px;
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

       .login-btn {
            /* Changed display to flex so it can behave like a block for margin auto */
            display: flex; 
            justify-content: center; /* Centers the text and star inside the button */
            
            /* This centers the button itself horizontally */
            margin: 20px auto 0; 
            align-items: center;
            gap: 10px;
            background: #1a1f35;
            border: 1px solid rgba(79,142,247,0.35);
            color: #e8edf8;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 15px 32px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
        }

        .login-btn:hover {
            background: rgba(79,142,247,0.18);
            box-shadow: 0 4px 24px rgba(79,142,247,0.3);
            transform: translateY(-1px);
        }

        .error-msg {
            margin-top: 18px;
            padding: 10px 14px;
            background: rgba(255,90,90,0.1);
            border: 1px solid rgba(255,90,90,0.25);
            border-radius: 8px;
            color: #ff7070;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-row">
            <span class="logo-star">💳</span>
            <span class="logo-name">FinLog</span>
        </div>
        <p class="login-sub">Welcome to FinLog Expense Tracker</p>

        <form method="POST" autocomplete="off">
            <div class="field-block">
                <label class="field-label">Email / Username</label>
                <input class="field-input" type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="field-block">
                <label class="field-label">Password</label>
                <input class="field-input" type="password" name="password" placeholder="••••••••" required>
            </div>
            <button class="login-btn" type="submit" >Login ✦</button>
        </form>

        <?php if(isset($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p style="text-align:center; margin-top:22px; font-size:0.83rem; color:#7a8db3;">
            Don't have an account?
            <a href="register.php" style="color:#4f8ef7; text-decoration:none; font-weight:600;">Create Account</a>
        </p>
    </div>
</body>
</html>
