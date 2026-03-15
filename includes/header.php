<?php
// includes/header.php - Common header with navbar
$current_user = get_current_user_data();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title><?= h($page_title ?? 'DVR') ?> - DVR Certificazioni</title>
</head>
<body>
<nav class="navbar">
    <a href="/" class="navbar-brand">DVR</a>
    <?php if ($current_user): ?>
    <ul class="navbar-nav">
        <?php if ($current_user['ruolo'] === 'admin'): ?>
            <li><a href="/admin/">Dashboard</a></li>
            <li><a href="/admin/utenti.php">Utenti</a></li>
            <li><a href="/admin/aziende.php">Aziende</a></li>
        <?php elseif ($current_user['ruolo'] === 'super'): ?>
            <li><a href="/super/">Dashboard</a></li>
            <li><a href="/super/utenti.php">Utenti</a></li>
            <li><a href="/super/aziende.php">Aziende</a></li>
        <?php elseif ($current_user['ruolo'] === 'amministrativo'): ?>
            <li><a href="/amministrativo/">Dashboard</a></li>
            <li><a href="/amministrativo/clienti.php">Clienti</a></li>
            <li><a href="/amministrativo/assegna.php">Assegnazioni</a></li>
            <li><a href="/amministrativo/stato.php">Stato</a></li>
        <?php elseif ($current_user['ruolo'] === 'ingegnere'): ?>
            <li><a href="/ingegnere/">Dashboard</a></li>
            <li><a href="/ingegnere/clienti.php">Clienti Assegnati</a></li>
        <?php elseif ($current_user['ruolo'] === 'cliente'): ?>
            <li><a href="/cliente/">I Miei Certificati</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-user">
        <span><strong><?= h($current_user['nome']) ?></strong> (<?= h(format_ruolo($current_user['ruolo'])) ?>)</span>
        <a href="/logout.php" class="btn btn-sm btn-outline-light">Esci</a>
    </div>
    <?php endif; ?>
</nav>

<?php if ($flash): ?>
<div class="container" style="padding-bottom:0">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : h($flash['type']) ?>">
        <?= h($flash['message']) ?>
    </div>
</div>
<?php endif; ?>
