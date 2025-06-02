<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_request'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'];

    $stmt = $conn->prepare("UPDATE blood_requests SET status=?, admin_notes=? WHERE request_id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $request_id);
    $stmt->execute();
    $stmt->close();
}

// Filter by date
$where = "";
$params = [];
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $where = "WHERE DATE(updated_at) BETWEEN ? AND ?";
    $params = [$_GET['start_date'], $_GET['end_date']];
}

$query = "SELECT br.*, u.full_name AS requester_name, u.email 
          FROM blood_requests br 
          JOIN users u ON br.requester_id = u.id 
          $where 
          ORDER BY br.request_id DESC";

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
    <title>Manage Blood Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>Blood Requests</h3>

    <form method="get" class="form-inline mb-3">
        <label class="mr-2">From:</label>
        <input type="date" name="start_date" class="form-control mr-2" value="<?= $_GET['start_date'] ?? '' ?>">
        <label class="mr-2">To:</label>
        <input type="date" name="end_date" class="form-control mr-2" value="<?= $_GET['end_date'] ?? '' ?>">
        <button type="submit" class="btn btn-primary mr-2">Search</button>
        <a href="?" class="btn btn-secondary">Reset</a>
        <button onclick="exportTableToCSV('blood_requests.csv')" class="btn btn-success ml-auto">Export CSV</button>
        <button onclick="window.print()" class="btn btn-info ml-2">Print</button>
    </form>

    <table class="table table-bordered  table-sm" id="requestsTable">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Requester</th>
                <th>Type</th>
                <th>Patient</th>
                <th>Blood</th>
                <th>Qty</th>
                <th>Urgency</th>
                <th>Need By</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['request_id'] ?></td>
                <td><?= htmlspecialchars($row['requester_name']) ?></td>
                <td><?= $row['requester_type'] ?></td>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td><?= $row['blood_type_required'] ?></td>
                <td><?= $row['quantity_required'] ?></td>
                <td><?= ucfirst($row['urgency_level']) ?></td>
                <td><?= $row['required_by_date'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['admin_notes'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick='editRequest(<?= json_encode($row) ?>)'>Edit</button>
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
        <h5 class="modal-title">Update Blood Request</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="update_request" value="1">
        <input type="hidden" name="request_id" id="edit_request_id">

        <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit_status" class="form-control">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="fulfilled">Fulfilled</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="form-group">
            <label>Admin Notes</label>
            <textarea name="admin_notes" id="edit_admin_notes" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function editRequest(data) {
    document.getElementById('edit_request_id').value = data.request_id;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_admin_notes').value = data.admin_notes ?? '';
    $('#editModal').modal('show');
}

function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#requestsTable tr");
    for (var i = 0; i < rows.length; i++) {
        var cols = rows[i].querySelectorAll("td, th");
        var row = Array.from(cols).map(col => `"${col.innerText}"`).join(",");
        csv.push(row);
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
