<?php

declare(strict_types=1);
require "hotelFunctions.php";
require 'vendor/autoload.php';


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;
use benhall14\phpCalendar\Calendar as Calendar;


//ENV SETUP
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
//CALENDAR SETUP
$calendar = new Calendar;
$calendar->stylesheet();
$calendar->useMondayStartingDate();


echo $_ENV["USER_NAME"];

$db = connect("hotels.db");

?>

<!DOCTYPE html>
<html lang="en">

<head>
                    <meta charset="UTF-8" />
                    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>Document</title>

                    <link rel="stylesheet" href="style.css">
</head>

<body>
                    <header>
                                        <h1>The Good Morrow Hotel</h1>


                    </header>
                    <?php echo $calendar->draw(date('Y-01-01')); ?>



                    <script src="script.js"></script>
</body>

</html>
