-- Migration: 2026_05_19_000001_create_tb_system_logs_table.php
-- Cria a tabela de auditoria operacional do sistema.

CREATE TABLE IF NOT EXISTS `_tb_system_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `user_name` VARCHAR(150) NULL,
  `user_email` VARCHAR(150) NULL,
  `user_role` VARCHAR(100) NULL,
  `module` VARCHAR(80) NOT NULL,
  `action` VARCHAR(120) NOT NULL,
  `description` TEXT NOT NULL,
  `entity_type` VARCHAR(120) NULL,
  `entity_id` VARCHAR(120) NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `device_id` VARCHAR(120) NULL,
  `route` VARCHAR(255) NULL,
  `method` VARCHAR(12) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `_tb_system_logs_user_id_index` (`user_id`),
  KEY `_tb_system_logs_module_index` (`module`),
  KEY `_tb_system_logs_action_index` (`action`),
  KEY `_tb_system_logs_created_at_index` (`created_at`),
  KEY `_tb_system_logs_entity_type_entity_id_index` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`)
SELECT '2026_05_19_000001_create_tb_system_logs_table',
       COALESCE((SELECT MAX(`batch`) + 1 FROM (SELECT `batch` FROM `migrations`) AS migration_batches), 1);
