<?php
session_start();
include "config.php";

$message = "";
$success = false;

$token = $_GET["token"] ?? "";

if (empty($token)) {
    $message = "Invalid password reset link.";
} else {

    $stmt = $conn->prepare(
        "SELECT id FROM users
         WHERE reset_token = ?
         AND reset_expires > NOW()"
    );

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $message = "This reset link is invalid or expired.";
    }

    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {

    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $update = $conn->prepare(
            "UPDATE users
             SET password = ?,
                 reset_token = NULL,
                 reset_expires = NULL
             WHERE reset_token = ?"
        );

        $update->bind_param(
            "ss",
            $hashed_password,
            $token
        );

        if ($update->execute()) {

            $success = true;
            $message = "Password reset successful!";

        } else {

            $message = "Password reset failed.";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reset Password</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header>

    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </nav>

</header>


<div class="form-container">

    <h2>Reset Password</h2>

    <?php if (!empty($message)): ?>

        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <?php if (!$success && empty($message)): ?>

        <form method="POST">

            <label>New Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter new password"
                required
            >


            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm new password"
                required
            >


            <button type="submit">
                Reset Password
            </button>

        </form>

    <?php endif; ?>


    <?php if ($success): ?>

        <p>
            <a href="login.php">
                Go to Login
            </a>
        </p>

    <?php endif; ?>

</div>

</body>

</html>
