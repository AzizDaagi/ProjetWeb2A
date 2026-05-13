<?php
$pageTitle = 'Database Migration | Smart Nutrition';
require __DIR__ . '/../header.php';
?>

<section class="admin-dashboard-shell">
    <div class="admin-page-head">
        <p class="section-kicker">Database</p>
        <h2>Migration Successful</h2>
    </div>
    
    <div style="margin-top: 30px;">
        <div style="padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 20px;">
            <p style="color: #155724; margin: 0;"><strong>✅ <?php echo htmlspecialchars($message); ?></strong></p>
        </div>
        
        <h3 style="margin-top: 30px;">Table Structure: commande_item</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Field</th>
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($columns as $col): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong><?php echo htmlspecialchars($col['Field']); ?></strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($col['Type']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px;">
            <p>The cart system is now ready to be implemented. You can proceed with building the cart functionality.</p>
            <a href="<?php echo route_url('home'); ?>" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">Return to Home</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
