<?php
header("Content-Type:application/json");
require "../hotelVariables.php";
require "../hotelFunctions.php";

/*
                    arrival
                    departure
                    roomType
                    transferCode
*/
//Check Post
if (!isset($_POST["arrival"], $_POST["departure"], $_POST["roomType"], $_POST["transferCode"])) {
                    $response = [
                                        "usage" => "Make a POST request",
                                        "form_params" => [
                                                            "arrival" => "string: YYYY-MM-DD",
                                                            "departure" => "string: YYYY-MM-DD",
                                                            "roomType" => "string: basic/average/high",
                                                            "transferCode" => "string: uuid"
                                        ],
                                        "response" => "Array with message or error"
                    ];
} else {
                    //Sanitize
                    $arrival = htmlspecialchars($_POST["arrival"], ENT_QUOTES);
                    $departure = htmlspecialchars($_POST["departure"], ENT_QUOTES);
                    $roomType = htmlspecialchars($_POST["roomType"], ENT_QUOTES);
                    $transferCode = htmlspecialchars($_POST["transferCode"], ENT_QUOTES);

                    if (!validateDate($arrival) || !validateDate($departure)) {
                                        $response = ["Error" => "Please enter date in format: 'YYYY-MM-DD'"];
                    } else if (strtotime($departure) <= strtotime($arrival)) {
                                        $response = ["Error" => "Departure date is before arrival."];
                    } else if (!dateWithin($arrival, "2023-01-01", "2023-01-31") || !dateWithin($departure, "2023-01-01", "2023-01-31")) {
                                        $response = ["Error" => "Only bookings in January allowed!"];
                    } else if (!array_key_exists($roomType, $roomTypes)) {
                                        $response = ["Error" => "Invalid room type"];
                    } else if (!isValidUuid($transferCode)) {
                                        $response = ["Error" => "Invalid transferCode format"];
                    } else {
                                        connect("../hotel.db");
                                        $response = ["Message" => "Det funkar!"];
                    }




                    //Checks still needed. If date is within parameters and is available;

}



echo json_encode($response);
