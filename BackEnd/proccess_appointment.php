<?php
require_once 'connection.php';

$errors = [];
$success_message = '';
$medical_file_path = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get all form data
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

    // validate required fields are not empty
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

    // validate name format using regular expressions
    if (!empty($first_name) && !preg_match("/^[a-zA-Z\s\-']+$/", $first_name)) {
        $errors[] = "First name contains invalid characters.";
    }
    if (!empty($last_name) && !preg_match("/^[a-zA-Z\s\-']+$/", $last_name)) {
        $errors[] = "Last name contains invalid characters.";
    }
    // validate gender
    if (!empty($gender) && !in_array($gender, ['male', 'female'])) {
        $errors[] = "Invalid gender selection.";
    }
    // validate service
    $valid_services = ['general-medicine', 'cardiology', 'pediatrics', 'dermatology',
                       'gynecology-obstetrics', 'special-consultation', 'emergency-care',
                       'medical-imaging', 'laboratory-tests'];
    if (!empty($requested_service) && !in_array($requested_service, $valid_services)) {
        $errors[] = "Invalid service selection.";
    }
    // validate date format
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
    // validate time
    $valid_times = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
    if (!empty($preferred_time) && !in_array($preferred_time, $valid_times)) {
        $errors[] = "Invalid time selection.";
    }
    // validate phone format
    if (!empty($phone) && !preg_match("/^[\d\s\-\+\(\)]+$/", $phone)) {
        $errors[] = "Invalid phone number format.";
    }
    // file upload validation
    if (isset($_FILES['medical_file']) && $_FILES['medical_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['medical_file'];

        // check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error. Please try again.";
        } else {
            // validate file size to not exceed 5mb
            $max_size = 5 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                $errors[] = "File size must not exceed 5MB.";
            }
            // validate file type
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            // retrieve file type
            $file_mime = mime_content_type($file['tmp_name']);

            if (!in_array($file_mime, $allowed_types)) {
                $errors[] = "Invalid file type. Only PDF, JPG, and PNG files are allowed.";
            }
            // validate file extension
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Invalid file extension. Only .pdf, .jpg, .jpeg, and .png are allowed.";
            }
            // if no errors, upload the file
            if (empty($errors)) {
                $upload_dir = '../uploads/medical_files/';

                // generate unique filename
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;

                // move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $medical_file_path = $upload_path;
                } else {
                    $errors[] = "Failed to upload file. Please try again.";
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $mysqlClient = getDatabaseConnection();

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

            // prepare SQL statement with placeholders to prevents sql injection)
            $add_query = "INSERT INTO appointments
                (first_name, last_name, birthdate, gender, requested_service,
                 preferred_date, preferred_time, email, phone, address,
                 allergies_history, selected_doctor, medical_file, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $add_statement = $mysqlClient->prepare($add_query);

            $add_statement->execute([
                $first_name, $last_name, $birthdate, $gender, $requested_service,
                $preferred_date, $preferred_time, $email, $phone, $address,
                $allergies_history, $selected_doctor, $medical_file, $created_at
            ]);

            $success_message = "Your appointment request has been submitted successfully.";

        } catch (PDOException $e) {
            $errors[] = "Database error: Unable to save appointment. Please try again later.";
            // Log error for debugging
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
    <title>Appointment Confirmation | MediCare</title>

    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #2c3e50;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .confirmation-section {
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 0.8s ease-out;
        }

        .confirmation-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .icon.success { color: #4CAF50; }
        .icon.error   { color: #e74c3c; }
        .icon.warn    { color: #f39c12; }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
        }

        .success-text {
            color: #4CAF50;
            font-size: 1.1rem;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .error-box {
            background: #fff5f5;
            border: 1px solid #e74c3c;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            margin-bottom: 20px;
        }

        .error-box h2 {
            color: #e74c3c;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .error-box ul {
            padding-left: 18px;
        }

        .error-box li {
            margin-bottom: 6px;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, #4a90e2, #67b3e8);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(74,144,226,0.3);
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(74,144,226,0.4);
        }

        .info-text {
            margin-top: 15px;
            font-size: 0.95rem;
            color: #666;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .confirmation-card {
                padding: 30px;
            }

            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

<div class="confirmation-section">
    <div class="confirmation-card">

        <?php if (!empty($success_message)): ?>
            <div class="icon success">✔</div>
            <h1>Appointment Booked</h1>
            <p class="success-text"><?= htmlspecialchars($success_message) ?></p>
            <p class="info-text">
                Our team will contact you shortly to confirm your appointment details.
            </p>
            <a href="../FrontEnd/HomePage/index.html" class="btn">
                Return to Home
            </a>

        <?php elseif (!empty($errors)): ?>
            <div class="icon error">✖</div>
            <h1>Submission Failed</h1>

            <div class="error-box">
                <h2>Please fix the following:</h2>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="../FrontEnd/AppointmentPage/appointment.html" class="btn">
                Back to Appointment Form
            </a>

        <?php else: ?>
            <div class="icon warn">⚠</div>
            <h1>Invalid Request</h1>
            <p class="info-text">
                Please submit the appointment form correctly.
            </p>
            <a href="../FrontEnd/AppointmentPage/appointment.html" class="btn">
                Go to Appointment Page
            </a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
