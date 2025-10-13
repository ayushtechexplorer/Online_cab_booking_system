<?php
session_start();
include '../db.php'; // Include your database connection file

$errors = [];
$success = false;

$name = $email = $phone = $license_number = $username = $local_area = $gender = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $local_area = trim($_POST['local_area'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Server-side validation
    if (!preg_match("/^[A-Z][a-zA-Z ]*$/", $name)) {
        $errors[] = "Name should start with a capital letter and contain only letters and spaces.";
    }

    if (!preg_match("/[0-9]/", $email)) {
        $errors[] = "Email must contain at least one number.";
    }

    if (!preg_match("/^(98|97)\d{8}$/", $phone)) {
        $errors[] = "Phone number must start with 98 or 97 and contain exactly 10 digits.";
    }

    if (!preg_match("/[0-9]/", $username)) {
        $errors[] = "Username must contain at least one number.";
    }

    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
        $errors[] = "Password must be at least 8 characters long and contain both letters and numbers.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    if (empty($errors)) {
        $plain_password = $password; // Use plain text password directly

        $conn->begin_transaction();

        try {
            // Check if the email or phone already exists in the users table
            $sql = "SELECT id FROM drivers WHERE email = ? OR phone = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Email or Phone number already exists. Please use a different one.";
            } else {
                // Continue with the registration process if no duplicates are found
                // Insert into `users` and `drivers` as described earlier

                // Insert into users table
                $sql = "INSERT INTO users (username, password, role, local_area, gender) VALUES (?, ?, 'driver', ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $plain_password, $local_area, $gender);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting user: " . $stmt->error);
                }
                $user_id = $stmt->insert_id;

                // Insert into drivers table
                $sql = "INSERT INTO drivers (id, name, email, phone, license_number, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssss", $user_id, $name, $email, $phone, $license_number, $username, $plain_password);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting driver: " . $stmt->error);
                }

                $stmt->close();
                $conn->commit();

                // Set session variables and redirect
                $_SESSION['id'] = $user_id;
                $_SESSION['role'] = 'driver';

                header('Location: driver_dashboard.php');
                exit();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .error {
            color: #ff0000;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Driver Registration</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="post" id="registration-form">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($name); ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">

        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" required value="<?php echo htmlspecialchars($phone); ?>">

        <label for="license_number">License Number</label>
        <input type="text" name="license_number" id="license_number" required value="<?php echo htmlspecialchars($license_number); ?>">

        <label for="username">Username</label>
        <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <label for="local_area">Local Area</label>
        <input type="text" name="local_area" id="local_area" required value="<?php echo htmlspecialchars($local_area); ?>">

        <label for="gender">Gender</label>
        <select name="gender" id="gender" required>
            <option value="">Select Gender</option>
            <option value="male" <?php echo ($gender === 'male') ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo ($gender === 'female') ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo ($gender === 'other') ? 'selected' : ''; ?>>Other</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>

<script>
    document.getElementById('registration-form').addEventListener('submit', function (event) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirm_password = document.getElementById('confirm_password').value.trim();

        if (!/^[A-Z][a-zA-Z ]*$/.test(name)) {
            alert("Name must start with a capital letter and contain only letters and spaces.");
            event.preventDefault();
            return;
        }

        if (!/\d/.test(email)) {
            alert("Email must contain at least one number.");
            event.preventDefault();
            return;
        }

        if (!/^(98|97)\d{8}$/.test(phone)) {
            alert("Phone number must start with 98 or 97 and contain exactly 10 digits.");
            event.preventDefault();
            return;
        }

        if (!/\d/.test(username)) {
            alert("Username must contain at least one number.");
            event.preventDefault();
            return;
        }

        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(password)) {
            alert("Password must be at least 8 characters long and include both letters and numbers.");
            event.preventDefault();
            return;
        }

        if (password !== confirm_password) {
            alert("Password and Confirm Password must match.");
            event.preventDefault();
            return;
        }
    });
</script>

</body>
</html>
