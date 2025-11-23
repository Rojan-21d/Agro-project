<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$errors = []; // Array to store validation errors

// Checking if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirecting to Login Page
    exit;
}

//Database Connection
$conn=new mysqli("localhost","root", "", "agro_council");
    if($conn->connect_error){
        die("Connection Error".$conn->connect_error);
    }
// Role-specific layout
$userSelects = $_SESSION['usertype'];
if ($userSelects === "counsellor") {
    include 'counsellor/layout/header.php';
    include 'counsellor/layout/sidebar.php';
} else {
    include 'layout/header.php';
    include 'layout/left.php';
}

if ($userSelects == "farmer") {
    $sql = "SELECT * FROM farmer WHERE id = '" . $_SESSION['id'] . "'";
} elseif ($userSelects == "counsellor") {
    $sql = "SELECT * FROM counsellor WHERE id = '" . $_SESSION['id'] . "'";
}

$result = $conn->query($sql);

$row = null;
if ($result && $result->num_rows > 0) {
    // Fetch values in row
    $row = $result->fetch_assoc();
}
?>

<link rel="stylesheet" href="css/view_profile.css">
<div class="box-container">
    <div class="box">
        <h1>Your Profile</h1>
            <!-- <a href="home.php" class="back-button">Back</a> -->
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo isset($row['name']) ? $row['name'] : ''; ?>" readonly>
                <!-- <button type="button" class="edit-button" onclick="enableEdit('name')">Edit</button> -->
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo isset($row['address']) ? $row['address'] : ''; ?>" readonly>
                <!-- <button type="button" class="edit-button" onclick="enableEdit('address')">Edit</button> -->
            </div>
            <div class="form-group">
                <label for="contact">Mobile:</label>
                <input type="text" id="mobile" name="mobile" value="<?php echo isset($row['mobile']) ? $row['mobile'] : ''; ?>" readonly>
                <!-- <button type="button" class="edit-button" onclick="enableEdit('mobile')">Edit</button> -->
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($row['email']) ? $row['email'] : ''; ?>" readonly>
                <!-- <button type="button" class="edit-button" onclick="enableEdit('email')">Edit</button> -->
            </div>
        </form>
        <a href="<?php echo $_SESSION['usertype'] == 'farmer' ? 'home.php' : 'counsellor/view_predicament.php'; ?>" class="back-button">Back</a>
    </div>
</div>
