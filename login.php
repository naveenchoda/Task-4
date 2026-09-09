<?php
session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT users.id, users.name, users.email, users.password,
                    users.role_id, roles.role_name
             FROM users
             INNER JOIN roles ON users.role_id = roles.id
             WHERE users.email = ? AND users.status = 'active'"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["role_id"] = $user["role_id"];
                $_SESSION["role_name"] = $user["role_name"];

                // Update last login time
                $update = $conn->prepare(
                    "UPDATE users SET last_login = NOW() WHERE id = ?"
                );

                $update->bind_param("i", $user["id"]);
                $update->execute();
                $update->close();

                // Redirect based on role
                if ($user["role_name"] == "admin") {

                    header("Location: admin/index.php");
                    exit();

                } else {

                    header("Location: dashboard.php");
                    exit();
                }

            } else {

                $message = "Invalid email or password.";
            }

        } else {

            $message = "Invalid email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Online Bookstore</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header>

    <h1>📚 Online Bookstore</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
    </nav>

</header>


<div class="form-container">

    <h2>Login</h2>

    <?php if (!empty($message)): ?>

        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <form method="POST">

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
            placeholder="Enter your password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <p>
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

    <p>
        <a href="forgot_password.php">
            Forgot Password?
        </a>
    </p>

</div>

</body>

</html>
