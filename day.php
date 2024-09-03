<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'dbconnect.php';
$user_id = $_SESSION['user_id'];
$date = $_GET['date'];

$sql = "SELECT * FROM events WHERE user_id='$user_id' AND event_date='$date'";
$result = $conn->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row['event_description'];
}

// Functie om tijdslots te genereren
function generateTimeSlots() {
    $times = [];
    for ($i = 0; $i < 24; $i++) {
        $times[] = sprintf('%02d:00', $i);
        $times[] = sprintf('%02d:30', $i);
    }
    return $times;
}

$timeSlots = generateTimeSlots();
?>

<h2>Agenda-items voor <?php echo $date; ?></h2>
<a href="plan.php?date=<?php echo $date; ?>" style="float: right;">Klik hier om iets in te plannen</a>
<ul>
    <?php foreach ($timeSlots as $time): ?>
        <li><?php echo $time; ?> - 
            <?php 
            $found = false;
            foreach ($events as $event) {
                if (strpos($event, $time) === 0) {
                    echo $event;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "Geen evenementen";
            }
            ?>
        </li>
    <?php endforeach; ?>
</ul>
