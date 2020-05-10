<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'connection.php';

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$query = 'SELECT matrix
			FROM data';

$result = mysqli_query($link, $query) or die("Ошибка" . mysqli_error($link));


if($result){
	$rows = mysqli_num_rows($result);

	$totalPreferenceMatrix = [];
	for ($i = 0 ; $i < $rows ; $i++) {
		$row = mysqli_fetch_row($result);
		$array = str_split($row[0]);
		$len = sqrt(sizeof($array));
		$n = 0;

		$R = [];
		for ($j = 0; $j < $len; $j++) { 
			for ($k = 0; $k < $len; $k++) { 
				$R[$j][$k] += $array[$n];
				$n++;
			}
		}

		$P = preferenceFromAdjacencyMatrix($R);

		// echo 'P<br>';
		// for ($j=0; $j < $len; $j++) { 
		// 	for ($k=0; $k < $len; $k++) { 
		// 		echo $P[$j][$k];
		// 	}
		// 	echo '<br>';
		// }

		for ($j = 0; $j < $len; $j++) { 
			for ($k = 0; $k < $len; $k++) { 
				$totalPreferenceMatrix[$j][$k] += $P[$j][$k];
			}
	 	}

		 $n = 0;
	}

	// echo 'total<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $totalPreferenceMatrix[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	$R = contourRemoval($totalPreferenceMatrix);
	$Rcap = transitiveClosure($R);
	$Pcap = preferenceFromAdjacencyMatrix($Rcap);
	$asymRcap = asymMatrix($Rcap);

	// echo 'R<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $R[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo 'Rcap<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $Rcap[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	// echo 'asymRcap<br>';
	// for ($i=0; $i < $len; $i++) { 
	// 	for ($j=0; $j < $len; $j++) { 
	// 		echo $asymRcap[$i][$j];
	// 	}
	// 	echo '<br>';
	// }

	$row = [];

	for ($i = 0; $i < $len; $i++) {
		for ($j = 0; $j < $len; $j++) {
			$row[$i] += $asymRcap[$i][$j];
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












function contourRemoval($matrix) {
	$Rsum = adjacencyFromTotalPreferenceMatrix($matrix);

	do{
		$trRsum = transitiveClosure($Rsum);
		$TtrRsum = matrixTranspose($trRsum);
		$symtrRsum = matrixConjunction($trRsum, $TtrRsum);

		$TRsum = matrixTranspose($Rsum);
		$symRsum = matrixConjunction($Rsum, $TRsum);
		$asymRsum = asymMatrix($Rsum, $symRsum);

		$asymRk = matrixConjunction($asymRsum,$symtrRsum);

		$Rsum = matrixSubtract($Rsum, $asymRk);
	} while (matrixElemsSum($asymRk) != 0);

	return $Rsum;
}

function totalPreferenceMatrixFill() {
	$total = [];
	for ($i = 0 ; $i < $rows ; $i++) {
		 $n = 0;

		 $R = [];
		 for ($j = 0; $j < $len; $j++) { 
				for ($k = 0; $k < $len; $k++) { 
					$R[$j][$k] += $array[$n];
					$n++;
				}
		 }

		 $P = preferenceFromAdjacencyMatrix($R);

		for ($j = 0; $j < $len; $j++) { 
			for ($k = 0; $k < $len; $k++) { 
				$total[$j][$k] = $P[$j][$k];
			}
	 	}

		 $n = 0;
	}
	
	return $total;
}

function adjacencyFromTotalPreferenceMatrix($matrix) {
	for ($i=0; $i < count($matrix); $i++) { 
		for ($j=0; $j < count($matrix); $j++) { 
			if($i == $j){
				$temp[$i][$j] = 1;
			}else{
				if($matrix[$i][$j] >= $matrix[$j][$i]){
					$temp[$i][$j] = 1;
				}else{
					$temp[$i][$j] = 0;
				}
			}
		}
	}
	return $temp;
}

function weightFromTotalPreferenceMatrix($matrix) {
	for ($i=0; $i < count($matrix); $i++) {
		for ($j=0; $j < count($matrix); $j++) { 
			if($i == $j){
				$temp[$i][$j] = 0;
			}else{
				if ($matrix[$i][$j] - $matrix[$j][$i] < 0) {
					$temp[$i][$j] = INF;
				}else{
					$temp[$i][$j] = $matrix[$i][$j] - $matrix[$j][$i];
				}
			}
		}
	}
	return $temp;
}

function preferenceFromAdjacencyMatrix($matrix) {
	for ($i=0; $i < count($matrix); $i++) { 
		for ($j=0; $j < count($matrix); $j++) { 
			if($i == $j){
				$temp[$i][$j] = 1;
			}else{
				if($matrix[$i][$j] & $matrix[$j][$i]){
					$temp[$i][$j] = 0.5;
				}else{
					$temp[$i][$j] = $matrix[$i][$j];
				}
			}
		}
	}
	return $temp;
}

function symMatrix($matrix) {
	return matrixConjunction($matrix, matrixTranspose($matrix));
}

function asymMatrix($matrix) {
	return matrixSubtract($matrix, symMatrix($matrix));
}

function matrixTranspose($matrix) {
	for ($i = 0; $i < count($matrix); $i++) {
		for ($j = $i + 1; $j < count($matrix); $j++) {
			 $temp = $matrix[$i][$j];
			 $matrix[$i][$j] = $matrix[$j][$i];
			 $matrix[$j][$i] = $temp;
		}
  }
  return $matrix;
}

function transitiveClosure($matrix) {
	// $temp = $matrix;
	for ($i=0; $i < count($matrix); $i++) { 
		for ($j=0; $j < count($matrix); $j++) { 
			for ($k=0; $k < count($matrix); $k++) { 
				$temp[$j][$k] = $matrix[$j][$k] | $matrix[$j][$i] & $matrix[$i][$k];
			}
		}
		$matrix = $temp;
	}
	return $matrix;
}

function matrixSubtract($matrix1, $matrix2) {
	for ($i = 0; $i < count($matrix1); $i++) {
		for ($j = 0; $j < count($matrix1); $j++) {
			 $temp[$i][$j] = $matrix1[$i][$j] - $matrix2[$i][$j];
		}
	}
  return $temp;
}

function matrixConjunction($matrix1, $matrix2) {
	for ($i = 0; $i < count($matrix1); $i++) {
		for ($j = 0; $j < count($matrix1); $j++) {
			 $matrix[$i][$j] = $matrix1[$i][$j] & $matrix2[$i][$j];
		}
	}
  return $matrix;
}

function matrixDisjunction($matrix1, $matrix2) {
	for ($i = 0; $i < count($matrix1); $i++) {
		for ($j = 0; $j < count($matrix1); $j++) {
			 $matrix = $matrix1[$i][$j] | $matrix2[$i][$j];
		}
	}
  return $matrix;
}

function matrixElemsSum($matrix) {
	$sum = 0;
	for ($i=0; $i < count($matrix); $i++) { 
		for ($j=0; $j < count($matrix); $j++) { 
			$sum += $matrix[$i][$j];
		}
	}
	return $sum;
}

mysqli_close($link);
?>