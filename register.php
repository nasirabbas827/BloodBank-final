<?php
include('config.php');

$username = $password = $email = $phone = $location = $blood_type = $user_type = "";
$username_err = $password_err = $email_err = $phone_err = $user_type_err = "";
$registration_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Full Name
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter full name.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $phone_err = "This phone number is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Location & Blood Type
    $location = trim($_POST["location"]);
    $blood_type = trim($_POST["blood_type"]);

    // User Type
    if (empty(trim($_POST["user_type"]))) {
        $user_type_err = "Please select a user type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    if (empty($username_err) && empty($password_err) && empty($email_err) && empty($phone_err) && empty($user_type_err)) {
        $status = ($user_type == "Blood Donor") ? "pending" : "approved";

        $sql = "INSERT INTO users (full_name, password, email, phone, location, blood_type, user_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssss", $username, $hashed_password, $email, $phone, $location, $blood_type, $user_type, $status);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success">Registration successful!</div>';
            $username = $password = $email = $phone = $location = $blood_type = $user_type = "";
        } else {
            $registration_msg = '<div class="alert alert-danger">Something went wrong. Please try again.</div>';
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Registration</h3>
            <?php echo $registration_msg; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="username" placeholder="Enter your full name"
                               class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $username; ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Enter your email"
                               class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="e.g., 03XXXXXXXXX"
                               class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $phone; ?>">
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="Enter your city or address"
                               class="form-control" value="<?php echo $location; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Blood Type</label>
                        <select name="blood_type" class="form-control" required>
                            <option value="">Select Blood Type</option>
                            <?php
                            $types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                            foreach ($types as $type) {
                                $selected = ($blood_type == $type) ? "selected" : "";
                                echo "<option value=\"$type\" $selected>$type</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password"
                               class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Register As</label>
                    <select name="user_type" class="form-control <?php echo (!empty($user_type_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select user type</option>
                        <option value="Blood Donor" <?php echo ($user_type == "Blood Donor") ? 'selected' : ''; ?>>Blood Donor</option>
                        <option value="Blood Recipient" <?php echo ($user_type == "Blood Recipient") ? 'selected' : ''; ?>>Blood Recipient</option>
                        <option value="Hospital Representative" <?php echo ($user_type == "Hospital Representative") ? 'selected' : ''; ?>>Hospital Representative</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $user_type_err; ?></span>
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">Register Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
