<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Registratie succesvol!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
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
                    <li><a href="login.php">Inloggen</a></li>
                </ul>
            </nav>
        </header>
        <form method="post">
            Gebruikersnaam: <input type="text" name="username" required><br>
            Wachtwoord: <input type="password" name="password" required><br>
            <input type="submit" value="Registreren">
        </form>
    </div>
</body>
</html>
