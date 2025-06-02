<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Add new stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $blood_type = $_POST['blood_type'];
    $units = (int)$_POST['units_available'];
    $stmt = mysqli_prepare($conn, "INSERT INTO blood_stock (blood_type, units_available, last_updated) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "si", $blood_type, $units);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = (int)$_POST['stock_id'];
    $blood_type = $_POST['blood_type'];
    $units = (int)$_POST['units_available'];
    $stmt = mysqli_prepare($conn, "UPDATE blood_stock SET blood_type = ?, units_available = ?, last_updated = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $blood_type, $units, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Fetch all stock
$result = mysqli_query($conn, "SELECT * FROM blood_stock ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Blood Stock</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Blood Stock Inventory</h2>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addStockModal">Add Blood Stock</button>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Blood Type</th>
                <th>Units Available</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['blood_type']) ?></td>
                <td><?= $row['units_available'] ?></td>
                <td><?= $row['last_updated'] ?></td>
                <td>
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editStockModal<?= $row['id'] ?>">Edit</button>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editStockModal<?= $row['id'] ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="">
                        <input type="hidden" name="stock_id" value="<?= $row['id'] ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Blood Stock</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Blood Type</label>
                                    <select name="blood_type" class="form-control" required>
                                        <?php
                                        $types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                                        foreach ($types as $type):
                                        ?>
                                            <option value="<?= $type ?>" <?= $row['blood_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Units Available</label>
                                    <input type="number" name="units_available" class="form-control" value="<?= $row['units_available'] ?>" required min="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_stock" class="btn btn-success">Update</button>
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

<!-- Add Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Blood Stock</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Blood Type</label>
                        <select name="blood_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Units Available</label>
                        <input type="number" name="units_available" class="form-control" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_stock" class="btn btn-primary">Add</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
