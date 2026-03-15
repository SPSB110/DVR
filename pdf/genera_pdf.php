<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Allow access to ingegnere, cliente, admin
require_login();
$currentUser = get_current_user_data();

$idC = (int)($_GET['idC'] ?? 0);

// Fetch certificate data
$cert = $pdo->prepare("
    SELECT c.*, 
           cli.nome AS nome_cliente, cli.sesso, cli.nascita,
           ing.nome AS nome_ingegnere,
           a.ragione_sociale, a.p_iva, a.indirizzo, a.telefono, a.email
    FROM certificati c
    JOIN utenti cli ON c.idCliente = cli.idU
    JOIN utenti ing ON c.idI = ing.idU
    JOIN azienda a ON c.idA = a.idA
    WHERE c.idC = ?
");
$cert->execute([$idC]);
$cert = $cert->fetch();

if (!$cert) {
    die('Certificato non trovato.');
}

// Access control
if ($currentUser['ruolo'] === 'cliente' && $cert['idCliente'] !== $currentUser['idU']) {
    die('Accesso negato.');
}
if ($currentUser['ruolo'] === 'ingegnere' && $cert['idI'] !== $currentUser['idU']) {
    die('Accesso negato.');
}
if ($currentUser['ruolo'] === 'amministrativo' && $cert['idA'] !== $currentUser['idA']) {
    die('Accesso negato.');
}

// Calculate age at time of cert
$nascita = new DateTime($cert['nascita']);
$dataCert = new DateTime($cert['data_creazione']);
$eta = $dataCert->diff($nascita)->y;

// Get factors
$risultato = calcola_indice_sollevamento(
    $cert['sesso'], $eta,
    $cert['altezza_mani_soll'], $cert['distanza_verticale'],
    $cert['distanza_orizzontale'], $cert['dislocazione_angolare'],
    $cert['giudizio_presa_carico'], $cert['frequenza_gesti'],
    $cert['frequenza_lavoro'], $cert['peso_sollevato']
);

$esitoColors = [
    'positivo' => '#059669',
    'negativo' => '#dc2626',
    'da_rivedere' => '#d97706',
];
$esitoLabels = [
    'positivo' => 'POSITIVO',
    'negativo' => 'NEGATIVO',
    'da_rivedere' => 'DA RIVEDERE',
];
$esitoColor = $esitoColors[$cert['esito']] ?? '#333';
$esitoLabel = $esitoLabels[$cert['esito']] ?? strtoupper($cert['esito']);

$freqLavoroLabels = ['1' => 'Continuo < 1 ora', '2' => 'Continuo 1-2 ore', '3' => 'Continuo 2-8 ore'];

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #1e293b; margin: 0; padding: 30px; }
    .header { border-bottom: 3px solid #1a56db; padding-bottom: 15px; margin-bottom: 20px; }
    .header h1 { color: #1a56db; font-size: 22pt; margin: 0; }
    .header .subtitle { color: #64748b; font-size: 10pt; margin-top: 4px; }
    .header .cert-id { float: right; color: #64748b; font-size: 10pt; margin-top: 5px; }
    
    .section { margin-bottom: 18px; }
    .section h2 { font-size: 13pt; color: #1a56db; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
    
    table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    table.data th { background: #f1f5f9; text-align: left; padding: 6px 10px; border: 1px solid #e2e8f0; color: #475569; font-weight: 600; }
    table.data td { padding: 6px 10px; border: 1px solid #e2e8f0; }
    
    .esito-box { text-align: center; padding: 20px; margin: 15px 0; border: 3px solid ' . $esitoColor . '; border-radius: 10px; }
    .esito-box .indice { font-size: 36pt; font-weight: 800; color: ' . $esitoColor . '; }
    .esito-box .label { font-size: 10pt; color: #64748b; margin-bottom: 5px; }
    .esito-box .esito { font-size: 16pt; font-weight: 700; color: ' . $esitoColor . '; margin-top: 5px; }
    
    .info-grid { width: 100%; }
    .info-grid td { width: 50%; vertical-align: top; padding: 0 5px; }
    
    .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 9pt; color: #94a3b8; text-align: center; }
    
    .note-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 5px; font-size: 10pt; }
    
    .legend { font-size: 9pt; color: #64748b; margin-top: 10px; }
    .legend strong { color: #1e293b; }
</style>
</head>
<body>

<div class="header">
    <span class="cert-id">Certificato N° ' . $cert['idC'] . '<br>Data: ' . date('d/m/Y', strtotime($cert['data_creazione'])) . '</span>
    <h1>DVR</h1>
    <div class="subtitle">Certificato di Valutazione del Rischio — Metodo NIOSH</div>
</div>

<div class="section">
    <h2>Dati Azienda Certificatrice</h2>
    <table class="data">
        <tr><th width="30%">Ragione Sociale</th><td>' . htmlspecialchars($cert['ragione_sociale']) . '</td></tr>
        <tr><th>Partita IVA</th><td>' . htmlspecialchars($cert['p_iva']) . '</td></tr>
        <tr><th>Indirizzo</th><td>' . htmlspecialchars($cert['indirizzo'] ?? '—') . '</td></tr>
        <tr><th>Contatti</th><td>' . htmlspecialchars(($cert['telefono'] ?? '') . ' — ' . ($cert['email'] ?? '')) . '</td></tr>
    </table>
</div>

<div class="section">
    <h2>Dati Soggetto Valutato</h2>
    <table class="data">
        <tr><th width="30%">Nome</th><td>' . htmlspecialchars($cert['nome_cliente']) . '</td></tr>
        <tr><th>Sesso</th><td>' . ($cert['sesso'] === 'M' ? 'Maschio' : 'Femmina') . '</td></tr>
        <tr><th>Data Nascita</th><td>' . date('d/m/Y', strtotime($cert['nascita'])) . '</td></tr>
        <tr><th>Età al momento della valutazione</th><td>' . $eta . ' anni</td></tr>
        <tr><th>Ingegnere Valutatore</th><td>' . htmlspecialchars($cert['nome_ingegnere']) . '</td></tr>
    </table>
</div>

<div class="esito-box">
    <div class="label">INDICE DI SOLLEVAMENTO NIOSH</div>
    <div class="indice">' . $cert['indice_sollevamento'] . '</div>
    <div class="esito">Esito: ' . $esitoLabel . '</div>
</div>

<div class="section">
    <h2>Parametri di Rilevazione</h2>
    <table class="data">
        <tr><th width="40%">Altezza Mani Sollevamento</th><td>' . $cert['altezza_mani_soll'] . ' cm</td></tr>
        <tr><th>Distanza Verticale</th><td>' . $cert['distanza_verticale'] . ' cm</td></tr>
        <tr><th>Distanza Orizzontale</th><td>' . $cert['distanza_orizzontale'] . ' cm</td></tr>
        <tr><th>Dislocazione Angolare</th><td>' . $cert['dislocazione_angolare'] . '°</td></tr>
        <tr><th>Giudizio Presa Carico</th><td>' . ($cert['giudizio_presa_carico'] === 'B' ? 'Buono' : 'Scarso') . '</td></tr>
        <tr><th>Frequenza Gesti/min</th><td>' . $cert['frequenza_gesti'] . '</td></tr>
        <tr><th>Frequenza Lavoro</th><td>' . ($freqLavoroLabels[$cert['frequenza_lavoro']] ?? $cert['frequenza_lavoro']) . '</td></tr>
        <tr><th>Peso Sollevato</th><td>' . $cert['peso_sollevato'] . ' kg</td></tr>
    </table>
</div>

<div class="section">
    <h2>Fattori di Calcolo</h2>
    <table class="data">
        <tr><th width="40%">Peso Massimo Raccomandato (Fattore Età)</th><td>' . $risultato['fattori']['fattoreEta'] . ' kg</td></tr>
        <tr><th>Fattore Altezza (VM)</th><td>' . $risultato['fattori']['fattoreAltezza'] . '</td></tr>
        <tr><th>Fattore Dislocazione Verticale (DM)</th><td>' . $risultato['fattori']['fattoreDiscV'] . '</td></tr>
        <tr><th>Fattore Dislocazione Orizzontale (HM)</th><td>' . $risultato['fattori']['fattoreDiscO'] . '</td></tr>
        <tr><th>Fattore Dislocazione Angolare (AM)</th><td>' . $risultato['fattori']['fattoreAngolare'] . '</td></tr>
        <tr><th>Fattore Presa (CM)</th><td>' . $risultato['fattori']['fattorePresa'] . '</td></tr>
        <tr><th>Fattore Frequenza (FM)</th><td>' . $risultato['fattori']['frequenzaGesti'] . '</td></tr>
        <tr><th>Peso Limite Raccomandato (denominatore)</th><td>' . round($risultato['fattori']['den'], 4) . ' kg</td></tr>
    </table>
</div>';

if ($cert['note']) {
    $html .= '
<div class="section">
    <h2>Note</h2>
    <div class="note-box">' . nl2br(htmlspecialchars($cert['note'])) . '</div>
</div>';
}

$html .= '
<div class="legend">
    <strong>Legenda Esiti:</strong><br>
    <strong>Positivo</strong> (Indice ≤ 0.85): Rischio accettabile.<br>
    <strong>Da Rivedere</strong> (0.85 < Indice ≤ 1.0): Rischio borderline, monitoraggio consigliato.<br>
    <strong>Negativo</strong> (Indice > 1.0): Rischio presente, interventi correttivi necessari.
</div>

<div class="footer">
    Documento generato da DVR — Piattaforma di Certificazione Professionale<br>
    ' . date('d/m/Y H:i') . '
</div>

</body>
</html>';

// Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Certificato_' . $cert['idC'] . '_' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
