<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['ingegnere']);
$idU = $user['idU'];
$idA = $user['idA'];

$idCliente = (int)($_GET['idCliente'] ?? $_POST['idCliente'] ?? 0);

// Verify client is assigned to this engineer
$stmtCheck = $pdo->prepare("SELECT u.idU, u.nome, u.sesso, u.nascita FROM assegnazioni a JOIN utenti u ON a.idCliente = u.idU WHERE a.idIngegnere = ? AND a.idCliente = ?");
$stmtCheck->execute([$idU, $idCliente]);
$cliente = $stmtCheck->fetch();

if (!$cliente) {
    set_flash('error', 'Cliente non trovato o non assegnato a te.');
    header('Location: /ingegnere/clienti.php');
    exit;
}

$page_title = 'Certificazione - ' . $cliente['nome'];
$risultato = null;

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $altezzaManiSoll = (float)$_POST['altezzaManiSoll'];
    $distanzaVerticale = (float)$_POST['distanzaVerticale'];
    $distanzaOrizzontale = (float)$_POST['distanzaOrizzontale'];
    $dislocazioneAngolare = (float)$_POST['dislocazioneAngolare'];
    $giudizioPresa = $_POST['giudizioPresa'];
    $frequenzaGesti = (float)$_POST['frequenzaGesti'];
    $frequenzaLavoro = (int)$_POST['frequenzaLavoro'];
    $pesoSollevato = (float)$_POST['pesoSollevato'];
    $note = trim($_POST['note'] ?? '');

    // Calculate age
    $nascita = new DateTime($cliente['nascita']);
    $oggi = new DateTime();
    $eta = $oggi->diff($nascita)->y;

    // Calculate NIOSH
    $risultato = calcola_indice_sollevamento(
        $cliente['sesso'], $eta,
        $altezzaManiSoll, $distanzaVerticale, $distanzaOrizzontale,
        $dislocazioneAngolare, $giudizioPresa, $frequenzaGesti,
        $frequenzaLavoro, $pesoSollevato
    );

    $esito = get_esito_from_indice($risultato['indice']);

    // Save to database
    if (isset($_POST['salva'])) {
        $sql = "INSERT INTO certificati (idCliente, idI, idA, esito, note, altezza_mani_soll, distanza_verticale, distanza_orizzontale, dislocazione_angolare, giudizio_presa_carico, frequenza_gesti, frequenza_lavoro, peso_sollevato, indice_sollevamento)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([
            $idCliente, $idU, $idA, $esito, $note,
            $altezzaManiSoll, $distanzaVerticale, $distanzaOrizzontale,
            $dislocazioneAngolare, $giudizioPresa, $frequenzaGesti,
            $frequenzaLavoro, $pesoSollevato, $risultato['indice']
        ]);

        log_calc('NuovoCertificato', [
            'idCliente' => $idCliente,
            'indice' => $risultato['indice'],
            'esito' => $esito
        ]);

        set_flash('success', 'Certificato salvato con successo! Indice di sollevamento: ' . $risultato['indice'] . ' — Esito: ' . ucfirst(str_replace('_', ' ', $esito)));
        header('Location: /ingegnere/clienti.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Nuova Certificazione</h1>
        <a href="/ingegnere/clienti.php" class="btn btn-secondary">← Torna ai Clienti</a>
    </div>

    <div class="alert alert-info">
        <strong>Cliente:</strong> <?= h($cliente['nome']) ?> — 
        <strong>Sesso:</strong> <?= $cliente['sesso'] === 'M' ? 'Maschio' : 'Femmina' ?> — 
        <strong>Data Nascita:</strong> <?= date('d/m/Y', strtotime($cliente['nascita'])) ?>
    </div>

    <?php if ($risultato): ?>
    <!-- RISULTATI -->
    <div class="indice-big <?= get_esito_from_indice($risultato['indice']) ?>">
        <div class="etichetta">Indice di Sollevamento</div>
        <div class="numero"><?= $risultato['indice'] ?></div>
        <div class="etichetta" style="font-size:1.1rem;font-weight:600;margin-top:0.5rem">
            Esito: <?= ucfirst(str_replace('_', ' ', get_esito_from_indice($risultato['indice']))) ?>
        </div>
    </div>

    <div class="result-panel">
        <h3>Dettaglio Fattori di Calcolo</h3>
        <div class="result-grid">
            <div class="result-item">
                <div class="label">Fattore Età (Peso Max)</div>
                <div class="value"><?= $risultato['fattori']['fattoreEta'] ?> kg</div>
            </div>
            <div class="result-item">
                <div class="label">Fattore Altezza</div>
                <div class="value"><?= $risultato['fattori']['fattoreAltezza'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Fattore Disl. Verticale</div>
                <div class="value"><?= $risultato['fattori']['fattoreDiscV'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Fattore Disl. Orizzontale</div>
                <div class="value"><?= $risultato['fattori']['fattoreDiscO'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Fattore Angolare</div>
                <div class="value"><?= $risultato['fattori']['fattoreAngolare'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Fattore Presa</div>
                <div class="value"><?= $risultato['fattori']['fattorePresa'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Frequenza Gesti</div>
                <div class="value"><?= $risultato['fattori']['frequenzaGesti'] ?></div>
            </div>
            <div class="result-item">
                <div class="label">Denominatore</div>
                <div class="value"><?= round($risultato['fattori']['den'], 4) ?></div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>Attenzione:</strong> Il calcolo sopra è un'anteprima. Clicca "Salva Certificato" nel modulo sottostante per confermare e salvare.
    </div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="form-card">
        <h3 class="mb-2">Dati di Rilevazione NIOSH</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="idCliente" value="<?= $idCliente ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Altezza Mani Sollevamento (cm) *</label>
                    <input type="number" name="altezzaManiSoll" min="0" max="300" required
                           value="<?= h($_POST['altezzaManiSoll'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Distanza Verticale (cm) *</label>
                    <input type="number" name="distanzaVerticale" min="25" max="300" required
                           value="<?= h($_POST['distanzaVerticale'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Distanza Orizzontale (cm) *</label>
                    <input type="number" name="distanzaOrizzontale" min="25" max="100" required
                           value="<?= h($_POST['distanzaOrizzontale'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Dislocazione Angolare (°) *</label>
                    <input type="number" name="dislocazioneAngolare" min="0" max="360" required
                           value="<?= h($_POST['dislocazioneAngolare'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Giudizio Presa Carico *</label>
                    <div class="radio-group">
                        <label><input type="radio" name="giudizioPresa" value="B" required <?= ($_POST['giudizioPresa'] ?? '') === 'B' ? 'checked' : '' ?>> Buono</label>
                        <label><input type="radio" name="giudizioPresa" value="S" <?= ($_POST['giudizioPresa'] ?? '') === 'S' ? 'checked' : '' ?>> Scarso</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Frequenza Gesti al Minuto *</label>
                    <input type="number" name="frequenzaGesti" min="0.2" max="60" step="0.1" required
                           value="<?= h($_POST['frequenzaGesti'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Frequenza Lavoro *</label>
                    <div class="radio-group" style="flex-direction:column;gap:0.5rem">
                        <label><input type="radio" name="frequenzaLavoro" value="1" required <?= ($_POST['frequenzaLavoro'] ?? '') == '1' ? 'checked' : '' ?>> Continuo &lt; 1 ora</label>
                        <label><input type="radio" name="frequenzaLavoro" value="2" <?= ($_POST['frequenzaLavoro'] ?? '') == '2' ? 'checked' : '' ?>> Continuo da 1 a 2 ore</label>
                        <label><input type="radio" name="frequenzaLavoro" value="3" <?= ($_POST['frequenzaLavoro'] ?? '') == '3' ? 'checked' : '' ?>> Continuo da 2 a 8 ore</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Peso Sollevato (kg) *</label>
                    <input type="number" name="pesoSollevato" min="0" max="350" step="0.1" required
                           value="<?= h($_POST['pesoSollevato'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Note</label>
                <textarea name="note" placeholder="Eventuali osservazioni sulla rilevazione..."><?= h($_POST['note'] ?? '') ?></textarea>
            </div>
            <div class="d-flex gap-1">
                <button type="submit" name="calcola" value="1" class="btn btn-secondary">Calcola Anteprima</button>
                <button type="submit" name="salva" value="1" class="btn btn-primary">Salva Certificato</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
