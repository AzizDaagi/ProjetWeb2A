<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 800px; margin: 0 auto; padding: <?= (isset($_GET['embed']) && $_GET['embed'] === 'true') ? '1rem' : '5rem 1rem' ?>; text-align: center;">
    <div class="glass-card" style="padding: 3rem;">
        <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10b981; margin-bottom: 1.5rem;"></i>
        <h2 style="color: var(--primary); margin-bottom: 1rem;"><i class="fa-solid fa-check-double"></i> Demande enregistree !</h2>

        <?php
        $mailStatus = $_SESSION['last_mail_status'] ?? null;
        $mailRecipient = $_SESSION['last_mail_recipient'] ?? '';
        $mailTransport = $_SESSION['last_mail_transport'] ?? '';
        $mailMessageId = $_SESSION['last_mail_message_id'] ?? '';
        $mailError = $_SESSION['last_mail_error'] ?? '';
        unset($_SESSION['last_mail_status'], $_SESSION['last_mail_recipient'], $_SESSION['last_mail_transport'], $_SESSION['last_mail_message_id'], $_SESSION['last_mail_error']);
        ?>

        <?php if ($mailStatus === 'sent'): ?>
            <div style="margin: 1.5rem 0 2rem; padding: 1rem 1.2rem; border: 1px solid rgba(16, 185, 129, 0.45); background: rgba(16, 185, 129, 0.12); border-radius: 10px; color: #d1fae5; text-align: left;">
                <strong style="display: block; margin-bottom: 0.35rem;"><i class="fa-solid fa-envelope-circle-check"></i> Email de confirmation transmis</strong>
                <span style="font-size: 0.95rem;">Votre message de remerciement a ete transmis a <strong><?= htmlspecialchars($mailRecipient) ?></strong> via <strong><?= htmlspecialchars($mailTransport ?: 'mail server') ?></strong>.</span>
                <?php if (!empty($mailMessageId)): ?>
                    <div style="margin-top: 0.5rem; font-size: 0.85rem; opacity: 0.95;">ID Brevo: <code><?= htmlspecialchars($mailMessageId) ?></code></div>
                <?php endif; ?>
                <div style="margin-top: 0.55rem; font-size: 0.85rem; opacity: 0.9;">Si vous ne le voyez pas, verifiez vos dossiers Spam/Promotions et attendez 2-5 minutes.</div>
            </div>
        <?php elseif ($mailStatus === 'failed'): ?>
            <div style="margin: 1.5rem 0 2rem; padding: 1rem 1.2rem; border: 1px solid rgba(239, 68, 68, 0.45); background: rgba(239, 68, 68, 0.12); border-radius: 10px; color: #fecaca; text-align: left;">
                <strong style="display: block; margin-bottom: 0.35rem;"><i class="fa-solid fa-triangle-exclamation"></i> Envoi email non confirme</strong>
                <span style="font-size: 0.95rem;">Votre demande est bien enregistree, mais l'email de remerciement n'a pas pu etre transmis cette fois.</span>
                <?php if (!empty($mailError)): ?>
                    <div style="margin-top: 0.55rem; font-size: 0.85rem; opacity: 0.95;">Detail technique: <?= htmlspecialchars($mailError) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        if (isset($_SESSION['last_suggestions'])):
            $sug = $_SESSION['last_suggestions'];
            unset($_SESSION['last_suggestions']);
        ?>
            <div style="margin: 2rem 0; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; text-align: left;">
                <h3 style="color: var(--secondary); margin-bottom: 1rem;"><i class="fa-solid fa-bolt"></i> Programme genere instantanement pour : <?= htmlspecialchars($sug['goal']) ?></h3>
                <p style="margin-bottom: 1rem; color: var(--text-muted);">Voici vos recommandations personnalisees calculees immediatement :</p>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($sug['activities'] as $act): ?>
                        <div style="padding: 1rem; background: rgba(0,0,0,0.2); border-left: 4px solid var(--primary); border-radius: 4px;">
                            <strong style="color: #fff;"><?= htmlspecialchars($act['nom_activite']) ?></strong>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;"><?= htmlspecialchars($act['description']) ?></p>
                            <span style="font-size: 0.8rem; color: var(--primary);"><?= $act['calories_brulees'] ?> kcal / <?= $act['duree_minutes'] ?> min</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($sug['api_exercises'])): ?>
                    <h3 style="color: var(--accent); margin: 2rem 0 1rem;"><i class="fa-solid fa-dumbbell"></i> Exercices recommandes (IA)</h3>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach ($sug['api_exercises'] as $ex): ?>
                            <div style="padding: 1.2rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="color: #fff; font-size: 1.1rem;"><?= htmlspecialchars($ex['name']) ?></strong>
                                    <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; background: rgba(243, 156, 18, 0.2); color: var(--accent); text-transform: uppercase;">
                                        <?= htmlspecialchars($ex['difficulty']) ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 0.8rem;">
                                    <span style="font-size: 0.85rem; color: var(--secondary);"><i class="fa-solid fa-mound"></i> Muscle: <?= htmlspecialchars($ex['muscle']) ?></span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; white-space: pre-line;">
                                    <?= htmlspecialchars($ex['instructions']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 2rem;">
                Votre programme a ete genere avec succes ! Vous pouvez maintenant explorer vos recommandations personnalisees.
            </p>
        <?php endif; ?>

<?php 
$isEmbed = (isset($_GET['embed']) && $_GET['embed'] === 'true');
$embedQuery = $isEmbed ? '&embed=true' : '';
?>
        <a href="index.php?action=nutrition_request<?= $embedQuery ?>" class="btn" style="display: inline-block; padding: 0.75rem 2rem; font-size: 1.1rem;">
            <i class="fa-solid fa-plus-circle"></i> <?= $isEmbed ? 'Nouvelle Demande' : 'Retour' ?>
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
