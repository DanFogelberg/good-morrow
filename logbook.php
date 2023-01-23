<?php

declare(strict_types=1);
require "code/hotelFunctions.php";
require "code/hotelVariables.php";
require "vendor/autoload.php";

$logbook = "logbook.json";

// check and validate for a logbook
if (file_exists(__DIR__ . '/' . $logbook)) {

    $visits = json_decode(file_get_contents(__DIR__ . '/' . $logbook), true);

    if (isset($visits)) {
        if (array_key_exists('vacation', $visits)) {
            $visits = $visits['vacation'];
        } else {
            echo "No vacation found";
            exit;
        }
    } else {
        echo "No data found";
        exit;
    }
} else {
    echo "No logbook found";
    exit;
}

// sort the visits
usort($visits, function ($a, $b) {
    $aDate = new DateTime($a['arrival_date']);
    $bDate = new DateTime($b['arrival_date']);

    if ($aDate == $bDate) {
        return 0;
    }
    return ($aDate < $bDate) ? -1 : 1;
});


// FACTS

//CONNECT DB
$db = connect("./hotels.db");
//Get bookings from DB
$statement = $db->prepare("SELECT * FROM bookings");
$statement->execute();
$bookings = $statement->fetchAll(PDO::FETCH_ASSOC);

// //Get rooms from DB
// $statement = $db->prepare("SELECT * FROM rooms");
// $statement->execute();
// $rooms = $statement->fetchAll(PDO::FETCH_ASSOC);



$amountOfBookings = 0;
$totalRevenue = 0;
foreach ($bookings as $booking) {
    $amountOfBookings++;
    $arrivalDate = new DateTime($booking['arrival_date']);
    $departureDate = new DateTime($booking['departure_date']);
    $roomNumber = $booking['room_number'];
    $cost = 0;

    $amountOfDaysStaying = intval($departureDate->diff($arrivalDate)->format('%d'));
    for ($i = 0; $i < $amountOfDaysStaying; $i++) {
        foreach ($rooms as $room) {
            if ($room['roomNumber'] == $roomNumber) {
                $cost += $room['cost'];
            }
        }
    }

    $totalRevenue += $cost;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>logbook</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/logbook.css">

</head>

<body>
    <?php require "./header.php"; ?>
    <h1>logbook</h1>

    <ul class="logbook-wrapper">
        <?php
        foreach ($visits as $visit) :
        ?>
            <li class="logbook-item">
                <h2>
                    <?= $visit['hotel'] ?> with <?= $visit['stars'] ?> stars
                </h2>
                <h3>
                    <?= $visit['island'] ?>
                </h3>
                <p>
                    from <?= $visit['arrival_date'] ?> to <?= $visit['departure_date'] ?>
                </p>
                <?php
                if (isset($visit['features'])) : ?>
                    <ul class="features">
                        <?php
                        foreach ($visit['features'] as $feature) : ?>
                            <li>
                                <h4>
                                    <?= $feature['name'] ?>
                                </h4>
                                <p>cost: <?= $feature['cost'] ?>$</p>
                            </li>
                        <?php
                        endforeach;
                        ?>
                    </ul>
                <?php
                endif;
                ?>
                <p class="logbook-item-total-cost">total cost: <?= $visit['total_cost'] ?>$</p>
            </li>
        <?php
        endforeach;
        ?>

    </ul>
    <article class="facts">
        <h2>hotel facts</h2>
        <p>amount of bookings: <?= $amountOfBookings ?>$</p>
        <p>total revenue: <?= $totalRevenue ?>$</p>
        <p>revenue per booking: <?= $totalRevenue / $amountOfBookings ?>$</p>
    </article>
</body>

</html>
