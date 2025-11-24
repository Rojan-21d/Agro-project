<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/sweetAlert.css">
    <link rel="stylesheet" href="css/myfarm.css"> <!--CSS link for form-->
    <title>Add Form</title>
</head>
<body>


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include File
include('layout/header.php');
include('layout/left.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Add farm when the form is submitted
if (isset($_POST['submit'])) {
    $farmer_id = $_SESSION['id'];
    $farmarea = trim($_POST['farmarea'] ?? '');
    $farmunit = trim($_POST['farmunit'] ?? '');
    $farmtype = trim($_POST['farmtype'] ?? '');

    $errors = [];
  // Check if any of the required fields is empty
    if (empty($farmarea) || empty($farmunit) || empty($farmtype)) {
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
        // Insert farm data into the database with ownership check
        $stmt = $conn->prepare("INSERT INTO farm (farm_area, farm_unit, farm_type, farmer_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $farmarea, $farmunit, $farmtype, $farmer_id);
        $result = $stmt->execute();
        if ($result) {
            ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Successful',
                    text: 'Farm Added!',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                }).then(function(){
                    window.location.href = "home.php";
                });
            </script>

            <?php

        } else {
            ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error adding farm. Please try again.',
                });
            </script>
            <?php
        }
        }
    }

?>


<!-- HTML form for adding a farm -->
    <div class="cont">
        <div id="right">
            <form action="" method="post">
                <h1>Add Farm</h1>
                <div>
                    <label for="farmarea" class="far">Farm Area</label>
                    <input type="text" name="farmarea" /><br>
                    <label for="farmunit">Farm Unit</label>
                    <select name="farmunit" id="farea" >
                        <option value="acre">Acre</option>
                        <option value="biga">Biga</option>
                        <option value="aana">Aana</option>
                        <option value="ropani">Ropani</option>
                    </select>
                </div>
                <div>
                    <label for="farmtype">Farm Type</label>
                    <input type="text" name="farmtype" /><br>
                </div>
                <input type="hidden" value="<?php echo $_SESSION['id']; ?>" name="farmerid">
                <div class="actions-inline">
                    <input type="submit" value="Add Farm" name="submit" />
                    <a class="ghost-btn" href="home.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
</body>
</html>
