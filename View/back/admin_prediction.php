<?php
$pageTitle = 'Smart Nutrition | Product Prediction';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-brain icon"></i> Product Prediction</h1>
            <p class="subtitle">AI-powered price and calories prediction for products.</p>
        </div>
    </div>
    <?php $predMessage = trim((string) ($_GET['pred_message'] ?? '')); $predType = (string) ($_GET['pred_type'] ?? 'success'); ?>

    <?php if ($predMessage !== ''): ?>
    <div style="margin: 12px 0; padding: 12px 14px; border-radius: 10px; border: 1px solid <?= $predType === 'error' ? 'rgba(220,38,38,0.45)' : 'rgba(34,197,94,0.45)' ?>; background: <?= $predType === 'error' ? 'rgba(220,38,38,0.10)' : 'rgba(34,197,94,0.10)' ?>; color: <?= $predType === 'error' ? '#fecaca' : '#bbf7d0' ?>;">
        <i class="fa-solid <?= $predType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>&nbsp;
        <?= htmlspecialchars($predMessage) ?>
    </div>
    <?php endif; ?>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Quick Calories Prediction</h2>
            </div>
            <div style="padding:12px;">
                <form method="POST" action="<?= htmlspecialchars(route_url('admin.prediction.quick')) ?>">
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:220px;">
                            <label>Name</label>
                            <input type="text" name="name" class="input" placeholder="Product name">
                        </div>
                        <div style="flex:2; min-width:320px;">
                            <label>Description</label>
                            <input type="text" name="description" class="input" placeholder="Short product description">
                        </div>
                        <div style="min-width:160px;">
                            <label>Prediction</label>
                            <select name="prediction_type" class="input">
                                <option value="calories">Calories</option>
                                <option value="price">Price</option>
                            </select>
                        </div>
                        <div style="align-self:end">
                            <button type="submit" class="btn">Predict</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
