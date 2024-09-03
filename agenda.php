<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'dbconnect.php';
$user_id = $_SESSION['user_id'];

// Functie om de dagen van de komende maand te krijgen
function getDaysInMonth() {
    $days = [];
    $currentDate = new DateTime();
    $currentDate->modify('first day of this month');
    $endDate = clone $currentDate;
    $endDate->modify('first day of next month');

    while ($currentDate < $endDate) {
        $days[] = $currentDate->format('Y-m-d');
        $currentDate->modify('+1 day');
    }

    return $days;
}

$daysInMonth = getDaysInMonth();

// Haal alle evenementen voor de komende maand op
$sql = "SELECT * FROM events WHERE user_id='$user_id' AND event_date >= CURDATE() AND event_date < DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
$result = $conn->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[$row['event_date']][] = $row['event_description'];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Agenda</title>
    <link rel="stylesheet" href="styles.css">
    <a href="todo.php">To Do List</a>
    <style>
        /* Grid CSS */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .grid-item {
            background-color: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div id="branding">
                <h1>Agenda App</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="logout.php">Uitloggen</a></li>
                </ul>
            </nav>
        </header>
        <h2>Agenda voor de komende maand</h2>
        <div class="grid-container">
            <?php foreach ($daysInMonth as $day): ?>
                <div class="grid-item">
                    <a href="#" class="day-link" data-date="<?php echo $day; ?>"><?php echo $day; ?></a>
                    <ul>
                        <?php if (isset($events[$day])): ?>
                            <?php foreach ($events[$day] as $event): ?>
                                <li><?php echo $event; ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Niks op de planning</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modaal venster -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <div id="modal-body"></div>
        </div>
    </div>

    <script>
        // JavaScript voor het modaal venster
        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];

        document.querySelectorAll('.day-link').forEach(function(element) {
            element.onclick = function() {
                var date = this.getAttribute('data-date');
                fetch('day.php?date=' + date)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('modal-body').innerHTML = data;
                        modal.style.display = "block";
                    });
            };
        });

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
