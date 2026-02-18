<?php
session_start();
include 'db.php';

$message = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user'] = $row['username'];
        $_SESSION['role'] = $row['role'];   // ⭐ role stored
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">

    <h2 class="mb-3">Login</h2>

    <?php if ($message != "") { ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php } ?>

    <form method="post">
        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <button name="login" class="btn btn-success w-100">Login</button>
    </form>

    <p class="mt-3">New user? <a href="register.php">Register</a></p>

</div>
</body>
</html>
