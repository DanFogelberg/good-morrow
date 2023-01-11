<?php
$adress = "http://www.garagehider.com/good-morrow";



$data = json_decode(file_get_contents(__DIR__ . "/hotelVariables.json"), true);
$rooms = $data["rooms"]; //key = basic/average/high. Contains cost = int, roomNumber = int
$stars = $data["stars"]; //Star level of hotel.
$extras = $data["extras"];



$discounts = ["fullWeek" => true]; //True means discount is active and available. Should be moved into hotelVariables.txt so admin can edit.
