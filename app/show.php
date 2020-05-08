<?php

header('Content-Type: text/html; charset=utf-8'); 

require_once 'connection.php';

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$query = "SELECT * 
            FROM data 
            ORDER BY id";

$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 

// $query = "SELECT ch.id, ex.name, alt.name, ch.order 
// 			FROM choices ch, experts ex, alternatives alt 
// 			WHERE ch.experts_id = ex.id AND ch.alternatives_id = alt.id
//             ORDER BY ch.id";

// $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 

if($result)
{
    echo '<h1>База ответов экспертов:</h1>';

    $rows = mysqli_num_rows($result);
     
    echo "<table><tr><th>Id</th><th>Эксперт</th><th>Матрица</th></tr>";

    for ($i = 0 ; $i < $rows ; ++$i)
    {
        $row = mysqli_fetch_row($result);
        
        echo "<tr>";

        for ($j = 0 ; $j < 3 ; ++$j) echo "<td>$row[$j]</td>";

        echo "</tr>";
    }

    echo "</table>";
     
    mysqli_free_result($result);
} 

// if($result)
// {
// 	echo '<h1>База ответов экспертов:</h1>';

//     $rows = mysqli_num_rows($result);
     
//     echo "<table><tr><th>Id</th><th>Эксперт</th><th>Альтернатива</th><th>Место</th></tr>";

//     for ($i = 0 ; $i < $rows ; ++$i)
//     {
//         $row = mysqli_fetch_row($result);
        
//         echo "<tr>";

//         for ($j = 0 ; $j < 4 ; ++$j) echo "<td>$row[$j]</td>";

//         echo "</tr>";
//     }

//     echo "</table>";
     
//     mysqli_free_result($result);
// } 

mysqli_close($link);

?>