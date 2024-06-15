<?php
// delete_note.php

$conn = mysqli_connect("localhost", "root", "", "DeskReport");

if (!$conn) {
    die("Connection failed: ". mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    try {
        $query = "DELETE FROM zametki WHERE id =?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false));
        }
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
}

mysqli_close($conn);
?>