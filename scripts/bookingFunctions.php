<?php
//Functions for validating bookings, calculating costs, validating transfer codes, adding transfer codes to account and adding bookings to database


//Validation//////////////////////////////////////////////////////////////////


//Returns true if valid or string with error
function checkBooking($arrival, $departure, $room, $rooms, $db): string | bool
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


//Cost calculation////////////////////////////////////////////////////////////////////////


function totalCost($arrival, $departure, $roomCost, $extras = []): float | int
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
function checkDiscounts($arrival, $departure, $roomCost, $extras, $daysBooked, $totalCost): float | int
{
    $totalCost = fullWeekDiscount($daysBooked, $totalCost);
    return $totalCost;
}

//Specific 20% for full week
function fullWeekDiscount($daysBooked, $totalCost): float
{
    global $discounts; //Discounts from hotelVariables.php
    if ($daysBooked >= 7 & $discounts["fullWeek"] === true) {
        $totalCost = $totalCost * 0.8;
    }
    return $totalCost;
}





//Database manipulation////////////////////////////////////////////////////////////////////////

//Put booking into database
function insertBooking($arrival, $departure, $room, $rooms, $db): bool
{


    $statement = $db->prepare('INSERT INTO bookings(room_number, arrival_date, departure_date) VALUES(:room_number, :arrival_date, :departure_date)');

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


//Response////////////////////////////////////////////////////////////////////////

function createBookingResponse()
{
}
