<?php
session_start();
include '../db.php'; // Include your database connection file

// Initialize error messages and success flag
$errors = [];
$success = false;

// Initialize variables
$name = $email = $phone = $username = $address = $gender = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Input validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required';
    }
    if (empty($phone) || !preg_match('/^(98|97)\d{8}$/', $phone)) {
        $errors[] = 'Phone number must start with 98 or 97 and be 10 digits long';
    }
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    if (empty($gender)) {
        $errors[] = 'Gender is required';
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Start the transaction
            $conn->begin_transaction();
        
            // Check if username already exists in the users table
            $sql = "SELECT id FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                // Username exists, get the user ID
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
        
                // Update the existing user details
                $sql = "UPDATE users SET password = ?, gender = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $password, $gender, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating user: " . $stmt->error);
                }
        
                // Check if the customer already exists in the customers table
                $sql = "SELECT id FROM customers WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $customer_result = $stmt->get_result();
        
                if ($customer_result->num_rows === 0) {
                    // Insert into the customers table if not already present
                    $sql = "INSERT INTO customers (id, name, email, phone, address, username, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issssss", $user_id, $name, $email, $phone, $address, $username, $password);
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting into customers: " . $stmt->error);
                    }
                }
            } else {
                // Insert new user
                $sql = "INSERT INTO users (username, password, role, gender) VALUES (?, ?, 'customer', ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $password, $gender);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting user: " . $stmt->error);
                }
                $user_id = $stmt->insert_id;
        
                // Insert into customers table
                $sql = "INSERT INTO customers (id, name, email, phone, address, username, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssss", $user_id, $name, $email, $phone, $address, $username, $password);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting into customers: " . $stmt->error);
                }
            }
        
            // Commit the transaction
            $conn->commit();
        
            // Set session variables for the logged-in customer
            $_SESSION['id'] = $user_id;
            $_SESSION['role'] = 'customer';
        
            // Redirect to customer dashboard after successful registration
            header('Location: customer_dashboard.php');
            exit();
        } catch (Exception $e) {
            // Rollback the transaction in case of error
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
    <title>Customer Registration</title>
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
    <h2>Customer Registration</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register_customer.php" method="post" onsubmit="return validateForm()">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($name); ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">

        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" required value="<?php echo htmlspecialchars($phone); ?>">

        <label for="address">Address</label>
        <input type="text" name="address" id="address" required value="<?php echo htmlspecialchars($address); ?>">

        <label for="username">Username</label>
        <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

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
    function validateForm() {
        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;
        const phone = document.getElementById("phone").value;
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;

        const nameRegex = /^[A-Z][a-zA-Z\s]*$/;
        const emailRegex = /^(?=.*\d)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const phoneRegex = /^(98|97)\d{8}$/;
        const usernameRegex = /\d/;
        const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d).{8,}$/;

        if (!nameRegex.test(name)) {
            alert("Name must start with a capital letter.");
            return false;
        }

        if (!emailRegex.test(email)) {
            alert("Email must contain at least one number.");
            return false;
        }

        if (!phoneRegex.test(phone)) {
            alert("Phone number must start with 98 or 97 and be 10 digits long.");
            return false;
        }

        if (!usernameRegex.test(username)) {
            alert("Username must contain at least one number.");
            return false;
        }

        if (!passwordRegex.test(password)) {
            alert("Password must be at least 8 characters long and contain both letters and numbers.");
            return false;
        }

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }

        return true;
    }
</script>
</body>

</html>
