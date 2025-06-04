-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 04 juin 2025 à 17:30
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `jpo_connect`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `jpo_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_comment` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('awaiting','approved','denied') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'awaiting',
  PRIMARY KEY (`id`),
  KEY `jpo_id` (`jpo_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `date_comment` (`date_comment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comments_responses`
--

DROP TABLE IF EXISTS `comments_responses`;
CREATE TABLE IF NOT EXISTS `comments_responses` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `response_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `user_id` (`user_id`),
  KEY `response_date` (`response_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jpo`
--

DROP TABLE IF EXISTS `jpo`;
CREATE TABLE IF NOT EXISTS `jpo` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` text,
  `date_jpo` datetime NOT NULL,
  `place` enum('Marseille','Paris','Cannes','Martigues','Toulon','Brignoles') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `capacity` int NOT NULL,
  `registered` int DEFAULT '0',
  `status` enum('upcoming','finished','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'upcoming',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date_jpo` (`date_jpo`),
  KEY `place` (`place`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `registration`
--

DROP TABLE IF EXISTS `registration`;
CREATE TABLE IF NOT EXISTS `registration` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `jpo_id` int UNSIGNED NOT NULL,
  `date_registration` datetime DEFAULT CURRENT_TIMESTAMP,
  `presence` tinyint(1) DEFAULT '0',
  `reminder_sent` tinyint(1) DEFAULT '0',
  `reminder_sent_date` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`user_id`,`jpo_id`),
  KEY `jpo_id` (`jpo_id`),
  KEY `date_registration` (`date_registration`),
  KEY `presence` (`presence`),
  KEY `reminder_sent` (`reminder_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` text NOT NULL,
  `description` text NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `surname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` enum('user','employee','manager','director') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'user',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `phone` varchar(20) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `date_creation` (`date_creation`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `email_logs` (nouvelle table pour tracer les emails)
--

DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NULL,
  `jpo_id` int UNSIGNED NULL,
  `email_to` varchar(150) NOT NULL,
  `email_type` enum('registration_confirmation','reminder','cancelation','test') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('sent','failed') DEFAULT 'sent',
  `error_message` text NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `jpo_id` (`jpo_id`),
  KEY `email_type` (`email_type`),
  KEY `sent_at` (`sent_at`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`jpo_id`) REFERENCES `jpo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comments_responses`
--
ALTER TABLE `comments_responses`
  ADD CONSTRAINT `comments_responses_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `registration`
--
ALTER TABLE `registration`
  ADD CONSTRAINT `registration_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_ibfk_2` FOREIGN KEY (`jpo_id`) REFERENCES `jpo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_logs_ibfk_2` FOREIGN KEY (`jpo_id`) REFERENCES `jpo` (`id`) ON DELETE SET NULL;

--
-- Insertion des données par défaut
--

--
-- Paramètres par défaut pour la table `settings`
--
INSERT INTO `settings` (`setting_key`, `value`, `description`) VALUES
('site_title', 'JPO Connect - La Plateforme', 'Titre du site web'),
('contact_email', 'contact@laplateforme.io', 'Email de contact principal'),
('email_notifications', '1', 'Activer les notifications par email (1=oui, 0=non)'),
('reminder_days', '1', 'Nombre de jours avant la JPO pour envoyer le rappel'),
('max_registrations_per_user', '5', 'Nombre maximum d\'inscriptions par utilisateur'),
('registration_deadline_hours', '24', 'Délai minimum en heures avant la JPO pour s\'inscrire'),
('site_description', 'Plateforme d\'inscription aux Journées Portes Ouvertes de La Plateforme', 'Description du site'),
('maintenance_mode', '0', 'Mode maintenance (1=activé, 0=désactivé)'),
('smtp_host', '', 'Serveur SMTP pour l\'envoi d\'emails'),
('smtp_port', '587', 'Port SMTP'),
('smtp_username', '', 'Nom d\'utilisateur SMTP'),
('smtp_password', '', 'Mot de passe SMTP (crypté)'),
('use_smtp', '0', 'Utiliser SMTP pour l\'envoi d\'emails (1=oui, 0=non)');

--
-- Utilisateur administrateur par défaut
-- Mot de passe : admin123 (à changer en production !)
--
INSERT INTO `users` (`name`, `surname`, `email`, `password`, `role`, `is_active`, `email_verified`) VALUES
('Admin', 'Système', 'admin@laplateforme.io', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director', 1, 1);

--
-- Exemples de JPO pour les tests
--
INSERT INTO `jpo` (`description`, `date_jpo`, `place`, `capacity`, `status`) VALUES
('Découvrez nos formations en développement web et mobile. Rencontrez nos équipes pédagogiques et visitez nos locaux.', '2025-07-15 14:00:00', 'Marseille', 50, 'upcoming'),
('Journée spéciale IA et Data Science. Présentations des cursus et ateliers pratiques.', '2025-07-20 10:00:00', 'Paris', 30, 'upcoming'),
('JPO dédiée aux formations en cybersécurité et infrastructure.', '2025-07-25 15:30:00', 'Cannes', 25, 'upcoming');

--
-- Vues pour faciliter les requêtes complexes
--

--
-- Vue pour les statistiques des JPO
--
CREATE OR REPLACE VIEW `jpo_stats` AS
SELECT 
    j.id,
    j.description,
    j.date_jpo,
    j.place,
    j.capacity,
    j.registered,
    j.status,
    ROUND((j.registered / j.capacity) * 100, 2) as fill_rate,
    COUNT(DISTINCT c.id) as comments_count,
    COUNT(DISTINCT r.id) as actual_registrations
FROM jpo j
LEFT JOIN comments c ON j.id = c.jpo_id AND c.status = 'approved'
LEFT JOIN registration r ON j.id = r.jpo_id
GROUP BY j.id;

--
-- Vue pour les statistiques des utilisateurs
--
CREATE OR REPLACE VIEW `user_stats` AS
SELECT 
    u.id,
    u.name,
    u.surname,
    u.email,
    u.role,
    u.date_creation,
    u.last_login,
    COUNT(DISTINCT r.id) as total_registrations,
    COUNT(DISTINCT CASE WHEN r.presence = 1 THEN r.id END) as attended_jpos,
    COUNT(DISTINCT c.id) as comments_count
FROM users u
LEFT JOIN registration r ON u.id = r.user_id
LEFT JOIN comments c ON u.id = c.user_id
GROUP BY u.id;

--
-- Procédures stockées utiles
--

DELIMITER //

--
-- Procédure pour nettoyer les anciennes JPO
--
CREATE PROCEDURE CleanOldJpos()
BEGIN
    -- Marquer comme terminées les JPO passées qui sont encore "upcoming"
    UPDATE jpo 
    SET status = 'finished' 
    WHERE date_jpo < NOW() 
    AND status = 'upcoming';
    
    -- Optionnel : supprimer les JPO très anciennes (plus de 2 ans)
    -- DELETE FROM jpo WHERE date_jpo < DATE_SUB(NOW(), INTERVAL 2 YEAR);
END //

--
-- Procédure pour envoyer les rappels automatiques
--
CREATE PROCEDURE SendReminders()
BEGIN
    DECLARE reminder_days INT DEFAULT 1;
    
    -- Récupérer le nombre de jours de rappel depuis les paramètres
    SELECT value INTO reminder_days 
    FROM settings 
    WHERE setting_key = 'reminder_days' 
    LIMIT 1;
    
    -- Marquer les inscriptions qui nécessitent un rappel
    UPDATE registration r
    INNER JOIN jpo j ON r.jpo_id = j.id
    SET r.reminder_sent = 1, r.reminder_sent_date = NOW()
    WHERE j.date_jpo BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL reminder_days DAY)
    AND j.status = 'upcoming'
    AND r.reminder_sent = 0;
    
    -- Retourner les inscriptions qui viennent d'être marquées
    SELECT r.*, u.email, u.name, u.surname, j.description, j.date_jpo, j.place
    FROM registration r
    INNER JOIN users u ON r.user_id = u.id
    INNER JOIN jpo j ON r.jpo_id = j.id
    WHERE r.reminder_sent = 1 
    AND r.reminder_sent_date >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);
END //

--
-- Fonction pour calculer le taux de présence global
--
CREATE FUNCTION GetGlobalAttendanceRate() RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_registrations INT DEFAULT 0;
    DECLARE total_attended INT DEFAULT 0;
    DECLARE attendance_rate DECIMAL(5,2) DEFAULT 0.00;
    
    SELECT COUNT(*) INTO total_registrations
    FROM registration r
    INNER JOIN jpo j ON r.jpo_id = j.id
    WHERE j.status = 'finished';
    
    SELECT COUNT(*) INTO total_attended
    FROM registration r
    INNER JOIN jpo j ON r.jpo_id = j.id
    WHERE j.status = 'finished' AND r.presence = 1;
    
    IF total_registrations > 0 THEN
        SET attendance_rate = (total_attended / total_registrations) * 100;
    END IF;
    
    RETURN attendance_rate;
END //

DELIMITER ;

--
-- Index supplémentaires pour optimiser les performances
--

-- Index composites pour les requêtes fréquentes
CREATE INDEX idx_jpo_date_status ON jpo(date_jpo, status);
CREATE INDEX idx_registration_user_date ON registration(user_id, date_registration);
CREATE INDEX idx_comments_jpo_status ON comments(jpo_id, status);

--
-- Triggers pour maintenir la cohérence des données
--

DELIMITER //

-- Trigger pour mettre à jour le compteur d'inscrits lors d'une inscription
CREATE TRIGGER after_registration_insert
AFTER INSERT ON registration
FOR EACH ROW
BEGIN
    UPDATE jpo 
    SET registered = registered + 1 
    WHERE id = NEW.jpo_id;
END //

-- Trigger pour mettre à jour le compteur d'inscrits lors d'une désinscription
CREATE TRIGGER after_registration_delete
AFTER DELETE ON registration
FOR EACH ROW
BEGIN
    UPDATE jpo 
    SET registered = registered - 1 
    WHERE id = OLD.jpo_id;
END //

-- Trigger pour mettre à jour last_login lors de la connexion
CREATE TRIGGER after_user_login
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NOT NULL AND (OLD.last_login IS NULL OR NEW.last_login > OLD.last_login) THEN
        SET NEW.last_login = NOW();
    END IF;
END //

DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;