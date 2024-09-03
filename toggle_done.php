<?php
include 'dbconnect.php'; // Verbindt met de database

$item_id = $_POST['item_id'];

$sql = "UPDATE todo_items SET is_done = NOT is_done WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);

if ($stmt->execute()) {
    echo "To-Do item bijgewerkt.";
} else {
    echo "Fout: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
