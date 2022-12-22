<?php

declare(strict_types=1);
require "hotelFunctions.php";
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;
use benhall14\phpCalendar\Calendar as Calendar;


$rooms = [];
//Index = room_number
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
$stmt = $db->query("SELECT * FROM bookings");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Add each booking to corresponding room calendar
foreach ($bookings as $booking) {
                    $rooms[$booking["room_number"]]["calendar"]->addEvent(
                                        $booking["start_date"],   # start date in either Y-m-d or Y-m-d
                                        $booking["end_date"],   # end date in either Y-m-d or Y-m-d
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
                                                            echo $room["calendar"]->draw(date('Y-01-01'));
                                        } ?>




                    <script src="script.js"></script>
</body>

</html>
