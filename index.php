<?php
include('config.php');

// Blood stock search query
$search_result = [];
if (isset($_GET['stock_search']) && !empty($_GET['stock_search_blood_type'])) {
    $search_type = $_GET['stock_search_blood_type'];
    $sql = "SELECT blood_type, units_available, last_updated FROM blood_stock WHERE blood_type = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $search_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $search_result = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    $sql = "SELECT blood_type, units_available, last_updated FROM blood_stock";
    $result = mysqli_query($conn, $sql);
    $search_result = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Placeholder for chart data (replace with actual data if available)
$status_data = [
    'Available' => 150,
    'Low Stock' => 50,
    'Critical' => 20,
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodBank+</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #ff6f61; /* Red from heart icon */
            --highlight: #6f42c1;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            color: var(--dark);
        }
        .navbar {
            background-color: var(--dark);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .navbar-brand {
                        color: white !important;

            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .navbar-brand .heart-icon {
            color: var(--accent);
            margin-right: 8px;
            font-size: 1.5rem;
        }
        .nav-link {
            color: white !important;
        }
        .navbar-brand:hover, .nav-link:hover {
                        color: yellow !important;

        }
        .hero-section {
            height: 600px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 30px 30px;
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hero-section .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            font-size: 1.2rem;
            transition: transform 0.3s, background-color 0.3s;
        }
        .hero-section .btn-primary:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
            transform: scale(1.05);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .search-form {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            border-left: 5px solid var(--accent);
        }
        .section {
            padding: 80px 0;
        }
        .section-title {
            color: var(--highlight);
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background-color: var(--accent);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .feature-icon {
            font-size: 3em;
            color: var(--accent);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .card:hover .feature-icon {
            color: var(--highlight);
        }
        .chart-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            border-top: 4px solid var(--accent);
        }
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        .btn-success:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.3);
        }
        .heart-icon{
            color:red;
            
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="display-4  fw-bold"><i class="fas fa-heart heart-icon "></i>BloodBank+</h1>
        <p class="lead mb-4">Save Lives with Blood Donation</p>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Login to Donate or Request</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <!-- Blood Stock Search Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Search Blood Stock</h2>
            <form method="get" class="search-form mb-5">
                <div class="row g-3">
                    <div class="col-md-10">
                        <select name="stock_search_blood_type" class="form-control" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'A+' ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'A-' ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'B+' ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'B-' ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'AB+' ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'AB-' ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'O+' ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= isset($_GET['stock_search_blood_type']) && $_GET['stock_search_blood_type'] === 'O-' ? 'selected' : '' ?>>O-</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="stock_search" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Search</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($search_result)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Blood Type</th>
                                        <th>Units Available</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_result as $stock): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($stock['blood_type']) ?></td>
                                            <td><?= htmlspecialchars($stock['units_available']) ?></td>
                                            <td><?= htmlspecialchars($stock['last_updated']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No blood stock available for the selected type.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- About Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">About BloodBank+</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="lead">BloodBank+ is a platform dedicated to connecting donors with those in need of blood. It provides real-time updates on blood stock, facilitates donation requests, and ensures efficient management of blood resources across communities.</p>
                    <p>Our mission is to save lives by promoting blood donation, maintaining a reliable blood supply, and supporting hospitals and patients in critical situations.</p>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="text-center mb-4">Blood Stock Status</h4>
                        <canvas id="bloodStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-tint feature-icon"></i>
                            <h5 class="card-title">Donate Blood</h5>
                            <p class="card-text">Register as a donor and schedule your donation to help save lives.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-search feature-icon"></i>
                            <h5 class="card-title">Check Stock</h5>
                            <p class="card-text">Search for available blood units by type across our network.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-hospital feature-icon"></i>
                            <h5 class="card-title">Request Blood</h5>
                            <p class="card-text">Submit emergency blood requests for patients in need.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 BloodBank+. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Blood Stock Pie Chart
        const ctx = document.getElementById('bloodStockChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Available', 'Low Stock', 'Critical'],
                datasets: [{
                    data: [
                        <?= $status_data['Available'] ?>,
                        <?= $status_data['Low Stock'] ?>,
                        <?= $status_data['Critical'] ?>,
                    ],
                    backgroundColor: [
                        '#28a745', // Success (green)
                        '#ffc107', // Warning (yellow)
                        '#dc3545', // Danger (red)
                    ],
                    borderColor: 'rgba(255,255,255,0.8)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 14, family: 'Segoe UI' },
                            color: 'var(--dark)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'var(--dark)',
                        titleFont: { size: 16 },
                        bodyFont: { size: 14 }
                    }
                }
            }
        });
    </script>
</body>
</html>