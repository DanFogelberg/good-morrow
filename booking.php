<?php
//CONNECT DB
$db = connect("./hotels.db");
//CHECK BOOKING VALIDITY
if (empty($_POST["arrival"]) || empty($_POST["departure"])) {
                    echo "Please set dates!";
                    die();
}


$arrival = strtotime($_POST["arrival"]);
$departure = strtotime($_POST["departure"]);

if ($departure <= $arrival) {
                    echo "Time of departure is before arrival.";
                    die();
}




//Put booking into DB



//RETURN CONFIRMATION
?>
DET ÄR NU SÅ JÄVLA BOKAT!
