<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['super']);
$page_title = 'Dashboard Super Utente';

// Stats for companies managed
$totalAziende = $pdo->query("SELECT COUNT(*) FROM azienda WHERE valida = 1")->fetchColumn();
$totalUtenti = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo IN ('amministrativo','ingegnere','cliente')")->fetchColumn();
$totalCertificati = $pdo->query("SELECT COUNT(*) FROM certificati")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard Super Utente</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-value"><?= $totalAziende ?></div>
            <div class="stat-label">Aziende Attive</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalUtenti ?></div>
            <div class="stat-label">Utenti Gestiti</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-value"><?= $totalCertificati ?></div>
            <div class="stat-label">Certificati Totali</div>
        </div>
    </div>

    <div class="cards-grid" style="margin:0;padding:0;margin-top:1rem;">
        <div class="card">
            <h3>Gestione Aziende</h3>
            <p>Crea, modifica e gestisci le aziende di certificazione registrate sulla piattaforma.</p>
            <a href="/super/aziende.php" class="btn btn-primary">Gestisci Aziende</a>
        </div>
        <div class="card">
            <h3>Gestione Utenti</h3>
            <p>CRUD completo su tutti gli utenti: amministrativi, ingegneri e clienti delle aziende.</p>
            <a href="/super/utenti.php" class="btn btn-primary">Gestisci Utenti</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
