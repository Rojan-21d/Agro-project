<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>    
    <div class="auth-shell"> 
        <div class="auth-card">
            <div class="auth-header">
                <p class="eyebrow">Reset password</p>
                <h1>Get a reset code</h1>
                <p class="helper">We’ll send a one-time code to your email. Pick your role to locate your account.</p>
            </div>
            <form id="forgotPasswordForm" action="send_resetpwd.php" method="post" class="auth-form">
                <div class="field">
                    <label for="email" class="labelll">Email</label>
                    <input type="email" placeholder="Enter your email" name="email" id="email" required>
                </div>    
                <div class="user-selects pill-group">
                    <div class="farmer-part">
                        <input type="radio" id="farmer" name="userselects" value="farmer" checked>
                        <label for="farmer">Farmer</label>
                    </div>
                    <div class="counsellor-part">
                        <input type="radio" id="counsellor" name="userselects" value="counsellor">
                        <label for="counsellor">Counsellor</label>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" name="submit" value="submit">Send code</button>
                </div>
                <p class="helper center"><a href="../login.php">Back to login</a></p>
            </form>
        </div>
    </div>    
</body>
</html>
