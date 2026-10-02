-- =============================================================================
-- Capistra migration 001 - dynamic system foundation (idempotent)
-- =============================================================================
-- For EXISTING installs only. Fresh installs get everything from schema.sql.
-- Safe to run more than once (CREATE TABLE IF NOT EXISTS / ADD COLUMN IF NOT
-- EXISTS). It is purely additive and never drops or rewrites existing data.
--
--   USE capistra;
--   SOURCE database/migrations/001_dynamic_foundation.sql;
-- =============================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `transaction_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ledger_account_id` int(11) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_txn_category_slug` (`type`,`slug`),
  KEY `idx_txn_category_type` (`type`),
  CONSTRAINT `fk_txn_category_account` FOREIGN KEY (`ledger_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `financial_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `type` enum('cash','bank','e_wallet','broker','credit_card','loan','other') NOT NULL DEFAULT 'cash',
  `institution` varchar(150) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'NPR',
  `opening_balance` decimal(18,2) NOT NULL DEFAULT 0,
  `masked_reference` varchar(60) DEFAULT NULL,
  `ledger_account_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_financial_account_name` (`name`),
  KEY `idx_financial_account_type` (`type`),
  CONSTRAINT `fk_financial_account_ledger` FOREIGN KEY (`ledger_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_account_id` int(11) NOT NULL,
  `to_account_id` int(11) NOT NULL,
  `transfer_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `reference` varchar(60) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `journal_entry_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transfer_date` (`transfer_date`),
  CONSTRAINT `fk_transfer_from` FOREIGN KEY (`from_account_id`) REFERENCES `financial_accounts` (`id`),
  CONSTRAINT `fk_transfer_to` FOREIGN KEY (`to_account_id`) REFERENCES `financial_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reconciliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `financial_account_id` int(11) NOT NULL,
  `statement_date` date NOT NULL,
  `statement_balance` decimal(18,2) NOT NULL DEFAULT 0,
  `system_balance` decimal(18,2) NOT NULL DEFAULT 0,
  `difference` decimal(18,2) NOT NULL DEFAULT 0,
  `status` enum('balanced','discrepancy') NOT NULL DEFAULT 'discrepancy',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reconciliation` (`financial_account_id`,`statement_date`),
  CONSTRAINT `fk_reconciliation_account` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `market_exchanges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `country` varchar(80) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'NPR',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_market_exchange_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `market_sectors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exchange_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_market_sector` (`exchange_id`,`slug`),
  CONSTRAINT `fk_market_sector_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `market_exchanges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `securities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exchange_id` int(11) NOT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `symbol` varchar(30) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `security_type` enum('equity','mutual_fund','debenture','preference','other') NOT NULL DEFAULT 'equity',
  `face_value` decimal(12,2) DEFAULT NULL,
  `listing_status` enum('unverified','listed','suspended','delisted') NOT NULL DEFAULT 'unverified',
  `listing_date` date DEFAULT NULL,
  `delisted_date` date DEFAULT NULL,
  `market_data_symbol` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_symbol` (`exchange_id`,`symbol`),
  KEY `idx_security_sector` (`sector_id`),
  CONSTRAINT `fk_security_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `market_exchanges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_security_sector` FOREIGN KEY (`sector_id`) REFERENCES `market_sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `security_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `security_id` int(11) NOT NULL,
  `price_date` date NOT NULL,
  `open` decimal(12,2) DEFAULT NULL,
  `high` decimal(12,2) DEFAULT NULL,
  `low` decimal(12,2) DEFAULT NULL,
  `close` decimal(12,2) NOT NULL,
  `volume` bigint(20) DEFAULT NULL,
  `source` varchar(40) NOT NULL DEFAULT 'manual',
  `fetched_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_price` (`security_id`,`price_date`,`source`),
  CONSTRAINT `fk_security_price_security` FOREIGN KEY (`security_id`) REFERENCES `securities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portfolio_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) DEFAULT NULL,
  `security_id` int(11) NOT NULL,
  `transaction_type` enum('BUY','SELL','IPO','FPO','RIGHT_BUY','BONUS','CASH_DIVIDEND','STOCK_DIVIDEND','SPLIT','MERGER_ADJUSTMENT','MANUAL_ADJUSTMENT') NOT NULL,
  `transaction_date` date NOT NULL,
  `units` decimal(18,4) NOT NULL DEFAULT 0,
  `price_per_unit` decimal(15,4) NOT NULL DEFAULT 0,
  `gross_amount` decimal(18,2) NOT NULL DEFAULT 0,
  `fees` decimal(18,2) NOT NULL DEFAULT 0,
  `tax` decimal(18,2) NOT NULL DEFAULT 0,
  `net_amount` decimal(18,2) NOT NULL DEFAULT 0,
  `financial_account_id` int(11) DEFAULT NULL,
  `journal_entry_id` int(11) DEFAULT NULL,
  `reference` varchar(60) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `ratio_from` decimal(12,4) DEFAULT NULL,
  `ratio_to` decimal(12,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stock_txn_security` (`security_id`),
  KEY `idx_stock_txn_date` (`transaction_date`),
  CONSTRAINT `fk_stock_txn_security` FOREIGN KEY (`security_id`) REFERENCES `securities` (`id`),
  CONSTRAINT `fk_stock_txn_portfolio` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stock_txn_account` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `corporate_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `security_id` int(11) NOT NULL,
  `portfolio_id` int(11) DEFAULT NULL,
  `action_type` enum('cash_dividend','bonus','rights','split','consolidation','merger','other') NOT NULL DEFAULT 'other',
  `action_date` date NOT NULL,
  `ratio_from` decimal(12,4) DEFAULT NULL,
  `ratio_to` decimal(12,4) DEFAULT NULL,
  `dividend_per_share` decimal(15,4) DEFAULT NULL,
  `units_basis` decimal(18,4) DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `stock_transaction_id` int(11) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_corp_action_security` (`security_id`),
  CONSTRAINT `fk_corp_action_security` FOREIGN KEY (`security_id`) REFERENCES `securities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fee_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `transaction_context` varchar(40) NOT NULL DEFAULT 'buy',
  `calculation_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `rate` decimal(8,4) DEFAULT NULL,
  `fixed_amount` decimal(18,2) DEFAULT NULL,
  `min_amount` decimal(18,2) DEFAULT NULL,
  `max_amount` decimal(18,2) DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fee_context` (`transaction_context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `period_year` smallint(4) NOT NULL,
  `period_month` tinyint(2) NOT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_period` (`period_year`,`period_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `budget_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_item` (`budget_id`,`category_id`),
  CONSTRAINT `fk_budget_item_budget` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budget_item_category` FOREIGN KEY (`category_id`) REFERENCES `transaction_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recurring_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('income','expense','transfer') NOT NULL DEFAULT 'expense',
  `category_id` int(11) DEFAULT NULL,
  `financial_account_id` int(11) DEFAULT NULL,
  `to_account_id` int(11) DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL DEFAULT 0,
  `frequency` enum('weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
  `next_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `mode` enum('auto','reminder') NOT NULL DEFAULT 'reminder',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run_date` date DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_recurring_next` (`next_date`),
  CONSTRAINT `fk_recurring_category` FOREIGN KEY (`category_id`) REFERENCES `transaction_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_recurring_account` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `target_amount` decimal(18,2) NOT NULL DEFAULT 0,
  `current_amount` decimal(18,2) NOT NULL DEFAULT 0,
  `target_date` date DEFAULT NULL,
  `financial_account_id` int(11) DEFAULT NULL,
  `status` enum('active','achieved','paused','cancelled') NOT NULL DEFAULT 'active',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_goal_account` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `transaction_type` enum('income','expense','transfer','stock') NOT NULL,
  `transaction_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaction_tag` (`tag_id`,`transaction_type`,`transaction_id`),
  CONSTRAINT `fk_transaction_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `classification_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_key` varchar(50) NOT NULL,
  `label` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classification_option` (`group_key`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `benchmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_benchmark_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `benchmark_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `benchmark_id` int(11) NOT NULL,
  `price_date` date NOT NULL,
  `value` decimal(18,4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_benchmark_price` (`benchmark_id`,`price_date`),
  CONSTRAINT `fk_benchmark_price_benchmark` FOREIGN KEY (`benchmark_id`) REFERENCES `benchmarks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `import_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dataset` varchar(40) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `total_rows` int(11) NOT NULL DEFAULT 0,
  `imported_rows` int(11) NOT NULL DEFAULT 0,
  `skipped_rows` int(11) NOT NULL DEFAULT 0,
  `status` enum('preview','committed','failed') NOT NULL DEFAULT 'preview',
  `error_report` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_import_dataset` (`dataset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `income`          ADD COLUMN IF NOT EXISTS `financial_account_id` int(11) DEFAULT NULL AFTER `customer_id`;
ALTER TABLE `expenses`        ADD COLUMN IF NOT EXISTS `financial_account_id` int(11) DEFAULT NULL AFTER `remarks`;
ALTER TABLE `stock_investments` ADD COLUMN IF NOT EXISTS `security_id` int(11) DEFAULT NULL AFTER `company_symbol`;
ALTER TABLE `stock_investments` ADD COLUMN IF NOT EXISTS `portfolio_id` int(11) DEFAULT NULL AFTER `security_id`;
