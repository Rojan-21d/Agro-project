<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit;
}

include('../counsellor/layout/header.php');
include('../counsellor/layout/sidebar.php');
require_once __DIR__ . '/../algorithms/predicament_priority.php';
require_once __DIR__ . '/../algorithms/counsellor_matching.php';

$conn = null;
require_once __DIR__ . '/../config/db.php';

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$row = null;

// Fetch counsellors for recommendation context
$counsellors = [];
$workloadMap = [];
$specialtySelect = "NULL AS specialty";
$colCheck = $conn->query("SHOW COLUMNS FROM counsellor LIKE 'specialty'");
if ($colCheck && $colCheck->num_rows > 0) {
    $specialtySelect = "specialty";
} else {
    $colCheck = $conn->query("SHOW COLUMNS FROM counsellor LIKE 'speciality'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $specialtySelect = "speciality AS specialty";
    }
}
$cResult = $conn->query("SELECT id, name, address, email, status, {$specialtySelect} FROM counsellor WHERE status = 'Approved'");
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

if ($pid > 0) {
    $stmt = $conn->prepare("SELECT predicament.*, farmer.name as farmer_name, farmer.address as farmer_address, farm.farm_area, farm.farm_unit, farm.farm_type
            FROM predicament 
            INNER JOIN farmer ON predicament.farmer_id = farmer.id
            LEFT JOIN (
                SELECT farmer_id, MAX(fid) as fid, MAX(farm_area) as farm_area, MAX(farm_unit) as farm_unit, MAX(farm_type) as farm_type
                FROM farm
                GROUP BY farmer_id
            ) as farm ON farm.farmer_id = farmer.id
            WHERE predicament.pid = ?");
    $stmt->bind_param("i", $pid);
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
        $ranked = rank_counsellors_for_predicament($counsellors, [
            'title' => $row['title'],
            'description' => $row['description'],
            'farmer_address' => $row['farmer_address'] ?? ''
        ], ['workload' => $workloadMap]);
        $row['recommended_counsellor'] = $ranked[0] ?? null;
    }
}

function buildPhotoUrl($photoPath) {
    if (empty($photoPath)) return null;
    if (strpos($photoPath, 'http') === 0 || $photoPath[0] === '/') {
        return $photoPath;
    }
    return '../' . ltrim($photoPath, '/');
}
?>

<link rel="stylesheet" href="../css/perdicament_form.css">

<div class="container">
    <div id="right">
        <?php if ($row) { 
            $photoUrl = buildPhotoUrl($row['photo_path'] ?? '');
            ?>
            <h1><?php echo htmlspecialchars($row['title']); ?></h1>
            <div class="textbox">
                <label>Farmer</label>
                <div class="pillish"><?php echo htmlspecialchars($row['farmer_name']); ?></div>
            </div>
            <div class="textbox">
                <label>Address</label>
                <div class="pillish"><?php echo htmlspecialchars($row['farmer_address'] ?? ''); ?></div>
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
                <label>Description</label>
                <div class="detail-box"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            </div>
            <div class="textbox">
                <label>Priority Score</label>
                <div class="pillish"><?php echo htmlspecialchars($row['priority_score']); ?></div>
            </div>
            <?php if (!empty($row['recommended_counsellor'])) { ?>
            <div class="textbox">
                <label>Suggested Counsellor</label>
                <div class="pillish">
                    <?php
                        $rc = $row['recommended_counsellor'];
                        $label = htmlspecialchars($rc['name']) . " (" . $rc['match_score'] . ")";
                        $spec = $rc['specialty'] ?? $rc['speciality'] ?? '';
                        if (!empty($spec)) {
                            $label .= " · " . htmlspecialchars($spec);
                        }
                        echo $label;
                    ?>
                </div>
            </div>
            <?php } ?>
            <div class="button-row">
                <form method="post" action="add_guidelines.php">
                    <input type="hidden" value="<?php echo $row['pid']; ?>" name="pid" />
                    <input type="submit" value="Add Guidelines" name="add_guidelines" />
                </form>
                <a href="view_predicament.php" class="ghost-btn">Back to Predicaments</a>
            </div>
        <?php } else { ?>
            <h1>No predicament found</h1>
            <div class="button-row">
                <a href="view_predicament.php">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
