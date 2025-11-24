<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include Files
include('layout/header.php');
include('layout/left.php');
require_once __DIR__ . '/algorithms/predicament_priority.php';
$predicaments = [];

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Delete record
if (isset($_POST['delete'])) {
    $id = intval($_POST['pid']);
    $photoPath = null;

    $photoStmt = $conn->prepare("SELECT photo_path FROM predicament WHERE pid = ? AND farmer_id = ?");
    $photoStmt->bind_param("ii", $id, $_SESSION['id']);
    $photoStmt->execute();
    $photoRes = $photoStmt->get_result();
    if ($photoRes && $photoRes->num_rows > 0) {
        $photoRow = $photoRes->fetch_assoc();
        $photoPath = $photoRow['photo_path'] ?? null;
    }

    $stmt = $conn->prepare("DELETE FROM predicament WHERE pid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $result = $stmt->execute();
    if ($result) {
        if ($photoPath && file_exists(__DIR__ . '/' . $photoPath)) {
            @unlink(__DIR__ . '/' . $photoPath);
        }
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'success',title:'Deleted',text:'Predicament removed successfully'});}else{Swal.fire({icon:'success',title:'Deleted',text:'Predicament removed successfully'});}});</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded',function(){if(window.fireThemed){fireThemed({icon:'error',title:'Error',text:'Could not delete predicament. Please try again.'});}else{Swal.fire({icon:'error',title:'Error',text:'Could not delete predicament. Please try again.'});}});</script>";
    }
}

// Fetch Predicament
if (isset($_SESSION['id'])) { // Check if $_SESSION['id'] is set
    $stmt = $conn->prepare("SELECT predicament.*, farm.farm_area, farm.farm_unit, farm.farm_type 
                            FROM predicament 
                            LEFT JOIN (
                                SELECT farmer_id, MAX(fid) as fid, MAX(farm_area) as farm_area, MAX(farm_unit) as farm_unit, MAX(farm_type) as farm_type
                                FROM farm
                                GROUP BY farmer_id
                            ) as farm ON predicament.farmer_id = farm.farmer_id 
                            WHERE predicament.farmer_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $predicaments = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $scoreData = score_predicament_priority(
                ['title' => $row['title'], 'description' => $row['description']],
                ['farm_area' => $row['farm_area'], 'farm_unit' => $row['farm_unit'], 'farm_type' => $row['farm_type']]
            );
            $row['priority_score'] = $scoreData['score'];
            $predicaments[] = $row;
        }
        usort($predicaments, function ($a, $b) {
            return $b['priority_score'] <=> $a['priority_score'];
        });
    }
}
?>

<link rel="stylesheet" href="css/table.css">
<div class="con">
    <h1>Predicament Details</h1>
    <div class="table-wrapper">

        <table class="fl-table">
            <tbody>
                <tr>
                    <th width=8%>SN</th>
                    <th width=22%>Title</th>
                    <th width=14%>Photo</th>
                    <th width=28%>Description</th>
                    <th width=12%>Submitted Date</th>
                    <th width=8%>Priority</th>
                    <th width=18%>Action</th>
                </tr>
                <?php if (!empty($predicaments)) { // Check if $result is set
                    $i = 1;
                    foreach ($predicaments as $row) { ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td>
                                <?php if (!empty($row['photo_path'])) { ?>
                                    <a href="<?php echo htmlspecialchars($row['photo_path']); ?>" target="_blank" rel="noopener">
                                        <img src="<?php echo htmlspecialchars($row['photo_path']); ?>" alt="Predicament photo" class="photo-thumb">
                                    </a>
                                <?php } else { ?>
                                    &mdash;
                                <?php } ?>
                            </td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['submitted_date']; ?></td>
                            <td><?php echo $row['priority_score']; ?></td>

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
