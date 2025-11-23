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

// Delete record
if (isset($_POST['delete'])) {
    $id = intval($_POST['pid']);
    $stmt = $conn->prepare("DELETE FROM predicament WHERE pid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $result = $stmt->execute();
    if ($result) {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'success',title:'Deleted',text:'Predicament removed successfully'});}else{Swal.fire({icon:'success',title:'Deleted',text:'Predicament removed successfully'});}});</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'error',title:'Error',text:'Could not delete predicament. Please try again.'});}else{Swal.fire({icon:'error',title:'Error',text:'Could not delete predicament. Please try again.'});}});</script>";
    }
}

// Fetch Predicament
if (isset($_SESSION['id'])) { // Check if $_SESSION['id'] is set
    $stmt = $conn->prepare("SELECT * FROM predicament WHERE farmer_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<link rel="stylesheet" href="css/table.css">
<div class="con">
    <h1>Predicament Details</h1>
    <div class="table-wrapper">

        <table class="fl-table">
            <tbody>
                <tr>
                    <th width=10%>SN</th>
                    <th width=25%>Title</th>
                    <th width=45%>Description</th>
                    <th width=10%>Submitted Date</th>
                    <th width=26%>Action</th>
                </tr>
                <?php if (isset($result) && $result->num_rows > 0) { // Check if $result is set
                    $i = 1;
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['submitted_date']; ?></td>

                            <td class="action-cell">
                                <div class="button-row">
                                    <form method="post" action="edit_predicament.php">
                                        <input type="hidden" value="<?php echo $row['pid']; ?>" name="pid" />
                                        <input type="submit"  value="Update" name="edit_predicament" />
                                    </form>

                                <form method="post" action="predicament_table.php" onsubmit="confirmDelete(event)">
                                    <input type="hidden" value="<?php echo $row['pid']; ?>" name="pid" />
                                    <input type="hidden" name="delete" value="1" />
                                    <input type="submit" value="Delete" />
                                </form>
                                </div>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="6">No Predicament found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/confirmationSA.js"></script>
