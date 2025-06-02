<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Blood Donor') {
    header("Location: ../login.php");
    exit();
}

$donor_id = $_SESSION['id'];

// Fetch user details (blood type, email, etc.)
$user_query = $conn->query("SELECT email, blood_type FROM users WHERE id = $donor_id");
$user_data = $user_query->fetch_assoc();
$email = $user_data['email'] ?? 'N/A';
$blood_type = $user_data['blood_type'] ?? 'Unknown';

// Fetch total completed donations
$donation_query = $conn->query("SELECT COUNT(*) as total FROM donations WHERE donor_id = $donor_id AND status = 'completed'");
$donation_data = $donation_query->fetch_assoc();
$total_completed = $donation_data['total'] ?? 0;

// Fetch latest next eligible date
$eligible_query = $conn->query("SELECT next_eligible_date FROM donations WHERE donor_id = $donor_id AND next_eligible_date IS NOT NULL AND next_eligible_date >= CURDATE() ORDER BY next_eligible_date ASC LIMIT 1");

$eligible_row = $eligible_query->fetch_assoc();
$next_date = $eligible_row['next_eligible_date'] ?? 'N/A';

// Determine eligibility status
$current_date = date('Y-m-d');
$eligibility_status = ($next_date !== 'N/A' && $next_date <= $current_date) ? 'AVAILABLE' : 'Not Available';

// Fetch donation history
$history_query = $conn->query("SELECT donation_date, donation_type, status FROM donations WHERE donor_id = $donor_id ORDER BY donation_date DESC LIMIT 3");
$donation_history = [];
while ($row = $history_query->fetch_assoc()) {
    $donation_history[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Donor Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h5 {
            font-size: 16px;
            font-weight: 500;
            color: #666;
            margin: 0;
        }
        .card-body {
            text-align: center;
        }
        .card-body h3 {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 10px 0;
        }
        .card-body p {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        .card-body .blood-type {
            font-size: 24px;
            font-weight: 700;
            color: #e74c3c;
        }
        .card-body .status-available {
            font-size: 16px;
            font-weight: 500;
            color: #2ecc71;
        }
        .card-body .status-unavailable {
            font-size: 16px;
            font-weight: 500;
            color: #e74c3c;
        }
        .history-card, .actions-card {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .history-card {
            margin-right: 4%;
        }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-item span {
            font-size: 14px;
            color: #333;
        }
        .history-item .status {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 15px;
            background-color: #e0f7fa;
            color: #26a69a;
        }
        .actions-card button {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        .actions-card .schedule-btn {
            background-color: #e74c3c;
            color: #fff;
        }
        .actions-card .update-btn, .actions-card .find-btn {
            background-color: #fff;
            border: 1px solid #ddd;
            color: #333;
        }
        .heart-icon {
            color: #e74c3c;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

    <div class="container">
        <h1>Donor Dashboard</h1>

        <!-- Summary Cards -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div class="card" style="width: 32%;">
                <div class="card-header">
                    <h5>TOTAL DONATIONS</h5>
                    <span class="heart-icon">❤️</span>
                </div>
                <div class="card-body">
                    <h3><?php echo $total_completed; ?></h3>
                    <p>Thank you for your contributions!</p>
                </div>
            </div>
            <div class="card" style="width: 32%;">
                <div class="card-header">
                    <h5>BLOOD TYPE</h5>
                </div>
                <div class="card-body">
                    <p class="blood-type"><?php echo $blood_type; ?></p>
                    <p>Your blood type</p>
                </div>
            </div>
            <div class="card" style="width: 32%;">
                <div class="card-header">
                    <h5>NEXT DONATION</h5>
                </div>
                <div class="card-body">
                    <p class="<?php echo $eligibility_status === 'AVAILABLE' ? 'status-available' : 'status-unavailable'; ?>">
    <?php echo ($next_date !== 'N/A') ? date('d M Y', strtotime($next_date)) : 'Not Scheduled'; ?>
</p>
<p><?php echo ($eligibility_status === 'AVAILABLE') ? 'You can donate now!' : 'Please wait until the next eligible date.'; ?></p>

                </div>
            </div>
        </div>

        <!-- Donation History and Quick Actions -->
        <div style="display: flex; justify-content: space-between;">
            <!-- Donation History -->
            <div class="card history-card">
                <h5>DONATION HISTORY</h5>
                <p style="color: #888; font-size: 14px;">Your recent blood donations</p>
                <?php if (empty($donation_history)): ?>
                    <p style="color: #888; font-size: 14px;">No donations yet.</p>
                <?php else: ?>
                    <?php foreach ($donation_history as $donation): ?>
                        <div class="history-item">
                            <span><?php echo strtoupper($donation['donation_type']); ?><br><?php echo date('Y-m-d', strtotime($donation['donation_date'])); ?></span>
                            <span class="status"><?php echo ucfirst($donation['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="card actions-card">
                <h5>QUICK ACTIONS</h5>
                <p style="color: #888; font-size: 14px;">Manage your donor profile and schedule donations</p>
                <button class="schedule-btn" onclick="window.location.href='donations.php'">Schedule Donation</button>
                <button class="update-btn" onclick="window.location.href='update_profile.php'">Update Profile</button>
            </div>
        </div>
    </div>
</body>
</html>