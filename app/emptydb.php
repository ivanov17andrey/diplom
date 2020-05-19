<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'connection.php';

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$query = 'DELETE FROM `data` WHERE `data`.`id` > 0';

$result = mysqli_query($link, $query) or die("Ошибка" . mysqli_error($link));


mysqli_close($link);
?>