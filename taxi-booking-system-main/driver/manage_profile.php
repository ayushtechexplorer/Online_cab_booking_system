<?php
session_start();
include '../db.php';

// Check if the user is a driver
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

// Get the driver_id from the session
$driver_id = $_SESSION['id'];

// Fetch the driver's profile details from the drivers table
$query = "SELECT name, email, phone, license_number FROM drivers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Fetch the associated cabs of the driver
$cabsQuery = "SELECT id, model, plate_number, capacity, fuel_type, price_per_km, availability FROM cabs WHERE driver_id = ?";
$cabsStmt = $conn->prepare($cabsQuery);
$cabsStmt->bind_param("i", $driver_id);
$cabsStmt->execute();
$cabsResult = $cabsStmt->get_result();

// Change password if the form is submitted
if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Fetch the current password from the users table
    $currentPasswordQuery = "SELECT password FROM users WHERE id = ?";
    $currentPasswordStmt = $conn->prepare($currentPasswordQuery);
    $currentPasswordStmt->bind_param("i", $driver_id);
    $currentPasswordStmt->execute();
    $currentPasswordResult = $currentPasswordStmt->get_result();

    if ($currentPasswordResult->num_rows === 0) {
        $password_error_message = "User not found.";
    } else {
        $currentPassword = $currentPasswordResult->fetch_assoc()['password'];

        if (password_verify($old_password, $currentPassword)) {
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
            $passwordUpdateQuery = "UPDATE users SET password = ? WHERE id = ?";
            $passwordUpdateStmt = $conn->prepare($passwordUpdateQuery);
            $passwordUpdateStmt->bind_param("si", $hashedPassword, $driver_id);

            if ($passwordUpdateStmt->execute()) {
                $password_success_message = "Password changed successfully.";
            } else {
                $password_error_message = "Error changing password. Please try again.";
            }
        } else {
            $password_error_message = "Old password is incorrect.";
        }
    }
}

// Handle cab management (add/edit/delete)
if (isset($_POST['add_cab'])) {
    $model = $_POST['model'];
    $plate_number = $_POST['plate_number'];
    $capacity = (int) $_POST['capacity'];
    $fuel_type = $_POST['fuel_type'];
    $price_per_km = (float) $_POST['price_per_km'];
    $availability = $_POST['availability'];

    $addCabQuery = "INSERT INTO cabs (driver_id, model, plate_number, capacity, fuel_type, price_per_km, created_at, availability) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
    $addCabStmt = $conn->prepare($addCabQuery);
    $addCabStmt->bind_param("ississs", $driver_id, $model, $plate_number, $capacity, $fuel_type, $price_per_km, $availability);

    if ($addCabStmt->execute()) {
        $cab_success_message = "Cab added successfully.";
    } else {
        $cab_error_message = "Error adding cab: " . $addCabStmt->error;
    }

    $addCabStmt->close();
}

