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
if (isset($_POST["arrival"], $_POST["departure"], $_POST["roomType"], $_POST["transferCode"])) {
                    $response = ["Message" => "Hello. Det fungerar ju."];
} else {
                    $response = [
                                        "usage" => "Make a POST request",
                                        "form_params" => [
                                                            "arrival" => "int: yyyymmdd",
                                                            "departure" => "int: yyyymmdd",
                                                            "roomType" => "string: basic/average/high",
                                                            "transferCode" => "string: uuid"
                                        ],
                                        "response" => "array with message or error(s)"
                    ];
}



echo json_encode($response);
