<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$requested_role = $_GET['role'] ?? '';
$return_url = $_GET['return_url'] ?? '';
$valid_roles = ['admin', 'provider', 'customer'];
$requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
$return_url = filter_var($return_url, FILTER_SANITIZE_URL);

// إذا كان المستخدم مسجل دخوله بالفعل
if (isLoggedIn()) {
    if (getUserRole() === 'admin') {
        header('Location: /local-services-platform/admin/dashboard.php');
    } elseif (getUserRole() === 'provider') {
        header('Location: /local-services-platform/provider/dashboard.php');
    } else {
        header('Location: /local-services-platform/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $requested_role = $_POST['role'] ?? $requested_role;
    $return_url = $_POST['return_url'] ?? $return_url;
    $requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
    $return_url = filter_var($return_url, FILTER_SANITIZE_URL);

    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($requested_role === 'admin' && $user['role'] !== 'admin') {
                $error = 'Please login with an admin account.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                if ($requested_role === $user['role'] && !empty($return_url)) {
                    header("Location: $return_url");
                } elseif ($user['role'] === 'admin') {
                    header('Location: /local-services-platform/admin/dashboard.php');
                } elseif ($user['role'] === 'provider') {
                    header('Location: /local-services-platform/provider/dashboard.php');
                } else {
                    header('Location: /local-services-platform/index.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Local Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Login to Your Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <hr>
                        <p class="text-center mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>