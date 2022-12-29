<?php

declare(strict_types=1);
header('Content-Type: application/json');
require "hotelFunctions.php";
require "hotelVariables.php"; //Room prices
require "vendor/autoload.php";




use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;



//Check transferCode and room type from Post and sanitize
if (empty($_POST["transfer_code"]) || empty($_POST["room"])) {
                    echo "Please enter transfer code and cost properly.";
                    die();
} else {
                    $transferCode = htmlspecialchars($_POST["transfer_code"], ENT_QUOTES);
                    switch ($_POST["room"]) {
                                        case 1:
                                                            $totalCost = $basicPrice;
                                                            break;
                                        case 2:
                                                            $totalCost = $averagePrice;
                                                            break;
                                        case 3:
                                                            $totalCost = $highPrice;
                                                            break;
                                        default:
                                                            echo "Room not available";
                                                            die();
                    }
}

//Check with central bank if transferCode is valid for totalCost
$client = new GuzzleHttp\Client();
$options = [
                    'form_params' => [
                                        "transferCode" => $transferCode, "totalCost" => $totalCost
                    ]
];


try {
                    $response = $client->post("https://www.yrgopelago.se/centralbank/transferCode", $options);
                    $response = $response->getBody()->getContents();
                    $response = json_decode($response, true);
} catch (\Exception $e) {
                    echo "Error occured!" . $e;
                    die();
}
if (array_key_exists("error", $response)) {
                    echo "An error has occured! $response[error]";
                    die();
}
if (!array_key_exists("amount", $response) || $response["amount"] < $totalCost) {

                    echo "Transfer code is not valdid for enough money.";
}






$db = connect("./hotels.db");
//Check and sanitize rest of Post
if (empty($_POST["arrival"]) || empty($_POST["departure"])) {
                    echo "Please set dates!";
                    die();
} else if (!isset($_POST["room"])) {
                    echo "No room selected!";
                    die();
} else {
                    $room_number = (int)$_POST["room"];
                    $arrival = htmlspecialchars($_POST["arrival"], ENT_QUOTES);
                    $departure = htmlspecialchars($_POST["departure"], ENT_QUOTES);
}

//Check validity of booking dates
$arrivalTimestamp = strtotime($_POST["arrival"]);
$departureTimestamp = strtotime($_POST["departure"]);
if ($departureTimestamp <= $arrivalTimestamp) {
                    echo "Time of departure is before arrival.";
                    die();
} else {
                    //Get bookings from DB and check if already occupied
                    $statement = $db->prepare("SELECT * FROM bookings where room_number = :room_number");
                    $statement->bindParam(':room_number', $room_number, PDO::PARAM_INT);
                    $statement->execute();
                    $bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($bookings as $booking) {
                                        if ($booking["departure_date"] > $arrival && $booking["arrival_date"] < $departure) {
                                                            echo "Room already occupied!";
                                                            die();
                                        }
                    }
}




//Put booking into database
$statement = $db->prepare('INSERT INTO bookings(room_number, arrival_date, departure_date)
VALUES(:room_number, :arrival_date, :departure_date)');

$statement->bindParam(':room_number', $room_number, PDO::PARAM_INT);
$statement->bindParam(':arrival_date', $arrival, PDO::PARAM_STR);
$statement->bindParam(':departure_date', $departure, PDO::PARAM_STR);
$result = $statement->execute();
if ($result != true) {
                    echo "Database error.";
                    die();
}


// Transfer money
// $options = [
//                     'form_params' => [
//                                         "user" => "Dan",
//                                         "transferCode" => $transferCode
//                     ]
// ];

// try {
//                     $response = $client->post("https://www.yrgopelago.se/centralbank/deposit", $options);
//                     $response = $response->getBody()->getContents();
//                     $response = json_decode($response, true);
// } catch (\Exception $e) {
//                     echo "Error occured!" . $e;
//                     die();
// }




//RETURN CONFIRMATION


$bookingResponse = [
                    "island" => "Point Nemo",
                    "hotel" => "The Good Morrow",
                    "arrival_date" => $arrival,
                    "departure_date" => $departure,
                    "total_cost" => $totalCost,
                    "stars" => $stars,
                    "features" => "None",
                    "additional_info" => "Very good. Enjoy your stay. But not too much, you might never leave."
];
echo json_encode($bookingResponse);
