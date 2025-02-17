<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $inTime = $_POST['inTime'];
    $outTime = $_POST['outTime'];
    $hours = $_POST['hours'];
    $minutes = $_POST['minutes'];
    $extraot = $_POST['extraot'];  // Fixed variable name
    $remarks = $_POST['remarks'];

    // Ensure your SQL query is correct
    $sql = "UPDATE Attendance 
            SET InTime = ?, OutTime = ?, Hours = ?, Minutes = ?, ExtraOT = ?, Remarks = ?, IsSaved = 1 
            WHERE ID = ?";
    $params = array($inTime, $outTime, $hours, $minutes, $extraot, $remarks, $id);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
