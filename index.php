<html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.12/fabric.min.js"></script>
<center><canvas id="c" width="500" height="500">Browser Not Supported</canvas></center>
<script>
//This is where we set the canvas to the full window
var canvas = document.getElementById("c");
canvas.width  = window.innerWidth*0.98;
canvas.height = window.innerHeight*0.97;
</script>

<script>
	var canvas = new fabric.Canvas('c');

	
	var points = [
		{x: 10, y: 10},
		{x: 10, y: 510},
		{x: 225, y: 510},
		{x: 225, y: 550},
		{x: 305, y: 550},
		{x: 305, y: 510},
		{x: 510, y: 510},
		{x: 510, y: 10},
		]
	var room = new fabric.Polygon(points, {
		left: 100,
		top: 50,
		fill: 'white',
		strokeWidth: 10,
		stroke: 'black',
	});
var rectangle = new fabric.Rect({
	stroke: 'black', 
	strokeWidth: 10,
	fill: 'white',
	originX: 'center',
	originY: 'center',
	opacity: 1,
	left: 180,
	top: 180,
	height: 500,
	width: 500
});

var bedsideTable = new fabric.Rect({
	fill:'red',
	left:200,
	top:500,
	height:50,
	width:50
});

canvas.add(room, bedsideTable);

//code to check for changes, can add more situations in which to call the onChange function
canvas.on({
	'object:moving' : onChange
});

function onChange(options) {
	options.target.setCoords();
	canvas.forEachObject(function(obj) {
		//prevents the object from unnecessarily checking collisions with itself
		if (obj == options.target) return;
		if (options.target.intersectsWithObject(obj)) {
					 //placeholder code, will put unintersecting here
			alert("there was a collision")
		}
	});
}

canvas.renderAll();

//update
if(typeof(EventSource) !== 'undefined') {
	var source = new EventSource('back/check4update.php?room=<?php echo "TEST" ?>');
	source.onmessage = function(event) {
		var ajax = new XMLHttpRequest();
		ajax.open('POST', 'back/getupdate.php?room=<?php echo "TEST" ?>');
		ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		ajax.send('data='+JSON.stringify(canvas));
	};
} else {
	alert("Browser Not Supported :(");
}

function send() {
	var ajax = new XMLHttpRequest();
	ajax.open('POST', 'back/sendupdate.php?room=<?php echo "TEST" ?>');
	ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	ajax.send('data='+JSON.stringify(canvas));
}
</script>

</html>