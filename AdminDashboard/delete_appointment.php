<?php
session_start();
require_once '../BackEnd/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../BackEnd/login.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid appointment ID.";
    header('Location: manage_appointments.php');
    exit();
}

$appointment_id = intval($_GET['id']);

try {
    $pdo = getDatabaseConnection();

    // get the appointment to check if it has a medical file
    $stmt = $pdo->prepare("SELECT medical_file FROM appointments WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found.";
        header('Location: manage_appointments.php');
        exit();
    }

    // delete the medical file if it exists
    if (!empty($appointment['medical_file']) && file_exists($appointment['medical_file'])) {
        unlink($appointment['medical_file']);
    }

    // delete the appointment from database
    $delete_stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
    $delete_stmt->execute(['id' => $appointment_id]);

    $_SESSION['success_message'] = "Appointment deleted successfully!";

} catch (PDOException $e) {
    error_log("Error deleting appointment: " . $e->getMessage());
    $_SESSION['error_message'] = "Error deleting appointment. Please try again.";
}

header('Location: manage_appointments.php');
exit();
?>