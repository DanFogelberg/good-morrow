<?php

declare(strict_types=1);
require "hotelFunctions.php";
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;
use benhall14\phpCalendar\Calendar as Calendar;


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
///////////////////////////////
//PREPARED STATEMENTS PLEASE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!/////////
///////////////////////////////
//Get bookings from DB
$stmt = $db->query("SELECT * FROM bookings");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

///////////////////////////////////////////
////////////Calendar should show until Departuredate-1
////////////////////////////////////////////
//Add each booking to corresponding room calendar
foreach ($bookings as $booking) {
                    $rooms[$booking["room_number"]]["calendar"]->addEvent(
                                        $booking["arrival_date"],   # start date in either Y-m-d or Y-m-d
                                        $booking["departure_date"],   # end date in either Y-m-d or Y-m-d
                                        '',  # event name text
                                        true,           # should the date be masked - boolean default true
                    );
}
//echo $_ENV["USER_NAME"];
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
                    ?> <h2><?= $room["quality"] ?></h2> <?php
                                                            echo $room["calendar"]->draw(date('2023-01-01'));
                                        } ?>






                    <form method="post" action="./booking.php">
                                        <label for="transfer_code">Transfer Code</label>
                                        <input type="text" name="transfer_code">
                                        <label for="arrival">Arrival</label>
                                        <input type="date" name="arrival" min="2023-01-01" max="2023-01-31">
                                        <label for="departure">Departure</label>
                                        <input type="date" name="departure" min="2023-01-01" max="2023-01-31">
                                        <select>
                                                            <option value="basic">Basic</option>
                                                            <option value="average">Average</option>
                                                            <option value="high">High</option>
                                        </select>
                                        <button type="submit">Book!</button>

                    </form>
                    <script src="script.js"></script>
</body>

</html>
