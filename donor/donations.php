<?php
session_start();
include('../config.php');

// Validate donor session
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Blood Donor') {
    header("Location: ../login.php");
    exit();
}

$donor_id = $_SESSION['id'];

// Handle Add Donation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_donation'])) {
    $donation_date = $_POST['donation_date'];
    $donation_type = $_POST['donation_type'];
    $quantity = $_POST['quantity'];
    $health_screening_results = $_POST['health_screening_results'];

    $stmt = $conn->prepare("INSERT INTO donations (donor_id, donation_date, donation_type, quantity, health_screening_results) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $donor_id, $donation_date, $donation_type, $quantity, $health_screening_results);
    $stmt->execute();
    $stmt->close();
}

// Handle Edit Donation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_donation'])) {
    $donation_id = $_POST['donation_id'];
    $donation_date = $_POST['donation_date'];
    $donation_type = $_POST['donation_type'];
    $quantity = $_POST['quantity'];
    $health_screening_results = $_POST['health_screening_results'];

    $stmt = $conn->prepare("UPDATE donations SET donation_date=?, donation_type=?, quantity=?, health_screening_results=? WHERE donation_id=? AND status='pending' AND donor_id=?");
    $stmt->bind_param("ssissi", $donation_date, $donation_type, $quantity, $health_screening_results, $donation_id, $donor_id);
    $stmt->execute();
    $stmt->close();
}

// Handle Delete Donation
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM donations WHERE donation_id = $delete_id AND status='pending' AND donor_id=$donor_id");
}

// Fetch donations
$donations = $conn->query("SELECT * FROM donations WHERE donor_id = $donor_id ORDER BY donation_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Donations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h3>My Donations</h3>
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">Add Donation</button>

    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Rejection Reason</th>
                <th>Next Eligible</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $donations->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['donation_id'] ?></td>
                    <td><?= $row['donation_date'] ?></td>
                    <td><?= $row['donation_type'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['rejection_reason'] ?? '-' ?></td>
                    <td><?= $row['next_eligible_date'] ?? '-' ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <button class="btn btn-sm btn-primary" onclick="editDonation(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                            <a href="?delete_id=<?= $row['donation_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to Delete?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Donation Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Donation</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="add_donation" value="1">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="donation_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Donation Type</label>
            <select name="donation_type" class="form-control" required>
                <option>Whole Blood</option>
                <option>Plasma</option>
                <option>Platelets</option>
            </select>
        </div>
        <div class="form-group">
            <label>Quantity (units)</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label>Health Screening Results</label>
            <textarea name="health_screening_results" class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Donation</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_donation" value="1">
        <input type="hidden" name="donation_id" id="edit_donation_id">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="donation_date" id="edit_donation_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Donation Type</label>
            <select name="donation_type" id="edit_donation_type" class="form-control" required>
                <option>Whole Blood</option>
                <option>Plasma</option>
                <option>Platelets</option>
            </select>
        </div>
        <div class="form-group">
            <label>Quantity (units)</label>
            <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Health Screening Results</label>
            <textarea name="health_screening_results" id="edit_health_screening_results" class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function editDonation(data) {
    document.getElementById('edit_donation_id').value = data.donation_id;
    document.getElementById('edit_donation_date').value = data.donation_date;
    document.getElementById('edit_donation_type').value = data.donation_type;
    document.getElementById('edit_quantity').value = data.quantity;
    document.getElementById('edit_health_screening_results').value = data.health_screening_results;
    $('#editModal').modal('show');
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
