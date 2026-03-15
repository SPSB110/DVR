<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['ingegnere']);
$page_title = 'Clienti Assegnati';
$idU = $user['idU'];
$idA = $user['idA'];

// Get assigned clients with certificate info
$clienti = $pdo->prepare("
    SELECT u.idU, u.nome, u.sesso, u.nascita, 
           ass.data_assegnazione,
           (SELECT COUNT(*) FROM certificati c WHERE c.idCliente = u.idU AND c.idI = ?) AS tot_cert,
           (SELECT c2.esito FROM certificati c2 WHERE c2.idCliente = u.idU AND c2.idI = ? ORDER BY c2.data_creazione DESC LIMIT 1) AS ultimo_esito
    FROM assegnazioni ass
    JOIN utenti u ON ass.idCliente = u.idU
    WHERE ass.idIngegnere = ?
    ORDER BY ass.data_assegnazione DESC
");
$clienti->execute([$idU, $idU, $idU]);
$clienti = $clienti->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Clienti Assegnati</h1>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Sesso</th>
                    <th>Data Nascita</th>
                    <th>Assegnato il</th>
                    <th>Certificati</th>
                    <th>Ultimo Esito</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clienti as $c): ?>
                <tr>
                    <td><strong><?= h($c['nome']) ?></strong></td>
                    <td><?= $c['sesso'] === 'M' ? 'Maschio' : 'Femmina' ?></td>
                    <td><?= $c['nascita'] ? date('d/m/Y', strtotime($c['nascita'])) : '—' ?></td>
                    <td><?= date('d/m/Y', strtotime($c['data_assegnazione'])) ?></td>
                    <td><strong><?= $c['tot_cert'] ?></strong></td>
                    <td><?= $c['ultimo_esito'] ? format_esito($c['ultimo_esito']) : '<span style="color:var(--gray-400)">Nessuno</span>' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/ingegnere/certificato.php?idCliente=<?= $c['idU'] ?>" class="btn btn-sm btn-primary">Nuovo Certificato</a>
                            <a href="/ingegnere/storico.php?idCliente=<?= $c['idU'] ?>" class="btn btn-sm btn-secondary">Storico</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clienti)): ?>
                <tr><td colspan="7" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessun cliente assegnato. Contatta l'amministrativo della tua azienda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
