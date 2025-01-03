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
.check-card-button {
    background-color:#0c182f;
    color: white;
}

.check-card-button:hover {
    background-color: #e0a800;
}
.copy-button:hover, .check-dump-button:hover {
    background-color: #0056b3;
}
.copy-button {
    background-color: #0c182f;
    color: white;
  
    margin:0px 5px 0px 0px !important;
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
@keyframes shake-up-down {
    0% { transform: translateY(0); }
    25% { transform: translateY(-5px); }
    50% { transform: translateY(5px); }
    75% { transform: translateY(-5px); }
    100% { transform: translateY(0); }
}


.shake {
    animation: shake-up-down 0.5s ease-in-out;
}

#rules-btn:hover {
    animation: shake-up-down 0.5s ease-in-out;
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
            <table class="sold-dumps-table" id="soldDumpsTable">
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
                <td><?php echo htmlspecialchars(empty($dump['track1']) ? '' : $dump['track1']); ?></td>
                <td><?php echo htmlspecialchars($dump['track2']); ?></td>
                <td><?php echo htmlspecialchars($dump['pin'] ?: 'No'); ?></td>
                <td><?php echo htmlspecialchars($dump['country']); ?></td>
                <td style="padding: 10px;display: flex;justify-content: center;align-content: center;align-items: center;">
                    <button class="copy-button" style="padding: 6px 10px; 
                        border: none; border-radius: 3px; cursor: pointer; margin-right: 5px;" 
                        onclick="copyCardInfo(<?php echo htmlspecialchars($card['id']); ?>)">Copy</button>
                    <button class="check-card-button" style="padding: 6px 10px; 
                        border: none; border-radius: 3px; cursor: pointer; margin:0px 5px 0px 0px;" 
                        onclick="checkCard(<?php echo htmlspecialchars($card['id']); ?>)">Check</button>
                        <a type="button" onclick="deleteRow(<?php echo htmlspecialchars($dump['id']); ?>)" id="clear-btn" class="btn text-center btn-with-icon" style="background-color: #f44336; color: white; padding: 5px 15px; width:70px; border-radius: 4px; border: none; cursor: pointer; 
                                margin-top: -1px;">
                            <i class="fa fa-times"></i>
                            <span class="btn-text" style="text-align:center !important;">Delete</span>
                        </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>
<?php endif; ?>


  
     
    <div id="dumps-activity-log">
      
        <div style="display: flex; align-items: center; gap: 20px;">
        <h2>Dumps Activity Log</h2>
        <button id="rules-btnnew" 
                style="padding: 5px 15px; background-color: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 5px;" 
                onclick="openRulesPopup()">
            <i class="fas fa-gavel"></i>
            Rules
        </button>
    </div>

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

<div id="rules-popup2" class="popup-modal">
    <div class="popup-content">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <h2>Purchased Information</h2>
        <p>Here are the updated rules for using the system:</p>
        <ul>
           
            <li>1. Purchased information will be automatically removed from these sections after 30 days. </li>
            <li>2. Users are advised to download or copy their information before the 30-day period ends to avoid losing access.</li>
           
        </ul>
    </div>
</div>
<?php
include_once('../../footer.php');
?>

<script>
 
    document.addEventListener('DOMContentLoaded', function () {
     
        const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];

        deletedIds.forEach(id => {
            const row = document.getElementById(`dump-${id}`);
            if (row) {
                row.style.display = 'none';
            }
        });
    });


    // function deleteRow(cardId) {
    //     if (confirm('Are you sure you want to delete this row?')) {
   
    //         const row = document.getElementById(`dump-${cardId}`);
    //         if (row) {
    //             row.style.display = 'none';
    //         }

         
    //         const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];
    //         if (!deletedIds.includes(cardId)) {
    //             deletedIds.push(cardId);
    //             localStorage.setItem('deletedRows', JSON.stringify(deletedIds));
    //         }
    //     }
    // }

    $(document).ready(function() {
    $('#soldDumpsTable').DataTable({
        "paging": true,            
        "searching": false,
        "ordering": false,         
        "info": true,             
        "lengthChange": true,      
        "autoWidth": true,         
        "responsive": true        
    });
});

$(document).ready(function() {
    $('#activity-log-table').DataTable({
        "paging": true,            
        "searching": false,
        "ordering": false,         
        "info": true,             
        "lengthChange": true,      
        "autoWidth": true,         
        "responsive": true         
    });
});

function openRulesPopup() {
    document.getElementById("rules-popup2").style.display = "flex";
}


function closeRulesPopup() {
    document.getElementById('rules-popup2').style.display = 'none';
}

const rulesBtn = document.getElementById('rules-btnnew');
if (rulesBtn) {
    setInterval(() => {
        rulesBtn.classList.add('shake');
        setTimeout(() => {
            rulesBtn.classList.remove('shake');
        }, 500);
    }, 2000);
} else {
    console.error('Button with id "rules-btnnew" not found.');
}

</script>