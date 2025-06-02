<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid user ID";
    exit;
}

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");

if (!$result || mysqli_num_rows($result) === 0) {
    echo "User not found.";
    exit;
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile - <?= htmlspecialchars($user['full_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h3>User Profile: <?= htmlspecialchars($user['full_name']) ?></h3>
    <table class="table table-bordered">
        <tr><th>ID</th><td><?= $user['id'] ?></td></tr>
        <tr><th>Full Name</th><td><?= htmlspecialchars($user['full_name']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($user['phone']) ?></td></tr>
        <tr><th>Location</th><td><?= htmlspecialchars($user['location']) ?></td></tr>
        <tr><th>Blood Type</th><td><?= htmlspecialchars($user['blood_type']) ?></td></tr>
        <tr><th>Date of Birth</th><td><?= htmlspecialchars($user['dob']) ?></td></tr>
        <tr><th>Gender</th><td><?= htmlspecialchars($user['gender']) ?></td></tr>
        <tr><th>Full Address</th><td><?= htmlspecialchars($user['full_address']) ?></td></tr>
        <tr><th>User Type</th><td><?= htmlspecialchars($user['user_type']) ?></td></tr>
        <tr><th>Status</th><td><?= ucfirst($user['status']) ?></td></tr>
        <tr><th>Created At</th><td><?= $user['created_at'] ?></td></tr>
    </table>
    <a href="view_users.php" class="btn btn-secondary">Back to Users</a>
</div>

</body>
</html>
