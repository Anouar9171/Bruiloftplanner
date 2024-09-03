<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_user_id']) && isset($_POST['share_user_id'])) {
    $selected_user_id = $_POST['selected_user_id'];
    $share_user_id = $_POST['share_user_id'];

    // Haal alle agenda items van de geselecteerde gebruiker op
    $sql = "SELECT id FROM events WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row['id'];
    }

    // Deel alle agenda items met de geselecteerde gebruiker
    $stmt = $conn->prepare("INSERT INTO shared_agendas (event_id, user_id) VALUES (?, ?)");
    foreach ($events as $event_id) {
        $stmt->bind_param("ii", $event_id, $share_user_id);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();

    header('Location: admin.php');
    exit();
}
?>
