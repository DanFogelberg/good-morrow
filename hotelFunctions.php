<?php

declare(strict_types=1);

use GuzzleHttp\Client;

function connect(string $dbName): object
{

                    $dbPath = __DIR__ . '/' . $dbName;
                    $db = "sqlite:$dbPath";

                    // Open the database file and catch the exception if it fails.
                    try {
                                        $db = new PDO($db);
                                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                                        echo "Failed to connect to the database";
                                        throw $e;
                    }
                    return $db;
}

function guidv4(string $data = null): string
{
                    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
                    $data = $data ?? random_bytes(16);
                    assert(strlen($data) == 16);

                    // Set version to 0100
                    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
                    // Set bits 6-7 to 10
                    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

                    // Output the 36 character UUID.
                    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function isValidUuid(string $uuid): bool
{
                    if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
                                        return false;
                    }
                    return true;
}



function validateDate($date, $format = 'Y-m-d'): bool
{
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) === $date;
}

function dateWithin($date, $startDate, $endDate): bool
{
                    if (strtotime($date) >= strtotime($startDate) && strtotime($date) <= strtotime($endDate)) {
                                        return true;
                    } else {
                                        return false;
                    }
}


//NEED TO CHECK IF DATE FOR BOOKING HAS PASSED!
//Returns true if successful, otherwise error as string
function checkBooking($arrival, $departure, $room, $rooms, $db): string | bool
{

                    $response = [];

                    if (!validateDate($arrival) || !validateDate($departure)) {
                                        return "Please enter date in format: 'YYYY-MM-DD'";
                    } else if (date("Y-m-d") > $arrival) {
                                        return "Error. Day of arrival has already passed.";
                    } else if (strtotime($departure) <= strtotime($arrival)) {
                                        return "Departure date is before arrival.";
                    } else if (!dateWithin($arrival, "2023-01-01", "2023-01-31") || !dateWithin($departure, "2023-01-01", "2023-01-31")) {
                                        return "Only bookings in January allowed!";
                    } else if (!array_key_exists($room, $rooms)) {
                                        return "Invalid room type";
                    } else {

                                        //Get bookings from DB and check if already occupied
                                        $statement = $db->prepare("SELECT * FROM bookings where room_number = :room_number");
                                        $statement->bindParam(':room_number', $rooms[$room]["roomNumber"], PDO::PARAM_INT);
                                        $statement->execute();
                                        $bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($bookings as $booking) {

                                                            if ($booking["departure_date"] > $arrival && $booking["arrival_date"] < $departure) {
                                                                                return "Room is already occupied.";
                                                            }
                                        }
                    }

                    return true;
}

//Returns true if successful, otherwise error as string
function checkTransferCode($transferCode, $totalCost): string | bool
{
                    if (!isValidUuid($transferCode)) {
                                        return "Invalid transferCode format";
                    } else {
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
                                                            return "Error occured!" . $e;
                                        }
                                        if (array_key_exists("error", $response)) {
                                                            if ($response["error"] == "Not a valid GUID") {
                                                                                //The banks error message for a transferCode not being valid for enough can be misleading.
                                                                                return "An error has occured! $response[error]. This could be due to your Transfercode not being vaild for enough credit.";
                                                            }
                                                            return "An error has occured! $response[error]";
                                        }
                                        if (!array_key_exists("amount", $response) || $response["amount"] < $totalCost) {

                                                            return "Transfer code is not valdid for enough money.";
                                        }
                    }

                    return true;
}

//Put booking into database
function insertBooking($arrival, $departure, $room, $rooms, $db): bool
{


                    $statement = $db->prepare('INSERT INTO bookings(room_number, arrival_date, departure_date)
VALUES(:room_number, :arrival_date, :departure_date)');

                    $statement->bindParam(':room_number', $rooms[$room]["roomNumber"], PDO::PARAM_INT);
                    $statement->bindParam(':arrival_date', $arrival, PDO::PARAM_STR);
                    $statement->bindParam(':departure_date', $departure, PDO::PARAM_STR);
                    $result = $statement->execute();
                    if ($result != true) return false;
                    return true;
}



function transferMoney($transferCode): string | bool
{
                    $client = new GuzzleHttp\Client();
                    $options = [
                                        'form_params' => [
                                                            "user" => "Dan",
                                                            "transferCode" => $transferCode
                                        ]
                    ];

                    try {
                                        $result = $client->post("https://www.yrgopelago.se/centralbank/deposit", $options);
                                        $result = $result->getBody()->getContents();
                                        $result = json_decode($result, true);
                                        return true;
                    } catch (\Exception $e) {
                                        //Perhaps the best solution here would be to automatically remove the booking from the hotel?
                                        return "Booking successful but there was an error with the money transfer. Please contact the hotel to resolve this manually. Error:" . $e;
                    }
}


function totalCost($arrival, $departure, $roomCost, $extras = [])
{
                    $secondsBooked = strtotime($departure) - strtotime($arrival);
                    $daysBooked = $secondsBooked / (60 * 60 * 24);
                    $totalCost = $roomCost * $daysBooked;

                    foreach ($extras as $extra) {
                                        $totalCost += $extra["cost"] * $daysBooked;
                    }
                    return $totalCost;
}
