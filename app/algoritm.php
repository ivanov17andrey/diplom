<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'connection.php';

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$query = 'SELECT matrix
			FROM data';

$result = mysqli_query($link, $query) or die("Ошибка" . mysqli_error($link));

//Алгоритм
//$tempMatrix =  [];

//Заполнение суммарной матрицы предпочтений
// $totalPreferenceMatrix = [];

if($result){
    $rows = mysqli_num_rows($result);

    for ($i = 0 ; $i < $rows ; $i++)
    {
        $row = mysqli_fetch_row($result);

		  $array = str_split($row[0]);
		  $len = sqrt(sizeof($array));
        $n = 0;

      //   foreach ($array as $char) {
      //   	 echo $char;
      //   }

        // echo '<br><br>';

      //   echo 'Матрица суммарных предпочтений<br>';
        for ($j = 0; $j < $len; $j++) { 
			//   $totalPreferenceMatrix[$j] = [];
       		for ($k = 0; $k < $len; $k++) { 
       			$totalPreferenceMatrix[$j][$k] += $array[$n];
       			$n++;
       			// echo $totalPreferenceMatrix[$j][$k];
       		}
       		// echo '<br>';
        }

        $n = 0;
      //   echo '<br>';
    }

    //Заполнение матрицы смежности
    $adjacencyMatrix = [];

	for ($i=0; $i < $len; $i++) { 
		$adjacencyMatrix[$i] = [];
		for ($j=0; $j < $len; $j++) { 
			if($i == $j){
				$adjacencyMatrix[$i][$j] = 1;
			}else{
				if($totalPreferenceMatrix[$i][$j] >= $totalPreferenceMatrix[$j][$i]){
					$adjacencyMatrix[$i][$j] = 1;
				}else{
					$adjacencyMatrix[$i][$j] = 0;
				}
			}
		}
	}

	// echo 'Матрица смежности<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $adjacencyMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo '<br>';

	//Заполнение матрицы предпочтений
    $preferenceMatrix = [];

	for ($i=0; $i < $len; $i++) {
		$preferenceMatrix[$i] = []; 
		for ($j=0; $j < $len; $j++) { 
			if($i == $j){
				$preferenceMatrix[$i][$j] = 1;
			}else{
				if($adjacencyMatrix[$i][$j] == $adjacencyMatrix[$j][$i]){
					$preferenceMatrix[$i][$j] = 0.5;
				}else{
					$preferenceMatrix[$i][$j] = $adjacencyMatrix[$i][$j];
				}
			}
		}
	}

	// echo 'Матрица предпочтений<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $preferenceMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo '<br>';

	//Заполнение матрицы весов
	$weightMatrix = [];

	for ($i=0; $i < $len; $i++) { 
		$weightMatrix[$i] = [];
		for ($j=0; $j < $len; $j++) { 
			if($i == $j){
				$weightMatrix[$i][$j] = 0;
			}else{
				if ($totalPreferenceMatrix[$i][$j] - $totalPreferenceMatrix[$j][$i] < 0) {
					$weightMatrix[$i][$j] = Infinity;
				}else{
					$weightMatrix[$i][$j] = $totalPreferenceMatrix[$i][$j] - $totalPreferenceMatrix[$j][$i];
				}
			}
		}
	}

	// echo 'Матрица весов<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $weightMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo '<br>';

	//Матрица смежности и предпочтений не содержащая противоречивых контуров
	for ($i=0; $i < $len; $i++) { 
		for ($j=0; $j < $len; $j++) { 
			if($weightMatrix[$i][$j] == 1){
				$adjacencyMatrix[$i][$j] = 0;
				$preferenceMatrix[$i][$j] = 0;
			}
		}
	}

	// echo 'Матрица смежности не содержащая противоречивых контуров<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $adjacencyMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo '<br>';

	// echo 'Матрица предпочтений не содержащая противоречивых контуров<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $preferenceMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	//Транзитивное замыкание 
	$newAdjacencyMatrix = $adjacencyMatrix;
	for ($i=0; $i < $len; $i++) { 
		for ($j=0; $j < $len; $j++) { 
			for ($k=0; $k < $len; $k++) { 
				$newAdjacencyMatrix[$j][$k] = $adjacencyMatrix[$j][$k] | $adjacencyMatrix[$j][$i] & $adjacencyMatrix[$i][$k];
			}
		}
	}

	// echo '<br>';

	// echo 'Матрица смежности не содержащая противоречивых контуров + транзитивное замыкание<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $newAdjacencyMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	//Матрица предпочтений + транзитивное замыкание
	$newPreferenceMatrix = $preferenceMatrix;
	for ($i=0; $i < $len; $i++) { 
		for ($j=0; $j < $len; $j++) { 
			if($i == $j){
				$newPreferenceMatrix[$i][$j] = 1;
			}else{
				if($newAdjacencyMatrix[$i][$j] == $newAdjacencyMatrix[$j][$i]){
					$newPreferenceMatrix[$i][$j] = 0.5;
				}else{
					$newPreferenceMatrix[$i][$j] = $newAdjacencyMatrix[$i][$j];
				}
			}
		}
	}

	// echo '<br>';

	// echo 'Матрица предпочтений не содержащая противоречивых контуров + транзитивное замыкание<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $newPreferenceMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	$row = [];

	for ($i = 0; $i < $len; $i++) {
		for ($j = 0; $j < $len; $j++) {
			$row[$i] += $newPreferenceMatrix[$i][$j];
		}
	}

	for ($i = 0; $i < $len; $i++) {
		$num = $i + 1;
		$name = 'alternativa' . $num;
		$al[$i] = array('alt' => $name, 'sum' => $row[$i]);
	}

	$rows = [];

	for ($i = 0; $i < $len; $i++) { 
		$rows[$i] = $al[$i];
	}

	$result = json_encode($rows);
	echo $result;

    mysqli_free_result($result);
} 

mysqli_close($link);
?>