<html lang="en">
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.12/fabric.min.js"></script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="libraries/bootstrap-3.4.1-dist/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="libraries/bootstrap-3.4.1-dist/js/bootstrap.min.js"></script>
<script src="libraries/dialogs/js/bootstrap-dialog.min.js"></script>
<link rel="stylesheet" href="libraries/dialogs/css/bootstrap-dialog.min.css">
<title>DormDesigner</title>

<?php
	if (isset($_GET["room"])) {
		echo "
			<style>
				canvas {
					width: 100%;
					height: auto;
				}
			</style>
				<div class='row'>
					<div class='col-sm-6' style='outline:solid black;'>
						<canvas id='c' height='700' width='700'>Browser Not Supported</canvas>
					</div>
					<div class='col-sm-6'>
						<center>
						<h1>DormDesigner</h1>
						<h4>Editing: RPI Crockett 116 | Room Code: <a href='#?room=".$_GET["room"]."'>".$_GET["room"]."</a></h4>
						
						
						</center>
						Tools: <button type = 'button' onclick='deleteSelected()'> delete selected object</button>
						<hr>
						<h5>Furniture Library</h5>
						<button type = 'button' onclick='newFurnitureLib(\"TwinXL\", 3,6.667,\"red\")'>Twin XL Bed</button>
						<button type = 'button' onclick='newFurnitureLib(\"Desk\", 4,2,\"red\")'>Desk</button>
						<button type = 'button' onclick='newFurnitureLib(\"Fridge\", 2,2,\"red\")'>Minifridge</button>
						<hr>
						<h5>Custom Furniture</h5>
						<h6><i>Width and Heights are in feet</i></h6>
						<label>width:</label>
						<input type='text' id='furnitureWidth'><br>
						<label>height:</label>
						<input type='text' id='furnitureHeight'><br>
						<label>name:</label>
						<input type='text' id='furnitureName'><br>
						<label>color:</label>
						<input type='text' id='furnitureColor'><br>
						<button type = 'button' onclick='newFurniture()'> create new furniture</button>
					</div>
				</row>
				<script>


	var canvas = new fabric.Canvas('c');

	//setting up a way to get the previous position of a fabric object (previous frame)
	fabric.Object.prototype.prevX = 0;
	fabric.Object.prototype.prevY = 0;
	
	var points = [
		{x: 2.5, y: 2.5},
		{x: 2.5, y: 17.5},
		{x: 17.5, y: 17.5},
		{x: 17.5, y: 2.5},
		]


	//generates a list of rectangles that together form the shape of the room/floorplan
	function makeRoom(points) {

		for(let i = 0; i < points.length; i++) {
			points[i].x = feetToPixels(points[i].x);
			points[i].y = feetToPixels(points[i].y);
		}
		
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
	function Furniture(label, x, y, width, height, color) {
		this.shape = new fabric.Rect({
			fill: color,
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

//delete the actively selected object when the delete key is pressed
function deleteSelected() {
	canvas.remove(canvas.getActiveObject());
	canvas.renderAll();
	send();
}

//converting feet to pixel values
function feetToPixels(feet) {
	return Math.floor(feet*35)
}

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
	'object:rotating' : onChange,	
	'mouse:move' : mouseUpdater
});

//for adding new pieces of furniture from the GUI
function newFurniture() {
	var newItem = new Furniture(document.getElementById('furnitureName').value, 100, 100, feetToPixels(parseFloat(document.getElementById('furnitureWidth').value)), feetToPixels(parseFloat(document.getElementById('furnitureHeight').value)), document.getElementById('furnitureColor').value);
	canvas.add(newItem.furniture);
	send();
}

function newFurnitureLib(name, width, height, color) {
	var newItem = new Furniture(name, 100, 100, feetToPixels(width), feetToPixels(height), color);
	canvas.add(newItem.furniture);
	send();
}

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
	var source = new EventSource('back/check4update.php?room=".$_GET["room"]."');
	source.onmessage = function(event) {
		var ajax = new XMLHttpRequest();
		ajax.open('POST', 'back/getupdate.php?room=".$_GET["room"]."');
		ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		ajax.onreadystatechange = function() {
			if (ajax.readyState === 4) {
				canvas.loadFromJSON(ajax.response);
				canvas.renderAll();
			}
		}
		ajax.send('data='+JSON.stringify(canvas));
	};
} else {
	alert('Browser Not Supported :(');
}

function send() {
	var ajax = new XMLHttpRequest();
	ajax.open('POST', 'back/sendupdate.php?room=".$_GET["room"]."');
	ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	ajax.send('data='+JSON.stringify(canvas));
}
</script>
		";
		
include("./db.php");
if (!isset($_SESSION["LastData"])) {
	$_SESSION["LastData"] = "";
}
$str = "{}";
$conn = new mysqli($db_server, $db_user, $db_password, $db_db);
if ($conn->connect_error) {
	echo "<script>alert('Failed to connect to database :(');</script>";
}
$sql = "SELECT * FROM rooms WHERE RoomCode = '".$_GET["room"]."' LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$str = $row["Data"];
	}
}
$_SESSION["LastData"] = $str;
echo "
<script>
canvas.loadFromJSON('".$str."');
canvas.renderAll();
</script>
";
	} else {
		include("./db.php");
		$schools = "";
		$conn = new mysqli($db_server, $db_user, $db_password, $db_db);
		$sql = "SELECT School FROM floorplans GROUP BY School;";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$schools.="<option value='".$row["School"]."'>".$row["School"]."</option>";
			}
		}
$conn->close();
		echo "
	<center>
		<h1>DormDesigner</h1>
		<h4>Enter your Room Code here:</h4>
		<form action='' method='get'>
		<label>Room Code: </label> <input type='test' id='room' name='room'></input><input type='submit' value='Join'></input>
		</form>
		<h4>Or create a new room:</h4>
		<form action='' method='post'> 
			<select name='School' onchange='showCustomer(this.value)'>
				<option value=''>Select a School:</option>
				".$schools."
			</select>
			<select disabled name='School' onchange='showCustomer(this.value)'>
			<option value=''>Select a Building:</option>
			</select>
			<select disabled name='School' onchange='showCustomer(this.value)'>
			<option value=''>Select a Floor:</option>
			</select>
			<select disabled name='School' onchange='showCustomer(this.value)'>
			<option value=''>Select a Room:</option>
			</select>
			<input type='submit' value='create'></input>
		</form>
	</center>
		";
	}
?>

</html>