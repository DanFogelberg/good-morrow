<?php

declare(strict_types=1);
require "hotelFunctions.php";
require "hotelVariables.php";
require "vendor/autoload.php";

use benhall14\phpCalendar\Calendar as Calendar;
//Room stats from hotelVariables.php
$roomTypes = $rooms;


$rooms = [];
//Index equals room_number
$rooms[1] = [
    "quality" => "basic",
    "bookings" => [],
    "calendar" => new Calendar
];
$rooms[2] = [
    "quality" => "average",
    "bookings" => [],
    "calendar" => new Calendar
];
$rooms[3] = [
    "quality" => "high",
    "bookings" => [],
    "calendar" => new Calendar
];
//Calendars setup
foreach ($rooms as $room) {
    $room["calendar"]->stylesheet();
    $room["calendar"]->useMondayStartingDate();
}


//CONNECT DB
$db = connect("./hotels.db");


//Get bookings from DB
$statement = $db->prepare("SELECT * FROM bookings");
$statement->execute();
$bookings = $statement->fetchAll(PDO::FETCH_ASSOC);



//Add each booking to corresponding room calendar
foreach ($bookings as $booking) {
    //Room is only occupied until day before departure
    $last_occupied = new DateTime($booking["departure_date"]);
    $last_occupied->modify('-1 day');

    $rooms[$booking["room_number"]]["calendar"]->addEvent(
        $booking["arrival_date"],   # start date in either Y-m-d
        $last_occupied->format('Y-m-d'),   # end date in either Y-m-d
        '',  # event name text
        true,           # should the date be masked - boolean default true
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>The Good Morrow Hotel</title>

    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/booking.css">

</head>

<body>
    <?php require "./header.php"; ?>
    <h2 class="offer"> Right now you get 20% off if you book a whole week or more!</h2>
    <div class="calendarSelect">
        <h3 data-calendarnumber="0">Basic: <?= $roomTypes["basic"]["cost"] ?></h3>
        <h3 data-calendarnumber="1">Average: <?= $roomTypes["average"]["cost"] ?></h3>
        <h3 data-calendarnumber="2">High: <?= $roomTypes["high"]["cost"] ?></h3>
    </div>


    </div>
    <?php
    foreach ($rooms as $room) :
    ?>
        <div class=calendarContainer> <!-- Make kebab-case -->
            <div>
                <div class="image-container">
                    <img src="<?= $roomTypes[$room["quality"]]["image"] ?>">
                </div>
                <h2 class="room-title"><?= $room["quality"] ?> Price: <?= $roomTypes[$room["quality"]]["cost"] ?></h2>

            </div>



            <?php echo $room["calendar"]->draw(date('2023-01-01')); ?>


        </div>
    <?php endforeach ?>






    <!-- Should add $adress to action= on final version -->
    <form class="booking" method="post" action="./booking.php">
        <div class="booking-row">
            <label for="transfer_code">Transfer Code:</label>
            <input type="text" name="transfer_code">
        </div>
        <div class="booking-row">
            <label for="room">Room Standard:</label>
            <select name="room">
                <option value="basic" data-calendarnumber="0">Basic (Cost:<?= $roomTypes["basic"]["cost"] ?>)</option>
                <option value="average" data-calendarnumber="1">Average (Cost:<?= $roomTypes["average"]["cost"] ?>)</option>
                <option value="high" data-calendarnumber="2">High (Cost:<?= $roomTypes["high"]["cost"] ?>)</option>
            </select>
        </div>
        <div class="booking-row">
            <label for="arrival">Arrival:</label>
            <input type="date" name="arrival" id="arrival" min="2023-01-01" max="2023-01-31">
            <label for="departure">Departure:</label>
            <input type="date" name="departure" id="departure" min="2023-01-01" max="2023-01-31">
        </div>
        <div class="booking-row">
            Extras:
            <input type="checkbox" class="extra" name="poetryWaking" value=<?= $extras["poetryWaking"]["cost"] ?>>
            <label for="poetryWaking"> Waking by poetry reading (Cost: <?= $extras["poetryWaking"]["cost"] ?>) </label><br>




        </div>
        <div class="booking-row">
            <button type="submit">Book!</button>
            <p>Total Cost: </p>
        </div>


    </form>
    <script>
        //Sending data for use in javascript
        const roomTypes = <?php echo json_encode($roomTypes); ?>;
    </script>
    <script src="script.js">

    </script>
</body>

</html>
