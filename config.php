<?php
session_start();

// ============================================================
// Database Configuration
// ============================================================
$host    = 'localhost';
$db      = 'applicativo_sicurezza';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Errore di connessione al database: ' . $e->getMessage());
}

// ============================================================
// Helper Functions
// ============================================================

/**
 * Log calculation steps to file
 */
function log_calc($label, $value) {
    $logFile = __DIR__ . '/calcoli.log';
    $line = date('Y-m-d H:i:s') . ' [CALC] ' . $label . ': ' . json_encode($value) . PHP_EOL;
    error_log($line, 3, $logFile);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user data
 */
function get_current_user_data() {
    global $pdo;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT u.*, a.ragione_sociale FROM utenti u LEFT JOIN azienda a ON u.idA = a.idA WHERE u.idU = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Require login - redirect if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Require specific role(s)
 */
function require_role($roles) {
    require_login();
    if (!is_array($roles)) $roles = [$roles];
    $user = get_current_user_data();
    if (!$user || !in_array($user['ruolo'], $roles)) {
        http_response_code(403);
        include __DIR__ . '/includes/403.php';
        exit;
    }
    return $user;
}

/**
 * Check if current user has a specific role
 */
function has_role($role) {
    $user = get_current_user_data();
    return $user && $user['ruolo'] === $role;
}

/**
 * Sanitize output for HTML
 */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Set a flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF non valido.');
    }
}

/**
 * Generate a random password
 */
