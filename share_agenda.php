<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['share_event_id']) && isset($_POST['share_user_id'])) {
    $share_event_id = $_POST['share_event_id'];
    $share_user_id = $_POST['share_user_id'];

    $sql = "INSERT INTO shared_agendas (event_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $share_event_id, $share_user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>
