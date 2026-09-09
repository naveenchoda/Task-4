<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$book = $result->fetch_assoc();

$stmt->close();

if (!$book) {
    die("Book not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Books</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">

    <h2>📖 Book Details</h2>

    <div class="features">

        <div>

            <h2>
                <?php echo htmlspecialchars($book['title']); ?>
            </h2>

            <p>
                <strong>Author:</strong>
                <?php echo htmlspecialchars($book['author']); ?>
            </p>

            <p>
                <strong>Category:</strong>
                <?php echo htmlspecialchars($book['category']); ?>
            </p>

            <p>
                <strong>Price:</strong>
                ₹<?php echo number_format($book['price'], 2); ?>
            </p>

            <p>
                <strong>Available Stock:</strong>
                <?php echo $book['stock']; ?>
            </p>

            <p>
                <strong>Description:</strong>
                <?php echo htmlspecialchars($book['description']); ?>
            </p>

            <br>

            <a href="products.php" class="btn">
                ⬅ Back to Books
            </a>

        </div>

    </div>

</div>

<footer>
    <p>© 2026 Online Bookstore</p>
</footer>

</body>
</html>
