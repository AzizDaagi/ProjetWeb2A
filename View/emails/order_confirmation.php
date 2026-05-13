<?php
/** @var string $buyerName */
/** @var int $orderId */
/** @var string $buyerPhone */
/** @var string $buyerAddress */
/** @var array $items */
/** @var float $totalPrice */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px; width:100%; background:#ffffff; border-radius:10px; border:1px solid #e5e7eb; overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; background:#111827; color:#ffffff;">
                            <h2 style="margin:0; font-size:20px;">Smart Nutrition</h2>
                            <p style="margin:6px 0 0; font-size:13px; color:#d1d5db;">Order Confirmation</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 10px; font-size:15px;">Hello <?= htmlspecialchars($buyerName, ENT_QUOTES, 'UTF-8') ?>,</p>
                            <p style="margin:0 0 14px; font-size:14px; line-height:1.5; color:#374151;">
                                Thank you for your order. We have received your request and will process it shortly.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 16px; border:1px solid #e5e7eb; border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb; font-size:13px; color:#6b7280;">Order Number</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:right; font-size:14px; font-weight:700;">#<?= (int) $orderId ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb; font-size:13px; color:#6b7280;">Phone</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:right; font-size:14px;"><?= htmlspecialchars($buyerPhone, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; font-size:13px; color:#6b7280;">Delivery Address</td>
                                    <td style="padding:12px 14px; text-align:right; font-size:14px;"><?= nl2br(htmlspecialchars($buyerAddress, ENT_QUOTES, 'UTF-8')) ?></td>
                                </tr>
                            </table>

                            <h3 style="margin:0 0 10px; font-size:16px;">Order Items</h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                                <tr style="background:#f9fafb;">
                                    <th align="left" style="padding:10px 12px; font-size:12px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Product</th>
                                    <th align="right" style="padding:10px 12px; font-size:12px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Qty</th>
                                    <th align="right" style="padding:10px 12px; font-size:12px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Unit</th>
                                    <th align="right" style="padding:10px 12px; font-size:12px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Subtotal</th>
                                </tr>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="padding:10px 12px; font-size:14px; border-bottom:1px solid #f3f4f6;"><?= htmlspecialchars((string) ($item['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td align="right" style="padding:10px 12px; font-size:14px; border-bottom:1px solid #f3f4f6;"><?= (int) ($item['quantity'] ?? 0) ?></td>
                                    <td align="right" style="padding:10px 12px; font-size:14px; border-bottom:1px solid #f3f4f6;"><?= number_format((float) ($item['unit_price'] ?? 0), 2) ?> DH</td>
                                    <td align="right" style="padding:10px 12px; font-size:14px; border-bottom:1px solid #f3f4f6;"><?= number_format((float) ($item['subtotal'] ?? 0), 2) ?> DH</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" align="right" style="padding:12px; font-size:13px; font-weight:700;">Total</td>
                                    <td align="right" style="padding:12px; font-size:16px; font-weight:700; color:#111827;"><?= number_format($totalPrice, 2) ?> DH</td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0; font-size:12px; color:#6b7280;">
                                If you did not place this order, please contact support.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
