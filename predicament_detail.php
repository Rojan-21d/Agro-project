<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include('layout/header.php');
include('layout/left.php');
require_once __DIR__ . '/algorithms/predicament_priority.php';

$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$row = null;

if ($pid > 0 && isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT p.*, farm.farm_area, farm.farm_unit, farm.farm_type
            FROM predicament p
            LEFT JOIN (
                SELECT farmer_id, MAX(fid) as fid, MAX(farm_area) as farm_area, MAX(farm_unit) as farm_unit, MAX(farm_type) as farm_type
                FROM farm
                GROUP BY farmer_id
            ) as farm ON farm.farmer_id = p.farmer_id
            WHERE p.pid = ? AND p.farmer_id = ?");
    $stmt->bind_param("ii", $pid, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $scoreData = score_predicament_priority(
            ['title' => $row['title'], 'description' => $row['description']],
            ['farm_area' => $row['farm_area'], 'farm_unit' => $row['farm_unit'], 'farm_type' => $row['farm_type']]
        );
        $row['priority_score'] = $scoreData['score'];
        $row['priority_reasons'] = $scoreData['reasons'];
    }
}

function buildPhotoUrl($photoPath) {
    if (empty($photoPath)) return null;
    if (strpos($photoPath, 'http') === 0 || $photoPath[0] === '/') {
        return $photoPath;
    }
    return ltrim($photoPath, '/');
}
?>

<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <?php if ($row) { 
            $photoUrl = buildPhotoUrl($row['photo_path'] ?? '');
            ?>
            <h1><?php echo htmlspecialchars($row['title']); ?></h1>
            <div class="textbox">
                <label>Description</label>
                <div class="detail-box"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            </div>
            <div class="textbox">
                <label>Photo</label>
                <?php if ($photoUrl) { ?>
                    <div class="preview">
                        <a href="<?php echo htmlspecialchars($photoUrl); ?>" target="_blank" rel="noopener">
                            <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Predicament photo" class="photo-thumb">
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="pillish">No photo provided</div>
                <?php } ?>
            </div>
            <div class="textbox">
                <label>Submitted Date</label>
                <div class="pillish"><?php echo htmlspecialchars($row['submitted_date']); ?></div>
            </div>
            <div class="textbox">
                <label>Priority Score</label>
                <div class="pillish"><?php echo htmlspecialchars($row['priority_score']); ?></div>
            </div>
            <?php if (!empty($row['priority_reasons'])) { ?>
            <div class="textbox">
                <label>What affects this score?</label>
                <ul class="detail-list">
                    <?php foreach ($row['priority_reasons'] as $reason) { ?>
                        <li><?php echo htmlspecialchars($reason); ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
            <div class="textbox">
                <label>Farm Context</label>
                <div class="pillish">
                    <?php
                        $parts = array_filter([
                            !empty($row['farm_area']) ? $row['farm_area'] . ' ' . ($row['farm_unit'] ?? '') : null,
                            !empty($row['farm_type']) ? 'Type: ' . $row['farm_type'] : null
                        ]);
                        echo !empty($parts) ? htmlspecialchars(implode(' | ', $parts)) : 'Not provided';
                    ?>
                </div>
            </div>
            <div class="button-row">
                <a href="predicament_table.php" class="ghost-btn">Back to Predicaments</a>
            </div>
        <?php } else { ?>
            <h1>No predicament found</h1>
            <div class="button-row">
                <a href="predicament_table.php">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
