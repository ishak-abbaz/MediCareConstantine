<?php
    $mysqlClient = new PDO( 'mysql:host=localhost;dbname=medical_clinic;charset=utf8',
        'root',
        ''
    );
    echo 'connection established';
?>