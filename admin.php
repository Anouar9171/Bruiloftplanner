<?php
session_start();
/*
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
*/

include 'dbconnect.php';

// Haal alle gebruikers op
$sql = "SELECT id, username FROM users";
$result = $conn->query($sql);
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Haal alle gedeelde agenda's op
$sql = "SELECT e.event_description, u.username AS owner, su.username AS shared_with 
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        JOIN shared_agendas sa ON e.id = sa.event_id 
        JOIN users su ON sa.user_id = su.id";
$result = $conn->query($sql);
$shared_agendas = [];
while ($row = $result->fetch_assoc()) {
    $shared_agendas[] = $row;
}

// Haal alle gedeelde to-do lijsten op
$sql = "SELECT l.title, u.username AS owner, su.username AS shared_with 
        FROM todo_lists l 
        JOIN users u ON l.user_id = u.id 
        JOIN shared_todo_lists st ON l.list_id = st.list_id 
        JOIN users su ON st.user_id = su.id";
$result = $conn->query($sql);
$shared_todos = [];
while ($row = $result->fetch_assoc()) {
    $shared_todos[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        h1, h2 {
            text-align: center;
        }
        form {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form select, form button {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background: #333;
            color: #fff;
            cursor: pointer;
        }
        form button:hover {
            background: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Paneel</h1>
        <h2>Selecteer Gebruiker</h2>
        <form method="POST" action="admin.php">
            <label for="select_user_id">Kies een Gebruiker:</label>
            <select name="select_user_id" id="select_user_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Selecteer Gebruiker</button>
        </form>

        <?php if (isset($_POST['select_user_id'])): ?>
            <?php
            $selected_user_id = $_POST['select_user_id'];
            include 'dbconnect.php';

            // Haal alle to-do lijsten van de geselecteerde gebruiker op
            $sql = "SELECT list_id, title FROM todo_lists WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $selected_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $todo_lists = [];
            while ($row = $result->fetch_assoc()) {
                $todo_lists[] = $row;
            }

            // Haal alle agenda's van de geselecteerde gebruiker op
            $sql = "SELECT id, event_description FROM events WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $selected_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }

            $stmt->close();
            $conn->close();
            ?>

            <h2>Deel To-Do Lijsten van <?php echo htmlspecialchars($users[array_search($selected_user_id, array_column($users, 'id'))]['username']); ?></h2>
            <form method="POST" action="share_all_todos.php">
                <input type="hidden" name="selected_user_id" value="<?php echo $selected_user_id; ?>">
                <label for="share_user_id">Kies een Gebruiker om te delen:</label>
                <select name="share_user_id" id="share_user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Deel Alle To-Do Lijsten</button>
            </form>

            <h2>Deel Agenda Items van <?php echo htmlspecialchars($users[array_search($selected_user_id, array_column($users, 'id'))]['username']); ?></h2>
            <form method="POST" action="share_all_agendas.php">
                <input type="hidden" name="selected_user_id" value="<?php echo $selected_user_id; ?>">
                <label for="share_user_id">Kies een Gebruiker om te delen:</label>
                <select name="share_user_id" id="share_user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Deel Alle Agenda Items</button>
            </form>
        <?php endif; ?>

        <h2>Overzicht van Gedeelde Agenda's</h2>
        <table>
            <thead>
                <tr>
                    <th>Agenda Item</th>
                    <th>Eigenaar</th>
                    <th>Gedeeld met</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shared_agendas as $shared): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shared['event_description']); ?></td>
                        <td><?php echo htmlspecialchars($shared['owner']); ?></td>
                        <td><?php echo htmlspecialchars($shared['shared_with']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Overzicht van Gedeelde To-Do Lijsten</h2>
        <table>
            <thead>
                <tr>
                    <th>To-Do Lijst</th>
                    <th>Eigenaar</th>
                    <th>Gedeeld met</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shared_todos as $shared): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shared['title']); ?></td>
                        <td><?php echo htmlspecialchars($shared['owner']); ?></td>
                        <td><?php echo htmlspecialchars($shared['shared_with']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
