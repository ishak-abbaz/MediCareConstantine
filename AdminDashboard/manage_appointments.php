<?php
session_start();
require_once '../BackEnd/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../BackEnd/login.php');
    exit();
}

$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? $username;

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

try {
    $pdo = getDatabaseConnection();
    $appointments = $pdo->query(
            "SELECT * FROM appointments ORDER BY preferred_date DESC, preferred_time DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log("Appointments error: " . $e->getMessage());
    $appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Appointments | MediCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .brand-link { background: linear-gradient(to right, #4a90e2, #67b3e8) !important; }
        .main-sidebar { background: #2c3e50 !important; }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #4a90e2 !important;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            margin: 2px;
        }
        .action-buttons { white-space: nowrap; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="../FrontEnd/HomePage/index.html" class="nav-link">Visit Website</a>
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
                    <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User">
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
                <h1 class="m-0">Manage Appointments</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check"></i> <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-ban"></i> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Appointments</h3>
                        <div class="card-tools">
                            <a href="../FrontEnd/AppointmentPage/appointment.html" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Appointment
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="appointmentsTable" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>First name</th>
                                <th>Last name</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($appointments as $a): ?>
                                <tr>
                                    <td><strong>#<?= $a['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($a['first_name']) ?></td>
                                    <td><?= htmlspecialchars($a['last_name']) ?></td>
                                    <td><?= htmlspecialchars($a['requested_service']) ?></td>
                                    <td><?= date('M d, Y', strtotime($a['preferred_date'])) ?></td>
                                    <td><?= htmlspecialchars($a['preferred_time']) ?></td>
                                    <td>
                                        <?php
                                        $d = new DateTime($a['preferred_date']);
                                        $t = new DateTime(); $t->setTime(0,0,0);
                                        echo $d < $t ? '<span class="badge badge-info">Completed</span>' :
                                                ($d == $t ? '<span class="badge badge-warning">Today</span>' :
                                                        '<span class="badge badge-success">Upcoming</span>');
                                        ?>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-info btn-action" onclick="viewDetails(<?= $a['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-action" onclick="editAppointment(<?= $a['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-action"
                                                onclick="deleteAppointment(<?= $a['id'] ?>,'<?= htmlspecialchars($a['first_name'].' '.$a['last_name'],ENT_QUOTES) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php if (!empty($a['medical_file'])): ?>
                                            <button class="btn btn-success btn-action" onclick="downloadFile(<?= $a['id'] ?>)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-action" disabled>
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; 2025 MediCare Constantine</strong>
        <div class="float-right d-none d-sm-inline-block">
            Developed by Ishak ABBAZ
        </div>
    </footer>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#appointmentsTable').DataTable({
        order: [[0,'desc']],
        pageLength: 10
    });

    function viewDetails(id){ location.href='appointment_details.php?id='+id; }
    function editAppointment(id){ location.href='edit_appointment.php?id='+id; }
    function downloadFile(id){ location.href='download_file.php?id='+id; }

    function deleteAppointment(id,name){
        Swal.fire({
            title:'Delete?',
            html:'Delete appointment for <b>'+name+'</b>?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#d33'
        }).then(r=>{
            if(r.isConfirmed) location.href='delete_appointment.php?id='+id;
        });
    }
</script>

</body>
</html>
