<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'dbconnect.php';
$user_id = $_SESSION['user_id']; // Dynamisch user_id instellen

// Voeg een nieuwe to-do lijst toe als het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];

    $sql = "INSERT INTO todo_lists (user_id, title) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $title);

    if ($stmt->execute()) {
        $list_id = $stmt->insert_id;
        foreach ($_POST['items'] as $item) {
            $sql = "INSERT INTO todo_items (list_id, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $list_id, $item);
            $stmt->execute();
        }
    } else {
        echo "Fout: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Haal alle to-do lijsten en items op voor de gebruiker
$sql = "SELECT l.list_id, l.title, l.is_completed, i.item_id, i.description, i.is_done 
        FROM todo_lists l 
        LEFT JOIN todo_items i ON l.list_id = i.list_id 
        LEFT JOIN shared_todo_lists s ON l.list_id = s.list_id 
        WHERE l.user_id = ? OR s.user_id = ? 
        ORDER BY l.title, i.description";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$todos = [];
while ($row = $result->fetch_assoc()) {
    $todos[$row['list_id']]['title'] = $row['title'];
    $todos[$row['list_id']]['is_completed'] = $row['is_completed'];
    $todos[$row['list_id']]['items'][] = [
        'item_id' => $row['item_id'],
        'description' => $row['description'],
        'is_done' => $row['is_done']
    ];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>To-Do Lijsten</title>
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
        form input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #333;
            color: #fff;
            cursor: pointer;
        }
        form button:hover {
            background: #555;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .grid-item h3 {
            margin-top: 0;
        }
        .done {
            text-decoration: line-through;
        }
        .grid-item ul {
            padding: 0;
            list-style: none;
        }
        .grid-item ul li {
            margin-bottom: 5px;
        }
        .grid-item ul li input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>To-Do Lijsten</h1>
        <form method="POST" action="todo.php">
            <input type="text" name="title" placeholder="Titel van de To-Do Lijst" required>
            <div id="items">
                <input type="text" name="items[]" placeholder="To-Do Item" required>
            </div>
            <button type="button" onclick="addItem()">Voeg Item toe</button>
            <button type="submit">Voeg To-Do Lijst toe</button>
        </form>
        <h2>Actieve Lijsten</h2>
        <div class="grid-container">
            <?php foreach ($todos as $list_id => $todo): ?>
                <?php if (!$todo['is_completed']): ?>
                    <div class="grid-item">
                        <h3><?php echo htmlspecialchars($todo['title']); ?></h3>
                        <ul>
                            <?php foreach ($todo['items'] as $item): ?>
                                <li class="<?php echo $item['is_done'] ? 'done' : ''; ?>">
                                    <input type="checkbox" <?php echo $item['is_done'] ? 'checked' : ''; ?> onclick="toggleDone(<?php echo $item['item_id']; ?>)">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button onclick="markAsCompleted(<?php echo $list_id; ?>)">Afgerond</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <h2>Afgeronde Lijsten</h2>
        <div class="grid-container">
            <?php foreach ($todos as $list_id => $todo): ?>
                <?php if ($todo['is_completed']): ?>
                    <div class="grid-item">
                        <h3><?php echo htmlspecialchars($todo['title']); ?></h3>
                        <ul>
                            <?php foreach ($todo['items'] as $item): ?>
                                <li class="<?php echo $item['is_done'] ? 'done' : ''; ?>">
                                    <input type="checkbox" <?php echo $item['is_done'] ? 'checked' : ''; ?> disabled>
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function addItem() {
            const itemsDiv = document.getElementById('items');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'items[]';
            input.placeholder = 'To-Do Item';
            input.required = true;
            itemsDiv.appendChild(input);
        }

        function toggleDone(itemId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "toggle_done.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("item_id=" + itemId);
        }

        function markAsCompleted(listId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "mark_completed.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("list_id=" + listId);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    location.reload();
                }
            };
        }
    </script>
</body>
</html>
