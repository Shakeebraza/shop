<?php
include_once('../../header.php');
?>
<style>
       .dumps-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 16px;
        text-align: left;
    }

    .dumps-table thead {
        background-color: #0c182f;
        color: #ffffff;
    }

    .dumps-table th, .dumps-table td {
        padding: 10px;
        border: 1px solid #ddd;
    }

    .buy-button-dump {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 4px;
    }

    .buy-button-dump:hover {
        background-color: #0056b3;
    }

    .card-logo {
        height: 20px;
        vertical-align: middle;
    }
    .card-logo {
    height: 30px !important;
    vertical-align: middle;
    object-fit: contain !important;
}
.filter-container-dumps {
    display: inline-block !important;
    width: auto !important;
    margin-top:20px;
    border-radius: 0px !important;
    box-shadow:none !important;
}
form#dump-filters {
    display: flex !important;
    align-items: center !important;
    gap: 20px !important;
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
form#dump-filters {
    display: block !important;
}
}
.buy-button-dump {
    height: 38px !important;
    width: 100px !important;
    text-align: center !important;
}
.buy-button-dump .buy-now {
    display: block;
}

.buy-button-dump:hover .buy-now {
    display: none;
}
.buy-button-dump .price {
    display: none;
}

.buy-button-dump:hover .price {
    display: block;
}


</style>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="dumps" class="uuper">
    <h2>Dumps Section</h2>
    <div class="filter-container-dumps">
    <form id="dump-filters" method="post" action="#dumps">
    <div class="inpt-dmps-bx">
        <label for="dump-bin">BIN</label>
        <input type="text" name="dump_bin" id="dump-bin" placeholder="Comma-separated for multiple - e.g., 123456, 654321">
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="dump-country">Country</label>
        <select name="dump_country" id="dump-country">
            <option value="">All</option>
            <?php foreach ($dumpCountries as $country): ?>
                <option value="<?php echo htmlspecialchars($country); ?>">
                    <?php echo htmlspecialchars($country); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="dump-type">Type</label>
        <select name="dump_type" id="dump-type">
            <option value="all">All</option>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
            <option value="discover">Discover</option>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="dump-pin">PIN</label>
        <select name="dump_pin" id="dump-pin">
            <option value="all">All</option>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="dumps-per-page">Dumps per Page</label>
        <select name="dumps_per_page" id="dumps-per-page">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
</form>


    </div>
    

    <div id="dumps-list" class="main-tbl321">
    <?php if (!empty($dumps)): ?>
    <table id="dumpsTable"  class="dumps-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>BIN</th>
                <th>Exp Date</th>
                <th>PIN</th>
                <th>Country</th>
                <th>Price</th>
                <th>Buy</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
<?php else: ?>
    <p>No dumps available.</p>
<?php endif; ?>

    </div>
</div>

    </div>
</div>
<?php
include_once('../../footer.php');
?>
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#dumpsTable').DataTable({
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: '<?= $urlval ?>ajax/dumpdata.php', 
            type: 'POST',
            data: function(d) {
                d.dump_bin = $('#dump-bin').val();
                d.dump_country = $('#dump-country').val();
                d.dump_type = $('#dump-type').val();
                d.dump_pin = $('#dump-pin').val();
                d.dumps_per_page = $('#dumps-per-page').val();
            }
        },
        columns: [
            { data: 'card_logo' },
            { data: 'track2' },
            { data: 'expiry' },
            { data: 'pin' },
            { data: 'country' },
            { data: 'price' },
            { data: 'actions' }
        ]
    });

    // Reload the DataTable on any filter change
    $('#dump-filters input, #dump-filters select').on('change', function() {
        table.ajax.reload();
    });
});

</script>


</body>
</html>