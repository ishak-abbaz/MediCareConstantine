<?php

    function getDatabaseConnection(): PDO
    {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=medical_clinic;charset=utf8',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
//    $mysqlClient = new PDO( 'mysql:host=localhost;dbname=medical_clinic;charset=utf8',
//        'root',
//        ''
//    );
?>