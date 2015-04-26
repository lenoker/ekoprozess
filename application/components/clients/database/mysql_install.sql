DROP TABLE IF EXISTS `pr_client_param_values`;

DROP TABLE IF EXISTS `pr_client_params`;

DROP TABLE IF EXISTS `pr_clients`;

CREATE TABLE IF NOT EXISTS `pr_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int(10) unsigned DEFAULT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(128) NOT NULL DEFAULT '',
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  `active` boolean NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `city_id` (`city_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `pr_clients`
  ADD CONSTRAINT `pr_clients_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `pr_admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pr_clients_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `pr_city` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `pr_client_params` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  `active` boolean NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pr_client_param_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned DEFAULT NULL,
  `param_id` int(10) unsigned DEFAULT NULL,
  `value` varchar(1000) NOT NULL DEFAULT '',
  `active` boolean NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `param_id` (`param_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `pr_client_param_values`
  ADD CONSTRAINT `pr_client_param_values_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `pr_clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pr_client_param_values_ibfk_1` FOREIGN KEY (`param_id`) REFERENCES `pr_client_params` (`id`) ON DELETE CASCADE;