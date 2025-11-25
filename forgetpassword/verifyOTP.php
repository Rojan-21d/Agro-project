<?php
if(isset($_POST["verify"])){
    $otp = $_POST["otp"];

    $otp_hash = hash("sha256", $otp); // This should be $token, not $otp

    // Database connection
    require_once __DIR__ . '/../config/db.php';
    if ($conn->connect_error) {
        die("Connection Error" . $conn->connect_error);
    }

    session_start();
    $userSelects = $_SESSION['userSelects'];

    $table = ($userSelects === "farmer") ? "farmer" : "counsellor";
    $sql = "SELECT * FROM $table WHERE reset_otp_hash = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $otp_hash); // Binding

    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user === null) {
        $error_message = "Invalid OTP!"; // Set an error message for invalid OTP
    }

    if ($user !== null && strtotime($user["reset_otp_expires_at"]) <= time()) {
        $error_message = "OTP Expired!"; // Set an error message for expired OTP
    }

    if (!isset($error_message)) {
        // After verifying the OTP and before redirection
        $_SESSION['otp'] = $otp; // Store the verified OTP in a session variable

        header ('location: reset_pwd.php'); // This will cause a redirection
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>OTP Verification</title>
</head>
<body>
    <div class="auth-shell"> 
        <div class="auth-card">
            <div class="auth-header">
                <p class="eyebrow">Verify code</p>
                <h1>Enter your OTP</h1>
                <p class="helper">We sent a 6-digit code to your email. It expires in 30 minutes.</p>
            </div>
            <form action="" method="post" class="auth-form">
                <div class="field">
                    <label for="otp" class="labelll">One-time code</label>
                    <input type="text" maxlength="6" inputmode="numeric" placeholder="••••••" name="otp" id="otp" required style="padding-left: 15px;">
                </div>
                    <?php if (isset($error_message)) : ?>
                        <div class="alert error-message" id="errorOTP">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>                
                    <div class="button-group">
                        <?php if (isset($user) && strtotime($user["reset_otp_expires_at"]) <= time()) : ?>
                            <button type="button" id="resendOTPButton">Resend code</button>
                        <?php else : ?>
                            <button type="submit" name="verify">Verify</button>
                        <?php endif; ?>
                    </div>
                <p class="helper center"><a href="../login.php">Back to login</a></p>
            </form>
        </div>
    </div>
    <script>
        <?php if (isset($user) && strtotime($user["reset_otp_expires_at"]) <= time()) : ?>
            document.getElementById('resendOTPButton').addEventListener('click', function() {
                window.location.href = 'send_resetpwd.php';
            });
        <?php endif; ?>
    </script>
</body>
</html>
