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
		// echo 'Rsum<br>';
		// echo $Rsum[0][0] . $Rsum[0][1] . $Rsum[0][2]. $Rsum[0][3]. $Rsum[0][4];
		// echo "<br>";
		// echo $Rsum[1][0] . $Rsum[1][1] . $Rsum[1][2]. $Rsum[1][3]. $Rsum[1][4];
		// echo "<br>";
		// echo $Rsum[2][0] . $Rsum[2][1] . $Rsum[2][2]. $Rsum[2][3]. $Rsum[2][4];
		// echo "<br>";
		// echo $Rsum[3][0] . $Rsum[3][1] . $Rsum[3][2]. $Rsum[3][3]. $Rsum[3][4];
		// echo "<br>";
		// echo $Rsum[4][0] . $Rsum[4][1] . $Rsum[4][2]. $Rsum[4][3]. $Rsum[4][4];
		// echo "<br>";
		$trRsum = transitiveClosure($Rsum);
		$TtrRsum = matrixTranspose($trRsum);
		$symtrRsum = matrixConjunction($trRsum, $TtrRsum);

		$TRsum = matrixTranspose($Rsum);
		$symRsum = matrixConjunction($Rsum, $TRsum);
		$asymRsum = asymMatrix($Rsum, $symRsum);

		$asymRk = matrixConjunction($asymRsum,$symtrRsum);

		// echo 'asymRk<br>';
		// echo $asymRk[0][0] . $asymRk[0][1] . $asymRk[0][2]. $asymRk[0][3]. $asymRk[0][4];
		// echo "<br>";
		// echo $asymRk[1][0] . $asymRk[1][1] . $asymRk[1][2]. $asymRk[1][3]. $asymRk[1][4];
		// echo "<br>";
		// echo $asymRk[2][0] . $asymRk[2][1] . $asymRk[2][2]. $asymRk[2][3]. $asymRk[2][4];
		// echo "<br>";
		// echo $asymRk[3][0] . $asymRk[3][1] . $asymRk[3][2]. $asymRk[3][3]. $asymRk[3][4];
		// echo "<br>";
		// echo $asymRk[4][0] . $asymRk[4][1] . $asymRk[4][2]. $asymRk[4][3]. $asymRk[4][4];
		// echo "<br>";

		$Rsum = matrixSubtract($Rsum, $asymRk);
	} while (matrixElemsSum($asymRk) != 0);

	return $Rsum;
}

function demucron($matrix) {
	$row = [];

	for ($i = 0; $i < $len; $i++) {
		$row[$i] = 0;
		for ($j = 0; $j < $len; $j++) {
			$row[$i] += $matrix[$i][$j];
		}
	}

	return $row;
}

function returnData($matrix) {
	$row = demucron($matrix);

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