<?php

declare(strict_types=1);

require "code/hotelFunctions.php";
require "code/hotelVariables.php";
require "vendor/autoload.php";

use Dotenv\Dotenv;
//ENV SETUP
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (isset($_POST["logOut"])) {
    session_start();
    session_destroy();
}

//Is user logged in?
session_start();
$loggedIn = false;
if (isset($_SESSION["loggedIn"]) === true) $loggedIn = true;
if (isset($_POST["user"], $_POST["apiKey"])) {
    $user = htmlspecialchars($_POST["user"], ENT_QUOTES);
    $apiKey = htmlspecialchars($_POST["apiKey"], ENT_QUOTES);

    if ($_ENV["USER_NAME"] !== $user || $_ENV["API_KEY"] !== $apiKey) {
        echo "Wrong username or password";
    } else {
        $loggedIn = true;
        $_SESSION["loggedIn"] = true;
    }
}
//Remove bookings from DB
if ($loggedIn === true) {
    $db = connect("hotels.db");
    $removedBookings = [];
    foreach ($_POST as $id => $delete) {
        if ($delete === "delete") $removedBookings[] = $id; //Adds bookings selected with checkboxes to array for deletion
    }
    foreach ($removedBookings as $booking) { //Could be made into one statement for efficiency
        $statement = $db->prepare("DELETE FROM bookings WHERE id = :id");
        $statement->bindParam(':id', $booking);
        $result = $statement->execute();
    }
}

//Update Costs
if ($loggedIn === true) {
    $costUpdated = false;
    $updatedRooms = [];
    foreach ($rooms as $roomType => $room) {
        if (isset($_POST[$roomType . "NewCost"])) {
            $newCost = intval($_POST[$roomType . "NewCost"]); //Values from html are always string. This also sanitizes

            if ($room["cost"] != $newCost) {
                $room["cost"] = $newCost;
                $costUpdated = true;
            }
            $updatedRooms[$roomType] = $room;
        }
    }
    if ($costUpdated === true) {
        $rooms = $updatedRooms;
        $hotelData = ["rooms" => $updatedRooms, "stars" => $stars, "extras" => $extras];

        file_put_contents("./code/hotelVariables.txt", json_encode($hotelData));
    }
}

//Get bookings from DB
if ($loggedIn === true) {


    $statement = $db->prepare("SELECT * FROM bookings");
    $statement->execute();
    $bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require "./header.php"; ?>
    <?php if ($loggedIn != true) : ?>
        <form method="post" action="">
            <label for="user">User</label>
            <input type="text" name="user">
            <label for="apiKey">API Key</label>
            <input type="text" name="apiKey">
            <button type="submit">Login</button>
        </form>
    <?php else : ?>

        <form method="post" action="">
            <h3>Inloggad!</h3>
            <?php $i = 0;

            foreach ($bookings as $booking) : $i++; ?>
                <label for="booking">Booking From: <?= $booking["arrival_date"] ?> Until: <?= $booking["departure_date"] ?> In room: <?= $booking["room_number"] ?></label>
                <input type="checkbox" name="<?= $booking['id'] ?>" value="delete">
                <br>


            <?php endforeach;
            if ($i > 0) : ?>
                <button type="submit">Delete selected?</button>
            <?php endif ?>
        </form>
        <form method="post" action="">
            <?php foreach ($rooms as $roomType => $room) : ?>
                <?= $roomType ?> Current cost: <?= $room["cost"] ?>
                <input type="number" name="<?= $roomType ?>NewCost" value=<?= $room["cost"] ?>>

                <br>


            <?php endforeach ?>
            <button type="submit">Update costs</button>
        </form>




        <form method="post" action="">
            <button type="submit" name="logOut" value="logOut">Log out</button>
        </form>

    <?php endif ?>

</body>

</html>
