<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    // Controleer of de gebruiker toegang heeft tot het item
    session_start();
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT l.user_id, s.user_id AS shared_user_id 
            FROM todo_items i 
            JOIN todo_lists l ON i.list_id = l.list_id 
            LEFT JOIN shared_todo_lists s ON l.list_id = s.list_id 
            WHERE i.item_id = ? AND (l.user_id = ? OR s.user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $item_id, $user_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Gebruiker heeft toegang, update de status van het item
        $stmt->close();

        $sql = "UPDATE todo_items SET is_done = NOT is_done WHERE item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();
}
?>
