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
                    <title>Document</title>

                    <link rel="stylesheet" href="./css/header.css">
                    <link rel="stylesheet" href="./css/style.css">
                    <link rel="stylesheet" href="./css/booking.css">
</head>

<body>
                    <header>
                                        <h1>The Good Morrow Hotel</h1>
                                        <nav>
                                                            <a href="<?= $adress ?>/admin.php ">Admin</a>
                                                            <a href="<?= $adress ?>/api/bookings.php ">API</a>
                                        </nav>



                    </header>
                    <h2 class="offer"> Right now you get 20% off if you book a whole week or more!</h2>
                    <?php foreach ($rooms as $room) {
                    ?> <h2 class="room-title"><?= $room["quality"] ?> Price: <?= $roomTypes[$room["quality"]]["cost"] ?></h2> <?php
                                                                                                                                            echo $room["calendar"]->draw(date('2023-01-01'));
                                                                                                                        } ?>






                    <form class="booking" method="post" action="./booking.php">
                                        <div class="booking-row">
                                                            <label for="transfer_code">Transfer Code:</label>
                                                            <input type="text" name="transfer_code">
                                                            <label for="room">Room Standard:</label>
                                                            <select name="room">
                                                                                <option value="basic">Basic (cost:<?= $roomTypes["basic"]["cost"] ?>)</option>
                                                                                <option value="average">Average (cost:<?= $roomTypes["average"]["cost"] ?>)</option>
                                                                                <option value="high">High (cost:<?= $roomTypes["high"]["cost"] ?>)</option>
                                                            </select>
                                        </div>
                                        <div class="booking-row">
                                                            <label for="arrival">Arrival:</label>
                                                            <input type="date" name="arrival" min="2023-01-01" max="2023-01-31">
                                                            <label for="departure">Departure:</label>
                                                            <input type="date" name="departure" min="2023-01-01" max="2023-01-31">
                                        </div>
                                        <div class="booking-row">
                                                            <input type="checkbox" id="poetryWaking" name="poetryWaking" value="poetryWaking">
                                                            <label for="poetryWaking"> Waking by poetry reading. Price: <?= $extras["poetryWaking"]["cost"] ?> </label><br>


                                                            <button type="submit">Book!</button>
                                        </div>


                    </form>
                    <!-- <script src="script.js"></script> -->
</body>

</html>
