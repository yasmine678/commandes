-- Schema for ordersdb — fresh install script.
--
-- Notes vs. the original phpMyAdmin dump this project started from:
--   - users.usId: PRIMARY KEY + AUTO_INCREMENT (was missing entirely)
--   - users.email: UNIQUE index (needed for login lookup)
--   - engine switched MyISAM -> InnoDB on all tables (transactions + real FKs)
--   - added FOREIGN KEY constraints between orders/oderline/menu_service/users
--     and their parent tables
--   - orders.note holds the collaborator's free-text explanation of what they
--     actually need, shown alongside the auto-generated line summary
--     (orders.description).
--
-- No seed admin account: the FIRST account ever registered through
-- /inscription.php is automatically made 'administrateur' (see
-- controllers/users.php).

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `oderline`;
DROP TABLE IF EXISTS `menu_service`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `service`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `usId` int NOT NULL AUTO_INCREMENT,
  `lastName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('collaborateur','administrateur') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collaborateur',
  PRIMARY KEY (`usId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service` (
  `serId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`serId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu` (
  `meId` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date_begg` date NOT NULL,
  `date_end` date NOT NULL,
  `date_open` datetime NOT NULL,
  `date_endin` datetime NOT NULL,
  `statut` enum('brouillon','publie','archive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`meId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_service` (
  `meId` int NOT NULL,
  `serId` int NOT NULL,
  PRIMARY KEY (`meId`,`serId`),
  KEY `fk_menu_service_service` (`serId`),
  CONSTRAINT `fk_menu_service_menu` FOREIGN KEY (`meId`) REFERENCES `menu` (`meId`) ON DELETE CASCADE,
  CONSTRAINT `fk_menu_service_service` FOREIGN KEY (`serId`) REFERENCES `service` (`serId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `ordId` int NOT NULL AUTO_INCREMENT,
  `dateOrder` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateLivraison` date NOT NULL,
  `status` enum('en attente','en cours','terminée','annulée','livrée') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en attente',
  `usId` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ordId`),
  KEY `usId` (`usId`),
  CONSTRAINT `fk_orders_users` FOREIGN KEY (`usId`) REFERENCES `users` (`usId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `oderline` (
  `oLId` int NOT NULL AUTO_INCREMENT,
  `quantity` int NOT NULL DEFAULT '1',
  `ordId` int NOT NULL,
  `serId` int NOT NULL,
  PRIMARY KEY (`oLId`),
  KEY `ordId` (`ordId`),
  KEY `serId` (`serId`),
  CONSTRAINT `fk_oderline_orders` FOREIGN KEY (`ordId`) REFERENCES `orders` (`ordId`) ON DELETE CASCADE,
  CONSTRAINT `fk_oderline_service` FOREIGN KEY (`serId`) REFERENCES `service` (`serId`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
