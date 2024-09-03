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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            margin: 0 5px;
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
            border-radius: 4px;
        }

        .tab.active {
            background-color: #0056b3;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .done {
            text-decoration: line-through;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>To-Do Lijsten</h1>
    <div class="tabs">
        <div class="tab active" onclick="showTab('todo')">To-Do</div>
        <div class="tab" onclick="showTab('completed')">Voltooide Taken</div>
    </div>
    <form method="POST" action="todo.php">
        <input type="text" name="title" placeholder="Titel van de To-Do Lijst" required>
        <div id="items">
            <input type="text" name="items[]" placeholder="To-Do Item" required>
        </div>
        <button type="button" onclick="addItem()">Voeg Item toe</button>
        <button type="submit">Voeg To-Do Lijst toe</button>
    </form>
    <div id="todo" class="grid-container">
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
    <div id="completed" class="grid-container" style="display: none;">
        <?php foreach ($completed_todos as $list_id => $todo): ?>
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
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    location.reload();
                }
            };
            xhr.send("item_id=" + itemId);
        }

        function showTab(tab) {
            document.getElementById('todo').style.display = tab === 'todo' ? 'grid' : 'none';
            document.getElementById('completed').style.display = tab === 'completed' ? 'grid' : 'none';
            document.querySelectorAll('.tab').forEach(function (el) {
                el.classList.remove('active');
            });
            document.querySelector('.tab.' + tab).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('#todo input[type="checkbox"]');
            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    if (allChecked) {
                        showTab('completed');
                    }
                });
            });
        });
    </script>
</body>
</html>
