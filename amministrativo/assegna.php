<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['amministrativo']);
$page_title = 'Assegnazioni';
$idA = $user['idA'];

// Handle new assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $idIngegnere = (int)$_POST['idIngegnere'];
    $idCliente = (int)$_POST['idCliente'];

    // Verify both belong to same company
    $stmtIng = $pdo->prepare("SELECT idU FROM utenti WHERE idU = ? AND idA = ? AND ruolo = 'ingegnere'");
    $stmtIng->execute([$idIngegnere, $idA]);
    $stmtCli = $pdo->prepare("SELECT idU FROM utenti WHERE idU = ? AND idA = ? AND ruolo = 'cliente'");
    $stmtCli->execute([$idCliente, $idA]);

    if ($stmtIng->fetch() && $stmtCli->fetch()) {
        try {
            $pdo->prepare("INSERT INTO assegnazioni (idIngegnere, idCliente, idA) VALUES (?, ?, ?)")
                ->execute([$idIngegnere, $idCliente, $idA]);
            set_flash('success', 'Assegnazione creata con successo.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                set_flash('error', 'Questa assegnazione esiste già.');
            } else {
                throw $e;
            }
        }
    } else {
        set_flash('error', 'Ingegnere o cliente non valido.');
    }
    header('Location: /amministrativo/assegna.php');
    exit;
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM assegnazioni WHERE id = ? AND idA = ?")->execute([$_GET['delete'], $idA]);
    set_flash('success', 'Assegnazione rimossa.');
    header('Location: /amministrativo/assegna.php');
    exit;
}

// Get engineers and clients of this company
$ingegneri = $pdo->prepare("SELECT idU, nome FROM utenti WHERE ruolo = 'ingegnere' AND idA = ? AND attivo = 1 ORDER BY nome");
$ingegneri->execute([$idA]);
$ingegneri = $ingegneri->fetchAll();

$clienti = $pdo->prepare("SELECT idU, nome FROM utenti WHERE ruolo = 'cliente' AND idA = ? AND attivo = 1 ORDER BY nome");
$clienti->execute([$idA]);
$clienti = $clienti->fetchAll();

// Current assignments
$assegnazioni = $pdo->prepare("
    SELECT ass.id, ass.data_assegnazione,
           ing.nome AS nome_ingegnere, ing.idU AS idIng,
           cli.nome AS nome_cliente, cli.idU AS idCli
    FROM assegnazioni ass
    JOIN utenti ing ON ass.idIngegnere = ing.idU
    JOIN utenti cli ON ass.idCliente = cli.idU
    WHERE ass.idA = ?
    ORDER BY ass.data_assegnazione DESC
");
$assegnazioni->execute([$idA]);
$assegnazioni = $assegnazioni->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Assegnazioni Ingegnere — Cliente</h1>
    </div>

    <div class="form-card mb-3">
        <h3 class="mb-2">Nuova Assegnazione</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Ingegnere *</label>
                    <select name="idIngegnere" required>
                        <option value="">-- Seleziona Ingegnere --</option>
                        <?php foreach ($ingegneri as $ing): ?>
                        <option value="<?= $ing['idU'] ?>"><?= h($ing['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cliente *</label>
                    <select name="idCliente" required>
                        <option value="">-- Seleziona Cliente --</option>
                        <?php foreach ($clienti as $cli): ?>
                        <option value="<?= $cli['idU'] ?>"><?= h($cli['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Assegna</button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ingegnere</th>
                    <th>Cliente</th>
                    <th>Data Assegnazione</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assegnazioni as $ass): ?>
                <tr>
                    <td><strong><?= h($ass['nome_ingegnere']) ?></strong></td>
                    <td><?= h($ass['nome_cliente']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($ass['data_assegnazione'])) ?></td>
                    <td>
                        <a href="/amministrativo/assegna.php?delete=<?= $ass['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Rimuovere questa assegnazione?')">Rimuovi</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($assegnazioni)): ?>
                <tr><td colspan="4" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessuna assegnazione presente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
