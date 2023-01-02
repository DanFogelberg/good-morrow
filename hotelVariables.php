<?php

$data = json_decode(file_get_contents(__DIR__ . "/hotelVariables.txt"), true);
$rooms = $data["rooms"]; //key = basic/average/high. Contains cost = int, roomNumber = int
$stars = $data["stars"]; //Star level of hotel.
$extras = $data["extras"];
