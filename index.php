<?php

declare(strict_types=1);
require "hotelFunctions.php";
require "hotelVariables.php";
require "vendor/autoload.php";


use Dotenv\Dotenv;
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
//ENV SETUP
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

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

                    <link rel="stylesheet" href="style.css">
</head>

<body>
                    <header>
                                        <h1>The Good Morrow Hotel</h1>


                    </header>
                    <?php foreach ($rooms as $room) {
                    ?> <h2><?= $room["quality"] ?> Price: <?= $roomTypes[$room["quality"]]["cost"] ?></h2> <?php
                                                                                                                        echo $room["calendar"]->draw(date('2023-01-01'));
                                                                                                    } ?>






                    <form method="post" action="./booking.php">
                                        <label for="transfer_code">Transfer Code</label>
                                        <input type="text" name="transfer_code">
                                        <label for="arrival">Arrival</label>
                                        <input type="date" name="arrival" min="2023-01-01" max="2023-01-31">
                                        <label for="departure">Departure</label>
                                        <input type="date" name="departure" min="2023-01-01" max="2023-01-31">
                                        <select name="room">
                                                            <option value="basic">Basic</option>
                                                            <option value="average">Average</option>
                                                            <option value="high">High</option>
                                        </select>
                                        <input type="checkbox" id="poetryWaking" name="poetryWaking" value="poetryWaking">
                                        <label for="poetryWaking"> Waking by poetry reading. Price: <?= $extras["poetryWaking"]["cost"] ?> </label><br>


                                        <button type="submit">Book!</button>


                    </form>
                    <script src="script.js"></script>
</body>

</html>
