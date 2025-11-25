<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include files
include('layout/header.php');
include('layout/left.php');

// Database connection
require_once __DIR__ . '/config/db.php';

// Edit farm
if (isset($_POST['edit_farm'])) {
    $farmarea = trim($_POST['farmarea'] ?? '');
    $farmunit = trim($_POST['farmunit'] ?? '');
    $farmtype = trim($_POST['farmtype'] ?? '');
    $id = intval($_POST['fid']);

    $stmt = $conn->prepare("UPDATE farm SET farm_area = ?, farm_unit = ?, farm_type = ? WHERE fid = ? AND farmer_id = ?");
    $stmt->bind_param("sssii", $farmarea, $farmunit, $farmtype, $id, $_SESSION['id']);
    $result = $stmt->execute();
    if ($result && $stmt->affected_rows > 0) {
        header("Location: home.php");
        exit;
    } else {
        die("Error updating farm or unauthorized access.");
    }
}

// View farm details
if (isset($_POST['edit'])) {
    $id = intval($_POST['fid']);
    $stmt = $conn->prepare("SELECT * FROM farm WHERE fid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $farmarea = $row['farm_area'];
        $farmunit = $row['farm_unit'];
        $farmtype = $row['farm_type'];
    } else {
        die("No farm found with the given ID.");
    }
}
?>

<link rel="stylesheet" href="css/myfarm.css">

<link rel="stylesheet" href="css/myfarm.css">
<div class="cont">
    <div id="right">
        <form action="myfarm_edit.php" method="post">
            <h1>Edit Farm</h1>
            <div>
                <label for="farmarea" class="far">Farm Area</label>
                <input type="text" name="farmarea" value="<?php echo $farmarea; ?>" required/><br>
                <label for="framunit">Farm Unit</label>
                <select name="farmunit" id="farea" required>
                    <?php
                    $units = ['acre' => 'Acre', 'acers' => 'Acers', 'biga' => 'Biga', 'aana' => 'Aana', 'ropani' => 'Ropani'];
                    foreach ($units as $value => $label) {
                        $selected = ($farmunit === $value) ? 'selected' : '';
                        echo "<option value=\"$value\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="farmtype">Farm Type</label>
                <input type="text" name="farmtype" value="<?php echo $farmtype; ?>" required/><br>
            </div>
            <input type="hidden" value="<?php echo $id; ?>" name="fid">
            <div class="actions-inline">
                <input type="submit" value="Update" name="edit_farm" />
                <a class="ghost-btn" href="home.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
