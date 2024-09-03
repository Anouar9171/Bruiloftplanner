<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'dbconnect.php';
$user_id = $_SESSION['user_id'];
$date = $_GET['date'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $time = $_POST['time'];
    $description = $_POST['description'];
    $event_description = $time . ' - ' . $description;

    $sql = "INSERT INTO events (user_id, event_date, event_description) VALUES ('$user_id', '$date', '$event_description')";
    if ($conn->query($sql) === TRUE) {
        header('Location: agenda.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Plan een nieuw evenement</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <div id="branding">
                <h1>Plan een nieuw evenement</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="agenda.php">Terug naar agenda</a></li>
                    <li><a href="logout.php">Uitloggen</a></li>
                </ul>
            </nav>
        </header>
        <form method="post">
            Tijd: <input type="time" name="time" required><br>
            Beschrijving: <textarea name="description" required></textarea><br>
            <input type="submit" value="Toevoegen">
        </form>
    </div>
</body>
</html>
