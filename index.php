<?php
include 'db.php';
session_start();

// 1. PROTECTION: Ensure user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = '';

// 2. SAVE LOGIC: Record the expense for the SPECIFIC user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $amount = $_POST['amount'];
    $cat_id = $_POST['category_id'];
    $date = $_POST['date'];

    if (!empty($amount) && !empty($cat_id) && !empty($date)) {
        // We include user_id so it doesn't show up in everyone's account
        $stmt = $conn->prepare("INSERT INTO Expenses (amount, category_id, date, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("disi", $amount, $cat_id, $date, $user_id);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Database Error: " . $stmt->error;
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// 3. DATA FETCHING: Get categories to show in the dropdown
$res_cats = $conn->query("SELECT * FROM Categories");
$categories = [];
while($r = $res_cats->fetch_assoc()) {
    $categories[] = $r;
}

// 4. TOTAL: Calculate total only for THIS user
$total_res = $conn->prepare("SELECT SUM(amount) AS total FROM Expenses WHERE user_id = ?");
$total_res->bind_param("i", $user_id);
$total_res->execute();
$total_row = $total_res->get_result()->fetch_assoc();
$total_spent = $total_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinLog | Record Expense</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; background: #0b0f1e; }
        body {
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f1e;
            background-image:
                radial-gradient(ellipse 100% 50% at 50% 0%, rgba(30,70,160,0.45) 0%, transparent 65%),
                radial-gradient(ellipse 50% 40% at 90% 90%, rgba(20,50,120,0.2) 0%, transparent 60%);
            color: #e8edf8;
            padding: 0 20px 60px;
        }
        .page { position: relative; z-index: 1; max-width: 560px; margin: 0 auto; }
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 28px 0 32px; }
        .logo { font-family: 'Space Grotesk', sans-serif; font-size: 1.1rem; font-weight: 700; color: #e8edf8; }
        .logout-btn { background: rgba(79,142,247,0.1); border: 1px solid rgba(79,142,247,0.28); color: #4f8ef7; padding: 7px 14px; border-radius: 20px; text-decoration: none; font-size: 0.78rem; font-weight: 600; }
        .section-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2.5px; color: #7a8db3; margin-bottom: 6px; }
        h2 { font-family: 'Space Grotesk', sans-serif; font-size: 1.55rem; font-weight: 700; margin-bottom: 22px; }
        .card { background: rgba(17, 24, 39, 0.85); border: 1px solid rgba(79,142,247,0.2); border-radius: 16px; padding: 28px; margin-bottom: 20px; backdrop-filter: blur(8px); }
        .field-label { display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; color: #7a8db3; margin-bottom: 8px; }
        
        /* CHANGED: Added relative position and ensured the calendar icon is visible */
        input, select { 
            width: 100%; 
            padding: 11px 14px; 
            background: rgba(22,29,48,0.9); 
            border: 1px solid rgba(79,142,247,0.22); 
            border-radius: 10px; 
            color: #e8edf8; 
            font-family: 'Outfit', sans-serif; 
            margin-bottom: 18px; 
            outline: none; 
            position: relative;
        }

        /* NEW: This makes the date picker icon white and clickable */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        .save-btn { width: 100%; background: linear-gradient(135deg, #2d5abf 0%, #4f8ef7 100%); color: white; border: none; border-radius: 10px; padding: 13px; font-family: 'Space Grotesk', sans-serif; font-weight: 700; cursor: pointer; box-shadow: 0 4px 20px rgba(79,142,247,0.35); }
        .success-msg { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25); color: #4ade80; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; }
        .error-msg { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; }
        .pills-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .pill { background: rgba(79,142,247,0.1); border: 1px solid rgba(79,142,247,0.22); color: #4f8ef7; border-radius: 20px; padding: 5px 13px; font-size: 0.8rem; }
        .nav-links { display: flex; gap: 10px; }
        .nav-links a { color: #4f8ef7; text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 8px 16px; border: 1px solid rgba(79,142,247,0.25); border-radius: 20px; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <div class="logo">💳 FinLog</div>
        <a href="logout.php" class="logout-btn">Logout ✦</a>
    </div>

    <?php if($success): ?>
        <div class="success-msg">✓ Expense saved successfully! (Total: ₹<?= number_format($total_spent, 2) ?>)</div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="error-msg">✕ <?= $error ?></div>
    <?php endif; ?>

    <p class="section-label">Daily Management</p>
    <h2> Record Expense </h2>

    <div class="card">
        <form method="POST" id="expenseForm">
            <label class="field-label">Amount (₹)</label>
            <input type="number" step="0.01" name="amount" placeholder="₹1,500.00" required>

            <label class="field-label">Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php if(!empty($categories)): ?>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label class="field-label">Date</label>
            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required onclick="this.showPicker()">

            <button type="submit" name="save" class="save-btn">Save ✦</button>
        </form>
    </div>

    <?php if(!empty($categories)): ?>
    <p class="section-label" style="margin-bottom:10px;">Available Categories</p>
    <div class="pills-row">
        <?php foreach($categories as $cat): ?>
            <span class="pill"><?= htmlspecialchars($cat['category_name']) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

     <nav class="nav-links">
        <a href="view.php">View History</a>
        <a href="summary.php">Summary</a>
    </nav>
</div>
</body>
</html>
</html>