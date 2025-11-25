<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include Files
include('layout/header.php');
include('layout/left.php');

// Database Connection
require_once __DIR__ . '/config/db.php';

// Fetch Guidelines for the logged-in farmer based on their predicaments
if (isset($_SESSION['id'])) { // Check if $_SESSION['id'] is set
    $stmt = $conn->prepare("SELECT g.gid, g.title, g.description, g.submitted_date, p.title AS predicament_title, c.name AS counsellor_name FROM guidelines g INNER JOIN predicament p ON g.predicament_id = p.pid LEFT JOIN counsellor c ON g.counsellor_id = c.id WHERE p.farmer_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<link rel="stylesheet" href="css/table.css">
<div class="con">
    <h1 align="center">Guidelines Details</h1>
    <div class="table-wrapper">
        <!-- <form action="add_guidelines.php" method="post">
            <input type="submit" value="Add" name="add">
        </form> -->
        <table class="fl-table">
            <tbody>
                <tr>
                    <th>SN</th>
                    <th>Predicament</th>
                    <th>Counsellor</th>
                    <th>Guideline Title</th>
                    <th>Description</th>
                    <th>Submitted Date</th>
                </tr>
                <?php if (isset($result) && $result->num_rows > 0) { // Check if $result is set
                    $i = 1;
                    while ($row = $result->fetch_assoc()) { 
                        $shortDesc = strlen($row['description']) > 60 ? substr($row['description'], 0, 60) . '...' : $row['description'];
                        ?>
                        <tr class="clickable" onclick="window.location='guideline_detail.php?gid=<?php echo $row['gid']; ?>'">
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['predicament_title']; ?></td>
                            <td><?php echo $row['counsellor_name'] ?? '—'; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $shortDesc; ?></td>
                            <td><?php echo $row['submitted_date']; ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="6">No Guidelines found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

