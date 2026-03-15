<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['amministrativo']);
$page_title = 'Dashboard Amministrativo';
$idA = $user['idA'];

$totalClienti = $pdo->prepare("SELECT COUNT(*) FROM utenti WHERE ruolo = 'cliente' AND idA = ?");
$totalClienti->execute([$idA]);
$totalClienti = $totalClienti->fetchColumn();

$totalIngegneri = $pdo->prepare("SELECT COUNT(*) FROM utenti WHERE ruolo = 'ingegnere' AND idA = ?");
$totalIngegneri->execute([$idA]);
$totalIngegneri = $totalIngegneri->fetchColumn();

$totalCert = $pdo->prepare("SELECT COUNT(*) FROM certificati WHERE idA = ?");
$totalCert->execute([$idA]);
$totalCert = $totalCert->fetchColumn();

$clientiSenzaCredenziali = $pdo->prepare("SELECT COUNT(*) FROM utenti WHERE ruolo = 'cliente' AND idA = ? AND credenziali_inviate = 0");
$clientiSenzaCredenziali->execute([$idA]);
$clientiSenzaCredenziali = $clientiSenzaCredenziali->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard Amministrativo</h1>
        <span class="badge badge-info" style="font-size:0.9rem;padding:0.4rem 1rem"><?= h($user['ragione_sociale']) ?></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $totalClienti ?></div>
            <div class="stat-label">Clienti</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value"><?= $totalIngegneri ?></div>
            <div class="stat-label">Ingegneri</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-value"><?= $totalCert ?></div>
            <div class="stat-label">Certificati</div>
        </div>
        <div class="stat-card red">
            <div class="stat-value"><?= $clientiSenzaCredenziali ?></div>
            <div class="stat-label">Credenziali da Inviare</div>
        </div>
    </div>

    <div class="cards-grid" style="margin:0;padding:0;margin-top:1rem;">
        <div class="card">
            <h3>Gestione Clienti</h3>
            <p>Inserisci i clienti della tua azienda e gestisci le loro informazioni e credenziali di accesso.</p>
            <a href="/amministrativo/clienti.php" class="btn btn-primary">Gestisci Clienti</a>
        </div>
        <div class="card">
            <h3>Assegnazioni</h3>
            <p>Assegna i clienti agli ingegneri per le visite di certificazione e la raccolta dati.</p>
            <a href="/amministrativo/assegna.php" class="btn btn-primary">Gestisci Assegnazioni</a>
        </div>
        <div class="card">
            <h3>Stato Clienti</h3>
            <p>Monitora lo stato dei clienti: credenziali inviate, primi accessi, certificati visualizzati.</p>
            <a href="/amministrativo/stato.php" class="btn btn-primary">Visualizza Stato</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
