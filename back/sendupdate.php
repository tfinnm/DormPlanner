<?php
session_start();
include("../db.php");
if (!isset($_SESSION["LastData"])) {
	$_SESSION["LastData"] = "";
}
$conn = new mysqli($db_server, $db_user, $db_password, $db_db);
	if ($conn->connect_error) {
		// connection failed error goes here
	}
	$dat =  $conn->real_escape_string($_POST["data"]);
	$sql = "UPDATE rooms SET Data = '".$dat."' WHERE RoomCode = '".$_GET["room"]."'";
	$conn->query($sql);	
	$_SESSION["LastData"] = $_POST["data"];
?>