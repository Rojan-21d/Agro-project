<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include files
include('../counsellor/layout/header.php');
include('../counsellor/layout/sidebar.php');

// Database connection
$conn = null;
require_once __DIR__ . '/../config/db.php';

// Fetch farm details by joining 'farm' and 'farmer' tables based on 'farmer_id'
$sql = "SELECT * FROM farm INNER JOIN farmer ON farm.farmer_id = farmer.id";
$result = $conn->query($sql);
?>
<!-- WHERE farmer_id = '" . $_SESSION['id'] . "' -->
<link rel="stylesheet" href="../css/table.css"> <!-- CSS link for table -->
<div class="con">
    <h1>Farm Details</h1>
    <div class="table-wrapper">
        <table class="fl-table">
            <tbody>
                <tr>
                    <th width=5%>SN</th>
                    <th width=25%>Farmer Name</th>
                    <th width=13%>Farm Area</th>
                    <th width=10%>Farm Unit</th>
                    <th width=20%>Farm Type</th>
                    <!-- <th>Action</th> -->
                </tr>
                <?php
                if ($result && $result->num_rows > 0) {
                    $i = 1;
                    // Loop through each row of the result set
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['farm_area']; ?></td>
                            <td><?php echo $row['farm_unit']; ?></td>
                            <td><?php echo $row['farm_type']; ?></td>
                            <!-- <td>
                                <form method="post" action="myfarm_edit.php">
                                    <input type="hidden" value="<?php //echo $row['fid']; ?>" name="fid" />
                                    <input type="submit" value="Edit" name="edit" />
                                </form>
                                <form method="post" action="myfarm.php">
                                    <input type="hidden" value="<?php //echo $row['fid']; ?>" name="fid" />
                                    <input type="submit" value="Delete" name="delete" />
                                </form>
                            </td> -->
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6">No farms found.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
