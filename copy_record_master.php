<?php
// db_connect.php should include your database connection details
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the latest record and copy it for the next day
    $sql = "SELECT TOP 1 * FROM Attendance ORDER BY AttnDate DESC";
    $stmt = sqlsrv_query($conn, $sql);

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Get the last saved date
        $lastAttnDate = $row['AttnDate']->format('Y-m-d');
        $nextAttnDate = date('Y-m-d', strtotime($lastAttnDate . ' +1 day'));

        // Prepare the data to copy (excluding InTime, OutTime)
        $sqlCopy = "INSERT INTO Attendance (AttnDate, SerialNo, EmpNo, EmpName, Department, IsSaved)
                    SELECT ?, SerialNo, EmpNo, EmpName, Department, 0 
                    FROM Attendance 
                    WHERE AttnDate = ?";

        $params = array($nextAttnDate, $lastAttnDate);
        $copyStmt = sqlsrv_query($conn, $sqlCopy, $params);

        if ($copyStmt) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "No records found";
    }
}
?>
