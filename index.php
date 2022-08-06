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

	//setting up a way to get the previous position of a fabric object (previous frame)
	fabric.Object.prototype.prevX = 0;
	fabric.Object.prototype.prevY = 0;
	
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
	height: 20,
	width: 20
});





canvas.add(room, rectangle);

//a little hacky, but this object holds the mouse pointer position of both the current frame and the previous frame
var mouseData = {prevX:0,prevY0:0, currentX:0, currentY:0, velocityX:0, velocityY:0};

//code to check for changes, can add more situations in which to call the onChange function
canvas.on({
	'object:moving' : onChange,
	'mouse:move' : mouseUpdater
});

function mouseUpdater(options) {
	mouseData.prevX = mouseData.currentX;
	mouseData.prevY = mouseData.currentY;
	mouseData.currentX = options.e.layerX;
	mouseData.currentY = options.e.layerY;
	mouseData.velocityX = mouseData.currentX-mouseData.prevX;
	mouseData.velocityY = mouseData.currentY-mouseData.prevY;
}

function onChange(options) {

	
	// sends positions on the backend
	send()

	options.target.setCoords();

	//currently, this function only houses collision code
	canvas.forEachObject(function(obj) {
		
		if (options.target.intersectsWithObject(obj)) {

			if (obj === options.target) return;

			options.target.setLeft(options.target.prevX);
			options.target.setTop(options.target.prevY);
			options.target.setCoords();
		} 
		

	});

	options.target.prevX = options.target.getLeft();
	options.target.prevY = options.target.getTop();

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