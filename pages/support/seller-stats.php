<?php
include_once('../../header.php');

?>
    <!-- Main Content Area -->
    <div class="main-content">

    <?php if ($user['seller'] == 1): ?>
    <div id="seller-stats" class="section">
        <h2><i class="fas fa-chart-bar"></i> Seller Stats</h2> <!-- Main title -->

        <!-- Seller Percentage -->
        <div class="stats-container">
            <h3>Seller Percentage</h3>
            <div class="stat-item">Percentage: <strong><?php echo number_format($user['seller_percentage'], 2); ?>%</strong></div>
            <div class="stat-item">Actual Balance: <strong>
                <?php 
                    $totalEarned = $user['credit_cards_balance'] + $user['dumps_balance'];
                    echo '$' . number_format($totalEarned, 2);
                ?>
            </strong></div>
            		<div class="stat-item">Total earned from Credit Cards: <strong>$<?php echo number_format($user['credit_cards_total_earned'], 2); ?></strong></div>
            		<div class="stat-item">Total earned from Dumps <strong>$<?php echo number_format($user['dumps_total_earned'], 2); ?></strong></div>
        </div>

        <!-- Credit Cards Stats -->
        <div class="stats-container">
            <h3>Credit Cards Stats</h3>
            <div class="stat-item">Uploaded Cards: <strong><?php echo $totalCardsUploaded; ?></strong></div>
            <div class="stat-item">Unsold Cards: <strong><?php echo $unsoldCards; ?></strong></div>
            <div class="stat-item">Sold Cards: <strong><?php echo $soldCardsCount; ?></strong></div>
        </div>

        <!-- Dumps Stats -->
        <div class="stats-container">
            <h3>Dumps Stats</h3>
            <div class="stat-item">Uploaded Dumps: <strong><?php echo $totalDumpsUploaded; ?></strong></div>
            <div class="stat-item">Unsold Dumps: <strong><?php echo $unsoldDumps; ?></strong></div>
            <div class="stat-item">Sold Dumps: <strong><?php echo $soldDumpsCount; ?></strong></div>
        </div>
    </div>
<?php endif; ?>

    </div>
</div>
<?php
include_once('../../footer.php');
?>