<html lang="en">
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.12/fabric.min.js"></script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="libraries/bootstrap-3.4.1-dist/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="libraries/bootstrap-3.4.1-dist/js/bootstrap.min.js"></script>
<script src="libraries/dialogs/js/bootstrap-dialog.min.js"></script>
<link rel="stylesheet" href="libraries/dialogs/css/bootstrap-dialog.min.css">
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


	//generates a list of rectangles that together form the shape of the room/floorplan
	function makeRoom(points) {
		
		var roomRects = [];

		for(let i = 0; i < points.length-1; i++) {
			let wallRect = new fabric.Rect({selectable: false, fill: 'black', originX:'center', originY:'center', height: 10, width:25, left:(points[i].x), top:(points[i].y)});
			let xDiff = (points[i+1].x - points[i].x)
			let yDiff = (points[i+1].y - points[i].y)
			let angle = Math.atan2(yDiff,xDiff)*(180/Math.PI);
			let rectWidth = Math.sqrt(yDiff*yDiff + xDiff*xDiff);

			wallRect.setAngle(angle);
			wallRect.setOriginX('left');
			wallRect.setWidth(rectWidth);
			wallRect.setCoords();
			roomRects.push(wallRect);
		}

		//close off the top of the room
		let wallRect = new fabric.Rect({selectable: false, fill: 'black', originX:'center', originY:'center', height: 10, width:25, left:(points[points.length-1].x), top:(points[points.length-1].y)});
		let xDiff = (points[0].x - points[points.length-1].x)
		let yDiff = (points[0].y - points[points.length-1].y)
		let angle = Math.atan2(yDiff,xDiff)*(180/Math.PI);
		let rectWidth = Math.sqrt(yDiff*yDiff + xDiff*xDiff);

		wallRect.setAngle(angle);
		wallRect.setOriginX('left');
		wallRect.setWidth(rectWidth);
		wallRect.setCoords();
		roomRects.push(wallRect);
		return roomRects;
	}

	var room = makeRoom(points);

	var furnitureArr = [];
	function Furniture(label, x, y, width, height) {
		this.shape = new fabric.Rect({
			fill: 'red',
			width: width,
			height: height,
			originX: 'left',
			originY: 'top'
		});
		
		this.text = new fabric.Text(label, {fontSize:20, originX: 'left', originY:'top'})

		this.furniture = new fabric.Group([this.shape, this.text], {left:x, top:y})

		this.furniture.setControlsVisibility({
			mt: false,
			mb: false,
			ml: false,
			mr: false,
			bl: false,
			tl: false,
			br: false,
			tr: false
		});

		furnitureArr.push(this);
	}

var table = new Furniture('table', 200, 200, 100, 100);
var bed = new Furniture('item', 100, 100, 25, 25)

//draw all the added furniture
furnitureArr.forEach(item => {
	canvas.add(item.furniture);
})

//the room is an array of rectangles, so we have to loop through and render them all
room.forEach(wall => {
	canvas.add(wall);
})

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