<?php

declare(strict_types=1);

require "hotelFunctions.php";
require "hotelVariables.php";
require "vendor/autoload.php";


use Dotenv\Dotenv;
//ENV SETUP
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$loggedIn = false;

if (isset($_POST["user"], $_POST["apiKey"])) {
                    $user = htmlspecialchars($_POST["user"], ENT_QUOTES);
                    $apiKey = htmlspecialchars($_POST["apiKey"], ENT_QUOTES);

                    if ($_ENV["USER_NAME"] !== $user || $_ENV["API_KEY"] !== $apiKey) {
                                        echo "Wrong username or password";
                    } else {
                                        $loggedIn = true;
                    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Document</title>
</head>

<body>
                    <?php if ($loggedIn != true) : ?>
                                        <form method="post" action="">
                                                            <label for="user">User</label>
                                                            <input type="text" name="user">
                                                            <label for="apiKey">API Key</label>
                                                            <input type="text" name="apiKey">
                                                            <button type="submit">Login</button>
                                        </form>
                    <?php else : ?>


                                        Inloggad!

                    <?php endif ?>

</body>

</html>
