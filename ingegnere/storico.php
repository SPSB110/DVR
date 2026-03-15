<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['ingegnere']);
$page_title = 'Storico Certificati';
$idU = $user['idU'];

$idCliente = (int)($_GET['idCliente'] ?? 0);

// Verify assignment
$stmtCheck = $pdo->prepare("SELECT u.nome FROM assegnazioni a JOIN utenti u ON a.idCliente = u.idU WHERE a.idIngegnere = ? AND a.idCliente = ?");
$stmtCheck->execute([$idU, $idCliente]);
$cliente = $stmtCheck->fetch();

if (!$cliente) {
    set_flash('error', 'Cliente non trovato.');
    header('Location: /ingegnere/clienti.php');
    exit;
}

$certificati = $pdo->prepare("
    SELECT c.* FROM certificati c 
    WHERE c.idCliente = ? AND c.idI = ?
    ORDER BY c.data_creazione DESC
");
$certificati->execute([$idCliente, $idU]);
$certificati = $certificati->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Storico Certificati — <?= h($cliente['nome']) ?></h1>
        <div class="d-flex gap-1">
            <a href="/ingegnere/certificato.php?idCliente=<?= $idCliente ?>" class="btn btn-primary">+ Nuovo Certificato</a>
            <a href="/ingegnere/clienti.php" class="btn btn-secondary">← Clienti</a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Indice Soll.</th>
                    <th>Esito</th>
                    <th>Peso (kg)</th>
                    <th>Note</th>
                    <th>Visualizzato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificati as $cert): ?>
                <tr>
                    <td>#<?= $cert['idC'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($cert['data_creazione'])) ?></td>
                    <td><strong><?= $cert['indice_sollevamento'] ?></strong></td>
                    <td><?= format_esito($cert['esito']) ?></td>
                    <td><?= $cert['peso_sollevato'] ?></td>
                    <td><?= h(mb_substr($cert['note'] ?? '', 0, 50)) ?><?= mb_strlen($cert['note'] ?? '') > 50 ? '...' : '' ?></td>
                    <td><?= $cert['visualizzato_cliente'] ? '<span class="badge badge-success">Sì</span>' : '<span class="badge badge-secondary">No</span>' ?></td>
                    <td>
                        <a href="/ingegnere/dettaglio.php?idC=<?= $cert['idC'] ?>" class="btn btn-sm btn-secondary">Dettaglio</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($certificati)): ?>
                <tr><td colspan="8" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessun certificato per questo cliente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
