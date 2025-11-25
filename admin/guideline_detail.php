<?php
session_start();
if (!isset($_SESSION['adminname'])) {
    header('location: adminlogin.php');
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
$row = null;

if (isset($_POST['delete']) && $gid > 0) {
    $del = $conn->prepare("DELETE FROM guidelines WHERE gid = ?");
    $del->bind_param("i", $gid);
    if ($del->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'success', title:'Deleted', text:'Guideline removed.'}).then(()=>{ window.location.href='adminpanel.php?selected=guidelines'; });
                } else {
                    Swal.fire({icon:'success', title:'Deleted', text:'Guideline removed.'}).then(()=>{ window.location.href='adminpanel.php?selected=guidelines'; });
                }
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.fireThemed) {
                    fireThemed({icon:'error', title:'Error', text:'Could not delete guideline.'});
                } else {
                    Swal.fire({icon:'error', title:'Error', text:'Could not delete guideline.'});
                }
            });
        </script>";
    }
}

if ($gid > 0) {
    $stmt = $conn->prepare("SELECT g.gid, g.title, g.description, g.submitted_date, p.title AS predicament_title, f.name AS farmer_name, c.name AS counsellor_name
            FROM guidelines g
            INNER JOIN predicament p ON g.predicament_id = p.pid
            LEFT JOIN farmer f ON p.farmer_id = f.id
            LEFT JOIN counsellor c ON g.counsellor_id = c.id
            WHERE g.gid = ?");
    $stmt->bind_param("i", $gid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}
?>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/perdicament_form.css">
<link rel="stylesheet" href="../css/header.css">
<link rel="stylesheet" href="../css/sweetAlert.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/confirmationSA.js"></script>

<header>
    <nav class="adm-header">
        <a href="adminpanel.php">
            <img class="logo" src="../img/6835119.png" alt="agrocouncil">
        </a>
        <p align="center">Admin Panel</p>
        <a href="adminlogout.php">Logout</a>
    </nav>
</header>

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
                <div class="pillish"><?php echo htmlspecialchars($row['farmer_name'] ?? ''); ?></div>
            </div>
            <div class="textbox">
                <label>Counsellor</label>
                <div class="pillish"><?php echo htmlspecialchars($row['counsellor_name'] ?? ''); ?></div>
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
                <form method="post" action="adminpanel.php?selected=guidelines">
                    <input type="hidden" name="guidelines" value="1" />
                    <button type="submit" class="ghost-btn">Back to Guidelines</button>
                </form>
                <form method="post" action="edit_guidelines.php">
                    <input type="hidden" name="gid" value="<?php echo $gid; ?>" />
                    <input type="submit" value="Update" name="edit_guidelines" />
                </form>
                <form method="post" action="guideline_detail.php?gid=<?php echo $gid; ?>" onsubmit="confirmDelete(event)">
                    <input type="hidden" name="delete" value="1" />
                    <input type="submit" value="Delete" />
                </form>
            </div>
        <?php } else { ?>
            <h1>No guideline found</h1>
            <div class="button-row">
                <a href="adminpanel.php?selected=guidelines" class="ghost-btn">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
