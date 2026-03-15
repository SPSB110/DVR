<?php
require_once __DIR__ . '/config.php';

// If logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    $user = get_current_user_data();
    if ($user) {
        $dashboards = [
            'admin' => '/admin/',
            'super' => '/super/',
            'amministrativo' => '/amministrativo/',
            'ingegnere' => '/ingegnere/',
            'cliente' => '/cliente/',
        ];
        header('Location: ' . ($dashboards[$user['ruolo']] ?? '/'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>DVR - Piattaforma di Certificazione</title>
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">DVR</a>
    <div>
        <a href="/login.php" class="btn btn-outline-light btn-sm">Accedi</a>
    </div>
</nav>

<section class="hero">
    <h1>La Certificazione <span>Intelligente</span></h1>
    <p>Piattaforma professionale per la valutazione dei rischi da movimentazione manuale dei carichi secondo il metodo NIOSH.</p>
    <div class="d-flex gap-2 items-center" style="justify-content:center">
        <a href="/login.php" class="btn btn-primary btn-lg">Accedi alla Piattaforma</a>
        <a href="#ruoli" class="btn btn-outline-light btn-lg">Scopri di più</a>
    </div>
</section>

<div class="cards-grid" id="ruoli">
    <div class="card">
        <div class="card-icon blue">&#127970;</div>
        <h3>Sei un'Azienda?</h3>
        <p>Vuoi utilizzare il nostro sistema di certificazione per i tuoi clienti? Contattaci per attivare il tuo account aziendale.</p>
        <a href="/login.php" class="btn btn-primary">Accedi come Amministrativo</a>
    </div>
    <div class="card">
        <div class="card-icon green">&#128736;</div>
        <h3>Sei un Ingegnere?</h3>
        <p>Accedi per consultare i tuoi clienti assegnati, effettuare le visite e compilare le certificazioni.</p>
        <a href="/login.php" class="btn btn-success">Accedi come Ingegnere</a>
    </div>
    <div class="card">
        <div class="card-icon amber">&#128196;</div>
        <h3>Sei un Cliente?</h3>
        <p>Vuoi consultare e scaricare le tue certificazioni? Accedi con le credenziali che ti sono state fornite.</p>
        <a href="/login.php" class="btn btn-warning">Accedi come Cliente</a>
    </div>
</div>

<div class="container text-center" style="padding: 3rem 2rem;">
    <h2 style="font-size:1.5rem;margin-bottom:1rem;color:var(--gray-800)">Come Funziona</h2>
    <div class="cards-grid" style="margin-top:1.5rem;margin-bottom:0">
        <div class="card">
            <div class="card-icon slate">1</div>
            <h3>Registrazione Azienda</h3>
            <p>L'azienda di certificazione viene registrata nel sistema dal Super Utente con tutti i dati necessari.</p>
        </div>
        <div class="card">
            <div class="card-icon slate">2</div>
            <h3>Gestione Clienti</h3>
            <p>L'amministrativo inserisce i clienti e li assegna agli ingegneri per le visite di certificazione.</p>
        </div>
        <div class="card">
            <div class="card-icon slate">3</div>
            <h3>Certificazione</h3>
            <p>L'ingegnere effettua la valutazione NIOSH e produce il certificato con esito positivo, negativo o da rivedere.</p>
        </div>
        <div class="card">
            <div class="card-icon slate">4</div>
            <h3>Consultazione</h3>
            <p>Il cliente può accedere e consultare/scaricare i propri certificati in formato PDF.</p>
        </div>
    </div>
</div>

<footer class="footer">
    &copy; <?= date('Y') ?> DVR — Piattaforma di Certificazione Professionale
</footer>

</body>
</html>
