<?php
require_once 'connection.php';

// Initialize variables
$errors = [];
$success_message = '';
$medical_file_path = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =======================
    // 1. RETRIEVE AND VALIDATE INPUTS
    // =======================

    // Get all form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $requested_service = trim($_POST['requested_service'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $preferred_time = trim($_POST['preferred_time'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $allergies_history = trim($_POST['allergies_history'] ?? '');
    $selected_doctor = trim($_POST['selected_doctor'] ?? '');

    // Validate required fields are not empty
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    if (empty($birthdate)) {
        $errors[] = "Birthdate is required.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }
    if (empty($requested_service)) {
        $errors[] = "Requested service is required.";
    }
    if (empty($preferred_date)) {
        $errors[] = "Preferred date is required.";
    }
    if (empty($preferred_time)) {
        $errors[] = "Preferred time is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($address)) {
        $errors[] = "Address is required.";
    }
    if (empty($selected_doctor)) {
        $errors[] = "Doctor selection is required.";
    }

    // Validate name format (letters, spaces, hyphens, apostrophes only)
    if (!empty($first_name) && !preg_match("/^[a-zA-Z\s\-']+$/", $first_name)) {
        $errors[] = "First name contains invalid characters.";
    }
    if (!empty($last_name) && !preg_match("/^[a-zA-Z\s\-']+$/", $last_name)) {
        $errors[] = "Last name contains invalid characters.";
    }

    // Validate gender
    if (!empty($gender) && !in_array($gender, ['male', 'female'])) {
        $errors[] = "Invalid gender selection.";
    }

    // Validate service
    $valid_services = ['general-medicine', 'cardiology', 'pediatrics', 'dermatology',
                       'gynecology-obstetrics', 'special-consultation', 'emergency-care',
                       'medical-imaging', 'laboratory-tests'];
    if (!empty($requested_service) && !in_array($requested_service, $valid_services)) {
        $errors[] = "Invalid service selection.";
    }

    // Validate date format and not in past
    if (!empty($preferred_date)) {
        $date = DateTime::createFromFormat('Y-m-d', $preferred_date);
        if (!$date || $date->format('Y-m-d') !== $preferred_date) {
            $errors[] = "Invalid preferred date format.";
        } else {
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            if ($date < $today) {
                $errors[] = "Preferred date cannot be in the past.";
            }
        }
    }

    // Validate time
    $valid_times = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
    if (!empty($preferred_time) && !in_array($preferred_time, $valid_times)) {
        $errors[] = "Invalid time selection.";
    }

    // Validate phone format
    if (!empty($phone) && !preg_match("/^[\d\s\-\+\(\)]+$/", $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    // =======================
    // 2. FILE UPLOAD VALIDATION (OPTIONAL)
    // =======================

    if (isset($_FILES['medical_file']) && $_FILES['medical_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['medical_file'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error. Please try again.";
        } else {
            // Validate file size (5MB max)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                $errors[] = "File size must not exceed 5MB.";
            }

            // Validate file type
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            $file_mime = mime_content_type($file['tmp_name']);

            if (!in_array($file_mime, $allowed_types)) {
                $errors[] = "Invalid file type. Only PDF, JPG, and PNG files are allowed.";
            }

            // Validate file extension
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Invalid file extension. Only .pdf, .jpg, .jpeg, and .png are allowed.";
            }

            // If no errors, upload the file
            if (empty($errors)) {
                $upload_dir = '../uploads/medical_files/';

                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generate unique filename
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $medical_file_path = $upload_path;
                } else {
                    $errors[] = "Failed to upload file. Please try again.";
                }
            }
        }
    }

    // =======================
    // 3. SANITIZATION & DATABASE INSERT
    // =======================

    if (empty($errors)) {
        try {
            $mysqlClient = getDatabaseConnection();

            // Sanitize all inputs to prevent XSS
            $first_name = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
            $last_name = htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8');
            $gender = htmlspecialchars($gender, ENT_QUOTES, 'UTF-8');
            $requested_service = htmlspecialchars($requested_service, ENT_QUOTES, 'UTF-8');
            $selected_doctor = htmlspecialchars($selected_doctor, ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
            $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
            $allergies_history = htmlspecialchars($allergies_history, ENT_QUOTES, 'UTF-8');
            $medical_file = $medical_file_path ? htmlspecialchars($medical_file_path, ENT_QUOTES, 'UTF-8') : '';
            $created_at = date('Y-m-d H:i:s');

            // Prepare SQL statement with placeholders (prevents SQL injection)
            $add_query = "INSERT INTO appointments
                (first_name, last_name, birthdate, gender, requested_service,
                 preferred_date, preferred_time, email, phone, address,
                 allergies_history, selected_doctor, medical_file, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $add_statement = $mysqlClient->prepare($add_query);

            // Execute with sanitized data
            $add_statement->execute([
                $first_name, $last_name, $birthdate, $gender, $requested_service,
                $preferred_date, $preferred_time, $email, $phone, $address,
                $allergies_history, $selected_doctor, $medical_file, $created_at
            ]);

            // Set success message
            $success_message = "Your appointment request has been submitted successfully.";

        } catch (PDOException $e) {
            $errors[] = "Database error: Unable to save appointment. Please try again later.";
            // Log error for debugging (don't show to user)
            error_log("Database error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmation</title>
    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .message-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .success-message {
            color: #28a745;
            font-size: 20px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .error-list {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }

        .error-list h2 {
            color: #721c24;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .error-list ul {
            list-style-position: inside;
            color: #721c24;
        }

        .error-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .info-text {
            color: #666;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <?php if (!empty($success_message)): ?>
            <div class="success-icon">✓</div>
            <h1>Appointment Confirmed!</h1>
            <p class="success-message"><?php echo $success_message; ?></p>
            <p class="info-text">We will contact you shortly to confirm your appointment details.</p>
            <a href="../FrontEnd/HomePage/index.html" class="btn">Return to Home</a>
        <?php elseif (!empty($errors)): ?>
            <div class="error-icon">✕</div>
            <h1>Submission Failed</h1>
            <div class="error-list">
                <h2>Please correct the following errors:</h2>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="../FrontEnd/AppointmentPage/appointment.html" class="btn">Go Back to Form</a>
        <?php else: ?>
            <div class="error-icon">⚠</div>
            <h1>Invalid Request</h1>
            <p class="info-text">Please submit the form properly.</p>
            <a href="../FrontEnd/AppointmentPage/appointment.html" class="btn">Go to Appointment Form</a>
        <?php endif; ?>
    </div>
</body>
</html>
