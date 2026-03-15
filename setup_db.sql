-- ============================================================
-- applicativo_sicurezza - Database Schema
-- Execute this SQL in your MySQL database to set up tables
-- ============================================================

CREATE DATABASE IF NOT EXISTS applicativo_sicurezza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE applicativo_sicurezza;

-- Aziende (Companies)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Utenti (Users)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assegnazioni Ingegnere-Cliente
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Certificati (Certificates)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123 - CHANGE IN PRODUCTION!)
INSERT INTO utenti (login, password, nome, ruolo) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amministratore Sistema', 'admin');
-- The above hash is for 'password' - you should change it
