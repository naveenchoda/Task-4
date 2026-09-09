<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Books</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <h2>🛒 My Orders</h2>

    <?php if ($result->num_rows > 0): ?>

        <table>

            <tr>
                <th>Order ID</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Order Date</th>
            </tr>

            <?php while ($order = $result->fetch_assoc()): ?>

                <tr>
                    <td>#<?php echo $order['id']; ?></td>

                    <td>
                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($order['status']); ?>
                    </td>

                    <td>
                        <?php echo $order['created_at']; ?>
                    </td>
                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>You have not placed any orders yet.</p>

    <?php endif; ?>

</div>

<footer>
    <p>© 2026 Online Bookstore</p>
</footer>

</body>
</html>
