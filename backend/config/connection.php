<?php

header("Access-Control-Allow-Origin: *"); // Allow all origins, or specify a domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Updated to include PUT
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "sqlcodeblitz2025";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

?> 
