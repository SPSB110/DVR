<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['ingegnere', 'admin']);
$page_title = 'Dettaglio Certificato';

$idC = (int)($_GET['idC'] ?? 0);

$cert = $pdo->prepare("
    SELECT c.*, cli.nome AS nome_cliente, cli.sesso, cli.nascita,
           ing.nome AS nome_ingegnere, a.ragione_sociale
    FROM certificati c
    JOIN utenti cli ON c.idCliente = cli.idU
    JOIN utenti ing ON c.idI = ing.idU
    JOIN azienda a ON c.idA = a.idA
    WHERE c.idC = ?
");
$cert->execute([$idC]);
$cert = $cert->fetch();

if (!$cert) {
    set_flash('error', 'Certificato non trovato.');
    header('Location: /ingegnere/clienti.php');
    exit;
}

// Check access (engineer sees their own, admin sees all)
if ($user['ruolo'] === 'ingegnere' && $cert['idI'] !== $user['idU']) {
    set_flash('error', 'Non hai accesso a questo certificato.');
    header('Location: /ingegnere/clienti.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Certificato #<?= $cert['idC'] ?></h1>
        <div class="d-flex gap-1">
            <a href="/pdf/genera_pdf.php?idC=<?= $cert['idC'] ?>" class="btn btn-primary" target="_blank">Scarica PDF</a>
            <a href="javascript:history.back()" class="btn btn-secondary">← Indietro</a>
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
        <h3>Dati Generali</h3>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Cliente</div>
                <div class="value"><?= h($cert['nome_cliente']) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Sesso</div>
                <div class="value"><?= $cert['sesso'] === 'M' ? 'Maschio' : 'Femmina' ?></div>
            </div>
            <div class="result-item">
                <div class="label">Data Nascita</div>
                <div class="value"><?= date('d/m/Y', strtotime($cert['nascita'])) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Ingegnere</div>
                <div class="value"><?= h($cert['nome_ingegnere']) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Azienda</div>
                <div class="value"><?= h($cert['ragione_sociale']) ?></div>
            </div>
            <div class="result-item">
                <div class="label">Data Creazione</div>
                <div class="value"><?= date('d/m/Y H:i', strtotime($cert['data_creazione'])) ?></div>
            </div>
        </div>
    </div>

    <div class="result-panel">
        <h3>Parametri di Rilevazione</h3>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Altezza Mani Soll.</div>
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
                <div class="label">Freq. Gesti/min</div>
                <div class="value"><?= $cert['frequenza_gesti'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Frequenza Lavoro</div>
                <div class="value">
                    <?php
                    $fl = ['1' => '< 1 ora', '2' => '1-2 ore', '3' => '2-8 ore'];
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
