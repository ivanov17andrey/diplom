<?php

header('Content-Type: text/html; charset=utf-8');

require_once 'connection.php';

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$query1 = 'INSERT INTO data VALUES (null,\'' . $_POST[name] . '\',\'' . $_POST[str] . '\')';

$result1 = mysqli_query($link, $query1) or die("Ошибка " . mysqli_error($link));

mysqli_close($link);

?>