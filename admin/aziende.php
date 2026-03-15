<?php
require_once __DIR__ . '/../config.php';
$user = require_role(['admin']);
$page_title = 'Gestione Aziende';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("UPDATE azienda SET valida = 0 WHERE idA = ?")->execute([$_GET['delete']]);
    set_flash('success', 'Azienda disattivata con successo.');
    header('Location: /admin/aziende.php');
    exit;
}

// Handle create/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $idA = !empty($_POST['idA']) ? (int)$_POST['idA'] : null;
    $ragione_sociale = trim($_POST['ragione_sociale']);
    $p_iva = trim($_POST['p_iva']);
    $indirizzo = trim($_POST['indirizzo']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $nome_riferimento = trim($_POST['nome_riferimento_interno']);
    $valida = isset($_POST['valida']) ? 1 : 0;

    if ($idA) {
        $sql = "UPDATE azienda SET ragione_sociale=?, p_iva=?, indirizzo=?, telefono=?, email=?, nome_riferimento_interno=?, valida=? WHERE idA=?";
        $pdo->prepare($sql)->execute([$ragione_sociale, $p_iva, $indirizzo, $telefono, $email, $nome_riferimento, $valida, $idA]);
        set_flash('success', 'Azienda aggiornata.');
    } else {
        $sql = "INSERT INTO azienda (ragione_sociale, p_iva, indirizzo, telefono, email, nome_riferimento_interno, valida) VALUES (?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$ragione_sociale, $p_iva, $indirizzo, $telefono, $email, $nome_riferimento, $valida]);
        set_flash('success', 'Azienda creata con successo.');
    }
    header('Location: /admin/aziende.php');
    exit;
}

$aziende = $pdo->query("SELECT * FROM azienda ORDER BY idA DESC")->fetchAll();

$editAzienda = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM azienda WHERE idA = ?");
    $stmt->execute([$_GET['edit']]);
    $editAzienda = $stmt->fetch();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestione Aziende</h1>
        <a href="/admin/aziende.php?new=1" class="btn btn-primary">+ Nuova Azienda</a>
    </div>

    <?php if (isset($_GET['new']) || $editAzienda): ?>
    <div class="form-card mb-3">
        <h3 class="mb-2"><?= $editAzienda ? 'Modifica Azienda' : 'Nuova Azienda' ?></h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php if ($editAzienda): ?>
                <input type="hidden" name="idA" value="<?= $editAzienda['idA'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Ragione Sociale *</label>
                    <input type="text" name="ragione_sociale" required value="<?= h($editAzienda['ragione_sociale'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Partita IVA *</label>
                    <input type="text" name="p_iva" required value="<?= h($editAzienda['p_iva'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Indirizzo</label>
                <input type="text" name="indirizzo" value="<?= h($editAzienda['indirizzo'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" value="<?= h($editAzienda['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= h($editAzienda['email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nome Riferimento Interno</label>
                    <input type="text" name="nome_riferimento_interno" value="<?= h($editAzienda['nome_riferimento_interno'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <label style="font-weight:400"><input type="checkbox" name="valida" value="1" <?= ($editAzienda['valida'] ?? 1) ? 'checked' : '' ?>> Azienda Valida/Attiva</label>
                </div>
            </div>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary"><?= $editAzienda ? 'Salva' : 'Crea Azienda' ?></button>
                <a href="/admin/aziende.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ragione Sociale</th>
                    <th>P.IVA</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aziende as $az): ?>
                <tr>
                    <td><?= $az['idA'] ?></td>
                    <td><strong><?= h($az['ragione_sociale']) ?></strong></td>
                    <td><?= h($az['p_iva']) ?></td>
                    <td><?= h($az['email'] ?? '—') ?></td>
                    <td><?= h($az['telefono'] ?? '—') ?></td>
                    <td><?= $az['valida'] ? '<span class="badge badge-success">Attiva</span>' : '<span class="badge badge-danger">Disattivata</span>' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/aziende.php?edit=<?= $az['idA'] ?>" class="btn btn-sm btn-secondary">Modifica</a>
                            <?php if ($az['valida']): ?>
                            <a href="/admin/aziende.php?delete=<?= $az['idA'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Disattivare questa azienda?')">Disattiva</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($aziende)): ?>
                <tr><td colspan="7" class="text-center" style="padding:2rem;color:var(--gray-400)">Nessuna azienda registrata.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
