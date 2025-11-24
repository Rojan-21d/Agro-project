<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include files
include('layout/header.php');
include('layout/left.php');

// Database connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$title = '';
$description = '';
$photoPath = null;



// Edit farm 
if (isset($_POST['update'])) {
    $id=intval($_POST['pid']);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $currentPhoto = $_POST['existing_photo'] ?? null;
    if ($currentPhoto === '') {
        $currentPhoto = null;
    }
    $photoPath = $currentPhoto;
    $errors = [];

    if (empty($title) || empty($description)) {
        $errors[] = 'Title and description are required.';
    }

    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Photo upload failed. Please try again.';
        } elseif (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $errors[] = 'Please upload a JPG, PNG, WEBP, or GIF image.';
        } elseif ($_FILES['photo']['size'] > $maxSize) {
            $errors[] = 'Photo must be smaller than 2MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/predicaments/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $errors[] = 'Could not prepare upload folder.';
            } else {
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $fileName = 'pred_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $photoPath = 'uploads/predicaments/' . $fileName;
                    if ($currentPhoto && file_exists(__DIR__ . '/' . $currentPhoto)) {
                        @unlink(__DIR__ . '/' . $currentPhoto);
                    }
                } else {
                    $errors[] = 'Could not save the uploaded photo.';
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE predicament SET title = ?, description = ?, photo_path = ? WHERE pid = ? AND farmer_id = ?");
        $stmt->bind_param("sssii", $title, $description, $photoPath, $id, $_SESSION['id']);
        $result=$stmt->execute();
        if($result && $stmt->affected_rows > 0){
            echo "<script>
                document.addEventListener('DOMContentLoaded', function(){
                    if (window.fireThemed) {
                        fireThemed({icon:'success', title:'Updated', text:'Predicament updated successfully'}).then(()=>{ window.location.href='predicament_table.php'; });
                    } else {
                        Swal.fire({icon:'success', title:'Updated', text:'Predicament updated successfully'}).then(()=>{ window.location.href='predicament_table.php'; });
                    }
                });
            </script>";
        }
        else{
            echo "<script>
                document.addEventListener('DOMContentLoaded', function(){
                    if (window.fireThemed) {
                        fireThemed({icon:'error', title:'Error', text:'Could not update predicament.'});
                    } else {
                        Swal.fire({icon:'error', title:'Error', text:'Could not update predicament.'});
                    }
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                const msg = '" . implode("<br>", array_map('htmlspecialchars', $errors)) . "';
                if (window.fireThemed) {
                    fireThemed({icon:'error', title:'Error', html: msg});
                } else {
                    Swal.fire({icon:'error', title:'Error', html: msg});
                }
            });
        </script>";
        // keep values on the form
        $photoPath = $currentPhoto;
    }
}

// View Predicament details
if (isset($_POST['edit_predicament'])) {
    $id = intval($_POST['pid']);
    $stmt = $conn->prepare("SELECT title, description, photo_path FROM predicament WHERE pid = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $description = $row['description'];
        $photoPath = $row['photo_path'] ?? null;
    } else {
        die("Error: Not found or unauthorized.");
    }
}
?>

<!-- Guidelines Form -->
<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Predicament</h1>
        <form action="edit_predicament.php" method="post" enctype="multipart/form-data">
            <div class="pre">
                <label for="predicament">Predicament title</label>
                <input type="text" value="<?php echo $title;?>" name="title" id="title" required/><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter Guidelines" cols="30" rows="10" required><?php echo $description;?></textarea>
            </div>
            <div class="textbox">
                <label for="photo">Replace/Add photo</label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <p class="helper">Leave empty to keep the current photo.</p>
                <?php if (!empty($photoPath)) { ?>
                    <div class="preview">
                        <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Current photo" class="photo-thumb">
                    </div>
                <?php } ?>
            </div>
            <input type="hidden" value="<?php echo $id; ?>" name="pid">
            <input type="hidden" value="<?php echo htmlspecialchars($photoPath ?? ''); ?>" name="existing_photo">
            <input type="submit" value="Update" name="update"/>

            <!-- <input type="submit" value="Add Guidelines" name="add"/><br> -->
            <a href="predicament_table.php">Back</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/confirmationSA.js"></script>
