<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['amministrativo']);
$page_title = 'Gestione Clienti';
$idA = $user['idA'];

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM utenti WHERE idU = ? AND idA = ? AND ruolo = 'cliente'");
    $stmt->execute([$_GET['delete'], $idA]);
    set_flash('success', 'Cliente eliminato.');
    header('Location: /amministrativo/clienti.php');
    exit;
}

// Handle send credentials
if (isset($_GET['send_cred']) && is_numeric($_GET['send_cred'])) {
    $pdo->prepare("UPDATE utenti SET credenziali_inviate = 1 WHERE idU = ? AND idA = ?")->execute([$_GET['send_cred'], $idA]);
    set_flash('success', 'Credenziali segnate come inviate. (In produzione verrebbe inviata un\'email)');
    header('Location: /amministrativo/clienti.php');
    exit;
}

// Handle create/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $idU = !empty($_POST['idU']) ? (int)$_POST['idU'] : null;
    $login = trim($_POST['login']);
    $nome = trim($_POST['nome']);
    $sesso = $_POST['sesso'] ?? null;
    $nascita = !empty($_POST['nascita']) ? $_POST['nascita'] : null;
    $attivo = isset($_POST['attivo']) ? 1 : 0;

    // Check unique login
    $checkStmt = $pdo->prepare("SELECT idU FROM utenti WHERE login = ? AND idU != ?");
    $checkStmt->execute([$login, $idU ?? 0]);
    if ($checkStmt->fetch()) {
        set_flash('error', 'Login già esistente.');
        header('Location: /amministrativo/clienti.php' . ($idU ? "?edit=$idU" : '?new=1'));
        exit;
    }

    if ($idU) {
        $sql = "UPDATE utenti SET login=?, nome=?, sesso=?, nascita=?, attivo=? WHERE idU=? AND idA=? AND ruolo='cliente'";
        $pdo->prepare($sql)->execute([$login, $nome, $sesso, $nascita, $attivo, $idU, $idA]);
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE utenti SET password=? WHERE idU=? AND idA=?")->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $idU, $idA]);
        }
        set_flash('success', 'Cliente aggiornato.');
    } else {
        $password = !empty($_POST['password']) ? $_POST['password'] : generate_password();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO utenti (login, password, nome, ruolo, sesso, nascita, idA, attivo) VALUES (?,?,?,'cliente',?,?,?,?)";
        $pdo->prepare($sql)->execute([$login, $hash, $nome, $sesso, $nascita, $idA, $attivo]);
        set_flash('success', "Cliente creato. Password: $password — Ricordati di inviarla al cliente!");
    }
    header('Location: /amministrativo/clienti.php');
    exit;
}

// List clients of this company
$clienti = $pdo->prepare("SELECT * FROM utenti WHERE ruolo = 'cliente' AND idA = ? ORDER BY idU DESC");
$clienti->execute([$idA]);
$clienti = $clienti->fetchAll();

$editClient = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE idU = ? AND idA = ? AND ruolo = 'cliente'");
    $stmt->execute([$_GET['edit'], $idA]);
    $editClient = $stmt->fetch();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestione Clienti</h1>
        <a href="/amministrativo/clienti.php?new=1" class="btn btn-primary">+ Nuovo Cliente</a>
    </div>

    <?php if (isset($_GET['new']) || $editClient): ?>
    <div class="form-card mb-3">
        <h3 class="mb-2"><?= $editClient ? 'Modifica Cliente' : 'Nuovo Cliente' ?></h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php if ($editClient): ?>
                <input type="hidden" name="idU" value="<?= $editClient['idU'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Login (Email) *</label>
                    <input type="text" name="login" required value="<?= h($editClient['login'] ?? '') ?>" placeholder="email@esempio.it">
                </div>
                <div class="form-group">
                    <label>Password <?= $editClient ? '(vuoto = invariata)' : '' ?></label>
                    <input type="password" name="password" placeholder="<?= $editClient ? 'Lascia vuoto per mantenere' : 'Generata automaticamente se vuoto' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" required value="<?= h($editClient['nome'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Sesso *</label>
                    <div class="radio-group">
                        <label><input type="radio" name="sesso" value="M" required <?= ($editClient['sesso'] ?? '') === 'M' ? 'checked' : '' ?>> Maschio</label>
                        <label><input type="radio" name="sesso" value="F" <?= ($editClient['sesso'] ?? '') === 'F' ? 'checked' : '' ?>> Femmina</label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Data di Nascita *</label>
                    <input type="date" name="nascita" required value="<?= h($editClient['nascita'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <label style="font-weight:400"><input type="checkbox" name="attivo" value="1" <?= ($editClient['attivo'] ?? 1) ? 'checked' : '' ?>> Attivo</label>
                </div>
            </div>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary"><?= $editClient ? 'Salva' : 'Crea Cliente' ?></button>
                <a href="/amministrativo/clienti.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Nome</th>
                    <th>Sesso</th>
                    <th>Nascita</th>
                    <th>Credenziali</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clienti as $c): ?>
                <tr>
                    <td><?= $c['idU'] ?></td>
                    <td><strong><?= h($c['login']) ?></strong></td>
                    <td><?= h($c['nome']) ?></td>
                    <td><?= $c['sesso'] === 'M' ? 'Maschio' : 'Femmina' ?></td>
                    <td><?= $c['nascita'] ? date('d/m/Y', strtotime($c['nascita'])) : '—' ?></td>
                    <td>
                        <?php if ($c['credenziali_inviate']): ?>
                            <span class="badge badge-success">Inviate</span>
                        <?php else: ?>
                            <a href="/amministrativo/clienti.php?send_cred=<?= $c['idU'] ?>" class="btn btn-sm btn-warning"
                               onclick="return confirm('Segnare le credenziali come inviate?')">Invia Credenziali</a>
                        <?php endif; ?>
                    </td>
                    <td><?= $c['attivo'] ? '<span class="badge badge-success">Attivo</span>' : '<span class="badge badge-danger">Disattivato</span>' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/amministrativo/clienti.php?edit=<?= $c['idU'] ?>" class="btn btn-sm btn-secondary">Modifica</a>
                            <a href="/amministrativo/clienti.php?delete=<?= $c['idU'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Eliminare questo cliente?')">Elimina</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clienti)): ?>
                <tr><td colspan="8" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessun cliente registrato.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
