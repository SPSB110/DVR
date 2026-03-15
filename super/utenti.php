<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['super']);
$page_title = 'Gestione Utenti';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    // Super can delete amministrativo, ingegnere, cliente
    $stmt = $pdo->prepare("SELECT ruolo FROM utenti WHERE idU = ?");
    $stmt->execute([$idToDelete]);
    $target = $stmt->fetch();
    if ($target && in_array($target['ruolo'], ['amministrativo', 'ingegnere', 'cliente'])) {
        $pdo->prepare("DELETE FROM utenti WHERE idU = ?")->execute([$idToDelete]);
        set_flash('success', 'Utente eliminato.');
    } else {
        set_flash('error', 'Non puoi eliminare questo utente.');
    }
    header('Location: /super/utenti.php');
    exit;
}

// Handle create/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $idU = !empty($_POST['idU']) ? (int)$_POST['idU'] : null;
    $login = trim($_POST['login']);
    $nome = trim($_POST['nome']);
    $ruolo = $_POST['ruolo'];
    $sesso = $_POST['sesso'] ?? null;
    $nascita = !empty($_POST['nascita']) ? $_POST['nascita'] : null;
    $idA = !empty($_POST['idA']) ? (int)$_POST['idA'] : null;
    $attivo = isset($_POST['attivo']) ? 1 : 0;

    // Validate role - super can only create these roles
    if (!in_array($ruolo, ['amministrativo', 'ingegnere', 'cliente'])) {
        set_flash('error', 'Ruolo non valido.');
        header('Location: /super/utenti.php');
        exit;
    }
    
    // Check unique login
    $checkStmt = $pdo->prepare("SELECT idU FROM utenti WHERE login = ? AND idU != ?");
    $checkStmt->execute([$login, $idU ?? 0]);
    if ($checkStmt->fetch()) {
        set_flash('error', 'Login già esistente.');
        header('Location: /super/utenti.php' . ($idU ? "?edit=$idU" : '?new=1'));
        exit;
    }

    if ($idU) {
        $sql = "UPDATE utenti SET login=?, nome=?, ruolo=?, sesso=?, nascita=?, idA=?, attivo=? WHERE idU=?";
        $pdo->prepare($sql)->execute([$login, $nome, $ruolo, $sesso, $nascita, $idA, $attivo, $idU]);
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE utenti SET password=? WHERE idU=?")->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $idU]);
        }
        set_flash('success', 'Utente aggiornato.');
    } else {
        $password = !empty($_POST['password']) ? $_POST['password'] : generate_password();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO utenti (login, password, nome, ruolo, sesso, nascita, idA, attivo) VALUES (?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$login, $hash, $nome, $ruolo, $sesso, $nascita, $idA, $attivo]);
        set_flash('success', "Utente creato. Password: $password");
    }
    header('Location: /super/utenti.php');
    exit;
}

// Filters
$filterRuolo = $_GET['ruolo'] ?? '';
$filterAzienda = $_GET['azienda'] ?? '';
$where = "WHERE u.ruolo IN ('amministrativo','ingegnere','cliente')";
$params = [];
if ($filterRuolo) {
    $where .= " AND u.ruolo = ?";
    $params[] = $filterRuolo;
}
if ($filterAzienda) {
    $where .= " AND u.idA = ?";
    $params[] = $filterAzienda;
}

$utenti = $pdo->prepare("SELECT u.*, a.ragione_sociale FROM utenti u LEFT JOIN azienda a ON u.idA = a.idA $where ORDER BY u.idU DESC");
$utenti->execute($params);
$utenti = $utenti->fetchAll();

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE idU = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

