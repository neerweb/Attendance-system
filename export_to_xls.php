<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include "db_connect.php";  // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the selected export type (daily or all)
    $exportType = $_POST['exportType'];

    // Get current date and set start/end date accordingly
    $currentDate = new DateTime();
    $startDate = $endDate = null;

    if ($exportType == "daily") {
        // Export today's data
        $startDate = $currentDate->format('Y-m-d');
        $endDate = $currentDate->format('Y-m-d');
    } elseif ($exportType == "all") {
        // Export all records (set an early start date)
        $startDate = '2000-01-01';  // All records start from this date
        $endDate = $currentDate->format('Y-m-d');
    }

    // Prepare SQL query to fetch the data
    $sql = "SELECT EmpNo, EmpName, AttnDate, Department, InTime, OutTime, Hours, Minutes, ExtraOT, Remarks  
            FROM Attendance 
            WHERE AttnDate BETWEEN ? AND ?";
    $params = array($startDate, $endDate);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Set headers for Excel export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="attendance_export_' . $exportType . '.xls"');
    header('Cache-Control: max-age=0');

    // Title Header (centered, manually formatted)
    echo "DISHA ENTERPRISE ATTENDANCE SHEET - VATVA\n";
    echo "DATE " . $startDate . " TO " . $endDate . "\n";
    echo "(" . strtoupper(strftime("%B", strtotime($currentDate->format('Y-m-d')))) . " - " . $currentDate->format('Y') . ")\n";
    echo "\n";

    // Output column headers for Excel
    echo "SR NO\tDATE\tEMP.NO\tNAME\tDEPARTMENT\tIN\tOUT\tHOUR\tMINS\tEXTRA OT\tREMARKS\n";

    // Output data rows
    $rowNumber = 1;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Format date and time values
        $attnDate = $row['AttnDate'] ? $row['AttnDate']->format('d-m-Y') : '';
        $inTime = $row['InTime'] ? $row['InTime']->format('H:i') : '';
        $outTime = $row['OutTime'] ? $row['OutTime']->format('H:i') : '';
        
        // Calculate hours and minutes
        $hours = 0;
        $minutes = 0;
        if ($row['InTime'] && $row['OutTime']) {
            $inTimeObj = new DateTime($row['InTime']->format('H:i'));
            $outTimeObj = new DateTime($row['OutTime']->format('H:i'));
            $diff = $inTimeObj->diff($outTimeObj);
            $hours = $diff->h;
            $minutes = $diff->i;
        }

        // Handle Extra OT
        $extraOT = $row['ExtraOT'] ?? '';

        // Remarks (Handle null values)
        $remarks = $row['Remarks'] ?? '';

        // Output the row data
        echo $rowNumber . "\t";  // SR NO
        echo $attnDate . "\t";  // DATE
        echo $row['EmpNo'] . "\t";  // EMP NO
        echo $row['EmpName'] . "\t";  // NAME
        echo $row['Department'] . "\t";  // DEPARTMENT
        echo $inTime . "\t";  // IN TIME
        echo $outTime . "\t";  // OUT TIME
        echo $hours . "\t";  // HOURS
        echo $minutes . "\t";  // MINS
        echo $extraOT . "\t";  // EXTRA OT
        echo $remarks . "\n";  // REMARKS

        $rowNumber++;  // Increment row number for each entry
    }

    exit;  // End the script to prevent further output
}
?>
