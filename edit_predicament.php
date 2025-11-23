<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include files
include('layout/header.php');
include('layout/left.php');

// Database connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}



// Edit farm 
if (isset($_POST['update'])) {
    $id=intval($_POST['pid']);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stmt = $conn->prepare("UPDATE predicament SET title = ?, description = ? WHERE pid = ? AND farmer_id = ?");
    $stmt->bind_param("ssii", $title, $description, $id, $_SESSION['id']);
    $result=$stmt->execute();
    if($result && $stmt->affected_rows > 0){
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'success', title:'Updated', text:'Predicament updated successfully'}).then(()=>{ window.location.href='predicament_table.php'; });
                } else {
                    Swal.fire({icon:'success', title:'Updated', text:'Predicament updated successfully'}).then(()=>{ window.location.href='predicament_table.php'; });
                }
            });
        </script>";
    }
    else{
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'error', title:'Error', text:'Could not update predicament.'});
                } else {
                    Swal.fire({icon:'error', title:'Error', text:'Could not update predicament.'});
                }
            });
        </script>";
    }
}

// View Predicament details
if (isset($_POST['edit_predicament'])) {
    $id = intval($_POST['pid']);
    $stmt = $conn->prepare("SELECT title, description FROM predicament WHERE pid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $description = $row['description'];
    } else {
        die("Error: Not found or unauthorized.");
    }
}
?>

<!-- Guidelines Form -->
<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Predicament</h1>
        <form action="edit_predicament.php" method="post">
            <div class="pre">
                <label for="predicament">Predicament title</label>
                <input type="text" value="<?php echo $title;?>" name="title" id="title" required/><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter Guidelines" cols="30" rows="10" required><?php echo $description;?></textarea>
            </div>
            <input type="hidden" value="<?php echo $id; ?>" name="pid">
            <input type="submit" value="Update" name="update"/>

            <!-- <input type="submit" value="Add Guidelines" name="add"/><br> -->
            <a href="predicament_table.php">Back</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/confirmationSA.js"></script>
