<?php
include 'd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete review
    if (isset($_POST["delete_id"]) && isset($_POST["delete_name"])) {
        $id = intval($_POST["delete_id"]);
        $name = $conn->real_escape_string($_POST["delete_name"]);

        $deleteQuery = "DELETE FROM reviews WHERE id = $id AND name = '$name'";
        if (!$conn->query($deleteQuery)) {
            die("Delete Error: " . $conn->error);
        }
    }

    // Submit new review
    if (isset($_POST["name"]) && isset($_POST["comment"])) {
        $name = $conn->real_escape_string($_POST["name"]);
        $comment = $conn->real_escape_string($_POST["comment"]);

        $insertQuery = "INSERT INTO reviews (name, comment) VALUES ('$name', '$comment')";
        if (!$conn->query($insertQuery)) {
            die("Insert Error: " . $conn->error);
        }
    }

    $user = urlencode($_POST['name'] ?? $_POST['delete_name'] ?? '');
    header("Location: review.php?user=$user");
    exit();
}
?>
