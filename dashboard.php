<?php
session_start();
include "config.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get user's order count
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total_orders
     FROM orders
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$order_data = $result->fetch_assoc();

$total_orders = $order_data["total_orders"];

$stmt->close();


// Get user's total spending
$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(total_amount), 0) AS total_spent
     FROM orders
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$spent_data = $result->fetch_assoc();

$total_spent = $spent_data["total_spent"];

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Dashboard</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header>

    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>


<div class="dashboard">

    <h2>
        Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>! 👋
    </h2>

    <p>
        Email:
        <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
    </p>


    <div class="dashboard-cards">

        <div class="card">

            <h3>📦 Total Orders</h3>

            <p>
                <?php echo $total_orders; ?>
            </p>

        </div>


        <div class="card">

            <h3>💰 Total Spent</h3>

            <p>
                ₹<?php echo number_format($total_spent, 2); ?>
            </p>

        </div>


        <div class="card">

            <h3>👤 Account</h3>

            <p>
                <?php echo ucfirst($_SESSION["role_name"]); ?>
            </p>

        </div>

    </div>


    <div class="dashboard-links">

        <a href="products.php" class="btn">
            📖 Browse Books
        </a>

        <a href="profile.php" class="btn">
            👤 My Profile
        </a>

        <a href="orders.php" class="btn">
            📦 My Orders
        </a>

    </div>

</div>


</body>

</html>
