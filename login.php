<?php
// include('layout/header.php');
//include('layout/left.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Check if the user is already logged in
if (isset($_SESSION['email'])) {
    header("location: home.php");
    exit;
}

if (isset($_POST['login'])) {
    // Get user username or password from the form
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $userselects = $_POST['userselects'];

    // Database Connection
    require_once __DIR__ . '/config/db.php';

    // Use prepared statements to prevent SQL injection
    $sql = "";
    if ($userselects == "farmer") {
        $sql = "SELECT * FROM farmer WHERE email = ?";
    } elseif ($userselects == "counsellor") {
        $sql = "SELECT * FROM counsellor WHERE email = ?";
    }

    // Prepares the SQL statement, binds the email parameter, executes the statement, and retrieves the result.
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If there is at least one row in the result, fetches the associative array representing the user data and retrieves the hashed password from the database.
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedpassword = $row['password'];

        if (password_verify($password, $hashedpassword)) {
            // Check if the counsellor is approved
            if ($userselects == "counsellor" && $row['status'] !== 'Approved') {
                // Display message and prevent login
                // echo "Wait for admin approval. You cannot log in at the moment.";
                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "Wait",
                        title: "Please Wait!",
                        text: "Wait for admin approval. You cannot log in at the moment.",
                        showCloseButton: true,
                    });
                });
            </script>';
            } else {

                // Password matches, store user details in session
                $_SESSION['id'] = $row['id'];
                $_SESSION['email'] = $email;
                $_SESSION['usertype'] = $userselects;

                if ($userselects == "farmer") {
                    header("location: home.php");
                } elseif ($userselects == "counsellor") {
                    header("location: counsellor/view_predicament.php");
                }
                exit;
            }
        } else {
            header("Location: login.php?error=1");  //If the password verification fails, redirects to the login page with an error parameter.
            exit;
        }
    } else {
        header("Location: login.php?error=1");      //If no user is found with the provided email, redirects to the login page with an error parameter
        exit;
    }
}
?>


<!--------- HTML --------->

<link rel="stylesheet" href="css/login.css">

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-header">
            <p class="eyebrow">Sign in</p>
            <h1>Welcome back</h1>
            <p class="helper">Access your AgroCouncil dashboard as a farmer or counsellor.</p>
        </div>

        <?php if (isset($_GET['error'])) { ?>
        <div class="alert error">
            Email or password is invalid.
        </div>
        <?php } ?>

        <form method="post" action="login.php" autocomplete="off" class="auth-form">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="user-selects pill-group">
                <div class="farmer-part">  
                    <input type="radio" name="userselects" id="farmer" value="farmer" checked>
                    <label for="farmer">Farmer</label>
                </div>
                <div class="counsellor-part">
                    <input type="radio" name="userselects" id="counsellor" value="counsellor">
                    <label for="counsellor">Counsellor</label>
                </div> 
            </div>

            <div class="links-row">
                <a class="muted-link" href="forgetpassword/forgetpwd.php">Forgot password?</a>
            </div>

            <div class="button-group">
                <button type="submit" name="login" value="login">Sign in</button>
            </div>
            <p class="helper center">Don’t have an account? <a href="registration.php">Create one</a></p>
        </form>
    </div>
</div>


