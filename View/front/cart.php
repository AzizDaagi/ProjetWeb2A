<?php
$pageTitle = 'Shopping Cart | Smart Nutrition';
require __DIR__ . '/../header.php';
?>

<section class="products-shell" style="max-width: 1000px; margin: 0 auto;">
    <div class="section-head">
        <p class="section-kicker">Your Order</p>
        <h2>Shopping Cart</h2>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <strong>✅ Success!</strong> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <strong>❌ Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
    <div style="text-align: center; padding: 60px 20px;">
        <p style="font-size: 18px; color: var(--text-muted); margin-bottom: 30px;">Your cart is empty</p>
        <a href="<?php echo route_url('home'); ?>" class="btn btn-primary">
            <i class="fa-solid fa-arrow-left"></i> Continue Shopping
        </a>
    </div>
    <?php else: ?>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-top: 30px;">
        <!-- Cart Items -->
        <div>
            <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <form method="POST" action="<?php echo route_url('cart.update'); ?>" novalidate>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f5f5f5; border-bottom: 2px solid #e0e0e0;">
                                <th style="padding: 15px; text-align: left;">Product</th>
                                <th style="padding: 15px; text-align: center; width: 100px;">Unit Price</th>
                                <th style="padding: 15px; text-align: center; width: 120px;">Quantity</th>
                                <th style="padding: 15px; text-align: right; width: 100px;">Subtotal</th>
                                <th style="padding: 15px; text-align: center; width: 60px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr style="border-bottom: 1px solid #e0e0e0; hover-background: #fafafa;">
                                <td style="padding: 15px;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php if ($item['product_image']): ?>
                                        <img src="<?php echo upload_url($item['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-image" style="color: #ccc;"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <?php echo number_format($item['unit_price'], 2); ?> DH
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                     <input type="text" 
                                           name="cart_update[<?php echo $item['product_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           style="width: 60px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                </td>
                                <td style="padding: 15px; text-align: right;">
                                    <strong><?php echo number_format($item['subtotal'], 2); ?> DH</strong>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="<?php echo route_url('cart.remove', ['product_id' => $item['product_id']]); ?>" 
                                       class="btn-icon"
                                       style="color: #e74c3c; cursor: pointer;"
                                       title="Remove from cart">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="padding: 15px; border-top: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                        <button type="submit" class="btn btn-secondary" style="flex: 1; margin-right: 10px;">
                            <i class="fa-solid fa-sync"></i> Update Cart
                        </button>
                        <a href="<?php echo route_url('home'); ?>" class="btn btn-outline" style="flex: 1;">
                            <i class="fa-solid fa-plus"></i> Add More Items
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cart Summary (Sidebar) -->
        <div>
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    <i class="fa-solid fa-receipt"></i> Order Summary
                </h3>

                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <span>Items:</span>
                    <strong><?php echo $cartCount; ?></strong>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                        <span>Subtotal:</span>
                        <span><?php echo number_format($cartTotal, 2); ?> DH</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; font-size: 16px;">
                    <strong>Total:</strong>
                    <strong style="color: var(--primary); font-size: 22px;">
                        <?php echo number_format($cartTotal, 2); ?> DH
                    </strong>
                </div>

                <a href="<?php echo route_url('cart.checkout'); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                    <i class="fa-solid fa-credit-card"></i> Proceed to Checkout
                </a>

                <a href="<?php echo route_url('cart.clear'); ?>" class="btn btn-outline" style="width: 100%; text-align: center; margin-top: 10px;">
                    <i class="fa-solid fa-trash"></i> Clear Cart
                </a>

                <a href="<?php echo route_url('home'); ?>" style="display: block; text-align: center; margin-top: 15px; color: var(--primary); text-decoration: none;">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
