<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="../css/sweetAlert.css">
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$otp = $_SESSION['otp'];
$otp_hash = hash("sha256", $otp);

// Database connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error" . $conn->connect_error);
}

$userselects = ($_SESSION['userSelects'] === "farmer") ? "farmer" : "counsellor";

$table = ($userselects === "farmer") ? "farmer" : "counsellor";

$sql = "SELECT * FROM $table WHERE reset_otp_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $otp_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Invalid OTP",
                text: "The OTP Not Found.",
                icon: "error",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "forgetpwd.php";
                }
            });
        });
    </script>';
    exit;
}

if (strtotime($user["reset_otp_expires_at"]) <= time()) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "OTP Expired",
                text: "The OTP has expired.",
                icon: "error",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "forgetpwd.php";
                }
            });
        });
    </script>';
    exit;
}

$errors = [];

if (isset($_POST["verify"])) {
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];
    
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = "Password must be between 8 and 16 characters";
    }
    
    if ($password !== $password_confirmation) {
        $errors[] = 'Passwords do not match!';
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "UPDATE $table 
                SET password = ?,
                    reset_otp_hash = NULL,
                    reset_otp_expires_at = NULL
                WHERE id = ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user['id']);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Success",
                        text: "Password Changed",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../login.php";
                        }
                    });
                });
            </script>';
        } else {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Error",
                        text: "Password Change Failed",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
            </script>';
        }
    }
}


// Display errors using SweetAlert
if (!empty($errors)) {
    $errorMessages = join("<br>", $errors);
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Password Change Errors",
            html: "'.$errorMessages.'",
            showCloseButton: true,
        });
    </script>';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>Reset Password</title>
</head>
<body>
    <div class="auth-shell"> 
        <div class="auth-card">
            <div class="auth-header">
                <p class="eyebrow">Set new password</p>
                <h1>Choose a strong password</h1>
                <p class="helper">Use 8-16 characters. This will replace your old password.</p>
            </div>
            <form action="" method="post" class="auth-form">    
                <div class="field" >
                    <label for="password">New password</label>
                    <input type="password" placeholder="Enter new password" name="password" id="password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" placeholder="Confirm your password" name="password_confirmation" id="password_confirmation" required>
                </div>            
                <div class="button-group">
                    <button type="submit" name="verify">Reset password</button>
                </div>        
            </form>
        </div>
    </div>
    <!-- Confirm password validaion  -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector(".login");
            const passwordField = document.getElementById("password");
            const confirmPasswordField = document.getElementById("password_confirmation");

            form.addEventListener("submit", function (event) {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                const errors = [];

                if (password !== confirmPassword) {
                    errors.push("Passwords do not match.");
                }
                if (password.length < 8 || password.length > 16) {
                    errors.push("Password must be between 8 and 16 characters.");
                }

                if (errors.length > 0) {
                    event.preventDefault();
                    const errorMessages = errors.join("<br>");
                    
                    Swal.fire({
                        icon: "error",
                        title: "Password Reset Errors",
                        html: errorMessages,
                        showCloseButton: true,
                    });
                }
            });
        });
    </script>
</body>
</html>
