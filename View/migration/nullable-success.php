<?php
$pageTitle = 'Database Migration | Smart Nutrition';
require __DIR__ . '/../header.php';
?>

<section class="admin-dashboard-shell">
    <div class="admin-page-head">
        <p class="section-kicker">Database</p>
        <h2>Schema Migration Complete</h2>
    </div>
    
    <div style="margin-top: 30px;">
        <div style="padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
            <p style="color: #155724; margin: 0;"><strong>✅ Migration completed successfully!</strong></p>
        </div>

        <h3 style="margin-bottom: 15px;">Migration Steps</h3>
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($messages as $message): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-family: monospace; font-size: 13px;">
                    <?php echo htmlspecialchars($message); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="margin-top: 30px;">
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                Your database is now ready for multi-product cart orders. The product_id column can now be NULL for orders created from the shopping cart.
            </p>
            <a href="<?php echo route_url('home'); ?>" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">Return to Home</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
