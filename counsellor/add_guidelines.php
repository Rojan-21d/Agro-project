<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include('../counsellor/layout/header.php');
include('../counsellor/layout/sidebar.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

if(isset($_POST['add_guidelines'])) {
    $pid = $_POST['pid'];
}

    // Handle the Add Guidelines form submission
    if (isset($_POST['add'])) {
        $counsellor_id = $_SESSION['id'];
        $title = $_POST['title'];
        $pid = $_POST['pid'];
        echo $pid;
        $description = $_POST['description'];

        // Sanitize inputs
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);

        $sql = "INSERT INTO guidelines (counsellor_id, title, predicament_id, description) VALUES ('$counsellor_id', '$title', '$pid', '$description')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Guidelines Inserted Successfully')</script>";
            // Redirect back to view_predicament.php
            header("Location: view_predicament.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
?>
<!-- Guidelines Form -->
<link rel="stylesheet" href="../css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Guidelines</h1>
        <form action="" method="post">
            <div class="pre">
                <label for="guidelines">Guidelines title</label>
                <input type="text" name="title" required/><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter Guidelines" cols="30" rows="10" required></textarea>
            </div>
            <input type="hidden" value="<?php echo $_SESSION['id']; ?>" name="counsellorid">
            <input type="hidden" value="<?php echo $pid; ?>" name="pid" />

            <div class="button-row actions-inline">
                <input type="submit" value="Add Guidelines" name="add"/>
                <a class="ghost-btn" href="view_predicament.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
