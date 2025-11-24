<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include Files
include('layout/header.php');
include('layout/left.php');

// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Add Predicament
if (isset($_POST['submit'])) {
    $farmer_id = $_SESSION['id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = [];
    $photoPath = null;

    // Validate form inputs
    if (empty($title) || empty($description)) {
        $errors[] = 'All fields are required!';
    }

    // Handle image upload (optional)
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
                } else {
                    $errors[] = 'Could not save the uploaded photo.';
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO predicament (farmer_id, title, description, photo_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $farmer_id, $title, $description, $photoPath);
        $result = $stmt->execute();
        if ($result) {
            echo "<script>alert('Predicament Inserted Successfully')</script>";
            header("Location: predicament_table.php");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>',
                showConfirmButton: true,
                confirmButtonText: 'OK',
            });
        </script>
        <?php
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="css/sweetAlert.css">
<link rel="stylesheet" href="css/myfarm.css"> <!--CSS link for form-->
<link rel="stylesheet" href="css/perdicament_form.css">

<div class="container">
    <div id="right">
        <h1>Add Predicament</h1>
        <form action="add_predicament.php" method="post" enctype="multipart/form-data">
            <div class="pre">
                <label for="predicament">Predicament title</label>
                <input type="text" name="title" /><br>
            </div>
            <div class="textbox">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter your predicament description" cols="30" rows="10"></textarea>
            </div>
            <div class="textbox">
                <label for="photo">Attach a photo (optional)</label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <p class="helper">Max 2MB. JPG, PNG, WEBP, or GIF.</p>
            </div>
            <!-- Hidden input field to store the logged-in farmer's ID -->
            <input type="hidden" value="<?php echo $_SESSION['id']; ?>" name="farmerid">
            <input type="submit" value="Add Predicament" name="submit" /><br>
            <a href="predicament_table.php">Back</a>

        </form>
    </div>
</div>

<script src="js/confirmationSA.js"></script>
