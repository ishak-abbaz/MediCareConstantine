<?php
    require_once 'connection.php';
    $first_name = '';
    $last_name = '';
    $birthdate = '';
    $gender = '';
    $requested_service = '';
    $preferred_date = '';
    $preferred_time = '';
    $email = '';
    $phone = '';
    $address = '';
    $allergies_history = '';
    $selected_doctor = '';
    $medical_file = '';
    $created_at = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $first_name         = $_POST['first_name'] ?? '';
        $last_name          = $_POST['last_name'] ?? '';
        $birthdate          = $_POST['birthdate'] ?? '';
        $gender             = $_POST['gender'] ?? '';
        $requested_service  = $_POST['requested_service'];
        $preferred_date     = $_POST['preferred_date'] ?? '';
        $preferred_time     = $_POST['preferred_time'];
        //    ?? '00:00:00'           ?? ''
        echo "Requsted Service: $requested_service, Preferred Time: $preferred_time";
        $email              = $_POST['email'] ?? '';
        $phone              = $_POST['phone'] ?? '';
        $address            = $_POST['address'] ?? '';
        $allergies_history  = $_POST['allergies_history'] ?? '';
        $selected_doctor    = $_POST['selected_doctor'] ?? '';
        $medical_file       = '';  // still empty for now
        $created_at         = date('Y-m-d H:i:s') ?? '';
        echo "\ndata got successfully\n";
        // This query is valid and will work (hard-coded values)
        //    $add_query = "INSERT INTO appointments
        //        (first_name, last_name, birthdate, gender, requested_service,
        //         preferred_date, preferred_time, email, phone, address,
        //         allergies_history, selected_doctor, medical_file, created_at)
        //        VALUES (?, ?, ?, ?, ?,
        //         ?, ?, ?, ?, ?,
        //         ?, ?, ?, ?)";
        //
        //    $add_statement = $mysqlClient->prepare($add_query);
        //
        //    // This is the ONLY correct way here:
        //    $add_statement->execute([$first_name, $last_name, $birthdate, $gender, $requested_service,
        //         $preferred_date, $preferred_time, $email, $phone, $address,
        //         $allergies_history, $selected_doctor, $medical_file, $created_at]);   // ← no parameters needed because values are already in the query
        // OR if you want to use the form values instead (recommended):
        // $add_statement->execute([
        //     $first_name, $last_name, $birthdate, $gender, $requested_service,
        //     $preferred_date, $preferred_time, $email, $phone, $address,
        //     $allergies_history, $selected_doctor, $medical_file, $created_at
        // ]);
        echo "\ndata inserted successfully 22222222222222222\n";
    }
?>