<?php

$CONFIG['db'] = parse_ini_file('public/config/db.ini', true);
$CONFIG['main'] = parse_ini_file('public/config/main.ini', true);

$db = new mysqli($CONFIG['db']['database']['host'], $CONFIG['db']['database']['user'], $CONFIG['db']['database']['password'], $CONFIG['db']['database']['db']);
if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

$db->query(
    "CREATE TABLE IF NOT EXISTS `user` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `login` varchar(15) COLLATE utf8_polish_ci NOT NULL,
    `password` varchar(255) COLLATE utf8_polish_ci NOT NULL,
    `addData` datetime NOT NULL DEFAULT current_timestamp(),
    `modData` datetime NOT NULL DEFAULT current_timestamp(),
    `name` varchar(40) COLLATE utf8_polish_ci NOT NULL,
    `surname` varchar(60) COLLATE utf8_polish_ci NOT NULL,
    `active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 or 1 only',
    `lastLogin` datetime DEFAULT NULL,
    `failedLogin` tinyint(4) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `User_UN` (`login`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci");

$db->query(
    "CREATE TABLE IF NOT EXISTS `roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `role` enum('admin','worker','user') COLLATE utf8_polish_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `roles_FK` (`user_id`),
    CONSTRAINT `roles_FK` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci");

$db->query(
    "CREATE TABLE IF NOT EXISTS `account` (
    `number` varchar(32) COLLATE utf8_polish_ci NOT NULL,
    `user_id` int(10) unsigned NOT NULL,
    `balance` double(10,2) NOT NULL DEFAULT 0.00,
    `currency` enum('PLN') COLLATE utf8_polish_ci NOT NULL,
    `debit` double(10,2) NOT NULL DEFAULT 2000.00,
    PRIMARY KEY (`number`),
    KEY `account_FK` (`user_id`),
    CONSTRAINT `account_FK` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci");

$db->query(
    "CREATE TABLE IF NOT EXISTS `transfer` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `sender` varchar(32) COLLATE utf8_polish_ci NOT NULL,
    `reciver` varchar(32) COLLATE utf8_polish_ci NOT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `title` varchar(100) COLLATE utf8_polish_ci NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `name` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
    `description` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
    `address` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sender_FK` (`sender`),
    KEY `reciver_FK` (`reciver`),
    CONSTRAINT `reciver_FK` FOREIGN KEY (`reciver`) REFERENCES `account` (`number`),
    CONSTRAINT `sender_FK` FOREIGN KEY (`sender`) REFERENCES `account` (`number`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci");

$db->query(
  "CREATE TABLE IF NOT EXISTS `credit` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `currency` enum('PLN') COLLATE utf8_polish_ci NOT NULL DEFAULT 'PLN',
    `installment` smallint(6) NOT NULL DEFAULT 6,
    `state` enum('pending','granted','closed') COLLATE utf8_polish_ci NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`),
    KEY `Credit_FK` (`user_id`),
    CONSTRAINT `Credit_FK` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `installment` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `credit_id` int(10) unsigned NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `currency` enum('PLN') COLLATE utf8_polish_ci NOT NULL DEFAULT 'PLN',
    `paid` tinyint(4) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `installment_FK` (`credit_id`),
    CONSTRAINT `installment_FK` FOREIGN KEY (`credit_id`) REFERENCES `credit` (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `contact` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(100) COLLATE utf8_polish_ci NOT NULL,
    `text` mediumtext COLLATE utf8_polish_ci NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci"
);

$password_hash = '$2y$10$tL92/fy7RAQUvP.b9i2glOftdTUPYE7pg.4bzEey/.pE7Tz1a1DHq'; // hasło Password
$now = date('Y-m-d H:i:s');

$db->query(
  "INSERT INTO `user`
  (id, login, password, addData, modData, name, surname, active, lastLogin, failedLogin) VALUES 
  (1, 'Admin', '{$password_hash}', '{$now}', '{$now}', 'Admin', 'Admin', 1, NULL, NULL),
  (2, 'JPD', '{$password_hash}', '{$now}', '{$now}', 'JPD', 'JPD', 1, NULL, NULL),
  (3, 'Plus', '{$password_hash}', '{$now}', '{$now}', 'Plus', 'Plus', 1, NULL, NULL),
  (4, 'Tmobile', '{$password_hash}', '{$now}', '{$now}', 'Tmobile', 'Tmobile', 1, NULL, NULL),
  (5, 'Orange', '{$password_hash}', '{$now}', '{$now}', 'Orange', 'Orange', 1, NULL, NULL),
  (6, 'Play', '{$password_hash}', '{$now}', '{$now}', 'Play', 'Play', 1, NULL, NULL),
  (7, 'Inni', '{$password_hash}', '{$now}', '{$now}', 'Inni', 'Inni', 1, NULL, NULL),
  (8, 'Worker', '{$password_hash}', '{$now}', '{$now}', 'Worker', 'Worker', 1, NULL, NULL);");

$db->query(
  "INSERT INTO roles
  (id, user_id, role) VALUES 
  (1, 1, 'admin'),
  (2, 2, 'user'),
  (3, 3, 'user'),
  (4, 4, 'user'),
  (5, 5, 'user'),
  (6, 6, 'user'),
  (7, 7, 'user'),
  (8, 8, 'worker');");

$db->query(
  "INSERT INTO account 
  (`number`, user_id, balance, currency, debit) VALUES 
  ('{$CONFIG["main"]["bank"]["bank_account"]}', 2, 0, 'PLN', 1000000),
  ('{$CONFIG["main"]["plus"]["account"]}', 3, 0, 'PLN', 50000),
  ('{$CONFIG["main"]["tmobile"]["account"]}', 4, 0, 'PLN', 50000),
  ('{$CONFIG["main"]["orange"]["account"]}', 5, 0, 'PLN', 50000),
  ('{$CONFIG["main"]["play"]["account"]}', 6, 0, 'PLN', 50000),
  ('{$CONFIG["main"]["inni"]["account"]}', 7, 0, 'PLN', 50000);");

$x = 1;

?>