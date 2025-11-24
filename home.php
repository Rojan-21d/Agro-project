<?php
include('layout/header.php');

// Check if the user is logged in; redirect to login page if not logged in
session_start();
if(!isset($_SESSION['email']))
{
   header("location:login.php");
}
$email=$_SESSION['email'];

// Set the active page based on the user's login status
// $activePage = $email ? "myfarm.php" : "default"; // Set "myfarm.php" as active if logged in, else set "default"

include('layout/left.php');
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Delete record if the delete form is submitted
if (isset($_POST['delete'])) {
    $id = $_POST['fid'];
    $stmt = $conn->prepare("DELETE FROM farm WHERE fid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $result = $stmt->execute();
    if ($result) {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'success',title:'Deleted',text:'Farm removed successfully'});}else{Swal.fire({icon:'success',title:'Deleted',text:'Farm removed successfully'});}});</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'error',title:'Error',text:'Could not delete farm. Please try again.'});}else{Swal.fire({icon:'error',title:'Error',text:'Could not delete farm. Please try again.'});}});</script>";
    }
}



// Fetch farms for the logged-in user
$result = null;
$stmt = $conn->prepare("SELECT * FROM farm WHERE farmer_id = ?");
$stmt->bind_param("i", $_SESSION['id']);
if ($stmt->execute()) {
    $result = $stmt->get_result();
}
?>


<link rel="stylesheet" href="css/table.css"> <!--CSS link for table-->
<div class="con">
    
        
    <?php if (!isset($_POST['add'])) { ?>
        <h1>Farm Details</h1>
        <div class="table-wrapper">
            <form action="farmform.php" method="post">
                <input type="submit" value="Add Farm" name="add">
            </form>
            <table class="fl-table">
                <tbody>
                    <tr>
                        <th>SN</th>
                        <th>Farm Area</th>
                        <th>Farm Unit</th>
                        <th>Farm Type</th>
                        <th>Action</th>
                    </tr>
                    <?php if ($result && $result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $row['farm_area']; ?></td>
                                <td><?php echo $row['farm_unit']; ?></td>
                                <td><?php echo $row['farm_type']; ?></td>
                                <td class="action-cell">
                                    <div class="button-row inline">
                                        <form method="post" action="myfarm_edit.php">
                                            <input type="hidden" value="<?php echo $row['fid']; ?>" name="fid" />
                                            <input type="submit" value="Edit" name="edit" />
                                        </form>
                                        <form method="post" action="home.php" onsubmit="confirmDelete(event)">
                                            <input type="hidden" value="<?php echo $row['fid']; ?>" name="fid" />
                                            <input type="hidden" name="delete" value="1" />
                                            <input type="submit" value="Delete" />
                                        </form>
                                        <form action="add_predicament.php" method="post">
                                            <input type="submit" value="Add Predicament" name="add">
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6">No farms found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/confirmationSA.js"></script>
