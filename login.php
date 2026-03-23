<?php
require_once __DIR__ . '/config.php';

function redirect_to_dashboard($role) {
    $dashboards = [
        'admin' => '/admin/',
        'super' => '/super/',
        'amministrativo' => '/amministrativo/',
        'ingegnere' => '/ingegnere/',
        'cliente' => '/cliente/',
    ];

    header('Location: ' . ($dashboards[$role] ?? '/'));
    exit;
}

if (is_logged_in()) {
    $currentUser = get_current_user_data();
    if ($currentUser) {
        redirect_to_dashboard($currentUser['ruolo']);
    }

    session_unset();
    session_regenerate_id(true);
}

$error = null;
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Inserisci login e password.';
    } else {
        $stmt = $pdo->prepare('SELECT idU, password, nome, ruolo, attivo, primo_accesso FROM utenti WHERE login = ? LIMIT 1');
        $stmt->execute([$loginValue]);
        $utente = $stmt->fetch();

        $passwordOk = $utente && password_verify($password, $utente['password']);

        if ($passwordOk && (int)$utente['attivo'] === 1) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$utente['idU'];
            $_SESSION['user_role'] = $utente['ruolo'];

            $updateStmt = $pdo->prepare('UPDATE utenti SET ultimo_accesso = NOW(), primo_accesso = COALESCE(primo_accesso, NOW()) WHERE idU = ?');
            $updateStmt->execute([$utente['idU']]);

            set_flash('success', 'Accesso effettuato. Benvenuto, ' . $utente['nome'] . '.');
            redirect_to_dashboard($utente['ruolo']);
        }

        usleep(300000);
        $error = 'Credenziali non valide o utente disattivato.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Login - DVR</title>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">DVR <span>AI</span></div>
        <h1>Accedi alla piattaforma</h1>
        <p class="subtitle">Inserisci le credenziali fornite dal tuo amministratore.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" value="<?= h($loginValue) ?>" autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Accedi</button>
        </form>

        <div class="text-center mt-2">
            <a href="/" class="btn btn-secondary btn-sm">Torna alla home</a>
        </div>
    </div>
</div>
</body>
</html>
