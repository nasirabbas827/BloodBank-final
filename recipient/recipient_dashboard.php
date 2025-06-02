<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Blood Recipient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['id'];
$search_result = [];
$blood_types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
$edit_mode = false;
$edit_request_id = null;

// Handle Blood Request Submission or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_request'])) {
        $patient_name = $_POST['patient_name'];
        $blood_type = $_POST['blood_type'];
        $quantity = $_POST['quantity'];
        $urgency = $_POST['urgency'];
        $required_by = $_POST['required_by'];
        $purpose = $_POST['purpose'];

        if (isset($_POST['edit_request_id']) && !empty($_POST['edit_request_id'])) {
            // Update existing request
            $edit_request_id = $_POST['edit_request_id'];
            $stmt = $conn->prepare("UPDATE blood_requests SET patient_name = ?, blood_type_required = ?, quantity_required = ?, urgency_level = ?, required_by_date = ?, purpose = ?, updated_at = NOW() WHERE request_id = ? AND requester_id = ? AND status = 'pending'");
            $stmt->bind_param("ssssssss", $patient_name, $blood_type, $quantity, $urgency, $required_by, $purpose, $edit_request_id, $user_id);
            $stmt->execute();
        } else {
            // Insert new request
            $stmt = $conn->prepare("INSERT INTO blood_requests (requester_id, requester_type, patient_name, blood_type_required, quantity_required, urgency_level, required_by_date, purpose, status, created_at, updated_at) VALUES (?, 'recipient', ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
            $stmt->bind_param("issssss", $user_id, $patient_name, $blood_type, $quantity, $urgency, $required_by, $purpose);
            $stmt->execute();
        }
        $stmt->close();
    } elseif (isset($_POST['delete_request'])) {
        $delete_request_id = $_POST['delete_request_id'];
        $stmt = $conn->prepare("DELETE FROM blood_requests WHERE request_id = ? AND requester_id = ? AND status = 'pending'");
        $stmt->bind_param("ii", $delete_request_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get Blood Stock
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && !empty($_GET['search_blood_type'])) {
    $search_type = $_GET['search_blood_type'];
    $stmt = $conn->prepare("SELECT * FROM blood_stock WHERE blood_type = ?");
    $stmt->bind_param("s", $search_type);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM blood_stock");
    $search_result = $result->fetch_all(MYSQLI_ASSOC);
}

// Get Previous Requests
$req_query = $conn->prepare("SELECT * FROM blood_requests WHERE requester_id = ? AND requester_type = 'recipient' ORDER BY request_id DESC");
$req_query->bind_param("i", $user_id);
$req_query->execute();
$requests = $req_query->get_result()->fetch_all(MYSQLI_ASSOC);
$req_query->close();

// Handle Edit Form Population
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_request_id = $_GET['edit'];
    foreach ($requests as $req) {
        if ($req['request_id'] == $edit_request_id && $req['status'] == 'pending') {
            $edit_mode = true;
            $edit_request = $req;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Recipient Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>

        .container {
            max-width: 1400px;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .card h5 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
        }
        .card .subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .form-control, .btn {
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .table {
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background-color: #e9ecef;
            font-weight: 600;
            color: #343a40;
        }
        .table td {
            vertical-align: middle;
        }
        .status-pending { color: #e67e22; font-weight: 500; }
        .status-approved { color: #28a745; font-weight: 500; }
        .status-rejected { color: #dc3545; font-weight: 500; }
        .action-btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 5px;
        }
        .modal-content {
            border-radius: 12px;
        }
        .modal-header {
            background-color: #007bff;
            color: #fff;
            border-radius: 12px 12px 0 0;
        }
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .dashboard-header .btn {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="dashboard-header">
            <h1>Recipient Dashboard</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addRequestModal">Add New Request</button>
        </div>
        <p class="email-info">Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>

            <!-- Blood Stock Card -->
            <div class="">
                <div class="card">
                    <h5>Search Blood Availability</h5>
                    <p class="subtitle">Check available blood units in stock</p>
                    <form method="GET" class="form-inline mb-4">
                        <div class="form-group flex-grow-1 mr-2">
                            <label for="search_blood_type" class="sr-only">Blood Type</label>
                            <select name="search_blood_type" id="search_blood_type" class="form-control w-100" required>
                                <option value="">Select Blood Type</option>
                                <?php foreach ($blood_types as $bt): ?>
                                    <option value="<?= $bt ?>" <?= (isset($_GET['search_blood_type']) && $_GET['search_blood_type'] === $bt) ? 'selected' : '' ?>><?= $bt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="search" class="btn btn-primary">Search</button>
                    </form>
                    <h5>
                        <?= (isset($_GET['search']) && !empty($_GET['search_blood_type'])) ? "Search Results" : "Available Blood Stock" ?>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Units Available</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($search_result)): ?>
                                    <?php foreach ($search_result as $stock): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($stock['blood_type']) ?></td>
                                            <td><?= htmlspecialchars($stock['units_available']) ?></td>
                                            <td><?= htmlspecialchars($stock['last_updated']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No blood stock available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Previous Requests Card -->
            <div class="col-lg-12">
                <div class="card">
                    <h5>Previous Blood Requests</h5>
                    <p class="subtitle">Status of your previous blood requests</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Patient Name</th>
                                    <th>Blood Type</th>
                                    <th>Quantity</th>
                                    <th>Urgency</th>
                                    <th>Required By</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($req['request_id']) ?></td>
                                            <td><?= htmlspecialchars($req['patient_name']) ?></td>
                                            <td><?= htmlspecialchars($req['blood_type_required']) ?></td>
                                            <td><?= htmlspecialchars($req['quantity_required']) ?></td>
                                            <td><?= htmlspecialchars(ucfirst($req['urgency_level'])) ?></td>
                                            <td><?= htmlspecialchars($req['required_by_date']) ?></td>
                                            <td><?= htmlspecialchars($req['purpose']) ?></td>
                                            <td class="status-<?= strtolower($req['status']) ?>">
                                                <?= ucfirst($req['status']) ?>
                                                <?= htmlspecialchars($req['admin_notes']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($req['created_at']) ?></td>
                                            <td>
                                                <?php if ($req['status'] === 'pending'): ?>
                                                    <button class="action-btn btn-primary" data-toggle="modal" data-target="#editRequestModal" 
                                                        data-id="<?= $req['request_id'] ?>" 
                                                        data-patient="<?= htmlspecialchars($req['patient_name']) ?>" 
                                                        data-blood="<?= $req['blood_type_required'] ?>" 
                                                        data-quantity="<?= $req['quantity_required'] ?>" 
                                                        data-urgency="<?= $req['urgency_level'] ?>" 
                                                        data-required="<?= $req['required_by_date'] ?>" 
                                                        data-purpose="<?= htmlspecialchars($req['purpose']) ?>">Edit</button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this request?');">
                                                        <input type="hidden" name="delete_request_id" value="<?= $req['request_id'] ?>">
                                                        <button type="submit" name="delete_request" class="m-2 action-btn btn-danger">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No previous requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Request Modal -->
    <div class="modal fade" id="addRequestModal" tabindex="-1" aria-labelledby="addRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRequestModalLabel">Add New Blood Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="addRequestForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="patient_name">Patient Name</label>
                            <input type="text" name="patient_name" id="patient_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="blood_type">Blood Type Required</label>
                            <select name="blood_type" id="blood_type" class="form-control" required>
                                <option value="">Select</option>
                                <?php foreach ($blood_types as $bt): ?>
                                    <option value="<?= $bt ?>"><?= $bt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity (units)</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="urgency">Urgency Level</label>
                            <select name="urgency" id="urgency" class="form-control" required>
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="required_by">Required By</label>
                            <input type="date" name="required_by" id="required_by" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="purpose">Purpose</label>
                            <input type="text" name="purpose" id="purpose" class="form-control" required placeholder="e.g., Surgery, Accident">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div class="modal fade" id="editRequestModal" tabindex="-1" aria-labelledby="editRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRequestModalLabel">Edit Blood Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="editRequestForm">
                    <input type="hidden" name="edit_request_id" id="edit_request_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_patient_name">Patient Name</label>
                            <input type="text" name="patient_name" id="edit_patient_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_blood_type">Blood Type Required</label>
                            <select name="blood_type" id="edit_blood_type" class="form-control" required>
                                <option value="">Select</option>
                                <?php foreach ($blood_types as $bt): ?>
                                    <option value="<?= $bt ?>"><?= $bt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_quantity">Quantity (units)</label>
                            <input type="number" name="quantity" id="edit_quantity" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_urgency">Urgency Level</label>
                            <select name="urgency" id="edit_urgency" class="form-control" required>
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_required_by">Required By</label>
                            <input type="date" name="required_by" id="edit_required_by" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_purpose">Purpose</label>
                            <input type="text" name="purpose" id="edit_purpose" class="form-control" required placeholder="e.g., Surgery, Accident">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="submit_request" class="btn btn-primary">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate Edit Modal
        $('#editRequestModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var patient = button.data('patient');
            var blood = button.data('blood');
            var quantity = button.data('quantity');
            var urgency = button.data('urgency');
            var required = button.data('required');
            var purpose = button.data('purpose');

            var modal = $(this);
            modal.find('#edit_request_id').val(id);
            modal.find('#edit_patient_name').val(patient);
            modal.find('#edit_blood_type').val(blood);
            modal.find('#edit_quantity').val(quantity);
            modal.find('#edit_urgency').val(urgency);
            modal.find('#edit_required_by').val(required);
            modal.find('#edit_purpose').val(purpose);
        });

        // Client-side validation for forms
        document.getElementById('addRequestForm').addEventListener('submit', function(e) {
            var quantity = document.getElementById('quantity').value;
            if (quantity < 1) {
                e.preventDefault();
                alert('Quantity must be at least 1.');
            }
        });

        document.getElementById('editRequestForm').addEventListener('submit', function(e) {
            var quantity = document.getElementById('edit_quantity').value;
            if (quantity < 1) {
                e.preventDefault();
                alert('Quantity must be at least 1.');
            }
        });
    </script>
</body>
</html>