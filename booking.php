<?php

declare(strict_types=1);

require "code/hotelFunctions.php";
require "code/bookingFunctions.php";
require "code/hotelVariables.php"; //Room costs
require "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;
//ENV SETUP
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');
$response = [];
$db = connect("hotels.db");

//Check Post
if (empty($_POST["transfer_code"]) || empty($_POST["room"])) {
    $response["error"] = "Please enter transfer code and room properly.";
    echo json_encode($response);
    die();
} else if (empty($_POST["arrival"]) || empty($_POST["departure"])) {
    $response["error"] = "Please set dates!";
    echo json_encode($response);
    die();
}
//Sanitize
$transferCode = htmlspecialchars($_POST["transfer_code"], ENT_QUOTES);
$room = htmlspecialchars($_POST["room"], ENT_QUOTES);
$arrival = htmlspecialchars($_POST["arrival"], ENT_QUOTES);
$departure = htmlspecialchars($_POST["departure"], ENT_QUOTES);

$bookedExtras = [];
foreach ($extras as $key => $extra) {
    if (isset($_POST[$key]) === true) $bookedExtras[] = $extra;
}

$totalCost = totalCost($arrival, $departure, $rooms[$room]["cost"], $bookedExtras);
//Checks for any possible errors or problems with booking. Rooms is array of room types from hotelVariables
$result = checkTransferCode($transferCode, $totalCost);
if ($result !== true) $response["error"] = $result;
$result = checkBooking($arrival, $departure, $room, $rooms, $db);
if ($result !== true) $response["error"] = $result;

if (isset($response["error"])) {
    echo json_encode($response);
    die();
}

//Insert booking into database
$result = insertBooking($arrival, $departure, $room, $rooms, $db);
if ($result !== true) $response["error"] = "Database error on booking insert.";
if (isset($response["error"])) {
    errorLog($response["error"]);
    echo json_encode($response);
    die();
}

// Transfer money. Done after putting booking into database because fixing database in case of bank error seems easier than the opposite.
$result = transferMoney($transferCode);
if ($result !== true) $response["error"] = $result;
if (isset($response["error"])) {
    errorLog($response["error"]);
    echo json_encode($response);
    die();
}

//Return confirmation
$info = ["message" => "Very good. Enjoy your stay. But not too much, you might never leave."];
foreach ($bookedExtras as $extra) {
    if ($extra["name"] === "Poem") $info["poem"] = getPoem();
    if ($extra["name"] === "The Hotel Song Music Video") $info["musicVideo"] = "This is your secret link to the amazing music video: " . $_ENV["MUSIC_VIDEO"];
}
$bookingResponse = [
    "island" => "Point Nemo",
    "hotel" => "The Good Morrow",
    "arrival_date" => $arrival,
    "departure_date" => $departure,
    "total_cost" => $totalCost . "$",
    "stars" => $stars,
    "features" => $bookedExtras,
    "additional_info" => $info
];
$bookingResponse = json_encode($bookingResponse);
bookingLog($bookingResponse);
echo $bookingResponse;
