<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['admin']);
$page_title = 'Dashboard Admin';

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM utenti")->fetchColumn();
$totalAziende = $pdo->query("SELECT COUNT(*) FROM azienda")->fetchColumn();
$totalCertificati = $pdo->query("SELECT COUNT(*) FROM certificati")->fetchColumn();
$totalSuperUsers = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo = 'super'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard Amministratore Sistema</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Utenti Totali</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value"><?= $totalAziende ?></div>
            <div class="stat-label">Aziende Registrate</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-value"><?= $totalCertificati ?></div>
            <div class="stat-label">Certificati Emessi</div>
        </div>
        <div class="stat-card red">
            <div class="stat-value"><?= $totalSuperUsers ?></div>
            <div class="stat-label">Super Utenti</div>
        </div>
    </div>

    <div class="cards-grid" style="margin:0;padding:0;margin-top:1rem;">
        <div class="card">
            <h3>Gestione Utenti</h3>
            <p>Crea, modifica ed elimina utenti di qualsiasi ruolo. L'admin può gestire tutti gli utenti del sistema.</p>
            <a href="/admin/utenti.php" class="btn btn-primary">Gestisci Utenti</a>
        </div>
        <div class="card">
            <h3>Gestione Aziende</h3>
            <p>Visualizza e gestisci tutte le aziende registrate sulla piattaforma.</p>
            <a href="/admin/aziende.php" class="btn btn-primary">Gestisci Aziende</a>
        </div>
        <div class="card">
            <h3>Super Utenti</h3>
            <p>Crea e gestisci i Super Utenti che gestiscono le aziende di certificazione.</p>
            <a href="/admin/utenti.php?ruolo=super" class="btn btn-primary">Gestisci Super Utenti</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