$aziende = $pdo->query("SELECT idA, ragione_sociale FROM azienda WHERE valida = 1 ORDER BY ragione_sociale")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestione Utenti</h1>
        <a href="/super/utenti.php?new=1" class="btn btn-primary">+ Nuovo Utente</a>
    </div>

    <div class="d-flex gap-1 mb-2 flex-wrap">
        <a href="/super/utenti.php" class="btn btn-sm <?= !$filterRuolo ? 'btn-primary' : 'btn-secondary' ?>">Tutti</a>
        <?php foreach (['amministrativo','ingegnere','cliente'] as $r): ?>
        <a href="/super/utenti.php?ruolo=<?= $r ?>" 
           class="btn btn-sm <?= $filterRuolo === $r ? 'btn-primary' : 'btn-secondary' ?>"><?= format_ruolo($r) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_GET['new']) || $editUser): ?>
    <div class="form-card mb-3">
        <h3 class="mb-2"><?= $editUser ? 'Modifica Utente' : 'Nuovo Utente' ?></h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php if ($editUser): ?>
                <input type="hidden" name="idU" value="<?= $editUser['idU'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Login *</label>
                    <input type="text" name="login" required value="<?= h($editUser['login'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password <?= $editUser ? '(vuoto = invariata)' : '*' ?></label>
                    <input type="password" name="password" <?= $editUser ? '' : 'required' ?>>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" required value="<?= h($editUser['nome'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Ruolo *</label>
                    <select name="ruolo" required>
                        <option value="">-- Seleziona --</option>
                        <?php foreach (['amministrativo','ingegnere','cliente'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($editUser['ruolo'] ?? '') === $r ? 'selected' : '' ?>><?= format_ruolo($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Sesso</label>
                    <div class="radio-group">
                        <label><input type="radio" name="sesso" value="M" <?= ($editUser['sesso'] ?? '') === 'M' ? 'checked' : '' ?>> Maschio</label>
                        <label><input type="radio" name="sesso" value="F" <?= ($editUser['sesso'] ?? '') === 'F' ? 'checked' : '' ?>> Femmina</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Data di Nascita</label>
                    <input type="date" name="nascita" value="<?= h($editUser['nascita'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Azienda *</label>
                    <select name="idA" required>
                        <option value="">-- Seleziona --</option>
                        <?php foreach ($aziende as $az): ?>
                        <option value="<?= $az['idA'] ?>" <?= ($editUser['idA'] ?? '') == $az['idA'] ? 'selected' : '' ?>><?= h($az['ragione_sociale']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <label style="font-weight:400"><input type="checkbox" name="attivo" value="1" <?= ($editUser['attivo'] ?? 1) ? 'checked' : '' ?>> Attivo</label>
                </div>
            </div>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary"><?= $editUser ? 'Salva' : 'Crea Utente' ?></button>
                <a href="/super/utenti.php" class="btn btn-secondary">Annulla</a>
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
                    <th>Ruolo</th>
                    <th>Azienda</th>
                    <th>Stato</th>
                    <th>Credenziali</th>
                    <th>Ultimo Accesso</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $u): ?>
                <tr>
                    <td><?= $u['idU'] ?></td>
                    <td><strong><?= h($u['login']) ?></strong></td>
                    <td><?= h($u['nome']) ?></td>
                    <td><span class="badge badge-info"><?= h(format_ruolo($u['ruolo'])) ?></span></td>
                    <td><?= h($u['ragione_sociale'] ?? '—') ?></td>
                    <td><?= $u['attivo'] ? '<span class="badge badge-success">Attivo</span>' : '<span class="badge badge-danger">Disattivato</span>' ?></td>
                    <td><?= $u['credenziali_inviate'] ? '<span class="badge badge-success">Inviate</span>' : '<span class="badge badge-secondary">Non inviate</span>' ?></td>
                    <td><?= $u['ultimo_accesso'] ? date('d/m/Y H:i', strtotime($u['ultimo_accesso'])) : '<span style="color:var(--gray-400)">Mai</span>' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/super/utenti.php?edit=<?= $u['idU'] ?>" class="btn btn-sm btn-secondary">Modifica</a>
                            <a href="/super/utenti.php?delete=<?= $u['idU'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Eliminare questo utente?')">Elimina</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($utenti)): ?>
                <tr><td colspan="9" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessun utente trovato.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
