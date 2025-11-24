<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include('layout/header.php');
include('layout/sidebar.php');

// Database connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}



// View guidelines details
if (isset($_POST['update'])) {
    $id=$_POST['gid'];
    $title = $_POST['title'];
    $description = $_POST['description'];
// Use a WHERE clause to uniquely identify the guideline by its primary key
$sql = "UPDATE guidelines SET title='$title', description='$description' WHERE gid = '$id'";
$result=$conn->query($sql);
    if($result){
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'success', title:'Updated', text:'Guideline updated successfully'}).then(()=>{ window.location.href='guidelines_table.php'; });
                } else {
                    Swal.fire({icon:'success', title:'Updated', text:'Guideline updated successfully'}).then(()=>{ window.location.href='guidelines_table.php'; });
                }
            });
        </script>";
    }
    else{
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'error', title:'Error', text:'Could not update guideline.'});
                } else {
                    Swal.fire({icon:'error', title:'Error', text:'Could not update guideline.'});
                }
            });
        </script>";
    }
}

// Edit farm
if (isset($_POST['edit_guidelines'])) {
    $id = $conn->real_escape_string($_POST['gid']);
    $sql = "SELECT title, description FROM guidelines WHERE gid='$id'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $description = $row['description'];
    } else {
        die("Error: " . $conn->error);
    }
}

?>

<!-- Guidelines Form -->
<link rel="stylesheet" href="../css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Guidelines</h1>
        <form action="edit_guidelines.php" method="post">
            <div class="pre">
                <label for="guidelines">Guidelines title</label>
                <input type="text" value="<?php echo $title;?>" name="title" id="title" required/><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter Guidelines" cols="30" rows="10" required><?php echo $description;?></textarea>
            </div>
            <input type="hidden" value="<?php echo $id; ?>" name="gid">
            <input type="submit" value="Update" name="update"/>

            <!-- <input type="submit" value="Add Guidelines" name="add"/><br> -->
            <a href="guidelines_table.php">Back</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/confirmationSA.js"></script>
