<?php
session_start();
require_once '../BackEnd/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../BackEnd/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? $username;
$role = $_SESSION['role'] ?? 'admin';

try {
    $pdo = getDatabaseConnection();
    $total_appointments_query = $pdo->query("SELECT COUNT(*) as total FROM appointments");
    $total_appointments = $total_appointments_query->fetch()['total'];
    $today_appointments_query = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE preferred_date = CURDATE()");
    $today_appointments = $today_appointments_query->fetch()['total'];
    $pending_appointments_query = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE preferred_date >= CURDATE()");
    $pending_appointments = $pending_appointments_query->fetch()['total'];
    $completed_appointments_query = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE preferred_date < CURDATE()");
    $completed_appointments = $completed_appointments_query->fetch()['total'];
    $recent_appointments_query = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 10");
    $recent_appointments = $recent_appointments_query->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_appointments = 0;
    $today_appointments = 0;
    $pending_appointments = 0;
    $completed_appointments = 0;
    $recent_appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MediCare Constantine | Dashboard</title>
    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <!--    ToDo: fix plugins calls from https to local files-->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->
       <link rel="stylesheet" href="./plugins/fontawesome-free/css/font_awesome.css">
       <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> // downloaded -->
   <link rel="stylesheet" href="./dist/css/adminlte.min.css">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"> // downloaded -->
    <style>
        .brand-link {
            background: linear-gradient(to right, #4a90e2, #67b3e8) !important;
        }
        .main-sidebar {
            background: #2c3e50 !important;
        }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #4a90e2 !important;
        }
        .small-box.bg-info { background: linear-gradient(to right, #4a90e2, #67b3e8) !important; }
        .small-box.bg-success { background: linear-gradient(to right, #27ae60, #2ecc71) !important; }
        .small-box.bg-warning { background: linear-gradient(to right, #f39c12, #f1c40f) !important; }
        .small-box.bg-danger { background: linear-gradient(to right, #9b59b6, #8e44ad) !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
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
                    <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/user2-160x160.jpg"
                         class="img-circle elevation-2" alt="User">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-tasks"></i>
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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total_appointments; ?></h3>
                                <p>Total Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $today_appointments; ?></h3>
                                <p>Today's Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar-day"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $pending_appointments; ?></h3>
                                <p>Pending Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $completed_appointments; ?></h3>
                                <p>Completed</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Appointments</h3>
                                <div class="card-tools">
                                    <a href="manage_appointments.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-tasks"></i> Manage All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <?php if (count($recent_appointments) > 0): ?>
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Patient Name</th>
                                            <th>Service</th>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($appointment['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['requested_service'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['selected_doctor'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($appointment['preferred_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['preferred_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php
                                                    $appointment_date = new DateTime($appointment['preferred_date']);
                                                    $today = new DateTime();
                                                    $today->setTime(0, 0, 0);
                                                    if ($appointment_date < $today) {
                                                        echo '<span class="badge badge-info">Completed</span>';
                                                    } elseif ($appointment_date == $today) {
                                                        echo '<span class="badge badge-warning">Today</span>';
                                                    } else {
                                                        echo '<span class="badge badge-success">Upcoming</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">No appointments found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2025 <a href="#">MediCare Constantine</a>.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed by</b> Ishak ABBAZ
        </div>
    </footer>
</div>

<script src="./plugins/jquery/jquery-3.6.0.min.js"></script>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> // downloaded -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> // downloaded -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script> // downloaded -->
</body>
</html>