<?php
session_start();
include("../db.php");
if (!isset($_SESSION["LastData"])) {
	$_SESSION["LastData"] = "";
}
$str = "{}";
$conn = new mysqli($db_server, $db_user, $db_password, $db_db);
if ($conn->connect_error) {
	// connection failed error goes here
}
$sql = "SELECT * FROM rooms WHERE RoomCode = '".$_GET["room"]."' LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$str = $row["Data"];
	}
}
//$new = xdiff_string_merge3($str, $_POST["data"], $_SESSION["LastData"]);
$new = $str;
$_SESSION["LastData"] = $new;
//$new2 =  $conn->real_escape_string($new);
//$sql = "UPDATE rooms SET Data = '".$new2."' WHERE RoomCode = '".$_GET["room"]."'";
//$conn->query($sql);
die ($new);
?>