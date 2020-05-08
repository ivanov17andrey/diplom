$(document).ready(function($){
	var deviceAgent = navigator.userAgent.toLowerCase();
	
	if (deviceAgent.match(/(iphone|ipod|ipad)/)) {
		$('.bg').addClass('scroll');
		$('.header').addClass('scroll');
	}
});

document.getElementById('ranging'). classList.toggle('hidden');
document.getElementById('pair'). classList.toggle('hidden');

document.getElementById('show-ranging').addEventListener('click', function(){
		let ranging = document.getElementById('ranging');
		let pair = document.getElementById('pair');
		let buttonRanging = document.getElementById('show-ranging');
		let buttonPair = document.getElementById('show-pair');
		if (!buttonRanging.classList.contains('comparition__method_active')) {
			if (buttonPair.classList.contains('comparition__method_active')) {
				buttonPair.classList.toggle('comparition__method_active');
				if (!pair.classList.contains('hidden')) {
					pair.classList.toggle('hidden');
				}
			}
			buttonRanging.classList.toggle('comparition__method_active');
			ranging.classList.toggle('hidden');
		}
		// if (!ranging.classList.contains('hidden')){
		// 	ranging.classList.toggle('hidden');
		// }
});

document.getElementById('show-pair').addEventListener('click', function(){
	let ranging = document.getElementById('ranging');
	let pair = document.getElementById('pair');
	let buttonRanging = document.getElementById('show-ranging');
	let buttonPair = document.getElementById('show-pair');
	if (!buttonPair.classList.contains('comparition__method_active')) {
		if (buttonRanging.classList.contains('comparition__method_active')) {
			buttonRanging.classList.toggle('comparition__method_active');
			if (!ranging.classList.contains('hidden')) {
				ranging.classList.toggle('hidden');
			}
		}
		buttonPair.classList.toggle('comparition__method_active');
		pair.classList.toggle('hidden');
	}
	// if (!pair.classList.contains('hidden')){
	// 	pair.classList.toggle('hidden');
	// }
});

document.getElementById('ranging-send').addEventListener('click', function() {
	let input = document.getElementById('ranging-name');
	if(input.value.trim() != ""){
		name = input.value;
		fillingMatrixRanging();
		stringRanging = matrixToString(matrixRanging);
		sendData(stringRanging);
		document.getElementById('show-ranging').classList.toggle('hidden');
		document.getElementById('ranging').classList.toggle('hidden');
		alert('Спасибо за ответ!')
	}else{
		input.value = '';
		alert('Введите имя');
	}
});

document.getElementById('show-result').addEventListener('click', function() {
	document.getElementById('result-table').innerHTML = '';
	receiveData();
});

function receiveData(){
	$.ajax({
		type: 'GET',
		url:'algoritm.php',
		success:function(msg){
			let data = JSON.parse(msg);
			showResult(data);
		},
		error: function(request) {
			console.log("ERROR", request);
		}
	})
}

function showResult(result) {
	result.sort(function(a, b){
		return a.sum - b.sum;
	})

	for (let i = 0; i < numOfAlternatives; i++) {
		let alternativaNumber = Number(result[i].alt[11]) - 1;
		result[i].alt = alternatives[alternativaNumber];
	}

	let alternativesSorted = [];

	let order = 1;

	alternativesSorted[0] = {
		name: result[0].alt,
		order: order
	};

	for (let i = 1; i < numOfAlternatives; i++) {
		if (result[i].sum > result[i - 1].sum) {
			order++
		}
		alternativesSorted[i] = {
			name: result[i].alt,
			order: order
		};
	}

	let table;
	let rowElem;

	for (let i = 0; i < numOfAlternatives; i++) {
		table = document.getElementById('result-table');

		rowElem = document.createElement('div');
		rowElem.classList.toggle('result__row');
		rowElem.innerHTML = "<div class='result__cell'>" + alternativesSorted[i].name + "</div><div class='result__cell'>" + alternativesSorted[i].order + "-е место</div>";

		table.append(rowElem);
	}

}

function makeResultBlock(alterObject) {
	let alternativaNumber = alterObject.alt[11] - 1;
	let alternativa = alternatives[alternativaNumber];
	let sum = alterObject.sum;
}


let numOfAlternatives = 6;
let alternatives = ['Bell 429 WLG', 'Bell 407 GXP', 'Airbus AS365 N3+', 'Airbus H145', 'Leonardo AW109', 'Sikorskiy S-76D'];
let name;

// Попарное сравнение

let matrixPair = createEMatrix(numOfAlternatives);
let tempMatrix = createEMatrix(numOfAlternatives);
let stringPair = '';
let row = 0, col = 1, iteration = 1;

