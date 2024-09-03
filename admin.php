<?php
session_start();
include 'dbconnect.php';

// Controleer of de gebruiker een admin is
// Dit is een vereenvoudigde controle, je kunt dit uitbreiden met een echte admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];

    $sql = "INSERT INTO events (user_id, event_date, event_description) VALUES ('$user_id', '$event_date', '$event_description')";
    $conn->query($sql);
}

$sql = "SELECT * FROM users";
$users = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <div id="branding">
                <h1>Admin Paneel</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="agenda.php">Agenda</a></li>
                    <li><a href="logout.php">Uitloggen</a></li>
                </ul>
            </nav>
        </header>
        <h2>Gebruiker toevoegen aan agenda</h2>
        <form method="post">
            Gebruiker:
            <select name="user_id" required>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                <?php endwhile; ?>
            </select><br>
            Datum: <input type="date" name="event_date" required><br>
