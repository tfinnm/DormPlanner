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

canvas.add(room);
canvas.renderAll();
</script>

</html>