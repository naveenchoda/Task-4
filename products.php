<?php
session_start();
include "config.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "";
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($search != "") {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ss";
}

if ($category != "") {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$categories = $conn->query(
    "SELECT DISTINCT category FROM products ORDER BY category"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books - Online Bookstore</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">

    <h2>📖 Available Books</h2>

    <form method="GET">

        <input
            type="text"
            name="search"
            placeholder="Search by title or author"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <select name="category">
            <option value="">All Categories</option>

            <?php while ($cat = $categories->fetch_assoc()): ?>

                <option
                    value="<?php echo htmlspecialchars($cat['category']); ?>"
                    <?php if ($category == $cat['category']) echo "selected"; ?>
                >
                    <?php echo htmlspecialchars($cat['category']); ?>
                </option>

            <?php endwhile; ?>

        </select>

        <button type="submit" class="btn">🔍 Search</button>

        <a href="products.php" class="btn">Reset</a>

    </form>

    <br>

    <div class="features">

        <?php if ($result->num_rows > 0): ?>

            <?php while ($book = $result->fetch_assoc()): ?>

                <div>

                    <h3>
                        📕 <?php echo htmlspecialchars($book['title']); ?>
                    </h3>

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
                        <strong>Stock:</strong>
                        <?php echo $book['stock']; ?>
                    </p>

                    <p>
                        <?php echo htmlspecialchars($book['description']); ?>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No books found.</p>

        <?php endif; ?>

    </div>

</div>
<?php
$countSql = "SELECT COUNT(*) AS total FROM products WHERE 1=1";

$countParams = [];
$countTypes = "";

if ($search != "") {
    $countSql .= " AND (title LIKE ? OR author LIKE ?)";
    $countParams[] = "%" . $search . "%";
    $countParams[] = "%" . $search . "%";
    $countTypes .= "ss";
}

if ($category != "") {
    $countSql .= " AND category = ?";
    $countParams[] = $category;
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countSql);

if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}

$countStmt->execute();
$totalBooks = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalBooks / $limit);
?>

<div class="pagination">

    <?php if ($page > 1): ?>
        <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>">
            ⬅ Previous
        </a>
    <?php endif; ?>

    <span>
        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
    </span>

    <?php if ($page < $totalPages): ?>
        <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>">
            Next ➡
        </a>
    <?php endif; ?>

</div>
<footer>
    <p>© 2026 Online Bookstore</p>
</footer>

</body>
</html>
