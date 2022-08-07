<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
session_start();
if (!isset($_SESSION["LastData"])) {
	$_SESSION["LastData"] = "";
}
include("../db.php");
$conn = new mysqli($db_server, $db_user, $db_password, $db_db);
		if ($conn->connect_error) {
			// connection failed error goes here
		}
		$sql = "SELECT * FROM rooms WHERE RoomCode = '".$_GET["room"]."' LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				if ($row["Data"] != $_SESSION["LastData"]) {
					echo "data: " . uniqid() . " \n\n";
					ob_end_flush();
					flush();
				}
			}
		} else {
			//Room Not Found return an error
		}
$conn->close();
?>