<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Blood Recipient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Initialize variables and error messages
$full_name = $phone = $location = $blood_type = $dob = $gender = $full_address = "";
$full_name_err = $phone_err = $location_err = $blood_type_err = $dob_err = $gender_err = $full_address_err = "";
$update_msg = "";

// Valid options
$valid_blood_types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
$valid_genders = ['Male', 'Female', 'Other'];

// Fetch existing data to prefill form
$sql = "SELECT full_name, phone, location, blood_type, dob, gender, full_address FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $full_name, $phone, $location, $blood_type, $dob, $gender, $full_address);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize POST data
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $location = trim($_POST["location"]);
    $blood_type = $_POST["blood_type"] ?? "";
    $dob = $_POST["dob"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $full_address = trim($_POST["full_address"]);

    // Validate full name
    if (empty($full_name)) {
        $full_name_err = "Please enter your full name.";
    }

    // Validate phone (basic validation)
    if (empty($phone)) {
        $phone_err = "Please enter your phone number.";
    } elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
        $phone_err = "Invalid phone number format.";
    }

    // Validate location
    if (empty($location)) {
        $location_err = "Please enter your location.";
    }

    // Validate blood type
    if (empty($blood_type)) {
        $blood_type_err = "Please select your blood type.";
    } elseif (!in_array($blood_type, $valid_blood_types)) {
        $blood_type_err = "Invalid blood type selected.";
    }

    // Validate DOB (optional but check format if given)
    if (!empty($dob) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $dob_err = "Invalid date format.";
    }

    // Validate gender
    if (empty($gender)) {
        $gender_err = "Please select your gender.";
    } elseif (!in_array($gender, $valid_genders)) {
        $gender_err = "Invalid gender selected.";
    }

    // Validate full address
    if (empty($full_address)) {
        $full_address_err = "Please enter your full address.";
    }

    // If no errors, update user data
    if (empty($full_name_err) && empty($phone_err) && empty($location_err) && empty($blood_type_err) && empty($dob_err) && empty($gender_err) && empty($full_address_err)) {
        $update_sql = "UPDATE users SET full_name = ?, phone = ?, location = ?, blood_type = ?, dob = ?, gender = ?, full_address = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $full_name, $phone, $location, $blood_type, $dob, $gender, $full_address, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $update_msg = '<div class="alert alert-success">Profile updated successfully.</div>';
            } else {
                $update_msg = '<div class="alert alert-danger">Error updating profile. Please try again.</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile - Blood Donor</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h2>Update Profile</h2>
            <?php echo $update_msg; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter full name">
                        <div class="invalid-feedback"><?php echo $full_name_err; ?></div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter phone number">
                        <div class="invalid-feedback"><?php echo $phone_err; ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($location); ?>" placeholder="Enter location">
                        <div class="invalid-feedback"><?php echo $location_err; ?></div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Blood Type</label>
                        <select name="blood_type" class="form-control <?php echo (!empty($blood_type_err)) ? 'is-invalid' : ''; ?>" required>
                            <option value="">Select Blood Type</option>
                            <?php
                            foreach ($valid_blood_types as $type) {
                                $selected = ($blood_type === $type) ? "selected" : "";
                                echo "<option value=\"$type\" $selected>$type</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $blood_type_err; ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($dob); ?>">
                        <div class="invalid-feedback"><?php echo $dob_err; ?></div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Gender</label>
                        <select name="gender" class="form-control <?php echo (!empty($gender_err)) ? 'is-invalid' : ''; ?>" required>
                            <option value="">Select Gender</option>
                            <?php
                            foreach ($valid_genders as $g) {
                                $selected = ($gender === $g) ? "selected" : "";
                                echo "<option value=\"$g\" $selected>$g</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $gender_err; ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Address</label>
                    <textarea name="full_address" class="form-control <?php echo (!empty($full_address_err)) ? 'is-invalid' : ''; ?>" rows="3" placeholder="Enter full address"><?php echo htmlspecialchars($full_address); ?></textarea>
                    <div class="invalid-feedback"><?php echo $full_address_err; ?></div>
                </div>

                <div class="form-group text-center">
                    <input type="submit" value="Update Profile" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