document.getElementById('box').addEventListener('click', function(event) {
	let id = event.target.id;

	switch (id) {
		case 'better':
			if (document.getElementById('pair-name').value.trim() == '') {
				alert('Введите Ваше имя');
				return;
			}
			document.getElementById('pair-name').disabled = 'true';
			matrixPair[col][row] = 0;
			matrixPair[row][col] = 1;
			checkTransitiveClosure();
			break;

		case 'worse':
			if (document.getElementById('pair-name').value.trim() == '') {
				alert('Введите Ваше имя');
				return;
			}
			document.getElementById('pair-name').disabled = 'true';
			matrixPair[col][row] = 1;
			matrixPair[row][col] = 0;
			checkTransitiveClosure();
			break;

		case 'equal':
			if (document.getElementById('pair-name').value.trim() == '') {
				alert('Введите Ваше имя');
				return;
			}
			document.getElementById('pair-name').disabled = 'true';
			matrixPair[col][row] = 1;
			matrixPair[row][col] = 1;
			checkTransitiveClosure();
			break;

		case 'unequal':
			if (document.getElementById('pair-name').value.trim() == '') {
				alert('Введите Ваше имя');
				return;
			}
			document.getElementById('pair-name').disabled = 'true';
			matrixPair[col][row] = 0;
			matrixPair[row][col] = 0;
			refreshCounters();
			checkTransitiveClosure();
			break;
	
		default:
			break;
	}
});

function checkTransitiveClosure() {
	matrixPair = transitiveClosure(matrixPair);

	while (matrixPair[col][row] == 1 || matrixPair[row][col] == 1) {
		refreshCounters();
		if (iteration == numOfAlternatives) {
			break;
		}
	}

	showNextQuestion();
}

function refreshCounters() {
	if (col < numOfAlternatives - 1) {
		row++;
		col++;
	} else {
		iteration++;
		row = 0;
		col = iteration;
	}
}

function showNextQuestion() {
	if (iteration != numOfAlternatives) {
		document.getElementById('better').textContent = `${alternatives[row]} лучше  ${alternatives[col]}`;
		document.getElementById('worse').textContent = `${alternatives[row]} хуже  ${alternatives[col]}`;
		document.getElementById('equal').textContent = `${alternatives[row]} равны  ${alternatives[col]}`;
		document.getElementById('unequal').textContent = `${alternatives[row]} несравнимы  ${alternatives[col]}`;
	} else {
		name = document.getElementById('pair-name').value;
		stringPair = matrixToString(matrixPair);
		sendData(stringPair);
		document.getElementById('box').classList.toggle('hidden');
		document.getElementById('show-pair').classList.toggle('hidden');
		alert('Спасибо за ответ!');
	}
}

function transitiveClosure(matrix) {
	let len = matrix.length;
	for (let i = 0; i < len; i++) {
		for (let j = 0; j < len; j++) {
			for (let k = 0; k < len; k++) {
				tempMatrix[j][k] = matrix[j][k] || matrix[j][i] && matrix[i][k];
			}
		}
		matrix = tempMatrix;
	}
	return matrix;
}

// Ранжирорвание

let matrixRanging =	createEMatrix(numOfAlternatives);
let stringRanging = '';

function fillingMatrixRanging(){
	let orderOfAlternatives = {};

	for (let i = 0; i <= numOfAlternatives - 1; i++) {
		let nameStr = 'order-' + (i + 1);

		let elem = document.getElementById(nameStr);

		let order = elem.options[elem.selectedIndex].value;

		orderOfAlternatives[i] = order;
	}
	
	for(let i = 0; i < numOfAlternatives; i++){
		for(let j = 0; j < numOfAlternatives; j++){
			if(i != j){
				if(orderOfAlternatives[i] >= orderOfAlternatives[j]){
					matrixRanging[i][j] = 0;
				}else{
					matrixRanging[i][j] = 1;
				}
			}
		}
	}
}

// Общие функции

function createEMatrix(n) {
	let matrix = [];

	for (let i = 0; i < n; i++) {
		matrix[i] = [];

		for (let j = 0; j < n; j++) {
			if (i == j) {
				matrix[i][j] = 1;
			} else {
				matrix[i][j] = 0;
			}
		}
	}

	return matrix;
}

function matrixToString(matr){
	let str = "";
	for(let i = 0; i < numOfAlternatives; i++){
		for(let j = 0; j < numOfAlternatives; j++){
			str += matr[i][j];
		}
	}
	return str;
}

function sendData(matrixStr){
	let	requestData = {
		name: name,
		str: matrixStr
	};

	$.ajax({
		url: 'http://a91756we.beget.tech/receive.php',
		type: 'POST',
		data: requestData,
		// contentType: 'application/json',
		//dataType: 'json',
		success: function() {
			// alert('Ваши данные успешно отправлены');
		}
	});
}

function removeLoader() {
	document.getElementById('preload').classList.toggle('fadeOut');
setTimeout(function() {
	document.getElementById('preload').remove();
}, 1000);
document.getElementsByTagName('body')[0].classList.toggle('overflow-hidden');
}


