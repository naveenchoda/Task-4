<?php
session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "Please fill all fields.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email.";
    } 
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } 
    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
    } 
    else {

        // Check whether email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered.";

        } else {

            // Secure password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // User role = 1
            $role_id = 1;

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role_id)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssi",
                $name,
                $email,
                $hashed_password,
                $role_id
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Online Bookstore</title>

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

    <h2>Create Account</h2>

    <?php if (!empty($message)): ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Name</label>
        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >

        <label>Email</label>
        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <label>Confirm Password</label>
        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm password"
            required
        >

        <button type="submit">Register</button>

    </form>

    <p>
        Already have an account?
        <a href="login.php">Login here</a>
    </p>

</div>

</body>
</html>
