<?php
include 'db.php';

$message = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validation
    if (strlen($username) < 3) {
        $message = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 4) {
        $message = "Password must be at least 4 characters.";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $message = "Username already exists. Try another.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Default role = user
            $stmt = $conn->prepare("INSERT INTO users(username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $hashed);

            if ($stmt->execute()) {
                $message = "Registered successfully! <a href='login.php'>Login</a>";
            } else {
                $message = "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">

    <h2 class="mb-3">Register</h2>

    <?php if ($message != "") { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>

    <form method="post">
        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <button name="register" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="mt-3">Already have account? <a href="login.php">Login</a></p>

</div>
</body>
</html>