function generate_password($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * NIOSH Lifting Index calculation
 */
function calcola_indice_sollevamento($sesso, $eta, $altezzaManiSoll, $altezzaManiVert, $altezzaManiOr, $dislocazioneAngolare, $giudizioPresa, $frequenzaGesti_in, $frequenzaLavoro, $pesoSollevato) {
    
    // Fattore Eta (peso massimo raccomandato)
    if ($eta > 18 && $sesso == "M") {
        $fattoreEta = 30;
    } elseif ($eta > 18 && $sesso == "F") {
        $fattoreEta = 20;
    } elseif ($eta <= 18 && $eta > 15 && $sesso == "M") {
        $fattoreEta = 20;
    } else {
        $fattoreEta = 15;
    }

    // Fattore Altezza
    if ($altezzaManiSoll < 25) {
        $fattoreAltezza = 0.78;
    } elseif ($altezzaManiSoll < 50) {
        $fattoreAltezza = 0.85;
    } elseif ($altezzaManiSoll < 75) {
        $fattoreAltezza = 0.93;
    } elseif ($altezzaManiSoll < 100) {
        $fattoreAltezza = 1;
    } elseif ($altezzaManiSoll < 125) {
        $fattoreAltezza = 0.93;
    } elseif ($altezzaManiSoll < 150) {
        $fattoreAltezza = 0.85;
    } elseif ($altezzaManiSoll < 175) {
        $fattoreAltezza = 0.78;
    } else {
        $fattoreAltezza = 0;
    }

    // Fattore Dislocazione Verticale
    if ($altezzaManiVert < 30) {
        $fattoreDiscV = 1;
    } elseif ($altezzaManiVert < 40) {
        $fattoreDiscV = 0.97;
    } elseif ($altezzaManiVert < 50) {
        $fattoreDiscV = 0.93;
    } elseif ($altezzaManiVert < 70) {
        $fattoreDiscV = 0.91;
    } elseif ($altezzaManiVert < 100) {
        $fattoreDiscV = 0.88;
    } elseif ($altezzaManiVert < 170) {
        $fattoreDiscV = 0.87;
    } elseif ($altezzaManiVert < 175) {
        $fattoreDiscV = 0.86;
    } else {
        $fattoreDiscV = 0;
    }

    // Fattore Dislocazione Orizzontale
    if ($altezzaManiOr < 30) {
        $fattoreDiscO = 1;
    } elseif ($altezzaManiOr < 40) {
        $fattoreDiscO = 0.83;
    } elseif ($altezzaManiOr < 50) {
        $fattoreDiscO = 0.63;
    } elseif ($altezzaManiOr < 55) {
        $fattoreDiscO = 0.50;
    } elseif ($altezzaManiOr < 60) {
        $fattoreDiscO = 0.45;
    } elseif ($altezzaManiOr < 63) {
        $fattoreDiscO = 0.42;
    } else {
        $fattoreDiscO = 0;
    }

    // Fattore Dislocazione Angolare
    if ($dislocazioneAngolare < 30) {
        $fattoreAngolare = 1;
    } elseif ($dislocazioneAngolare < 60) {
        $fattoreAngolare = 0.90;
    } elseif ($dislocazioneAngolare < 90) {
        $fattoreAngolare = 0.81;
    } elseif ($dislocazioneAngolare < 120) {
        $fattoreAngolare = 0.71;
    } elseif ($dislocazioneAngolare < 135) {
        $fattoreAngolare = 0.62;
    } elseif ($dislocazioneAngolare == 135) {
        $fattoreAngolare = 0.57;
    } else {
        $fattoreAngolare = 0;
    }

    // Giudizio Presa
    $fattorePresa = ($giudizioPresa == "B") ? 1 : 0.90;

    // Frequenza Gesti
    $freqTable = [
        [1, 0.94, 0.84, 0.75, 0.52, 0.37],     // FrequenzaLavoro 1
        [0.95, 0.88, 0.72, 0.50, 0.30, 0.21],   // FrequenzaLavoro 2
        [0.85, 0.75, 0.45, 0.27, 0.15, 0.00],   // FrequenzaLavoro 3
    ];
    
    $freqIdx = min((int)$frequenzaLavoro - 1, 2);
    if ($frequenzaGesti_in < 1) $col = 0;
    elseif ($frequenzaGesti_in < 4) $col = 1;
    elseif ($frequenzaGesti_in < 6) $col = 2;
    elseif ($frequenzaGesti_in < 9) $col = 3;
    elseif ($frequenzaGesti_in < 12) $col = 4;
    else $col = 5;
    
    $frequenzaGesti = $freqTable[$freqIdx][$col] ?? 0;

    // Calcolo denominatore
    $den = $fattoreEta * $fattoreAltezza * $fattoreDiscV * $fattoreDiscO * $fattoreAngolare * $fattorePresa * $frequenzaGesti;

    if ($den <= 0 || !is_numeric($pesoSollevato)) {
        return [
            'indice' => 0,
            'fattori' => compact('fattoreEta', 'fattoreAltezza', 'fattoreDiscV', 'fattoreDiscO', 'fattoreAngolare', 'fattorePresa', 'frequenzaGesti', 'den')
        ];
    }

    $indice = $pesoSollevato / $den;

    return [
        'indice' => round($indice, 4),
        'fattori' => compact('fattoreEta', 'fattoreAltezza', 'fattoreDiscV', 'fattoreDiscO', 'fattoreAngolare', 'fattorePresa', 'frequenzaGesti', 'den')
    ];
}

/**
 * Get the esito (outcome) based on lifting index
 */
function get_esito_from_indice($indice) {
    if ($indice <= 0.85) return 'positivo';
    if ($indice <= 1.0) return 'da_rivedere';
    return 'negativo';
}

/**
 * Format esito for display
 */
function format_esito($esito) {
    $map = [
        'positivo' => '<span class="badge badge-success">Positivo</span>',
        'negativo' => '<span class="badge badge-danger">Negativo</span>',
        'da_rivedere' => '<span class="badge badge-warning">Da Rivedere</span>',
    ];
    return $map[$esito] ?? $esito;
}

/**
 * Format role for display
 */
function format_ruolo($ruolo) {
    $map = [
        'admin' => 'Amministratore Sistema',
        'super' => 'Super Utente',
        'amministrativo' => 'Amministrativo',
        'ingegnere' => 'Ingegnere',
        'cliente' => 'Cliente',
    ];
    return $map[$ruolo] ?? $ruolo;
}
?>