<?php

require "../hotelVariables.php";
require "../hotelFunctions.php";
require "../vendor/autoload.php";




use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

header("Content-Type:application/json");
$response = [];
$db = connect("./hotels.db");

//Check Post
if (!isset($_POST["arrival"], $_POST["departure"], $_POST["room"], $_POST["transferCode"])) {
                    $response = [
                                        "usage" => "Make a POST request",
                                        "form_params" => [
                                                            "arrival" => "string: YYYY-MM-DD",
                                                            "departure" => "string: YYYY-MM-DD",
                                                            "room" => "string: 'basic'/'average'/'high'",
                                                            "transferCode" => "string: uuid",
                                                            "extras" => "Optional. array:[string: extra, string: extra ...] Available extras: poetryWaking (More to come)"
                                        ],
                                        "response" => "Array with message or error"
                    ];
                    echo json_encode($response);
                    die();
}

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
echo $totalCost;
die();
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

//Finally transfer money. This is done last as an error with the database would be easier to fix than an error with the bank.
$result = transferMoney($transferCode);
if ($result !== true) $response["error"] = $result;
if (isset($response["error"])) {
                    echo json_encode($response);
                    die();
}

if (count($bookedExtras) > 0) {
                    $features = "";
                    foreach ($bookedExtras as $extra) {
                                        $features .= $extra["name"];
                    }
}
$bookingResponse = [
                    "island" => "Point Nemo",
                    "hotel" => "The Good Morrow",
                    "arrival_date" => $arrival,
                    "departure_date" => $departure,
                    "total_cost" => $totalCost,
                    "stars" => $stars,
                    "features" => $totalCost,
                    "additional_info" => "Very good. Enjoy your stay. But not too much, you might never leave."
];
echo json_encode($bookingResponse);
