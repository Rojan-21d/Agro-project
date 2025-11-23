<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Include layout
include('layout/header.php');
include('layout/left.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
$row = null;

if ($gid > 0) {
    $stmt = $conn->prepare("SELECT g.title, g.description, g.submitted_date, p.title AS predicament_title, c.name AS counsellor_name FROM guidelines g INNER JOIN predicament p ON g.predicament_id = p.pid LEFT JOIN counsellor c ON g.counsellor_id = c.id WHERE g.gid = ? AND p.farmer_id = ?");
    $stmt->bind_param("ii", $gid, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
}
?>

<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <?php if ($row) { ?>
            <h1><?php echo htmlspecialchars($row['title']); ?></h1>
            <div class="textbox">
                <label>Predicament</label>
                <div class="pillish"><?php echo htmlspecialchars($row['predicament_title']); ?></div>
            </div>
            <div class="textbox">
                <label>Counsellor</label>
                <div class="pillish"><?php echo htmlspecialchars($row['counsellor_name'] ?? '—'); ?></div>
            </div>
            <div class="textbox">
                <label>Description</label>
                <div class="detail-box"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            </div>
            <div class="textbox">
                <label>Submitted Date</label>
                <div class="pillish"><?php echo htmlspecialchars($row['submitted_date']); ?></div>
            </div>
            <div class="button-row">
                <a href="guidelines.php">Back to Guidelines</a>
            </div>
        <?php } else { ?>
            <h1>No guideline found</h1>
            <div class="button-row">
                <a href="guidelines.php">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
