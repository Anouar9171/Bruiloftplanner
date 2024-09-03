<?php
session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: agenda.php');
        } else {
            echo "Ongeldig wachtwoord.";
        }
    } else {
        echo "Gebruiker niet gevonden.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <div id="branding">
                <h1>Agenda App</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="register.php">Registreren</a></li>
                </ul>
            </nav>
        </header>
        <form method="post">
            Gebruikersnaam: <input type="text" name="username" required><br>
            Wachtwoord: <input type="password" name="password" required><br>
            <input type="submit" value="Inloggen">
        </form>
    </div>
</body>
</html>
