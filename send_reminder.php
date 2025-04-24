<?php

include 'd.php';

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $habit = $_POST['habit'] ?? '';
    $time = $_POST['time'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $sendReminder = isset($_POST['reminder']) ? true : false;

    // Validate inputs
    if (empty($email) || empty($habit) || empty($time) || empty($duration)) {
        die('Please fill all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format');
    }

    // Insert the data into the database
    $stmt = $conn->prepare("INSERT INTO habit_plans (email, habit, time, duration, reminder) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $email, $habit, $time, $duration, $sendReminder);

    if ($stmt->execute()) {
        // Data inserted successfully
        $stmt->close();

        // Only proceed with email if reminder is enabled
        if ($sendReminder) {
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'adithakurji@gmail.com'; // Your Gmail
                $mail->Password   = 'vslpdutxtseonbzm';     // Your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Recipients
                $mail->setFrom('adithakurji@gmail.com', 'HabitBuddy');
                $mail->addAddress($email); // Add recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Habit Reminder: ' . htmlspecialchars($habit);
                
                $message = "
                    <h2>Habit Reminder</h2>
                    <p><strong>Habit:</strong> " . htmlspecialchars($habit) . "</p>
                    <p><strong>Scheduled Time:</strong> " . htmlspecialchars($time) . "</p>
                    <p><strong>Duration:</strong> " . htmlspecialchars($duration) . " minutes</p>
                    <p>This is your reminder to work on your habit!</p>
                    <p>Stay consistent and build that habit!</p>
                ";
                
                $mail->Body = $message;
                $mail->AltBody = strip_tags($message);

                $mail->send();
                
                // Return success response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Habit planned successfully! Reminder email sent.'
                ]);
                exit();
                
            } catch (Exception $e) {
                // Return error response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
                ]);
                exit();
            }
        } else {
            // Return success response without sending email
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Habit planned successfully! (No reminder set)'
            ]);
            exit();
        }
    } else {
        // Error occurred while inserting into the database
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error occurred while saving habit plan.'
        ]);
        exit();
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

?>