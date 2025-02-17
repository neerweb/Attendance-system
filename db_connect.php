<?php
$serverName = "EMD-SYS-PRIME\NJSQL"; // Update with your server details
$connectionOptions = array(
    "Database" => "attend",
    "Uid" => "sa",
    "PWD" => "Windsor@1"
);

// Connect to SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
?>
