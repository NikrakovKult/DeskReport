<?php
session_start();
require_once('connect.php');

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $query = "DELETE FROM users WHERE id =?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false));
    }
}
?>