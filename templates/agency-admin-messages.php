<?php
/** @var array<int, array<string, string>> $messages */
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages Contact</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background:#f6f8ff; color:#1f2235; }
        .wrap { max-width: 980px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; background:white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #eef1ff; text-align: left; }
        th { background: #edf1ff; }
        button { border: 0; padding: 8px 12px; border-radius: 8px; cursor: pointer; background:#3e72ff; color:white; }
        dialog { border: none; border-radius: 12px; width:min(620px, 95vw); padding:0; box-shadow:0 20px 40px rgba(0,0,0,.2); }
        dialog::backdrop { background: rgba(0,0,0,.45); }
        .modal-content { padding: 20px; }
        .meta { color:#57607e; font-size:.92rem; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Messages reçus depuis le formulaire de contact</h1>
    <p>URL admin discrète, non visible pour les visiteurs.</p>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Aperçu</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($messages) === 0): ?>
            <tr><td colspan="5">Aucun message pour le moment.</td></tr>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?></td>
                    <td><?= htmlspecialchars($message['name']) ?></td>
                    <td><?= htmlspecialchars($message['email']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($message['message'], 0, 90, '...')) ?></td>
                    <td><button type="button" data-modal-target="msg-<?= htmlspecialchars($message['id']) ?>">Afficher</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php foreach ($messages as $message): ?>
        <dialog id="msg-<?= htmlspecialchars($message['id']) ?>">
            <div class="modal-content">
                <h3>Message complet</h3>
                <p class="meta">
                    <strong><?= htmlspecialchars($message['name']) ?></strong> —
                    <?= htmlspecialchars($message['email']) ?> —
                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?>
                </p>
                <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                <form method="dialog">
                    <button type="submit">Fermer</button>
                </form>
            </div>
        </dialog>
    <?php endforeach; ?>
</div>

<script>
    document.querySelectorAll('[data-modal-target]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById(btn.dataset.modalTarget);
            if (dialog) {
                dialog.showModal();
            }
        });
    });
</script>
</body>
</html>
