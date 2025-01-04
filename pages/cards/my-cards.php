<?php
include_once('../../header.php');
?>
<style>
    .credit-card-item {
    transition: transform 0.6s ease-in-out; /* Apply a smooth transition to the transform */
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
}

thead tr {
    background-color:#0c182f !important; /* Nice blue color */
    color: white;
    text-align: left;
    font-weight: bold;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px 15px;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

table button {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.copy-button {
    background-color: #0c182f;
    color: white;
  
    margin-right:0px 5px 0px 0px !important;
}

.copy-button:hover {
    background-color: #218838;
}

.check-card-button {
    background-color:#0c182f;
    color: white;
}

.check-card-button:hover {
    background-color: #e0a800;
}
.activity-log-table th{
    background-color:#0c182f !important;
}

@media (max-width: 768px) {
    table {
        font-size: 14px;
    }
    td, th {
        padding: 8px 15px;
        text-wrap:nowrap !important;
    }
    
    .main-tbl321{
    width: 100% !important;
    overflow-x:scroll !important;}
    a.buy-button {
    height: 30px !important;
 
}
}
.copy-button {
    background-color: #0c182f;
    color: white;
  
    margin:0px 5px 0px 0px !important;
}
.ribbon {
            position: absolute;
            top: -3px;
            left: -21px;
            background: #4CAF50;
            color: white;
            padding: 5px 16px;
            transform: rotate(-45deg);
            transform-origin: top right;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1;
            border-radius: 8px;
            animation: swing 2s ease-in-out infinite;
        }
        @keyframes swing {
    0% {
        transform: rotate(-45deg);
    }
    25% {
        transform: rotate(-43deg);
    }
    50% {
        transform: rotate(-47deg);
    }
    75% {
        transform: rotate(-43deg);
    }
    100% {
        transform: rotate(-45deg);
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
  
    <div class="main-content">
    <div id="my-cards" class="uuper">
    <h2>My Cards Section</h2>
 <?php if (empty($soldCards)): ?>
        <p>No purchased cards available.</p>
    <?php else: ?>
        <div class="main-tbl321" style="overflow-x: auto; max-width: 100%; border: 1px solid #ddd; margin-top: 20px;">
        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; margin-top: 20px;" id="soldDumpsTable">
    <thead>
        <tr style="background-color: #f4f4f4; border-bottom: 2px solid #ddd;">
            <th style="padding: 10px; border: 1px solid #ddd; width: 18%;">Card Number</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Expiration</th>
            <th style="padding: 10px; border: 1px solid #ddd;">CVV</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Name on Card</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Address</th>
            <th style="padding: 10px; border: 1px solid #ddd;">City</th>
            <th style="padding: 10px; border: 1px solid #ddd;">MNN</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Account Number</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Sort Code</th>
            <th style="padding: 10px; border: 1px solid #ddd;">ZIP</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Country</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Phone Number</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Date of Birth</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
        </tr>

    </thead>
    <tbody>
        <?php foreach ($soldCards as $card): ?>
            <tr id="card-<?php echo htmlspecialchars($card['id']); ?>"  
                style="border-bottom: 1px solid #ddd; text-align: center;
            overflow: hidden;">
                <td style="padding: 10px; position: relative;">
                <?php if ($card['is_view'] == 0): ?>
                        <span class="ribbon">
                            New
                        </span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($card['card_number']); ?>
                  
                </td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['cvv']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['name_on_card']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['address']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['city']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['mmn']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['account_number']); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['sort_code']) ?></td>
           
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['zip']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['country']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['phone_number']) ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($card['date_of_birth'] ?? 'N/A'); ?></td>
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


    <div id="card-activity-log">
    <div style="display: flex; align-items: center; gap: 20px;">
        <h2>Card Activity Log</h2>
        <button id="rules-btnnew" 
                style="padding: 5px 15px; background-color: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 5px;" 
                onclick="openRulesPopup()">
            <i class="fas fa-gavel"></i>
            Rules
        </button>
    </div>

        <div class="main-tbl321">
        <table id="card_activity_log" class="activity-log-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Card Number</th>
            <th>Date Checked</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($checkedHistory)): ?>
            <tr>
                <td colspan="4" style="text-align: center;">No activity logged yet</td>
            </tr>
        <?php else: ?>
            <?php foreach ($checkedHistory as $history): ?>
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
            const row = document.getElementById(`card-${id}`);
            if (row) {
                row.style.display = 'none';
            }
        });
    });


    function deleteRow(cardId) {
        if (confirm('Are you sure you want to delete this row?')) {

            const row = document.getElementById(`card-${cardId}`);
            if (row) {
                row.style.display = 'none';
            }

    
            const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];
            if (!deletedIds.includes(cardId)) {
                deletedIds.push(cardId);
                localStorage.setItem('deletedRows', JSON.stringify(deletedIds));
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
    fetch('update_is_view.php', {
        method: 'POST',
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                
            } else {
            
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
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
    $('#card_activity_log').DataTable({
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


