<?php
include "db_connect.php";

// Fetch attendance records
$dateFilter = isset($_POST['attnDate']) ? $_POST['attnDate'] : date('Y-m-d'); // Default to today
$sql = "SELECT * FROM Attendance WHERE AttnDate = ? ORDER BY AttnDate DESC";
$params = array($dateFilter);
$stmt = sqlsrv_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: yellow; font-weight: bold; }
        input { width: 100%; border: none; text-align: center; }
        .save-btn { background: green; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .disabled { background: #ddd; pointer-events: none; }

        /* Dropdown box style */
        select {
            width: 150px;
            padding: 5px;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        /* Button style */
        button {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #218838;
        }

        /* Disabled button */
        button.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<h2 align="center">DISHA ENTERPRISE ATTENDANCE SHEET - VATVA</h2>

<!-- Date selection -->
<form method="POST" action="index.php">
    <label for="attnDate">Select Attendance Date: </label>
    <input type="date" id="attnDate" name="attnDate" value="<?= $dateFilter ?>">
    <button type="submit">Filter</button>
</form>

<!-- Export Button -->
<!-- Dropdown to select export type -->
<select id="exportType">
    <option value="daily">Daily Export</option>
    <option value="all">All Records Export</option>
</select>

<!-- Export button -->
<button id="exportBtn" class="save-btn">Export to XLS</button>

<!-- CopyMasterData Button -->

<button id="copyRecordMaster" class="save-btn">Copy Record Master</button>


<button onclick="window.open('View_attendance.php', '_blank')" 
        style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
    View Attendance
</button>


<div class="table-container">
    <table>
        <tr>
            <th>SR NO</th>
            <th>DATE</th>
            <th>EMP.NO</th>
            <th>NAME</th>
            <th>DEPARTMENTS</th>
            <th>IN</th>
            <th>OUT</th>
            <th>HOUR</th>
            <th>MINS</th>
            <th>OT</th>
            <th>REMARKS</th>
            <th>ACTION</th>
        </tr>

        <?php $sr = 1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <tr data-id="<?= $row['ID'] ?>">
                <td><?= $sr++ ?></td>
                <td><input type="date" value="<?= $row['AttnDate']->format('Y-m-d') ?>" disabled></td>
                <td><input type="text" value="<?= $row['EmpNo'] ?>" disabled></td>
                <td><input type="text" value="<?= $row['EmpName'] ?>" disabled></td>
                <td><input type="text" value="<?= $row['Department'] ?>" disabled></td>
                <td><input type="time" class="inTime" value="<?= isset($row['InTime']) ? $row['InTime']->format('H:i') : '' ?>"></td>
                <td><input type="time" class="outTime" value="<?= isset($row['OutTime']) ? $row['OutTime']->format('H:i') : '' ?>"></td>
                <td><input type="number" class="hours" value="<?= $row['Hours'] ?>" readonly></td>
                <td><input type="number" class="minutes" value="<?= $row['Minutes'] ?>" readonly></td>
                <td><input type="number" class="extraot" value="<?= $row['ExtraOT'] ?>"></td>
                <td><input type="text" class="remarks" value="<?= $row['Remarks'] ?>"></td>
                <td>
                    <?php if ($row['IsSaved'] == 1) { ?>
                        <button class="save-btn disabled">Saved</button>
                    <?php } else { ?>
                        <button class="save-btn">Save</button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<script>
$(document).ready(function() {
    // When the export button is clicked
    $("#exportBtn").click(function() {
        var exportType = $("#exportType").val();  // Get the selected export type

        // Make an AJAX request to export_to_xls.php
        $.ajax({
            url: "export_to_xls.php",  // PHP file to generate the export
            type: "POST",
            data: { exportType: exportType },  // Send the export type to the PHP script
            success: function(response) {
                // Force download of the Excel file
                var blob = new Blob([response], { type: "application/vnd.ms-excel" });
                var link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = 'attendance_export_' + exportType + '.xls';  // Set file name dynamically
                link.click();
            },
            error: function(xhr, status, error) {
                alert("An error occurred during the export process: " + error);
            }
        });
    });

    // Handle save button click
    $(".save-btn").click(function() {
        var row = $(this).closest("tr");
        var id = row.data("id");
        var inTime = row.find(".inTime").val();
        var outTime = row.find(".outTime").val();
        var extraot = row.find(".extraot").val();
        var remarks = row.find(".remarks").val();

        // Calculate Hours and Minutes
        var inTimeObj = new Date("2023-01-01 " + inTime);
        var outTimeObj = new Date("2023-01-01 " + outTime);
        var diff = outTimeObj - inTimeObj;
        var hours = Math.floor(diff / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        if (isNaN(hours) || isNaN(minutes)) {
            alert("Invalid IN/OUT Time");
            return;
        }

        $.ajax({
            url: "save_attendance.php",
            type: "POST",
            data: { id: id, inTime: inTime, outTime: outTime, hours: hours, minutes: minutes, extraot: extraot, remarks: remarks },
            success: function(response) {
                if (response === "success") {
                    row.find(".hours").val(hours);
                    row.find(".minutes").val(minutes);
                    row.find("input").prop("disabled", true);
                    row.find(".save-btn").addClass("disabled").text("Saved");
                } else {
                    alert("Error saving data");
                }
            }
        });
    });

    // Handle copy record master button click
    $("#copyRecordMaster").click(function() {
        $.ajax({
            url: "copy_record_master.php", // The PHP file you created above
            type: "POST",
            success: function(response) {
                if (response === "success") {
                    alert("Record copied for the next day.");
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert("Error copying record.");
                }
            }
        });
    });
});
</script>

</body>
</html>
