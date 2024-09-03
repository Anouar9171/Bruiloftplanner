<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['list_id'])) {
    $list_id = $_POST['list_id'];

    // Controleer of de gebruiker toegang heeft tot de lijst
    session_start();
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT l.user_id, s.user_id AS shared_user_id 
            FROM todo_lists l 
            LEFT JOIN shared_todo_lists s ON l.list_id = s.list_id 
            WHERE l.list_id = ? AND (l.user_id = ? OR s.user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $list_id, $user_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Gebruiker heeft toegang, markeer de lijst als voltooid
        $stmt->close();

        $sql = "UPDATE todo_lists SET is_completed = 1 WHERE list_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $list_id);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();
}
?>
