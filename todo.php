<?php
include 'dbconnect.php'; // Verbindt met de database

// Voeg een nieuwe to-do lijst toe als het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $user_id = 1; // Voorbeeld user_id, dit moet dynamisch worden bepaald

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
$user_id = 1; // Voorbeeld user_id, dit moet dynamisch worden bepaald
$sql = "SELECT l.list_id, l.title, i.item_id, i.description, i.is_done 
        FROM todo_lists l 
        LEFT JOIN todo_items i ON l.list_id = i.list_id 
        WHERE l.user_id = ? 
        ORDER BY l.title, i.description";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$todos = [];
while ($row = $result->fetch_assoc()) {
    $todos[$row['list_id']]['title'] = $row['title'];
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
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .done {
            text-decoration: line-through;
        }
    </style>
</head>
<body>
    <h1>To-Do Lijsten</h1>
    <form method="POST" action="todo.php">
        <input type="text" name="title" placeholder="Titel van de To-Do Lijst" required>
        <div id="items">
            <input type="text" name="items[]" placeholder="To-Do Item" required>
        </div>
        <button type="button" onclick="addItem()">Voeg Item toe</button>
        <button type="submit">Voeg To-Do Lijst toe</button>
    </form>
    <div class="grid-container">
        <?php foreach ($todos as $list_id => $todo): ?>
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
            </div>
        <?php endforeach; ?>
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
    </script>
</body>
</html>
