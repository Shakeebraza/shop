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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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
                <th>Item id</th>
                <th>Item Price</th>
                <th>Item Type</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
    <!-- Modal for displaying row data -->
    <div id="activityLogModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activity Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>User Name:</strong> <span id="modalUserName"></span></p>
                    <p><strong>Item:</strong> <span id="modalItem"></span></p>
                    <p><strong>Item Price:</strong> <span id="modalItemPrice"></span></p>
                    <p><strong>Item Type:</strong> <span id="modalItemType"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <!-- Additional Data Section -->
                    <p><strong>Additional Information:</strong> <span id="modalAdditionalData"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script>
    $(document).ready(function() {

        var table = $('#activityLogTable').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax": {
                "url": "ajax/fetch_activity_log.php",
                "data": function(d) {
                    d.item_type = $('#itemTypeFilter').val();
                }
            },
            "order": [
                [4, 'desc']
            ],
            "columns": [{
                    "data": "user_name"
                },
                {
                    "data": "buy_itm"
                },
                {
                    "data": "item_id"
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

        // Event listener for row click
        $('#activityLogTable tbody').on('click', 'tr', function() {
            var data = table.row(this).data(); // Get the data of the clicked row

            // Populate modal with the row data
            $('#modalUserName').text(data.user_name);
            $('#modalItem').text(data.buy_itm);
            $('#modalItemPrice').text(data.item_price);
            $('#modalItemType').text(data.item_type);
            $('#modalDate').text(data.created_at);

            $.ajax({
                url: 'ajax/fetch_additional_data.php',
                method: 'GET',
                data: {
                    item_id: data.item_id, // Correctly send item_id
                    item_type: data.item_type
                },
                success: function(response) {
                    // Display the fetched data in the modal
                    $('#modalAdditionalData').text(response); // Modify this as needed
                },
                error: function() {
                    alert('Failed to fetch additional data');
                }
            });


            // Open the modal
            $('#activityLogModal').modal('show');
        });

        // Reload the table data when filter changes
        $('#itemTypeFilter').on('change', function() {
            table.ajax.reload();
        });

    });
    </script>
</body>

</html>