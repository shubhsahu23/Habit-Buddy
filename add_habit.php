<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Ensure user is logged in
    $name = $_POST['habit_name'];
    $from = $_POST['time_from'];
    $to = $_POST['time_to'];
    $date = $_POST['habit_date'];

    $stmt = $conn->prepare("INSERT INTO habits (user_id, habit_name, time_from, time_to, habit_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $from, $to, $date);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
}
?>
