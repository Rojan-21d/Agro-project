<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
    // Database connection
    require_once __DIR__ . '/config/db.php';

// submission process
if(isset($_POST['signup'])){
    // get data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $userselects = $_POST['userselects'];
    $specialty = isset($_POST['specialty']) ? trim($_POST['specialty']) : '';
    $table = ($userselects === "farmer") ? "farmer" : "counsellor"; 


    // Validations
    $errors = [];

    // Validate form inputs
    if (empty($name) || empty($email) || empty($address) || empty($mobile) || empty($password)) {
        $errors[] = "All fields are required";
    }
    // Password validation
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = "Password must be between 8 and 16 characters.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Name validation
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $errors[] = "Name should only contain letters and spaces.";
    }

    // Mobile number validation
    if (strlen($mobile) !== 10 || !is_numeric($mobile)) {
        $errors[] = "Mobile number should be numeric and 10 digits only.";
    }

    // Uniqye Key Validation 
    $sql_check_mail = "SELECT * FROM $table WHERE email = '$email'";
    $result_check_mail = $conn->query($sql_check_mail);
    if ($result_check_mail->num_rows > 0){
        $errors[] = "Email Already Registered";
    }

    // Counsellor-only validations
    if ($userselects === "counsellor") {
        if ($specialty === '') {
            $errors[] = "Please choose your specialty.";
        }
    }



    // If there are no errors, proceed with inserting into the database
    if (empty($errors)) {
        // Prepare and execute the SQL query
        if ($userselects === "counsellor") {
            $sql = "INSERT INTO counsellor (name, address, mobile, email, password, specialty) VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "INSERT INTO farmer (name, address, mobile, email, password) VALUES (?, ?, ?, ?, ?)";
        }
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $errors[] = "Error in database connection.";
        } else {
            //Set initial status
            $status='Pending';  
            // Sanitize user inputs
            $name = mysqli_real_escape_string($conn, $name);
            $address = mysqli_real_escape_string($conn, $address);
            $mobile = mysqli_real_escape_string($conn, $mobile);
            $email = mysqli_real_escape_string($conn, $email);
            $password = mysqli_real_escape_string($conn, $password);

            // Hashing password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters and execute
            if ($userselects === "counsellor") {
                $stmt->bind_param("ssssss", $name, $address, $mobile, $email, $hashedPassword, $specialty);
            } else {
                $stmt->bind_param("sssss", $name, $address, $mobile, $email, $hashedPassword);
            }
            if ($stmt->execute()) {
                // Display a success message
                $successMessage = ($userselects === "farmer") ?
                "Your registration as a Farmer is successful." :
                "Your registration as a Counsellor is pending approval. Wait for admin approval before logging in.";

            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "success",
                        title: "Registration Successful",
                        text: "' . $successMessage . '",
                        showCloseButton: true,
                    });
                });
            </script>';
                // header("Location: login.php?success=1");
                // exit;// Make sure to exit after redirection
            } else {
                $errors[] = "An error occurred while processing your request. Please try again later.";
            }
        }
    }

    

   // Display errors using SweetAlert
    if (!empty($errors)) {
        $errorMessages = join("\n", $errors);
        echo '.<script>
        document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: "error",
            title: "Sign Up Errors",
            html: "' . $errorMessages . '",
            showCloseButton: true,
        });
    });

        </script>';
    }
}
?>


<!-- <script src="validation.js"></script> -->
<link rel="stylesheet" href="css/sweetAlert.css">
<link rel="stylesheet" href="css/login.css">

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-header">
            <p class="eyebrow">Create account</p>
            <h1>Join AgroCouncil</h1>
            <p class="helper">Farmers get fast guidance; counsellors share expertise. Choose your role below.</p>
        </div>
        <form method="post" action="registration.php" autocomplete="off" class="auth-form">
            <div class="field">
                <label for="name" class="labell">Name</label>
                <input type="text" id="name" name="name" placeholder="Full name" required>
            </div>
            <div class="field">
                <label for="address" class="labell">Address</label>
                <input type="text" id="address" name="address" placeholder="City / District" required>
            </div>
            <div class="field">
                <label for="mobile" class="labell">Mobile</label>
                <input type="tel" id="mobile" name="mobile" placeholder="10-digit number" required>
            </div>
            <div class="field">
                <label for="email" class="labell">Email</label>
                <input type="email" id="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="field">
                <label for="password" class="labell">Password</label>
                <input type="password" id="password" name="password" placeholder="8-16 characters" required>
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
            <div class="field counsellor-only" style="display:none">
                <label for="specialty" class="labell">Specialty</label>
                <select id="specialty" name="specialty">
                    <option value="">Select your specialty</option>
                    <option value="Crop diseases">Crop diseases</option>
                    <option value="Soil & fertility">Soil &amp; fertility</option>
                    <option value="Irrigation & water">Irrigation &amp; water</option>
                    <option value="Post-harvest & storage">Post-harvest &amp; storage</option>
                    <option value="Livestock / animal health">Livestock / animal health</option>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" name="signup" value="signup">Create account</button>
            </div>
            <p class="helper center">Already registered? <a href="login.php">Sign in</a></p>
        </form>
    </div>
</div>


<?php
// include('layout/footer.php');
?>
<script>
    (function() {
        const farmer = document.getElementById('farmer');
        const counsellor = document.getElementById('counsellor');
        const specialtyBlock = document.querySelector('.counsellor-only');
        function toggleSpecialty() {
            if (!farmer || !counsellor || !specialtyBlock) return;
            specialtyBlock.style.display = counsellor.checked ? 'block' : 'none';
        }
        [farmer, counsellor].forEach(function(el){
            if (el) el.addEventListener('change', toggleSpecialty);
        });
        toggleSpecialty();
    })();
</script>
