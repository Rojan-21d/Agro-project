<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$errors = [];
$successMessage = '';
$errorMessage = '';

// Protect route
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Database
require_once __DIR__ . '/config/db.php';

$userSelects = $_SESSION['usertype'];
// Role-specific layout
if ($userSelects === "counsellor") {
    include 'counsellor/layout/header.php';
    include 'counsellor/layout/sidebar.php';
} else {
    include 'layout/header.php';
    include 'layout/left.php';
}
$table = ($userSelects === "farmer") ? "farmer" : "counsellor";

// Fetch current user
$stmt = $conn->prepare("SELECT name, address, mobile, email FROM $table WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($mobile) || empty($address)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($newPassword) && (strlen($newPassword) < 8 || strlen($newPassword) > 16)) {
        $errors[] = "Password must be between 8 and 16 characters.";
    }

    if (strlen($mobile) !== 10 || !is_numeric($mobile)) {
        $errors[] = "Mobile number should be 10 digits only.";
    }

    if (empty($errors)) {
        $query = "UPDATE $table SET name = ?, address = ?, mobile = ?, email = ?";
        $types = "ssis";
        $params = [$name, $address, $mobile, $email];

        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $types .= "s";
            $params[] = $hashedPassword;
        }

        $query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $_SESSION['id'];

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $_SESSION['address'] = $address;
            $_SESSION['mobile'] = $mobile;
            $_SESSION['email'] = $email;
            $successMessage = "Profile updated successfully.";
            $row = ['name' => $name, 'address' => $address, 'mobile' => $mobile, 'email' => $email];
        } else {
            $errorMessage = "Error updating profile. Please try again.";
        }
    } else {
        $errorMessage = implode("<br>", $errors);
    }
}
?>

<link rel="stylesheet" type="text/css" href="css/profile.css">
<div class="container profile-shell">
    <div class="profile-head">
        <div>
            <p class="eyebrow">Account</p>
            <h1>Your Profile</h1>
            <p class="muted">View and update your details in one place.</p>
        </div>
        <span class="pill"><?php echo ucfirst($userSelects); ?></span>
    </div>

    <form action="" method="POST" class="profile-form" id="profileForm">
        <div class="form-grid">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>" readonly>

            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>" readonly>

            <label for="mobile">Mobile</label>
            <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($row['mobile'] ?? ''); ?>" readonly>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>" readonly>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter new password (optional)">
        </div>

        <div class="actions">
            <button type="button" class="ghost" id="editToggle">Edit</button>
            <button type="submit">Save changes</button>
            <a href="<?php echo $_SESSION['usertype'] == 'farmer' ? 'home.php' : 'counsellor/view_predicament.php'; ?>" class="ghost link">Back</a>
        </div>
    </form>
</div>

<script>
    <?php if (!empty($successMessage)) { ?>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.fireThemed) {
            fireThemed({icon:'success', title:'Saved', text:'<?php echo $successMessage; ?>'});
        } else {
            Swal.fire({icon:'success', title:'Saved', text:'<?php echo $successMessage; ?>'});
        }
    });
    <?php } ?>
    <?php if (!empty($errorMessage)) { ?>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.fireThemed) {
            fireThemed({icon:'error', title:'Error', html:'<?php echo $errorMessage; ?>'});
        } else {
            Swal.fire({icon:'error', title:'Error', html:'<?php echo $errorMessage; ?>'});
        }
    });
    <?php } ?>

    const toggleBtn = document.getElementById('editToggle');
    const inputs = document.querySelectorAll('.profile-form input:not([name="password"])');

    toggleBtn.addEventListener('click', () => {
        const isReadOnly = inputs[0].hasAttribute('readonly');
        inputs.forEach(input => {
            if (isReadOnly) {
                input.removeAttribute('readonly');
                input.classList.add('editable');
            } else {
                input.setAttribute('readonly', 'readonly');
                input.classList.remove('editable');
            }
        });
        toggleBtn.textContent = isReadOnly ? 'Cancel' : 'Edit';
    });
</script>
