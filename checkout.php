<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        $message = "Invalid quantity.";
    } else {

        $stmt = $conn->prepare(
            "SELECT price, stock FROM products WHERE id = ?"
        );

        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            $message = "Book not found.";
        } elseif ($quantity > $product['stock']) {
            $message = "Not enough stock available.";
        } else {

            $total = $product['price'] * $quantity;

            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, total_amount, status)
                 VALUES (?, ?, 'Pending')"
            );

            $stmt->bind_param("id", $user_id, $total);
            $stmt->execute();

            $order_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO order_items
                 (order_id, product_id, quantity, price)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiid",
                $order_id,
                $product_id,
                $quantity,
                $product['price']
            );

            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                "UPDATE products
                 SET stock = stock - ?
                 WHERE id = ?"
            );

            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
            $stmt->close();

            header("Location: orders.php");
            exit();
        }
    }
}

$products = $conn->query(
    "SELECT id, title, price, stock
     FROM products
     WHERE stock > 0
     ORDER BY title"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Books</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">My Orders</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <h2>🛒 Place an Order</h2>

    <?php if ($message != ""): ?>
        <p class="error">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Select Book</label>

        <select name="product_id" required>

            <option value="">-- Select Book --</option>

            <?php while ($book = $products->fetch_assoc()): ?>

                <option value="<?php echo $book['id']; ?>">
                    <?php echo htmlspecialchars($book['title']); ?>
                    - ₹<?php echo number_format($book['price'], 2); ?>
                    (Stock: <?php echo $book['stock']; ?>)
                </option>

            <?php endwhile; ?>

        </select>

        <label>Quantity</label>

        <input
            type="number"
            name="quantity"
            min="1"
            value="1"
            required
        >

        <button type="submit" class="btn">
            🛒 Place Order
        </button>

    </form>

</div>

<footer>
    <p>© 2026 Online Bookstore</p>
</footer>

</body>
</html>
