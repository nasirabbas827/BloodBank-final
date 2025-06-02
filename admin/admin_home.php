<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch total donors (approved users)
$total_donors_sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 'Blood Donor' AND status = 'approved'";
$total_donors_result = mysqli_query($conn, $total_donors_sql);
$total_donors = mysqli_fetch_assoc($total_donors_result)['count'];
// Fetch total donors (approved users)
$total_users_sql = "SELECT COUNT(*) as count FROM users";
$total_users_result = mysqli_query($conn, $total_users_sql);
$total_users = mysqli_fetch_assoc($total_users_result)['count'];

// Fetch total blood units
$total_units_sql = "SELECT SUM(units_available) as total FROM blood_stock";
$total_units_result = mysqli_query($conn, $total_units_sql);
$total_units = mysqli_fetch_assoc($total_units_result)['total'] ?? 0;

// Fetch pending requests
$pending_requests_sql = "SELECT COUNT(*) as count FROM blood_requests WHERE status = 'pending'";
$pending_requests_result = mysqli_query($conn, $pending_requests_sql);
$pending_requests = mysqli_fetch_assoc($pending_requests_result)['count'];

// Fetch blood stock
$blood_stock_sql = "SELECT blood_type, units_available FROM blood_stock";
$blood_stock_result = mysqli_query($conn, $blood_stock_sql);
$blood_stock = mysqli_fetch_all($blood_stock_result, MYSQLI_ASSOC);

// Fetch pending users
$pending_users_sql = "SELECT * FROM users WHERE status = 'pending'";
$pending_users_result = mysqli_query($conn, $pending_users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background-color: #fff; border-bottom: none; }
        .card-title { font-size: 16px; color: #6c757d; }
        .card-text { font-size: 24px; font-weight: bold; }
        .stock-card { width: 120px; height: 120px; display: inline-block; margin: 10px; text-align: center; padding: 10px; }
        .stock-card .badge { font-size: 12px; }
        .badge-adequate { background-color: #007bff; }
        .badge-low { background-color: #ffc107; }
        .badge-critical { background-color: #dc3545; }
        .badge-high { background-color: #28a745; }
        .update-section { padding: 20px; }
        .chart-container { position: relative; height: 300px; width: 300px; margin: 0 auto; }
        .table th, .table td { font-size: 14px; }
        .table th { background-color: #e9ecef; }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="dashboard-header">Administrator Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-header">Total Donors</div>
                <div class="card-body">
                    <p class="card-text"><?= $total_donors ?></p>
                    <p class="card-text text-muted">+12% from last month</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-header">Blood Units</div>
                <div class="card-body">
                    <p class="card-text"><?= $total_units ?></p>
                    <p class="card-text text-muted">Available in stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-header">Pending Requests</div>
                <div class="card-body">
                    <p class="card-text"><?= $pending_requests ?></p>
                    <p class="card-text text-muted">Awaiting approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-header">Total Users</div>
                <div class="card-body">
                    <p class="card-text"><?= $total_users ?></p>
                    <p class="card-text text-muted">+8% from last month</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Current Blood Stock</div>
                <div class="card-body">
                    <p class="card-text text-muted">Real-time inventory of available blood units</p>
                    <div>
                        <?php
                        $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                        foreach ($blood_types as $type) {
                            $unit = array_filter($blood_stock, fn($s) => $s['blood_type'] === $type);
                            $unit = $unit ? $unit[array_key_first($unit)]['units_available'] : 0;
                            $status = $unit >= 40 ? 'adequate' : ($unit >= 10 ? 'low' : 'critical');
                            $badge_class = $status === 'adequate' ? 'badge-adequate' : ($status === 'low' ? 'badge-low' : 'badge-critical');
                            echo "<div class='stock-card'><h5>$type</h5><p class='card-text'>$unit</p><span class='badge $badge_class'>$status</span></div>";
                        }
                        ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="bloodStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Pending User Approvals</div>
                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($pending_users_result) > 0): ?>
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Full Name</th>
                                    <th>User Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($pending_users_result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td><?= htmlspecialchars($row['user_type']) ?></td>
                                        <td>
                                            <form method="POST" action="approve_user.php" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="action" value="approve" class="mb-2 btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-3 alert alert-info">No users awaiting approval.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pie Chart for Blood Stock
    const ctx = document.getElementById('bloodStockChart').getContext('2d');
    const bloodStockChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($blood_stock, 'blood_type')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($blood_stock, 'units_available')); ?>,
                backgroundColor: ['#007bff', '#ffc107', '#28a745', '#dc3545', '#17a2b8', '#6c757d', '#fd7e14', '#20c997']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Blood Stock Distribution' }
            }
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>