<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['ingegnere']);
$page_title = 'Dashboard Ingegnere';
$idU = $user['idU'];

// Stats
$totalAssegnati = $pdo->prepare("SELECT COUNT(*) FROM assegnazioni WHERE idIngegnere = ?");
$totalAssegnati->execute([$idU]);
$totalAssegnati = $totalAssegnati->fetchColumn();

$totalCert = $pdo->prepare("SELECT COUNT(*) FROM certificati WHERE idI = ?");
$totalCert->execute([$idU]);
$totalCert = $totalCert->fetchColumn();

$certPositivi = $pdo->prepare("SELECT COUNT(*) FROM certificati WHERE idI = ? AND esito = 'positivo'");
$certPositivi->execute([$idU]);
$certPositivi = $certPositivi->fetchColumn();

$certNegativi = $pdo->prepare("SELECT COUNT(*) FROM certificati WHERE idI = ? AND esito = 'negativo'");
$certNegativi->execute([$idU]);
$certNegativi = $certNegativi->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard Ingegnere</h1>
        <span class="badge badge-info" style="font-size:0.9rem;padding:0.4rem 1rem"><?= h($user['ragione_sociale']) ?></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $totalAssegnati ?></div>
            <div class="stat-label">Clienti Assegnati</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-value"><?= $totalCert ?></div>
            <div class="stat-label">Certificati Emessi</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value"><?= $certPositivi ?></div>
            <div class="stat-label">Esiti Positivi</div>
        </div>
        <div class="stat-card red">
            <div class="stat-value"><?= $certNegativi ?></div>
            <div class="stat-label">Esiti Negativi</div>
        </div>
    </div>

    <div class="cards-grid" style="margin:0;padding:0;margin-top:1rem;">
        <div class="card">
            <h3>Clienti Assegnati</h3>
            <p>Consulta l'elenco dei clienti che ti sono stati assegnati e procedi alla compilazione dei certificati.</p>
            <a href="/ingegnere/clienti.php" class="btn btn-primary">Vedi Clienti</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
