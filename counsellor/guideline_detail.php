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
require_once __DIR__ . '/../algorithms/guideline_similarity.php';

$conn = null;
require_once __DIR__ . '/../config/db.php';

$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
$row = null;
$similarGuidelines = [];

if ($gid > 0) {
    $stmt = $conn->prepare("SELECT g.title, g.description, g.submitted_date, p.title AS predicament_title, f.name AS farmer_name FROM guidelines g INNER JOIN predicament p ON g.predicament_id = p.pid INNER JOIN farmer f ON p.farmer_id = f.id WHERE g.gid = ? AND g.counsellor_id = ?");
    $stmt->bind_param("ii", $gid, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $simStmt = $conn->prepare("SELECT g.gid, g.title, g.description, p.title AS predicament_title, g.submitted_date FROM guidelines g INNER JOIN predicament p ON g.predicament_id = p.pid WHERE g.counsellor_id = ? AND g.gid <> ? LIMIT 50");
        $simStmt->bind_param("ii", $_SESSION['id'], $gid);
        $simStmt->execute();
        $simResult = $simStmt->get_result();
        $candidates = [];
        if ($simResult && $simResult->num_rows > 0) {
            while ($cand = $simResult->fetch_assoc()) {
                $candidates[] = $cand;
            }
        }
        $similarGuidelines = rank_similar_guidelines(
            ['title' => $row['title'], 'description' => $row['description']],
            $candidates,
            3
        );
    }
}
?>

<link rel="stylesheet" href="../css/perdicament_form.css">

<div class="container">
    <div id="right">
        <?php if ($row) { ?>
            <h1><?php echo htmlspecialchars($row['title']); ?></h1>
            <div class="textbox">
                <label>Predicament</label>
                <div class="pillish"><?php echo htmlspecialchars($row['predicament_title']); ?></div>
            </div>
            <div class="textbox">
                <label>Farmer</label>
                <div class="pillish"><?php echo htmlspecialchars($row['farmer_name']); ?></div>
            </div>
            <div class="textbox">
                <label>Description</label>
                <div class="detail-box"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            </div>
            <div class="textbox">
                <label>Submitted Date</label>
                <div class="pillish"><?php echo htmlspecialchars($row['submitted_date']); ?></div>
            </div>
            <?php if (!empty($similarGuidelines)) { ?>
            <div class="textbox">
                <label>Related Guidelines</label>
                <ul class="detail-list">
                    <?php foreach ($similarGuidelines as $guideline) { ?>
                        <li>
                            <a href="guideline_detail.php?gid=<?php echo $guideline['gid']; ?>">
                                <?php echo htmlspecialchars($guideline['title']); ?>
                            </a>
                            <span class="muted">(Similarity: <?php echo $guideline['similarity']; ?>%)</span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } else { ?>
            <div class="textbox">
                <label>Related Guidelines</label>
                <div class="pillish">No related guidelines yet.</div>
            </div>
            <?php } ?>
            <div class="button-row">
                <form method="post" action="edit_guidelines.php">
                    <input type="hidden" name="gid" value="<?php echo $gid; ?>" />
                    <input type="submit" value="Update" name="edit_guidelines" />
                </form>
                <form method="post" action="guidelines_table.php" onsubmit="confirmDelete(event)">
                    <input type="hidden" name="gid" value="<?php echo $gid; ?>" />
                    <input type="hidden" name="delete" value="1" />
                    <input type="submit" value="Delete" />
                </form>
                <a href="guidelines_table.php" class="ghost-btn">Back to Guidelines</a>
            </div>
        <?php } else { ?>
            <h1>No guideline found</h1>
            <div class="button-row">
                <a href="guidelines_table.php">Back</a>
            </div>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/confirmationSA.js"></script>