// Handle cab deletion
if (isset($_GET['delete_cab'])) {
    $cab_id = $_GET['delete_cab'];
    $deleteCabQuery = "DELETE FROM cabs WHERE id = ?";
    $deleteCabStmt = $conn->prepare($deleteCabQuery);
    $deleteCabStmt->bind_param("i", $cab_id);

    if ($deleteCabStmt->execute()) {
        $cab_success_message = "Cab deleted successfully.";
    } else {
        $cab_error_message = "Error deleting cab. Please try again.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Driver Profile</title>
    <style>
        /* Sidebar and container styles */
        .sidebar { width: 60px; height: 100vh; background-color: #343a40; position: fixed; transition: width 0.3s; }
        .sidebar-header { display: flex; justify-content: center; align-items: center; height: 60px; background-color: #007BFF; }
        .menu-toggle { cursor: pointer; color: white; }
        .sidebar-menu a { padding: 15px 0; color: white; text-decoration: none; display: flex; justify-content: center; align-items: center; width: 100%; }
        .sidebar-menu .menu-text { display: none; margin-left: 10px; }
        .sidebar-menu a:hover { background-color: #007BFF; }
        .sidebar.active { width: 200px; }
        .sidebar.active .menu-text { display: inline; }
        .container { margin-left: 70px; padding: 20px; }

        /* Forms and tables */
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; }
        .btn { background-color: #007BFF; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; }
        .btn:hover { background-color: #0056b3; }
        .success { color: green; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></div>
    </div>
    <div class="sidebar-menu">
        <a href="driver_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span class="menu-text">Dashboard</span></a>
        <a href="manage_bookings.php"><i class="fas fa-folder"></i><span class="menu-text">Manage Bookings</span></a>
        <a href="manage_profile.php"><i class="fas fa-user"></i><span class="menu-text">Profile Details</span></a>
        <a href="vehicle_details.php"><i class="fas fa-car"></i> <span class="menu-text">Vehicle Details</span></a>
        <a href="../driver/logout.php"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Logout</span></a>
    </div>
</div>

<div class="container">
    <h1>Driver Profile</h1>

    <!-- Profile Update Form -->
    <form method="POST" action="">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($driver['name']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($driver['email']); ?>" required>

        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($driver['phone']); ?>" required>

        <label for="license_number">License Number</label>
        <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>

        <button type="submit" class="btn">Update Profile</button>
    </form>

    <!-- Password Update Form -->
    <h2>Change Password</h2>
    <?php if (isset($password_success_message)): ?>
        <p class="success"><?php echo $password_success_message; ?></p>
    <?php elseif (isset($password_error_message)): ?>
        <p class="error"><?php echo $password_error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="old_password">Old Password</label>
        <input type="password" id="old_password" name="old_password" required>

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>

        <button type="submit" name="change_password" class="btn">Change Password</button>
    </form>

    <!-- Cab Management Section -->
    <h2>Your Cabs</h2>
    <?php if ($cabsResult->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Model</th>
                    <th>Plate Number</th>
                    <th>Capacity</th>
                    <th>Fuel Type</th>
                    <th>Price per Km</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cab = $cabsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cab['model']); ?></td>
                        <td><?php echo htmlspecialchars($cab['plate_number']); ?></td>
                        <td><?php echo htmlspecialchars($cab['capacity']); ?></td>
                        <td><?php echo htmlspecialchars($cab['fuel_type']); ?></td>
                        <td><?php echo htmlspecialchars($cab['price_per_km']); ?></td>
                        <td><?php echo htmlspecialchars($cab['availability']); ?></td>
                        <td>
                            <a href="edit_cab.php?id=<?php echo $cab['id']; ?>" class="btn">Edit</a>
                            <a href="?delete_cab=<?php echo $cab['id']; ?>" class="btn">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No cabs found.</p>
    <?php endif; ?>

    <h2>Add New Cab</h2>
    <?php if (isset($cab_success_message)): ?>
        <p class="success"><?php echo $cab_success_message; ?></p>
    <?php elseif (isset($cab_error_message)): ?>
        <p class="error"><?php echo $cab_error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="model">Model</label>
        <input type="text" id="model" name="model" required>

        <label for="plate_number">Plate Number</label>
        <input type="text" id="plate_number" name="plate_number" required>

        <label for="capacity">Capacity</label>
        <input type="number" id="capacity" name="capacity" required>

        <label for="fuel_type">Fuel Type</label>
        <input type="text" id="fuel_type" name="fuel_type" required>

        <label for="price_per_km">Price per Km</label>
        <input type="number" id="price_per_km" name="price_per_km" required>

        <label for="availability">Availability</label>
        <select id="availability" name="availability" required>
            <option value="available">Available</option>
            <option value="not available">On Duty</option>
            <option value="in maintenance">Under Maintenance</option>
        </select>

        <button type="submit" name="add_cab" class="btn">Add Cab</button>
    </form>
</div>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
</script>

</body>
</html>
