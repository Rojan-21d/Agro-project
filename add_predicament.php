<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include Files
include('layout/header.php');
include('layout/left.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Add Predicament
if (isset($_POST['submit'])) {
    $farmer_id = $_SESSION['id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = [];

    // Validate form inputs
    if (empty($title) || empty($description)) {
        ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'All fields are required!',
                showConfirmButton: true,
                confirmButtonText: 'OK',
            });
        </script>
        <?php
    } else {

    $stmt = $conn->prepare("INSERT INTO predicament (farmer_id, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $farmer_id, $title, $description);
    $result = $stmt->execute();
    if ($result) {
        echo "<script>alert('Predicament Inserted Successfully')</script>";
        header("Location: predicament_table.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="css/sweetAlert.css">
<link rel="stylesheet" href="css/myfarm.css"> <!--CSS link for form-->
<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Predicament</h1>
        <form action="add_predicament.php" method="post">
            <div class="pre">
                <label for="predicament">Predicament title</label>
                <input type="text" name="title" /><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter your predicament description" cols="30" rows="10"></textarea>
            </div>
            <!-- Hidden input field to store the logged-in farmer's ID -->
            <input type="hidden" value="<?php echo $_SESSION['id']; ?>" name="farmerid">
            <input type="submit" value="Add Predicament" name="submit" /><br>
            <a href="predicament_table.php">Back</a>

        </form>
    </div>
</div>

<script src="js/confirmationSA.js"></script>
