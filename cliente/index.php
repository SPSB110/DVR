<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['cliente']);
$page_title = 'I Miei Certificati';
$idU = $user['idU'];

// Get certificates for this client
$certificati = $pdo->prepare("
    SELECT c.*, ing.nome AS nome_ingegnere, a.ragione_sociale
    FROM certificati c
    JOIN utenti ing ON c.idI = ing.idU
    JOIN azienda a ON c.idA = a.idA
    WHERE c.idCliente = ?
    ORDER BY c.data_creazione DESC
");
$certificati->execute([$idU]);
$certificati = $certificati->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>I Miei Certificati</h1>
    </div>

    <?php if (empty($certificati)): ?>
    <div class="card text-center" style="padding:3rem">
        <h3 style="color:var(--gray-500)">Nessun certificato disponibile</h3>
        <p style="color:var(--gray-400)">I tuoi certificati appariranno qui una volta completata la valutazione da parte dell'ingegnere.</p>
    </div>
    <?php else: ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= count($certificati) ?></div>
            <div class="stat-label">Certificati Totali</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value"><?= count(array_filter($certificati, fn($c) => $c['esito'] === 'positivo')) ?></div>
            <div class="stat-label">Positivi</div>
        </div>
        <div class="stat-card red">
            <div class="stat-value"><?= count(array_filter($certificati, fn($c) => $c['esito'] === 'negativo')) ?></div>
            <div class="stat-label">Negativi</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-value"><?= count(array_filter($certificati, fn($c) => $c['esito'] === 'da_rivedere')) ?></div>
            <div class="stat-label">Da Rivedere</div>
        </div>
    </div>

    <?php foreach ($certificati as $cert): ?>
    <div class="result-panel">
        <div class="d-flex justify-between items-center flex-wrap gap-1" style="margin-bottom:1rem">
            <div>
                <h3 style="border:none;padding:0;margin:0">Certificato #<?= $cert['idC'] ?></h3>
                <span style="color:var(--gray-500);font-size:0.85rem"><?= date('d/m/Y H:i', strtotime($cert['data_creazione'])) ?> — <?= h($cert['nome_ingegnere']) ?></span>
            </div>
            <div class="d-flex gap-1 items-center">
                <?= format_esito($cert['esito']) ?>
                <a href="/cliente/certificato.php?idC=<?= $cert['idC'] ?>" class="btn btn-sm btn-primary">Visualizza</a>
                <a href="/pdf/genera_pdf.php?idC=<?= $cert['idC'] ?>" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
            </div>
        </div>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Indice Sollevamento</div>
                <div class="value"><?= $cert['indice_sollevamento'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Peso Sollevato</div>
                <div class="value"><?= $cert['peso_sollevato'] ?> kg</div>
            </div>
            <div class="result-item">
                <div class="label">Azienda</div>
                <div class="value"><?= h($cert['ragione_sociale']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
