<?php
include_once('global.php');

function getDistinctItemTypes($pdo) {
    $query = "SELECT DISTINCT item_type FROM activity_log";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $itemTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $itemTypes;
}
$itemTypes = getDistinctItemTypes($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <!-- Include DataTable CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js">
    </script>
</head>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f4f7fc;
}

h1 {
    color: #333;
    font-size: 32px;
    margin-bottom: 20px;
}

/* Filter Dropdown styling */
label {
    font-size: 16px;
    margin-right: 10px;
    color: #444;
}

#itemTypeFilter {
    padding: 10px 15px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    transition: border-color 0.3s ease;
}

#itemTypeFilter:hover {
    border-color: #007bff;
}

/* Data Table styling */
#activityLogTable {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

#activityLogTable thead {
    background-color: #007bff;
    color: white;
}

#activityLogTable th,
#activityLogTable td {
    padding: 12px 15px;
    text-align: left;
}

#activityLogTable tr:nth-child(even) {
    background-color: #f9f9f9;
}

#activityLogTable tr:hover {
    background-color: #f1f1f1;
}

/* Table responsive design */
@media (max-width: 768px) {

    #activityLogTable th,
    #activityLogTable td {
        padding: 8px 10px;
    }

    #itemTypeFilter {
        width: 100%;
        margin-top: 10px;
    }
}
</style>
</head>

<body>
    <h1>Activity Log</h1>

    <!-- Filter Dropdown -->
    <label for="itemTypeFilter">Filter by Item Type: </label>
    <select id="itemTypeFilter">
        <option value="">All</option>
        <?php foreach ($itemTypes as $item) { ?>
        <option value="<?= htmlspecialchars($item['item_type']); ?>"><?= htmlspecialchars($item['item_type']); ?>
        </option>
        <?php } ?>
    </select>

    <!-- Data Table -->
    <table id="activityLogTable" class="display">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Item</th>
                <th>Item Price</th>
                <th>Item Type</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>

    <script>
    $(document).ready(function() {
        // Initialize the DataTable
        var table = $('#activityLogTable').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax": {
                "url": "ajax/fetch_activity_log.php", // PHP script to fetch data
                "data": function(d) {
                    d.item_type = $('#itemTypeFilter').val(); // Send selected item type as a filter
                }
            },
            "order": [
                [4, 'desc']
            ], // Default ordering by date in descending order
            "columns": [{
                    "data": "user_name"
                },
                {
                    "data": "buy_itm"
                },
                {
                    "data": "item_price"
                },
                {
                    "data": "item_type"
                },
                {
                    "data": "created_at"
                }
            ]
        });

        // Event listener for the filter dropdown
        $('#itemTypeFilter').on('change', function() {
            table.ajax.reload(); // Reload table data when filter changes
        });
    });
    </script>
</body>

</html>