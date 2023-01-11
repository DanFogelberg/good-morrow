<?php

declare(strict_types=1);
//Functions for validating bookings, calculating costs, validating transfer codes, adding transfer codes to account, adding bookings to database and logging results/errors


//Validation//////////////////////////////////////////////////////////////////


//Returns true if valid or string with error
function checkBooking(string $arrival, string $departure, string $room, array $rooms, object $db): string | bool
{

    $response = [];

    if (!validateDate($arrival) || !validateDate($departure)) {
        return "Please enter date in format: 'YYYY-MM-DD'";
    } else if (/*date("Y-m-d") > $arrival*/false) {
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

function validateDate(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function dateWithin(string $date, string $startDate, string $endDate): bool
{
    if (strtotime($date) >= strtotime($startDate) && strtotime($date) <= strtotime($endDate)) {
        return true;
    } else {
        return false;
    }
}

//Returns true if successful, otherwise error as string
function checkTransferCode(string $transferCode, int|float $totalCost): string | bool
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
        //Extra check because bank API doesn't handle floats very well. Accurate enough for 2 decimals.
        if (!array_key_exists("amount", $response) || $response["amount"] * 100 < intval($totalCost * 100)) {

            return "Transfer code is not valdid for enough money.";
        }
    }

    return true;
}


//Cost calculation////////////////////////////////////////////////////////////////////////


function totalCost(string $arrival, string $departure, int|float $roomCost, array $extras = []): float | int
{

    $secondsBooked = strtotime($departure) - strtotime($arrival);
    $daysBooked = $secondsBooked / (60 * 60 * 24);
    $totalCost = $roomCost * $daysBooked;

    foreach ($extras as $extra) {
        $totalCost += $extra["cost"]; //*daysBooked if want to add daily features
    }
    $totalCost = checkDiscounts($arrival, $departure, $roomCost, $extras, $daysBooked, $totalCost);
    return $totalCost;
}


//Will check for all discounts. Has extra parameters that will probably be used for future discounts
function checkDiscounts(string $arrival, string $departure, int|float $roomCost, array $extras, int $daysBooked, int|float $totalCost): float|int
{
    $totalCost = fullWeekDiscount($daysBooked, $totalCost);
    return $totalCost;
}

//Specific 20% for full week
function fullWeekDiscount(int $daysBooked, int|float $totalCost): float
{
    global $discounts; //Discounts from hotelVariables.php
    if ($daysBooked >= 7 & $discounts["fullWeek"] === true) {
        $totalCost = $totalCost * 0.8;
    }
    return $totalCost;
}





//Database manipulation////////////////////////////////////////////////////////////////////////

//Put booking into database
function insertBooking(string $arrival, string $departure, string $room, array $rooms, object $db): bool
{


    $statement = $db->prepare('INSERT INTO bookings(room_number, arrival_date, departure_date) VALUES(:room_number, :arrival_date, :departure_date)');

    $statement->bindParam(':room_number', $rooms[$room]["roomNumber"], PDO::PARAM_INT);
    $statement->bindParam(':arrival_date', $arrival, PDO::PARAM_STR);
    $statement->bindParam(':departure_date', $departure, PDO::PARAM_STR);
    $result = $statement->execute();
    if ($result != true) return false;
    return true;
}



function transferMoney(string $transferCode): string | bool
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
    return true;
}



//Log//////////////////////////////////////////////////////////////////////

function errorLog(string $error)
{
    date_default_timezone_set("Europe/Stockholm");
    file_put_contents(__DIR__ . "/../log/errorLog.txt", $error . " " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);
}

function bookingLog(string $booking)
{
    date_default_timezone_set("Europe/Stockholm");
    file_put_contents(__DIR__ . "/../log/bookingLog.txt", "New booking at: " . " " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);
    file_put_contents(__DIR__ . "/../log/bookingLog.txt", $booking . "\n", FILE_APPEND); //Not perfect format, but this is mostly just for fun
}


//Other //////////////////////////////////////////////////////////////////////////////////


//Returns random poem
function getPoem(): string
{
    $poems = [
        "Jag föddes sent på jorden
        Vilken fantastisk tur
        Jag lever nu
        Men ändå mest
        I det förgångna",

        "Det råder halvdager i min säng
        Skymningslandets bleka sken
        Mellan min misantropi och kärlek till mörkret
        Jag går för att möta en människa

        En människa sann
        En människa i niohundratjugoentusensexhundra pixlar
        Som kanske kan
        Lämna mig ifred",

        "Genom gyllene strålar
        och med hjälp av vitaminer
        känner jag din närvaro
        så som sjuka ser syner.

        Vi vandrar genom nya världar
        i mitt vardagsrum.
        Gud andas i hallen,
        du kysser hans mun

        Du kom för att säga
        något enkelt till mig.
        Att jag glömt hur mycket
        jag har saknat mig."
    ];


    return $poems[array_rand($poems)];
}
