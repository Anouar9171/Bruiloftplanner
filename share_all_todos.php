<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_user_id']) && isset($_POST['share_user_id'])) {
    $selected_user_id = $_POST['selected_user_id'];
    $share_user_id = $_POST['share_user_id'];

    // Haal alle to-do lijsten van de geselecteerde gebruiker op
    $sql = "SELECT list_id FROM todo_lists WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $todo_lists = [];
    while ($row = $result->fetch_assoc()) {
        $todo_lists[] = $row['list_id'];
    }

    // Deel alle to-do lijsten met de geselecteerde gebruiker
    $stmt = $conn->prepare("INSERT INTO shared_todo_lists (list_id, user_id) VALUES (?, ?)");
    foreach ($todo_lists as $list_id) {
        $stmt->bind_param("ii", $list_id, $share_user_id);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();

    header('Location: admin.php');
    exit();
}
?>
