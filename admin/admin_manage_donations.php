<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_donation'])) {
    $donation_id = $_POST['donation_id'];
    $status = $_POST['status'];
    $rejection_reason = ($status == 'rejected') ? $_POST['rejection_reason'] : null;
    $next_eligible_date = $_POST['next_eligible_date'];

    $stmt = $conn->prepare("UPDATE donations SET status=?, rejection_reason=?, next_eligible_date=? WHERE donation_id=?");
    $stmt->bind_param("sssi", $status, $rejection_reason, $next_eligible_date, $donation_id);
    $stmt->execute();
    $stmt->close();
}

// Search by date
$where = "";
$params = [];
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $where = "WHERE DATE(d.donation_date) BETWEEN ? AND ?";
    $params = [$_GET['start_date'], $_GET['end_date']];
}

$query = "
SELECT d.*, u.full_name AS donor_name, u.email 
FROM donations d 
JOIN users u ON d.donor_id = u.id 
$where 
ORDER BY d.donation_id DESC
";

$stmt = $conn->prepare($query);
if ($where) {
    $stmt->bind_param("ss", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Donations (Admin)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>All Donations</h3>

    <form method="get" class="form-inline mb-3">
        <label class="mr-2">From:</label>
        <input type="date" name="start_date" class="form-control mr-2" value="<?= $_GET['start_date'] ?? '' ?>">
        <label class="mr-2">To:</label>
        <input type="date" name="end_date" class="form-control mr-2" value="<?= $_GET['end_date'] ?? '' ?>">
        <button type="submit" class="btn btn-primary mr-2">Search</button>
        <a href="?" class="btn btn-secondary mr-2">Reset</a>
        <button type="button" class="btn btn-success mr-2" onclick="exportTableToCSV('donations.csv')">Export CSV</button>
        <button type="button" class="btn btn-info" onclick="window.print()">Print</button>
    </form>

    <table class="table table-bordered table-sm" id="donationTable">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Donor</th>
                <th>Email</th>
                <th>Date</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Next Eligible</th>
                <th>Rejection Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['donation_id'] ?></td>
                <td><?= htmlspecialchars($row['donor_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['donation_date'] ?></td>
                <td><?= $row['donation_type'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= $row['next_eligible_date'] ?? '-' ?></td>
                <td><?= $row['rejection_reason'] ?? '-' ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick='editDonation(<?= json_encode($row) ?>)'>Edit</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Donation</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="update_donation" value="1">
        <input type="hidden" name="donation_id" id="edit_donation_id">
        
        <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit_status" class="form-control" onchange="toggleRejectionField()">
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="form-group" id="rejectionGroup" style="display:none;">
            <label>Rejection Reason</label>
            <textarea name="rejection_reason" id="edit_rejection_reason" class="form-control" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label>Next Eligible Date</label>
            <input type="date" name="next_eligible_date" id="edit_next_eligible_date" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Donation</button>
      </div>
    </form>
  </div>
</div>

<script>
function editDonation(data) {
    document.getElementById('edit_donation_id').value = data.donation_id;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_rejection_reason').value = data.rejection_reason ?? '';
    document.getElementById('edit_next_eligible_date').value = data.next_eligible_date ?? '';

    toggleRejectionField();
    $('#editModal').modal('show');
}

function toggleRejectionField() {
    const status = document.getElementById('edit_status').value;
    document.getElementById('rejectionGroup').style.display = (status === 'rejected') ? 'block' : 'none';
}

function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#donationTable tr");
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(","));
    }
    var csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    var link = document.createElement("a");
    link.download = filename;
    link.href = window.URL.createObjectURL(csvFile);
    link.click();
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
