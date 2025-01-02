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
        <label for="type">Type</label>
        <select name="dump_type" id="type">
            <option value="all">All</option>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
            <option value="discover">Discover</option>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="pin">PIN</label>
        <select name="dump_pin" id="pin">
            <option value="all">All</option>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="dumps_per_page">Dumps per Page</label>
        <select name="dumps_per_page" id="dumps_per_page">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
    <div class="inpt-dmps-bx">
        <label for="dumps_per_page">All</label>
        <select name="dumps_per_page" id="dumps_per_page">
            <option value="No">No</option>
            <option value="yes">Yes</option>
           
        </select>
    </div>
</form>

    </div>
    
     <!-- Dumps List (this will be dynamically updated) -->
    <div id="dumps-list" class="main-tbl321">
    <?php if (!empty($dumps)): ?>
    <table class="dumps-table">
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
        <tbody>
            <?php foreach ($dumps as $dump): ?>
                <tr>
                    <td>
                   
                    <img src="/shop/images/cards/<?php echo strtolower($dump['card_type']); ?>.png" 
     alt="<?php echo htmlspecialchars($dump['card_type']); ?> logo" 
     class="card-logo">

                    </td>
                    <td><?php echo htmlspecialchars(substr($dump['track2'], 0, 6)); ?></td>
                    <td><?php echo htmlspecialchars($dump['monthexp'] . '/' . $dump['yearexp']); ?></td>
                    <td><?php echo !empty($dump['pin']) ? 'Yes' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($dump['country']); ?></td>
                    <td>$<?php echo htmlspecialchars($dump['price']); ?></td>
                    <td style="text-align:center;">
                        <a href="buy_dump.php?dump_id=<?php echo htmlspecialchars($dump['id']); ?> javascript:void(0);" 
                           class="buy-button-dump"  style="background-color:#0c182f;"
                           onclick="return confirm('Are you sure you want to buy this dump?');">
                           <span class="price">$<?php echo $dump['price']; ?></span>
                           <span class="buy-now">Buy Now</span>
                        
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
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