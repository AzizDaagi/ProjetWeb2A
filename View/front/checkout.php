<?php
$pageTitle = 'Checkout | Smart Nutrition';
require __DIR__ . '/../header.php';
?>

<section class="products-shell" style="max-width: 1000px; margin: 0 auto;">
    <div class="section-head">
        <p class="section-kicker">Final Step</p>
        <h2>Checkout</h2>
    </div>

        <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <strong>❌ Error!</strong> <?php echo htmlspecialchars((string) $error); ?>
        </div>
        <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
        <!-- Checkout Form -->
        <div style="background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <form method="POST" action="<?php echo route_url('cart.process'); ?>" novalidate>
                <div style="margin-bottom: 25px;">
                    <label for="buyer_name" style="display: block; margin-bottom: 8px; font-weight: 600;">Full Name *</label>
                    <input 
                        type="text" 
                        id="buyer_name"
                        name="buyer_name" 
                        value="<?php echo htmlspecialchars((string) ($old['buyer_name'] ?? '')); ?>"
                        placeholder="Enter your full name"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label for="buyer_phone" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone Number *</label>
                    <input 
                        type="text" 
                        id="buyer_phone"
                        name="buyer_phone" 
                        value="<?php echo htmlspecialchars((string) ($old['buyer_phone'] ?? '')); ?>"
                        placeholder="Enter your phone number"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit;">
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="buyer_address" style="display: block; margin-bottom: 8px; font-weight: 600;">Delivery Address *</label>
                    <textarea 
                        id="buyer_address"
                        name="buyer_address" 
                        placeholder="Enter your delivery address"
                        rows="4"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars((string) ($old['buyer_address'] ?? '')); ?></textarea>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fa-solid fa-check"></i> Complete Order
                    </button>
                    <a href="<?php echo route_url('cart.view'); ?>" class="btn btn-outline" style="flex: 1; text-align: center;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div>
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    <i class="fa-solid fa-receipt"></i> Order Summary
                </h3>

                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                    <?php foreach ($cartItems as $item): ?>
                    <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong style="font-size: 14px;"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <span style="font-weight: 600;"><?php echo number_format($item['subtotal'], 2); ?> DH</span>
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            <?php echo $item['quantity']; ?> × <?php echo number_format($item['unit_price'], 2); ?> DH
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <span>Items:</span>
                    <strong><?php echo $cartCount; ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 16px;">
                    <strong>Total:</strong>
                    <strong style="color: var(--primary); font-size: 22px;">
                        <?php echo number_format($cartTotal, 2); ?> DH
                    </strong>
                </div>

                <div style="background: #f0f7ff; border-left: 4px solid var(--primary); padding: 12px; border-radius: 4px; font-size: 13px; color: #333;">
                    <i class="fa-solid fa-info-circle"></i> Please ensure all information is correct before completing your order.
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
