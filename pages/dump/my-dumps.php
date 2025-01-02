<?php
include_once('../../header.php');
?>

<style>
.sold-dumps-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
}

.sold-dumps-table thead tr {
    background-color: #0c182f;
    border-bottom: 2px solid #dddddd;
}

.sold-dumps-table th, .sold-dumps-table td {
    padding: 12px 15px;

    border: 1px solid #dddddd;
}

.sold-dumps-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.sold-dumps-table tbody tr:hover {
    background-color: #f1f1f1;
}

.copy-button, .check-dump-button {
    padding: 8px 12px;
    margin: 0 5px;
    border: none;
    background-color:#0c182f;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}

.copy-button:hover, .check-dump-button:hover {
    background-color: #0056b3;
}
@media (max-width: 768px) {
    table {
        font-size: 14px;
    }
    td, th {
        padding: 8px 15px;
        text-wrap:nowrap !important;
    }
    a.buy-button {
        font-size: 12px;
        padding: 6px 10px;
    }
    .main-tbl321{
    width: 100% !important;
    overflow-x:scroll !important;}
    a.buy-button {
    height: 30px !important;
 
}
}
</style>

    <!-- Main Content Area -->
    <div class="main-content">
    <div id="my-dumps" class="uuper">
    <h2>My Dumps Section</h2>
    <?php if (empty($soldDumps)): ?>
        <p>No purchased dumps available.</p>
        <?php else: ?>
            <div class="main-tbl321">
    <table class="sold-dumps-table">
        <thead style="background:#0c182f; color:white;">
            <tr>
                <th>ID</th>
                <th>Track 1</th>
                <th>Track 2</th>
                <th>PIN</th>
                <th>Country</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($soldDumps as $dump): ?>
                <tr id="dump-<?php echo htmlspecialchars($dump['id']); ?>" class="dump-item">
                    <td><?php echo htmlspecialchars($dump['id']); ?></td>
                    <td><?php echo htmlspecialchars($dump['track1']); ?></td>
                    <td><?php echo htmlspecialchars($dump['track2']); ?></td>
                    <td><?php echo htmlspecialchars($dump['pin'] ?: 'No'); ?></td>
                    <td><?php echo htmlspecialchars($dump['country']); ?></td>
                    <td>
                        <button  class="copy-button" onclick="copyDumpInfo(<?php echo htmlspecialchars($dump['id']); ?>)">Copy</button>
                        <button class="check-dump-button" onclick="checkDump(<?php echo htmlspecialchars($dump['id']); ?>)">Check</button>
                        <button class="check-dump-button" onclick="deleteRow(<?php echo htmlspecialchars($dump['id']); ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>


    <!-- Dumps Activity Log Section -->
     
    <div id="dumps-activity-log">
      
        <h2>Dumps Activity Log</h2>
        <div class="main-tbl321">
        <table id="activity-log-table" class="activity-log-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Card Number</th>
                    <th>Date Checked</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkedDumpsHistory)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No activity logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($checkedDumpsHistory as $history): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['id']); ?></td>
                            <td><?php echo htmlspecialchars($history['card_number']); ?></td>
                            <td><?php echo htmlspecialchars($history['date_checked']); ?></td>
                            <td><?php echo htmlspecialchars($history['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

    </div>
</div>
<?php
include_once('../../footer.php');
?>

<script>
    // Check for hidden rows on page load
    document.addEventListener('DOMContentLoaded', function () {
        // Get deleted IDs from localStorage
        const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];

        // Hide rows that are marked as deleted
        deletedIds.forEach(id => {
            const row = document.getElementById(`dump-${id}`);
            if (row) {
                row.style.display = 'none';
            }
        });
    });

    // Delete row function
    function deleteRow(cardId) {
        if (confirm('Are you sure you want to delete this row?')) {
            // Hide the row
            const row = document.getElementById(`dump-${cardId}`);
            if (row) {
                row.style.display = 'none';
            }

            // Save the deleted ID to localStorage
            const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];
            if (!deletedIds.includes(cardId)) {
                deletedIds.push(cardId);
                localStorage.setItem('deletedRows', JSON.stringify(deletedIds));
            }
        }
    }
</script>