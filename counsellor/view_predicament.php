<?php

// Include Files
include('../counsellor/layout/header.php');
session_start();
if(!isset($_SESSION['email']))
{
   header("location:login.php");
}
// $name=$row['name'];
$email=$_SESSION['email'];
include('../counsellor/layout/sidebar.php');
require_once __DIR__ . '/../algorithms/predicament_priority.php';
require_once __DIR__ . '/../algorithms/counsellor_matching.php';
?>


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();


// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Fetch counsellors and workload snapshot for matching
$counsellors = [];
$workloadMap = [];
$cResult = $conn->query("SELECT id, name, address, email, status FROM counsellor WHERE status = 'Approved'");
if ($cResult && $cResult->num_rows > 0) {
    while ($cRow = $cResult->fetch_assoc()) {
        $counsellors[] = $cRow;
    }
}
$wResult = $conn->query("SELECT counsellor_id, COUNT(*) as total FROM guidelines GROUP BY counsellor_id");
if ($wResult && $wResult->num_rows > 0) {
    while ($wRow = $wResult->fetch_assoc()) {
        $workloadMap[$wRow['counsellor_id']] = (int)$wRow['total'];
    }
}

//Add Guidelines
if(isset($_POST['submit'])){
    $pid = $_POST['pid'];

    // Add Guidelines
    if (isset($_POST['add'])) {
        $counsellor_id = $_POST['counsellorid'];
        $title = $_POST['title'];
        $description = $_POST['description'];

        // Sanitize inputs
        $title = $conn->real_escape_string($title);
        $description = $conn->real_escape_string($description);

        $sql = "INSERT INTO guidelines (counsellor_id, title, predicament_id, description) VALUES ('$counsellor_id', '$title', '$pid', '$description')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Guidelines Inserted Successfully')</script>";
            header("Location: view_predicament.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}


// Fetch Predicament
$predicaments = [];
if (isset($_SESSION['id'])) { // Check if $_SESSION['id'] is set
    $sql = "SELECT predicament.*, farmer.name as farmer_name, farmer.address as farmer_address, farm.farm_area, farm.farm_unit, farm.farm_type
            FROM predicament 
            INNER JOIN farmer ON predicament.farmer_id = farmer.id
            LEFT JOIN (
                SELECT farmer_id, MAX(fid) as fid, MAX(farm_area) as farm_area, MAX(farm_unit) as farm_unit, MAX(farm_type) as farm_type
                FROM farm
                GROUP BY farmer_id
            ) as farm ON farm.farmer_id = farmer.id";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $scoreData = score_predicament_priority(
                ['title' => $row['title'], 'description' => $row['description']],
                ['farm_area' => $row['farm_area'], 'farm_unit' => $row['farm_unit'], 'farm_type' => $row['farm_type']]
            );
            $row['priority_score'] = $scoreData['score'];
            $row['priority_reasons'] = $scoreData['reasons'];
            // Recommend the best counsellor for this predicament
            $ranked = rank_counsellors_for_predicament($counsellors, [
                'title' => $row['title'],
                'description' => $row['description'],
                'farmer_address' => $row['farmer_address'] ?? ''
            ], ['workload' => $workloadMap]);
            $row['recommended_counsellor'] = $ranked[0] ?? null;
            $predicaments[] = $row;
        }
        usort($predicaments, function ($a, $b) {
            return $b['priority_score'] <=> $a['priority_score'];
        });
    }
}
?>
<!-- WHERE farmer_id = '" . $_SESSION['id'] . "' -->
<link rel="stylesheet" href="../css/table.css">
<div class="con">
    <h1>Predicament Details</h1>
    <div class="table-wrapper">
        <!-- <form action="add_guidelines.php" method="post">
            <input type="submit" value="Add Guidelines" name="add">
        </form> -->
        <table class="fl-table">
            <tbody>
                <tr>
                    <th width=10% >SN</th>
                    <th width=20% >Farmer Name</th>
                    <th width=20% >Title</th>
                    <th width=30% >Description</th>
                    <th width=10% >Priority</th>
                    <th width=15% >Suggested Counsellor</th>
                    <th width=10% >Action</th>
                </tr>
                <?php if (!empty($predicaments)) { // Check if $result is set
                    $i = 1;
                    foreach ($predicaments as $row) { 
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['farmer_name']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['priority_score']; ?></td>
                            <td>
                                <?php
                                if (!empty($row['recommended_counsellor'])) {
                                    echo htmlspecialchars($row['recommended_counsellor']['name']) . " (" . $row['recommended_counsellor']['match_score'] . ")";
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                            <!-- <form method="post" action="../counsellor/add_guidelines.php">
                                <input type="hidden" value="<?php //echo $row['pid']; ?>" name="pid" />
                                <input type="submit" value="Update" name="edit_guidelines" />
                            </form> -->

                            <form method="post" action="add_guidelines.php">
                                <input type="hidden" value="<?php echo $row['pid']; ?>" name="pid" />
                                <input type="submit" value="Add Guidelines" name="add_guidelines" />
                            </form>

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
