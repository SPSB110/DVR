<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['amministrativo']);
$page_title = 'Stato Clienti';
$idA = $user['idA'];

// Get all clients with their status
$clienti = $pdo->prepare("
    SELECT u.idU, u.login, u.nome, u.credenziali_inviate, u.primo_accesso, u.ultimo_accesso, u.attivo,
           (SELECT COUNT(*) FROM certificati c WHERE c.idCliente = u.idU) AS tot_certificati,
           (SELECT COUNT(*) FROM certificati c WHERE c.idCliente = u.idU AND c.visualizzato_cliente = 1) AS cert_visualizzati,
           (SELECT COUNT(*) FROM assegnazioni a WHERE a.idCliente = u.idU) AS assegnato
    FROM utenti u
    WHERE u.ruolo = 'cliente' AND u.idA = ?
    ORDER BY u.nome
");
$clienti->execute([$idA]);
$clienti = $clienti->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Stato Clienti</h1>
    </div>

    <div class="alert alert-info">
        Monitora lo stato di ogni cliente: se le credenziali sono state inviate, se ha effettuato accessi e se ha visualizzato i propri certificati.
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Login</th>
                    <th>Credenziali</th>
                    <th>Primo Accesso</th>
                    <th>Ultimo Accesso</th>
                    <th>Assegnato</th>
                    <th>Certificati</th>
                    <th>Visualizzati</th>
                    <th>Stato</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clienti as $c): ?>
                <tr>
                    <td><strong><?= h($c['nome']) ?></strong></td>
                    <td><?= h($c['login']) ?></td>
                    <td>
                        <?php if ($c['credenziali_inviate']): ?>
                            <span class="badge badge-success">Inviate</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Non inviate</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c['primo_accesso']): ?>
                            <span class="badge badge-success"><?= date('d/m/Y', strtotime($c['primo_accesso'])) ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Mai</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $c['ultimo_accesso'] ? date('d/m/Y H:i', strtotime($c['ultimo_accesso'])) : '<span style="color:var(--gray-400)">—</span>' ?>
                    </td>
                    <td>
                        <?= $c['assegnato'] > 0 ? '<span class="badge badge-success">Sì</span>' : '<span class="badge badge-warning">No</span>' ?>
                    </td>
                    <td><strong><?= $c['tot_certificati'] ?></strong></td>
                    <td>
                        <?php if ($c['tot_certificati'] > 0): ?>
                            <?= $c['cert_visualizzati'] ?>/<?= $c['tot_certificati'] ?>
                            <?= $c['cert_visualizzati'] == $c['tot_certificati'] ? '<span class="badge badge-success">Tutti</span>' : '' ?>
                        <?php else: ?>
                            <span style="color:var(--gray-400)">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $c['attivo'] ? '<span class="badge badge-success">Attivo</span>' : '<span class="badge badge-danger">Disattivato</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clienti)): ?>
                <tr><td colspan="9" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessun cliente registrato.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
