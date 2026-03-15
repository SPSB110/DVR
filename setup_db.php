<?php
/**
 * DVR - Database Setup Script
 * 
 * Run this script once to create the database and tables.
 * Access it via browser: http://localhost/setup_db.php
 * 
 * IMPORTANT: Delete this file after setup in production!
 */

$host    = 'localhost';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "<h1>DVR — Setup Database</h1>";
    echo "<pre>";

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS applicativo_sicurezza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'applicativo_sicurezza' creato/verificato.\n";

    $pdo->exec("USE applicativo_sicurezza");

    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS azienda (
            idA INT AUTO_INCREMENT PRIMARY KEY,
            ragione_sociale VARCHAR(255) NOT NULL,
            p_iva VARCHAR(20) NOT NULL UNIQUE,
            indirizzo VARCHAR(255) DEFAULT NULL,
            telefono VARCHAR(30) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            nome_riferimento_interno VARCHAR(255) DEFAULT NULL,
            valida TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Tabella 'azienda' creata.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS utenti (
            idU INT AUTO_INCREMENT PRIMARY KEY,
            login VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nome VARCHAR(255) NOT NULL,
            nascita DATE DEFAULT NULL,
            sesso ENUM('M','F') DEFAULT NULL,
            idA INT DEFAULT NULL,
            ruolo ENUM('admin','super','amministrativo','ingegnere','cliente') NOT NULL,
            primo_accesso DATETIME DEFAULT NULL,
            ultimo_accesso DATETIME DEFAULT NULL,
            credenziali_inviate TINYINT(1) NOT NULL DEFAULT 0,
            attivo TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idA) REFERENCES azienda(idA) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Tabella 'utenti' creata.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS assegnazioni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idIngegnere INT NOT NULL,
            idCliente INT NOT NULL,
            idA INT NOT NULL,
            data_assegnazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idIngegnere) REFERENCES utenti(idU) ON DELETE CASCADE,
            FOREIGN KEY (idCliente) REFERENCES utenti(idU) ON DELETE CASCADE,
            FOREIGN KEY (idA) REFERENCES azienda(idA) ON DELETE CASCADE,
            UNIQUE KEY unique_assignment (idIngegnere, idCliente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Tabella 'assegnazioni' creata.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS certificati (
            idC INT AUTO_INCREMENT PRIMARY KEY,
            idCliente INT NOT NULL,
            idI INT NOT NULL,
            idA INT NOT NULL,
            data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            esito ENUM('positivo','negativo','da_rivedere') NOT NULL DEFAULT 'da_rivedere',
            note TEXT DEFAULT NULL,
            altezza_mani_soll DECIMAL(8,2) DEFAULT NULL,
            distanza_verticale DECIMAL(8,2) DEFAULT NULL,
            distanza_orizzontale DECIMAL(8,2) DEFAULT NULL,
            dislocazione_angolare DECIMAL(8,2) DEFAULT NULL,
            giudizio_presa_carico ENUM('B','S') DEFAULT NULL,
            frequenza_gesti DECIMAL(8,2) DEFAULT NULL,
            frequenza_lavoro TINYINT DEFAULT NULL,
            peso_sollevato DECIMAL(8,2) DEFAULT NULL,
            indice_sollevamento DECIMAL(10,4) DEFAULT NULL,
            visualizzato_cliente TINYINT(1) NOT NULL DEFAULT 0,
            data_visualizzazione DATETIME DEFAULT NULL,
            FOREIGN KEY (idCliente) REFERENCES utenti(idU) ON DELETE CASCADE,
            FOREIGN KEY (idI) REFERENCES utenti(idU) ON DELETE CASCADE,
            FOREIGN KEY (idA) REFERENCES azienda(idA) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Tabella 'certificati' creata.\n";

    // Check if admin exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        // Create default admin (password: admin123)
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO utenti (login, password, nome, ruolo) VALUES (?, ?, ?, 'admin')")
            ->execute(['admin', $adminHash, 'Amministratore Sistema']);
        echo "✓ Utente admin creato. Login: admin / Password: admin123\n";
    } else {
        echo "→ Utente admin già esistente, non ricreato.\n";
    }

    // Create demo data
    $stmt = $pdo->query("SELECT COUNT(*) FROM azienda");
    if ($stmt->fetchColumn() == 0) {
        // Demo company
        $pdo->exec("INSERT INTO azienda (ragione_sociale, p_iva, indirizzo, telefono, email, nome_riferimento_interno) 
                     VALUES ('CertSicurezza S.r.l.', '12345678901', 'Via Roma 1, 00100 Roma', '06 1234567', 'info@certsicurezza.it', 'Mario Rossi')");
        $idA = $pdo->lastInsertId();
        echo "✓ Azienda demo 'CertSicurezza S.r.l.' creata.\n";

        // Super user
        $hash = password_hash('super123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO utenti (login, password, nome, ruolo) VALUES (?, ?, ?, 'super')")
            ->execute(['super', $hash, 'Super Utente Demo']);
        echo "✓ Super Utente creato. Login: super / Password: super123\n";

        // Amministrativo
        $hash = password_hash('ammin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO utenti (login, password, nome, ruolo, idA) VALUES (?, ?, ?, 'amministrativo', ?)")
            ->execute(['amministrativo', $hash, 'Anna Bianchi', $idA]);
        echo "✓ Amministrativo creato. Login: amministrativo / Password: ammin123\n";

        // Ingegnere
        $hash = password_hash('ing123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO utenti (login, password, nome, ruolo, sesso, nascita, idA) VALUES (?, ?, ?, 'ingegnere', 'M', '1985-03-15', ?)")
            ->execute(['ingegnere', $hash, 'Ing. Luca Verdi', $idA]);
        $idIng = $pdo->lastInsertId();
        echo "✓ Ingegnere creato. Login: ingegnere / Password: ing123\n";

        // Cliente
        $hash = password_hash('cliente123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO utenti (login, password, nome, ruolo, sesso, nascita, idA, credenziali_inviate) VALUES (?, ?, ?, 'cliente', 'M', '1990-07-20', ?, 1)")
            ->execute(['cliente', $hash, 'Paolo Neri', $idA]);
        $idCli = $pdo->lastInsertId();
        echo "✓ Cliente creato. Login: cliente / Password: cliente123\n";

        // Assignment
        $pdo->prepare("INSERT INTO assegnazioni (idIngegnere, idCliente, idA) VALUES (?, ?, ?)")
            ->execute([$idIng, $idCli, $idA]);
        echo "✓ Assegnazione Ingegnere → Cliente creata.\n";
    } else {
        echo "→ Dati demo già presenti, non ricreati.\n";
    }

    echo "\n===============================================\n";
    echo "SETUP COMPLETATO CON SUCCESSO!\n";
    echo "===============================================\n\n";
    echo "CREDENZIALI DI ACCESSO:\n";
    echo "  Admin:          admin / admin123\n";
    echo "  Super Utente:   super / super123\n";
    echo "  Amministrativo: amministrativo / ammin123\n";
    echo "  Ingegnere:      ingegnere / ing123\n";
    echo "  Cliente:        cliente / cliente123\n";
    echo "\n⚠ IMPORTANTE: Elimina questo file (setup_db.php) in produzione!\n";
    echo "</pre>";
    echo "<br><a href='/' style='padding:10px 20px;background:#1a56db;color:white;text-decoration:none;border-radius:8px;font-weight:600'>Vai alla Home →</a>";

} catch (PDOException $e) {
    echo "<h1>Errore Setup</h1>";
    echo "<pre style='color:red'>Errore: " . $e->getMessage() . "</pre>";
    echo "<p>Verifica che MySQL sia in esecuzione e che le credenziali in config.php siano corrette.</p>";
}
