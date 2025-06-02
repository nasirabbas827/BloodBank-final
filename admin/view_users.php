<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Update status if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $_POST['status'];
    $stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Delete operation
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Users</h2>
    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Blood Type</th>
                <th>User Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['blood_type']) ?></td>
                <td><?= htmlspecialchars($row['user_type']) ?></td>
                <td>
                    <span class="badge badge-<?= 
                        $row['status'] === 'approved' ? 'success' : 
                        ($row['status'] === 'rejected' ? 'danger' : 'secondary') 
                    ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="user_profile.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">View Profile</a>
                    <button class=" m-2 btn btn-sm btn-info" data-toggle="modal" data-target="#editStatusModal<?= $row['id'] ?>">Edit</button>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete user?')" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>

            <!-- Modal -->
            <div class="modal fade" id="editStatusModal<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel<?= $row['id'] ?>" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="statusModalLabel<?= $row['id'] ?>">Update User Status</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <select name="status" class="form-control" required>
                                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $row['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_status" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
              </div>
            </div>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
