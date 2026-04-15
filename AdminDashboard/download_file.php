<?php
session_start();
require_once '../BackEnd/connection.php';

// check is user logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../BackEnd/login.php');
    exit();
}

// get appointment id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid appointment ID.";
    header('Location: manage_appointments.php');
    exit();
}

$appointment_id = intval($_GET['id']);

try {
    $pdo = getDatabaseConnection();

    // get the appointment and file path
    $stmt = $pdo->prepare("SELECT medical_file, first_name, last_name FROM appointments WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found.";
        header('Location: manage_appointments.php');
        exit();
    }

    $file_path = $appointment['medical_file'];

    // check if file exists
    if (empty($file_path) || !file_exists($file_path)) {
        $_SESSION['error_message'] = "Medical file not found.";
        header('Location: manage_appointments.php');
        exit();
    }

    // get file information
    $file_name = basename($file_path);
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

    // set content type based on file extension
    $content_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $content_type = isset($content_types[$file_extension]) ? $content_types[$file_extension] : 'application/octet-stream';

    // create a friendly filename
    $patient_name = $appointment['first_name'] . '_' . $appointment['last_name'];
    $download_name = 'Medical_File_' . $patient_name . '_' . $appointment_id . '.' . $file_extension;
    // clear any output buffers
    if (ob_get_level()) {
        ob_end_clean();
    }

    // set headers for download
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . $download_name . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');

    // read and output the file
    readfile($file_path);
    exit();

} catch (PDOException $e) {
    error_log("Error downloading file: " . $e->getMessage());
    $_SESSION['error_message'] = "Error downloading file. Please try again.";
    header('Location: manage_appointments.php');
    exit();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred. Please try again.";
    header('Location: manage_appointments.php');
    exit();
}
?>