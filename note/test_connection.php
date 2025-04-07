<?php

$servername = '143.198.92.51';
$username = 'shan_remote_connect';
$password = 'StrongPassword123!';
$dbname = 'slot_maker';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    exit('Connection failed: '.$conn->connect_error);
}
echo 'Connected successfully';
