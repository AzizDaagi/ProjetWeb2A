<?php
$pageTitle = 'Migration Error | Smart Nutrition';
require __DIR__ . '/../header.php';
?>

<section class="admin-dashboard-shell">
    <div class="admin-page-head">
        <p class="section-kicker">Database</p>
        <h2>Migration Error</h2>
    </div>
    
    <div style="margin-top: 30px;">
        <div style="padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin-top: 20px;">
            <p style="color: #721c24; margin: 0;"><strong>❌ Database Error:</strong></p>
            <p style="color: #721c24; margin: 10px 0 0 0;"><?php echo htmlspecialchars($error); ?></p>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="<?php echo route_url('home'); ?>" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">Return to Home</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
