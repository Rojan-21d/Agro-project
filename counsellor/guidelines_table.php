<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include Files
include('../counsellor/layout/header.php');
include('../counsellor/layout/sidebar.php');
// include('layout/left.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Delete record
if (isset($_POST['delete'])) {
    $id = $_POST['gid'];
    $sql = "DELETE FROM guidelines WHERE gid = '$id'";
    $result = $conn->query($sql);
    if ($result) {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'success',title:'Deleted',text:'Guideline removed successfully'});}else{Swal.fire({icon:'success',title:'Deleted',text:'Guideline removed successfully'});}});</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'error',title:'Error',text:'Could not delete guideline. Please try again.'});}else{Swal.fire({icon:'error',title:'Error',text:'Could not delete guideline. Please try again.'});}});</script>";
    }
}

// Fetch Guidelines
if (isset($_SESSION['id'])) { // Check if $_SESSION['id'] is set
    $stmt = $conn->prepare("SELECT g.gid, g.title AS guideline_title, g.description, g.submitted_date, f.name AS farmer_name, p.title AS predicament_title FROM farmer f INNER JOIN predicament p ON f.id = p.farmer_id INNER JOIN guidelines g ON p.pid = g.predicament_id WHERE g.counsellor_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
}

function truncateText($text, $limit = 30) {
    $text = trim($text ?? '');
    return (strlen($text) > $limit) ? substr($text, 0, $limit) . '...' : $text;
}
?>

<link rel="stylesheet" href="../css/table.css">
<div class="con">
<?php if (!isset($_POST['edit_guidelines'])) { ?>

    <h1 align="center">Guidelines Details</h1>
    <div class="table-wrapper">
        <!-- <form action="add_guidelines.php" method="post">
            <input type="submit" value="Add" name="add">
        </form> -->
        <table class="fl-table">
            <tbody>
                <tr>
                    <th>SN</th>
                    <th>Farmer</th>
                    <th>Predicament</th>
                    <th>Guideline Title</th>
                    <th>Description</th>
                    <th>Submitted Date</th>
                    <th>Action</th>
                </tr>
                <?php if (isset($result) && $result->num_rows > 0) { // Check if $result is set
                    $i = 1;
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr class="clickable" data-href="guideline_detail.php?gid=<?php echo $row['gid']; ?>">
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['farmer_name']; ?></td>
                            <td><?php echo htmlspecialchars(truncateText($row['predicament_title'])); ?></td>
                            <td><?php echo htmlspecialchars(truncateText($row['guideline_title'])); ?></td>
                            <td><?php echo htmlspecialchars(truncateText($row['description'])); ?></td>
                            <td><?php echo $row['submitted_date']; ?></td>

                            <td class="action-cell">
                                <div class="button-row">
                                    <form method="post" action="edit_guidelines.php">
                                        <input type="hidden" value="<?php echo $row['gid']; ?>" name="gid" />
                                        <input type="submit"  value="Update" name="edit_guidelines" />
                                    </form>

                                    <form method="post" action="guidelines_table.php" onsubmit="confirmDelete(event)">
                                        <input type="hidden" value="<?php echo $row['gid']; ?>" name="gid" />
                                        <input type="hidden" name="delete" value="1" />
                                        <input type="submit" value="Delete" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="8">No Guidelines found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/confirmationSA.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('tr.clickable').forEach(function(row){
            row.addEventListener('click', function(e){
                const target = e.target;
                if (target.closest('form') || target.tagName === 'INPUT' || target.tagName === 'BUTTON' || target.tagName === 'A') {
                    return;
                }
                const href = row.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            });
            row.style.cursor = 'pointer';
        });
    });
</script>
