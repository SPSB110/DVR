<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['cliente']);
$page_title = 'Certificato';
$idU = $user['idU'];

$idC = (int)($_GET['idC'] ?? 0);

$cert = $pdo->prepare("
    SELECT c.*, ing.nome AS nome_ingegnere, a.ragione_sociale
    FROM certificati c
    JOIN utenti ing ON c.idI = ing.idU
    JOIN azienda a ON c.idA = a.idA
    WHERE c.idC = ? AND c.idCliente = ?
");
$cert->execute([$idC, $idU]);
$cert = $cert->fetch();

if (!$cert) {
    set_flash('error', 'Certificato non trovato.');
    header('Location: /cliente/');
    exit;
}

// Mark as viewed
if (!$cert['visualizzato_cliente']) {
    $pdo->prepare("UPDATE certificati SET visualizzato_cliente = 1, data_visualizzazione = NOW() WHERE idC = ?")->execute([$idC]);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Certificato #<?= $cert['idC'] ?></h1>
        <div class="d-flex gap-1">
            <a href="/pdf/genera_pdf.php?idC=<?= $cert['idC'] ?>" class="btn btn-primary" target="_blank">Scarica PDF</a>
            <a href="/cliente/" class="btn btn-secondary">← I Miei Certificati</a>
        </div>
    </div>

    <div class="indice-big <?= $cert['esito'] ?>">
        <div class="etichetta">Indice di Sollevamento</div>
        <div class="numero"><?= $cert['indice_sollevamento'] ?></div>
        <div class="etichetta" style="font-size:1.1rem;font-weight:600;margin-top:0.5rem">
            Esito: <?= ucfirst(str_replace('_', ' ', $cert['esito'])) ?>
        </div>
    </div>

    <div class="result-panel">
        <h3>Informazioni Certificato</h3>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Azienda Certificatrice</div>
                <div class="value"><?= h($cert['ragione_sociale']) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Ingegnere</div>
                <div class="value"><?= h($cert['nome_ingegnere']) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Data</div>
                <div class="value"><?= date('d/m/Y H:i', strtotime($cert['data_creazione'])) ?></div>
            </div>
        </div>
    </div>

    <div class="result-panel">
        <h3>Parametri di Rilevazione</h3>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Altezza Mani Sollevamento</div>
                <div class="value"><?= $cert['altezza_mani_soll'] ?> cm</div>
            </div>
            <div class="result-item">
                <div class="label">Distanza Verticale</div>
                <div class="value"><?= $cert['distanza_verticale'] ?> cm</div>
            </div>
            <div class="result-item">
                <div class="label">Distanza Orizzontale</div>
                <div class="value"><?= $cert['distanza_orizzontale'] ?> cm</div>
            </div>
            <div class="result-item">
                <div class="label">Dislocazione Angolare</div>
                <div class="value"><?= $cert['dislocazione_angolare'] ?>°</div>
            </div>
            <div class="result-item">
                <div class="label">Giudizio Presa</div>
                <div class="value"><?= $cert['giudizio_presa_carico'] === 'B' ? 'Buono' : 'Scarso' ?></div>
            </div>
            <div class="result-item">
                <div class="label">Frequenza Gesti/min</div>
                <div class="value"><?= $cert['frequenza_gesti'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Frequenza Lavoro</div>
                <div class="value">
                    <?php
                    $fl = ['1' => 'Continuo < 1h', '2' => 'Continuo 1-2h', '3' => 'Continuo 2-8h'];
                    echo $fl[$cert['frequenza_lavoro']] ?? $cert['frequenza_lavoro'];
                    ?>
                </div>
            </div>
            <div class="result-item">
                <div class="label">Peso Sollevato</div>
                <div class="value"><?= $cert['peso_sollevato'] ?> kg</div>
            </div>
        </div>
    </div>

    <?php if ($cert['note']): ?>
    <div class="result-panel">
        <h3>Note</h3>
        <p><?= nl2br(h($cert['note'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="alert alert-info mt-2">
        <strong>Legenda Esiti:</strong><br>
        <strong style="color:#065f46">Positivo</strong> (Indice ≤ 0.85): Rischio accettabile, nessuna azione necessaria.<br>
        <strong style="color:#92400e">Da Rivedere</strong> (0.85 &lt; Indice ≤ 1.0): Rischio borderline, monitoraggio consigliato.<br>
        <strong style="color:#991b1b">Negativo</strong> (Indice &gt; 1.0): Rischio presente, interventi correttivi necessari.
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
