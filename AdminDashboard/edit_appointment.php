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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDatabaseConnection();

        $allergies_history = $_POST['allergies_history'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE appointments SET
                first_name = :first_name,
                last_name = :last_name,
                birthdate = :birthdate,
                gender = :gender,
                email = :email,
                phone = :phone,
                address = :address,
                requested_service = :requested_service,
                selected_doctor = :selected_doctor,
                preferred_date = :preferred_date,
                preferred_time = :preferred_time,
                allergies_history = :allergies_history
            WHERE id = :id
        ");

        $stmt->execute([
                'first_name'        => $_POST['first_name'],
                'last_name'         => $_POST['last_name'],
                'birthdate'         => $_POST['birthdate'],
                'gender'            => $_POST['gender'],
                'email'             => $_POST['email'],
                'phone'             => $_POST['phone'],
                'address'           => $_POST['address'],
                'requested_service' => $_POST['requested_service'],
                'selected_doctor'   => $_POST['selected_doctor'],
                'preferred_date'    => $_POST['preferred_date'],
                'preferred_time'    => $_POST['preferred_time'],
                'allergies_history' => $allergies_history,
                'id'                => $appointment_id
        ]);

        $_SESSION['success_message'] = "Appointment updated successfully!";
        header('Location: manage_appointments.php');
        exit();

    } catch (PDOException $e) {
        error_log("UPDATE ERROR: " . $e->getMessage());
        $error_message = "Error updating appointment.";
    }
}

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
    header('Location: manage_appointments.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Appointment | MediCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .brand-link { background: linear-gradient(to right,#4a90e2,#67b3e8)!important; }
        .main-sidebar { background:#2c3e50!important; }
        .info-label {
            font-weight:600;
            color:#495057;
            margin-bottom:8px;
            font-size: 14px;
        }
        .section-header {
            background: linear-gradient(to right,#4a90e2,#67b3e8);
            color:#fff;
            padding:10px 15px;
            border-radius:5px;
            margin:20px 0 20px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .section-header i {
            margin-right: 8px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        .alert-info {
            background: #d1ecf1;
            border-color: #0c5460;
            color: #0c5460;
            border-radius: 5px;
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
                <h1 class="m-0">Edit Appointment</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-ban"></i> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i> Edit Appointment #<?= $appointment['id'] ?>
                        </h3>
                        <div class="card-tools">
                            <a href="appointment_details.php?id=<?= $appointment['id'] ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="manage_appointments.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="card-body">

                            <!-- PERSONAL INFORMATION -->
                            <div class="section-header">
                                <i class="fas fa-user"></i> Personal Information
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">First Name *</label>
                                        <input type="text" class="form-control" name="first_name"
                                               value="<?= htmlspecialchars($appointment['first_name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Last Name *</label>
                                        <input type="text" class="form-control" name="last_name"
                                               value="<?= htmlspecialchars($appointment['last_name']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Birthdate *</label>
                                        <input type="date" class="form-control" name="birthdate"
                                               value="<?= $appointment['birthdate'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Gender *</label>
                                        <select class="form-control" name="gender" required>
                                            <option value="male" <?= $appointment['gender']=='male'?'selected':'' ?>>Male</option>
                                            <option value="female" <?= $appointment['gender']=='female'?'selected':'' ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- CONTACT INFORMATION -->
                            <div class="section-header">
                                <i class="fas fa-phone"></i> Contact Information
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Email *</label>
                                        <input type="email" class="form-control" name="email"
                                               value="<?= htmlspecialchars($appointment['email']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Phone Number *</label>
                                        <input type="tel" class="form-control" name="phone"
                                               value="<?= htmlspecialchars($appointment['phone']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="info-label">Address *</label>
                                <textarea class="form-control" name="address" rows="3" required><?= htmlspecialchars($appointment['address']) ?></textarea>
                            </div>

                            <!-- APPOINTMENT INFORMATION -->
                            <div class="section-header">
                                <i class="fas fa-calendar-check"></i> Appointment Information
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Requested Service *</label>
                                        <select class="form-control" name="requested_service" required>
                                            <option value="">-- Select a service --</option>
                                            <option value="general-medicine" <?= $appointment['requested_service']=='general-medicine'?'selected':'' ?>>General Medicine</option>
                                            <option value="cardiology" <?= $appointment['requested_service']=='cardiology'?'selected':'' ?>>Cardiology</option>
                                            <option value="pediatrics" <?= $appointment['requested_service']=='pediatrics'?'selected':'' ?>>Pediatrics</option>
                                            <option value="dermatology" <?= $appointment['requested_service']=='dermatology'?'selected':'' ?>>Dermatology</option>
                                            <option value="gynecology-obstetrics" <?= $appointment['requested_service']=='gynecology-obstetrics'?'selected':'' ?>>Gynecology & Obstetrics</option>
                                            <option value="special-consultation" <?= $appointment['requested_service']=='special-consultation'?'selected':'' ?>>Special Consultation</option>
                                            <option value="emergency-care" <?= $appointment['requested_service']=='emergency-care'?'selected':'' ?>>Emergency Care</option>
                                            <option value="medical-imaging" <?= $appointment['requested_service']=='medical-imaging'?'selected':'' ?>>Medical Imaging</option>
                                            <option value="laboratory-tests" <?= $appointment['requested_service']=='laboratory-tests'?'selected':'' ?>>Laboratory Tests</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Selected Doctor *</label>
                                        <input type="text" class="form-control" name="selected_doctor"
                                               value="<?= htmlspecialchars($appointment['selected_doctor']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Preferred Date *</label>
                                        <input type="date" class="form-control" name="preferred_date"
                                               value="<?= $appointment['preferred_date'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="info-label">Preferred Time *</label>
                                        <select class="form-control" name="preferred_time" required>
                                            <option value=""><?= $appointment['preferred_time'] ?></option>
                                            <option value="08:00" <?= $appointment['preferred_time']=='08:00'?'selected':'' ?>>08:00</option>
                                            <option value="09:00" <?= $appointment['preferred_time']=='09:00'?'selected':'' ?>>09:00</option>
                                            <option value="10:00" <?= $appointment['preferred_time']=='10:00'?'selected':'' ?>>10:00</option>
                                            <option value="11:00" <?= $appointment['preferred_time']=='11:00'?'selected':'' ?>>11:00</option>
                                            <option value="12:00" <?= $appointment['preferred_time']=='12:00'?'selected':'' ?>>12:00</option>
                                            <option value="14:00" <?= $appointment['preferred_time']=='14:00'?'selected':'' ?>>14:00</option>
                                            <option value="15:00" <?= $appointment['preferred_time']=='15:00'?'selected':'' ?>>15:00</option>
                                            <option value="16:00" <?= $appointment['preferred_time']=='16:00'?'selected':'' ?>>16:00</option>
                                            <option value="17:00" <?= $appointment['preferred_time']=='17:00'?'selected':'' ?>>17:00</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- MEDICAL INFORMATION -->
                            <div class="section-header">
                                <i class="fas fa-notes-medical"></i> Medical Information
                            </div>

                            <div class="form-group">
                                <label class="info-label">Allergies / Medical History (Optional)</label>
                                <textarea class="form-control" name="allergies_history" rows="4"
                                          placeholder="Please describe any allergies, current medications, or medical conditions..."><?= htmlspecialchars($appointment['allergies_history']) ?></textarea>
                            </div>

                            <?php if (!empty($appointment['medical_file'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Medical file cannot be changed here. Current file:
                                    <strong><?= htmlspecialchars(basename($appointment['medical_file'])) ?></strong>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Appointment
                            </button>
                            <a href="manage_appointments.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="appointment_details.php?id=<?= $appointment['id'] ?>" class="btn btn-info float-right">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </form>

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
