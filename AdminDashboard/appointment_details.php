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

$appointment_id = (int) $_GET['id'];
$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found.";
        header('Location: manage_appointments.php');
        exit();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = "Error loading appointment details.";
    header('Location: manage_appointments.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Appointment Details | MediCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .brand-link { background: linear-gradient(to right,#4a90e2,#67b3e8)!important; }
        .main-sidebar { background:#2c3e50!important; }
        .info-label { font-weight:600; color:#495057; margin-bottom:5px; }
        .info-value { padding:10px; background:#f8f9fa; border-radius:5px; margin-bottom:15px; }
        .section-header {
            background: linear-gradient(to right,#4a90e2,#67b3e8);
            color:#fff;
            padding:10px 15px;
            border-radius:5px;
            margin:20px 0;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../BackEnd/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">MediCare</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/user2-160x160.jpg"
                         class="img-circle elevation-2">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($full_name) ?></a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_appointments.php" class="nav-link active">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Manage Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../FrontEnd/AppointmentPage/appointment.html" class="nav-link">
                            <i class="nav-icon fas fa-plus-circle"></i>
                            <p>Book Appointment</p>
                        </a>
                    </li>
                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item">
                        <a href="../BackEnd/logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Appointment Details</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Appointment #<?= $appointment['id'] ?>
                        </h3>
                        <div class="card-tools">
                            <a href="edit_appointment.php?id=<?= $appointment['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="manage_appointments.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- PERSONAL -->
                        <div class="section-header"><i class="fas fa-user"></i> Personal Information</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">First Name</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['first_name']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Last Name</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['last_name']) ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-label">Birthdate</div>
                                <div class="info-value"><?= date('F d, Y', strtotime($appointment['birthdate'])) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Gender</div>
                                <div class="info-value"><?= ucfirst($appointment['gender']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Age</div>
                                <div class="info-value">
                                    <?php
                                    $birth = new DateTime($appointment['birthdate']);
                                    echo (new DateTime())->diff($birth)->y . ' years';
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="section-header"><i class="fas fa-phone"></i> Contact Information</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['email']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['phone']) ?></div>
                            </div>
                        </div>

                        <div class="info-label">Address</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($appointment['address'])) ?></div>

                        <div class="section-header"><i class="fas fa-calendar-check"></i> Appointment Information</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-label">Service</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['requested_service']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Doctor</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['selected_doctor']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Preferred Date</div>
                                <div class="info-value"><?= date('F d, Y', strtotime($appointment['preferred_date'])) ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-label">Preferred Time</div>
                                <div class="info-value"><?= htmlspecialchars($appointment['preferred_time']) ?></div>
                            </div>
                            <?php if (!empty($appointment['time_slot'])): ?>
                                <div class="col-md-4">
                                    <div class="info-label">Time Slot</div>
                                    <div class="info-value"><?= ucfirst($appointment['time_slot']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="section-header"><i class="fas fa-notes-medical"></i> Medical Information</div>
                        <div class="info-value">
                            <?= $appointment['allergies_history']
                                    ? nl2br(htmlspecialchars($appointment['allergies_history']))
                                    : '<em class="text-muted">No medical history provided.</em>' ?>
                        </div>

                        <div class="section-header"><i class="fas fa-file-medical"></i> Medical File</div>
                        <?php if (!empty($appointment['medical_file'])): ?>
                            <a href="download_file.php?id=<?= $appointment['id'] ?>" class="btn btn-success">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No file uploaded.</span>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; 2025 MediCare Constantine</strong>
    </footer>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>
