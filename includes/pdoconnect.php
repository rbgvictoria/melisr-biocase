<?php

$dsn = 'mysql:host=localhost;dbname=melisr';
$username = '';
$passwd = '';

try {
    $db = new PDO($dsn, $username, $passwd);
}
catch (PDOException $e) {
    exit('PDO connection error: ' . $e);
}

?>
