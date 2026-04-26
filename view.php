<?php 
include 'db.php'; 
session_start();

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the current user's ID
$user_id = $_SESSION['user_id'];

if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    $stmt = $conn->prepare("DELETE FROM Expenses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: view.php");
    exit();
}

// FIX: Added "WHERE E.user_id = ?" to filter the data
$sql = "SELECT E.*, C.category_name FROM Expenses E 
        JOIN Categories C ON E.category_id = C.category_id 
        WHERE E.user_id = ? 
        ORDER BY date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinLog | History</title>
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

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(1px 1px at 8% 12%, rgba(255,255,255,0.55) 0%, transparent 100%),
                radial-gradient(1px 1px at 22% 38%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 48% 8%, rgba(255,255,255,0.45) 0%, transparent 100%),
                radial-gradient(1px 1px at 72% 22%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 88% 50%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 18% 68%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 58% 78%, rgba(255,255,255,0.35) 0%, transparent 100%),
                radial-gradient(1px 1px at 92% 18%, rgba(255,255,255,0.3) 0%, transparent 100%);
            pointer-events: none;
            z-index: 0;
        }

        .page { position: relative; z-index: 1; max-width: 560px; margin: 0 auto; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 0 32px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #e8edf8;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(79,142,247,0.1);
            border: 1px solid rgba(79,142,247,0.28);
            color: #4f8ef7;
            font-family: 'Outfit', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 20px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .logout-btn:hover { background: rgba(79,142,247,0.2); }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #7a8db3;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            transition: color 0.2s;
        }
        .back-link:hover { color: #4f8ef7; }

        .section-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #7a8db3;
            margin-bottom: 6px;
        }

        h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.55rem;
            font-weight: 700;
            color: #e8edf8;
            margin-bottom: 22px;
        }

        .card {
            background: rgba(17, 24, 39, 0.85);
            border: 1px solid rgba(79,142,247,0.2);
            border-radius: 16px;
            padding: 28px 28px 24px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(8px);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(79,142,247,0.55), transparent);
        }

        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #7a8db3;
            font-size: 0.9rem;
        }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            text-align: left;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #7a8db3;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(79,142,247,0.15);
        }

        tbody td {
            padding: 14px 0;
            border-bottom: 1px solid rgba(79,142,247,0.07);
            font-size: 0.88rem;
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }

        .date-cell { color: #7a8db3; font-size: 0.82rem; }

        .cat-badge {
            background: rgba(79,142,247,0.1);
            color: #4f8ef7;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .amount-cell {
            text-align: right;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: #4f8ef7;
        }

        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }

        .nav-links a {
            color: #4f8ef7;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 16px;
            border: 1px solid rgba(79,142,247,0.25);
            border-radius: 20px;
            transition: background 0.2s;
        }
        .nav-links a:hover { background: rgba(79,142,247,0.12); }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #ff6b6b;
            background: rgba(255,107,107,0.08);
            border: 1px solid rgba(255,107,107,0.25);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-danger:hover { background: rgba(255,107,107,0.16); }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <div class="logo">💳 FinLog</div>
        <a href="logout.php" class="logout-btn">Logout ✦</a>
    </div>

    <a href="index.php" class="back-link">← Back</a>
    <p class="section-label">View History</p>
    <h2> Expense History </h2>

    <div class="card">
        <?php if($res->num_rows === 0): ?>
            <div class="empty-state">No expenses recorded yet.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td class="date-cell"><?= date('M d, Y', strtotime($row['date'])) ?></td>
                    <td><span class="cat-badge"><?= htmlspecialchars($row['category_name']) ?></span></td>
                    <td class="amount-cell">₹<?= number_format($row['amount'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <nav class="nav-links">
        <a href="index.php">← Add New</a>
        <a href="summary.php">Summary Report</a>
    </nav>

    <a href="view.php?action=reset"
       onclick="return confirm('Clear all expense history permanently?')"
       class="btn-danger">
        🗑 Clear All History
    </a>
</div>
</body>
</html>
