<?php

require "../code/hotelVariables.php";
require "../code/hotelFunctions.php";
require "../code/bookingFunctions.php";
require "../vendor/autoload.php";



use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

header("Content-Type:application/json");
$response = [];
$db = connect("./hotels.db");


//Check Get and Post. Returns instructions if no set of correct variables
if (!isset($_POST["arrival"], $_POST["departure"], $_POST["room"], $_POST["transferCode"]) & !isset($_GET["bookings"]) & !isset($_GET["arrival"], $_GET["departure"], $_GET["room"])) {
    $response = [
        "usage" =>  "Make a POST request or GET Request",
        "POST" => [
            "form_params" => [
                "arrival" => "string: YYYY-MM-DD",
                "departure" => "string: YYYY-MM-DD",
                "room" => "string: 'basic'/'average'/'high'",
                "transferCode" => "string: uuid",
                "extras" => "Optional. array:[string: extra, string: extra ...] Available extras: poetryWaking (More to come)"
            ],
            "response" => "Array with message or error"
        ],
        "GET" => [

            "query" => [
                "bookings" => "true 'Get all bookings.'",
                "arrival" => "string: YYYY-MM-DD",
                "departure" => "string: YYYY-MM-DD",
                "room" => "string: 'basic'/'average'/'high'",
                "extras" => "Optional. array:[string: extra, string: extra ...] Available extras: poetryWaking (More to come)"
            ],
            "response" =>  "Array with contents depending on parameters sent: bookings returns all bookings. room + arrival + departure returns available = true/false and cost: int|float. extras can be added to this."
        ]
    ];

    echo json_encode($response);
    die();
}


//GET//////////////////////////////////////////////////////////////////////////////

//Gets bookings from DB
if (isset($_GET["bookings"])) {

    $statement = $db->prepare("SELECT * FROM bookings");
    $statement->execute();
    $bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
    $response["bookings"] = $bookings;
}
//Checks if room is availble and returns cost
if (isset($_GET["arrival"], $_GET["departure"], $_GET["room"])) {
    $bookedExtras = [];
    if (isset($_GET["extras"]) && is_array($_GET["extras"])) {
        foreach ($_GET["extras"] as $extra) {
            $extra = htmlspecialchars($extra, ENT_QUOTES);
            if (isset($extras[$extra])) $bookedExtras[] = $extras[$extra];
        }
    }
    $arrival = htmlspecialchars($_GET["arrival"], ENT_QUOTES);
    $departure = htmlspecialchars($_GET["departure"], ENT_QUOTES);
    $room = htmlspecialchars($_GET["room"], ENT_QUOTES);

    $result = checkBooking($arrival, $departure, $room, $rooms, $db);
    if ($result === true) {
        $response["available"] = true;
    } else {
        $response["error"] = $result;
    }

    $response["cost"] = totalCost($arrival, $departure, $rooms[$room]["cost"], $bookedExtras);
}

//Send back response from GET request, if any
if (!empty($response)) {
    echo json_encode($response);
    die();
}

//POST///////////////////////////////////////////////////////////////////
//If code got this far, it is a POST request with the correct variables

//Sanitize
$arrival = htmlspecialchars($_POST["arrival"], ENT_QUOTES);
$departure = htmlspecialchars($_POST["departure"], ENT_QUOTES);
$room = htmlspecialchars($_POST["room"], ENT_QUOTES);
$transferCode = htmlspecialchars($_POST["transferCode"], ENT_QUOTES);

$bookedExtras = [];
if (isset($_POST["extras"]) && is_array($_POST["extras"])) {
    foreach ($_POST["extras"] as $extra) {
        $extra = htmlspecialchars($extra, ENT_QUOTES);
        if (isset($extras[$extra])) $bookedExtras[] = $extras[$extra];
    }
}
$totalCost = totalCost($arrival, $departure, $rooms[$room]["cost"], $bookedExtras);
//Checks for potenial errors. Rooms is array of room types from hotelVariables
$result = checkTransferCode($transferCode, $rooms[$room]["cost"]);
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





//Finally transfer money. This is done last as an error with the database would be easier to fix than an error with the bank.
$result = transferMoney($transferCode);
if ($result !== true) $response["error"] = $result;
if (isset($response["error"])) {
    errorLog($response["error"]);
    echo json_encode($response);
    die();
}

$info = ["message" => "Very good. Enjoy your stay. But not too much, you might never leave."];
foreach ($bookedExtras as $extra) {
    if ($extra["name"] === "Poem") $info["poem"] = getPoem();
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
