<style> 

body {
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .absent {
            background-color: #ff4d4d !important; /* Red */
            color: white;
        }
        .present {
            background-color: #28a745 !important; /* Green */
            color: white;
        }
        .unknown {
            background-color: #ffc107 !important; /* Yellow */
            color: black;
        }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>
   


<?php
include "db_connect.php";

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$searchName = isset($_GET['search_name']) ? $_GET['search_name'] : '';

$query = "SELECT EmpNo, EmpName, AttnDate, Department, InTime, OutTime, Hours, Minutes, ExtraOT, Remarks  
          FROM Attendance WHERE 1=1";

$params = array();

// Date range filter
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND AttnDate BETWEEN ? AND ?";
    array_push($params, $startDate, $endDate);
}

// Search by employee name
if (!empty($searchName)) {
    $query .= " AND EmpName LIKE ?";
    array_push($params, "%$searchName%");
}

$query .= " ORDER BY AttnDate DESC";
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Attendance</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>

<h2>View Attendance Records</h2>

<form method="GET">
    <label>Start Date:</label>
    <input type="date" name="start_date" value="<?= $startDate ?>">
    
    <label>End Date:</label>
    <input type="date" name="end_date" value="<?= $endDate ?>">

    <label>Search Name:</label>
    <input type="text" name="search_name" value="<?= $searchName ?>">

    <button type="submit">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>SR NO</th>
            <th>Date</th>
            <th>Emp No</th>
            <th>Name</th>
            <th>Department</th>
            <th>IN</th>
            <th>OUT</th>
            <th>Hours</th>
            <th>Mins</th>
            <th>Extra OT</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php $sr = 1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
        <tr>
            <td><?= $sr++ ?></td>
            <td><?= $row['AttnDate']->format('d-m-Y') ?></td>
            <td><?= $row['EmpNo'] ?></td>
            <td><?= $row['EmpName'] ?></td>
            <td><?= $row['Department'] ?></td>
            <td><?= $row['InTime'] ? $row['InTime']->format('H:i') : 'A' ?></td>
            <td><?= $row['OutTime'] ? $row['OutTime']->format('H:i') : 'A' ?></td>
            <td><?= $row['Hours'] ?? '' ?></td>
            <td><?= $row['Minutes'] ?? '' ?></td>
            <td><?= $row['ExtraOT'] ?? '' ?></td>
            <td><?= $row['Remarks'] ?? '' ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
