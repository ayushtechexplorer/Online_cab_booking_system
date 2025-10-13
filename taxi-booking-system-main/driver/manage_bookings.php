<?php
session_start();
include '../db.php';

// Get the driver_id from the session
$driver_id = $_SESSION['id'];

// Fetch bookings for the driver
$bookingsQuery = "SELECT b.id, b.customer_id, b.pickup_location, b.dropoff_location, b.booking_status, b.booking_date 
                  FROM bookings b 
                  WHERE b.driver_id = ?";
$bookingsStmt = $conn->prepare($bookingsQuery);
$bookingsStmt->bind_param("i", $driver_id);
$bookingsStmt->execute();
$bookingsResult = $bookingsStmt->get_result();

// Update booking status when the form is submitted
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    // Ensure the new status is a valid value
    $valid_statuses = ['Pending', 'Confirmed', 'Completed', 'Canceled'];
    if (in_array($new_status, $valid_statuses)) {
        // Prepare the update query
        $updateStatusQuery = "UPDATE bookings SET booking_status = ? WHERE id = ? AND driver_id = ?";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("sii", $new_status, $booking_id, $driver_id);

        if ($updateStatusStmt->execute()) {
            $success_message = "Booking status updated successfully.";
        } else {
            $error_message = "Error updating booking status.";
        }
    } else {
        $error_message = "Invalid booking status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <title>Manage Bookings</title>
    <style>
        /* Sidebar styles */
        .sidebar {
            width: 60px;
            height: 100vh;
            background-color: #343a40;
            position: fixed;
            transition: width 0.3s;
        }

        .sidebar-header {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
            background-color: #007BFF;
        }

        .menu-toggle {
            cursor: pointer;
            color: white;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar-menu a {
            padding: 15px 0;
            color: white;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .sidebar-menu .menu-text {
            display: none;
            margin-left: 10px;
        }

        .sidebar-menu a:hover {
            background-color: #007BFF;
        }

        .sidebar.active {
            width: 200px;
        }

        .sidebar.active .menu-text {
            display: inline;
        }

        .container {
            margin-left: 70px;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        /* Styling for form elements and buttons */
        .success {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        form {
            margin-top: 10px;
        }

        select, button {
            padding: 5px 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        select {
            width: 150px;
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
    <div class="sidebar-menu" id="sidebar-menu">
        <a href="driver_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span class="menu-text">Dashboard</span></a>
        <a href="manage_bookings.php"><i class="fas fa-folder"></i><span class="menu-text">Manage Booking</span></a>
        <a href="manage_profile.php"><i class="fas fa-user"></i><span class="menu-text">Profile Details</span></a>
        <a href="vehicle_details.php"><i class="fas fa-car"></i> <span class="menu-text">Vehicle Details</span></a>
        <a href="../driver/logout.php"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Logout</span></a>
    </div>
</div>

<div class="container">
    <h1>Your Bookings</h1>

    <?php if (isset($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <?php if ($bookingsResult->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer ID</th>
                    <th>Pickup Location</th>
                    <th>Dropoff Location</th>
                    <th>Booking Status</th>
                    <th>Booking Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookingsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                        <td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td>
                            <form method="POST" action="manage_bookings.php" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <select name="new_status">
                                    <option value="Pending" <?php echo ($booking['booking_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Confirmed" <?php echo ($booking['booking_status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="Completed" <?php echo ($booking['booking_status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Canceled" <?php echo ($booking['booking_status'] === 'Canceled') ? 'selected' : ''; ?>>Canceled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>
</div>

<script>
    // Sidebar toggle functionality
    document.getElementById('menu-toggle').onclick = function() {
        var sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    };
</script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
