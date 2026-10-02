-- =============================================================================
-- Capistra - public database schema (STRUCTURE ONLY, NO DATA)
-- =============================================================================
-- This file contains no real data. It is safe to publish.
--
-- Privacy decisions baked into this schema:
--   * No KYC/registration tables from the original private system.
--   * No credential-vault columns (gmail passwords, internet-banking
--     passwords, security answers, Meroshare/TMS passwords, OTP secrets).
--     Non-secret account *metadata* is stored in `account_vault_metadata`.
--   * Sensitive employee fields are optional and nullable.
--
-- Prerequisites: MySQL / MariaDB 10.4+ (utf8mb4).
-- Usage:
--   CREATE DATABASE capistra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE capistra;
--   SOURCE database/schema.sql;
--   SOURCE database/demo_seed.sql;
-- =============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- Users / authentication
-- -----------------------------------------------------------------------------
CREATE TABLE `adminusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'password_hash() output, never plaintext',
  `phone` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('admin','accountant','viewer') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username_edit_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_adminusers_username` (`username`),
  UNIQUE KEY `uq_adminusers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Customers (simplified, privacy-first). Extended KYC is out of scope.
-- -----------------------------------------------------------------------------
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  -- Optional business identifiers (never logins / never passwords).
  `tax_identifier` varchar(50) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_customers_name` (`name`),
  KEY `idx_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Employees (sensitive fields optional)
-- -----------------------------------------------------------------------------
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `position` varchar(80) DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `employment_status` enum('active','on_leave','inactive') NOT NULL DEFAULT 'active',
  -- Optional personal details (nullable, only if needed by the operator).
  `dob` date DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `emergency_contact` varchar(120) DEFAULT NULL,
  `emergency_number` varchar(30) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  -- Optional payroll metadata. NOT a credential store.
  `bank_account_number` varchar(40) DEFAULT NULL,
  `pan_number` varchar(40) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `previous_experience` text DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employees_employee_id` (`employee_id`),
  KEY `idx_employees_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Income / Expenses / recycle bin
-- -----------------------------------------------------------------------------
CREATE TABLE `income` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `bill_file` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `journal_entry_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_income_date` (`date`),
  KEY `idx_income_category` (`category`),
  KEY `idx_income_journal` (`journal_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `bill_file` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `journal_entry_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_expenses_date` (`date`),
  KEY `idx_expenses_category` (`category`),
  KEY `idx_expenses_journal` (`journal_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recycle_binexpense` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `bill_file` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Investments (unified + per-asset detail)
-- -----------------------------------------------------------------------------
CREATE TABLE `investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment_type` enum('stock','business','loan','real_estate') NOT NULL,
  `invested_amount` decimal(18,2) NOT NULL,
  `investment_date` date NOT NULL,
  `current_value` decimal(18,2) DEFAULT NULL COMMENT 'latest locally known valuation',
  `valuation_date` date DEFAULT NULL,
  `status` enum('active','matured','closed') NOT NULL DEFAULT 'active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_investments_type` (`investment_type`),
  KEY `idx_investments_date` (`investment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_symbol` varchar(50) NOT NULL,
  `investment_term` enum('short_term','medium_term','long_term') NOT NULL DEFAULT 'long_term',
  `base_price` decimal(15,2) NOT NULL,
  `total_units` decimal(15,4) NOT NULL,
  `agreement_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stock_inv_investment` (`investment_id`),
  KEY `idx_stock_inv_symbol` (`company_symbol`),
  CONSTRAINT `fk_stock_investments_investment` FOREIGN KEY (`investment_id`)
    REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `business_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `investment_model` enum('equity','debt','profit_sharing') NOT NULL,
  `equity_percentage` decimal(5,2) DEFAULT NULL,
  `shareholder_rights` text DEFAULT NULL,
  `loan_amount` decimal(18,2) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `repayment_period` int(11) DEFAULT NULL,
  `profit_share_percentage` decimal(5,2) DEFAULT NULL,
  `distribution_schedule` varchar(255) DEFAULT NULL,
  `agreement_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_business_inv_investment` (`investment_id`),
  CONSTRAINT `fk_business_investments_investment` FOREIGN KEY (`investment_id`)
    REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loan_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment_id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `loan_type` varchar(100) DEFAULT NULL,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0,
  `loan_duration` int(11) NOT NULL DEFAULT 12,
  `repayment_schedule` varchar(255) DEFAULT NULL,
  `collateral` varchar(255) DEFAULT NULL,
  `collateral_details` text DEFAULT NULL,
  `agreement_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loan_inv_investment` (`investment_id`),
  CONSTRAINT `fk_loan_investments_investment` FOREIGN KEY (`investment_id`)
    REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `real_estate_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment_id` int(11) NOT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `property_location` varchar(255) NOT NULL,
  `property_size` decimal(12,2) DEFAULT NULL,
  `ownership_type` varchar(100) DEFAULT NULL,
  `property_description` text DEFAULT NULL,
  `purchase_document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_realestate_inv_investment` (`investment_id`),
  CONSTRAINT `fk_real_estate_investments_investment` FOREIGN KEY (`investment_id`)
    REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `real_estate_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_property_images_realestate` (`real_estate_id`),
  CONSTRAINT `fk_property_images_realestate` FOREIGN KEY (`real_estate_id`)
    REFERENCES `real_estate_investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Public market data cache. Not private. The app works offline using this cache.
CREATE TABLE `stock_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `symbol` varchar(20) NOT NULL,
  `ltp` decimal(12,2) NOT NULL DEFAULT 0,
  `price_change` decimal(12,2) NOT NULL DEFAULT 0,
  `percent_change` varchar(12) DEFAULT NULL,
  `volume` int(11) NOT NULL DEFAULT 0,
  `fetched_at` datetime NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stock_prices_symbol` (`symbol`),
  KEY `idx_stock_prices_fetched` (`fetched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Investors / fund management
-- -----------------------------------------------------------------------------
CREATE TABLE `investors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(30) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `date_of_investment` date DEFAULT NULL,
  `investment_type` enum('Equity','Debt','Profit-sharing') DEFAULT NULL,
  `investment_amount` decimal(18,2) DEFAULT NULL,
  `agreement_files` longtext DEFAULT NULL,
  `kyc_status` enum('Verified','Pending','Rejected') NOT NULL DEFAULT 'Pending',
  `bank_name` varchar(120) DEFAULT NULL,
  `bank_account_masked` varchar(40) DEFAULT NULL COMMENT 'masked only, e.g. ****1234',
  `nominee_name` varchar(150) DEFAULT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `investment_risk` enum('Low','Medium','High') DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_investors_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equity_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `equity_percentage` decimal(5,2) NOT NULL DEFAULT 0,
  `share_price` decimal(15,2) NOT NULL DEFAULT 0,
  `total_shares` int(11) NOT NULL DEFAULT 0,
  `dividend_policy` varchar(255) DEFAULT NULL,
  `voting_rights` enum('Yes','No') NOT NULL DEFAULT 'No',
  `resale_strategy` varchar(255) DEFAULT NULL,
  `share_transfer` enum('Allowed','Not Allowed') NOT NULL DEFAULT 'Not Allowed',
  PRIMARY KEY (`id`),
  KEY `idx_equity_investor` (`investor_id`),
  CONSTRAINT `fk_equity_details_investor` FOREIGN KEY (`investor_id`)
    REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `debt_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `debt_duration` varchar(50) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0,
  `repayment_schedule` enum('Monthly','Yearly','One-Time Payment') NOT NULL DEFAULT 'Monthly',
  `collateral` varchar(255) DEFAULT NULL,
  `total_interest` decimal(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_debt_investor` (`investor_id`),
  CONSTRAINT `fk_debt_details_investor` FOREIGN KEY (`investor_id`)
    REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profit_sharing_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `time_range` varchar(50) NOT NULL,
  `profit_percentage` decimal(5,2) NOT NULL DEFAULT 0,
  `payout_frequency` enum('Monthly','Quarterly','Yearly') NOT NULL DEFAULT 'Monthly',
  `return_method` enum('Bank Transfer','Digital Wallet','Reinvestment') NOT NULL DEFAULT 'Bank Transfer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_profit_sharing_investor` (`investor_id`),
  CONSTRAINT `fk_profit_sharing_details_investor` FOREIGN KEY (`investor_id`)
    REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `investor_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `return_type` enum('dividend','interest','profit_share','capital_return','other') NOT NULL DEFAULT 'other',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_returns_investor` (`investor_id`),
  CONSTRAINT `fk_investor_returns_investor` FOREIGN KEY (`investor_id`)
    REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Safe account metadata ("vault" WITHOUT secrets).
-- Replaces the original credential-vault tables. NEVER store passwords,
-- security answers, OTP secrets or recovery credentials here.
-- -----------------------------------------------------------------------------
CREATE TABLE `account_vault_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` enum('bank','broker','demat','email','wallet','other') NOT NULL DEFAULT 'other',
  `provider` varchar(150) NOT NULL COMMENT 'institution / provider name',
  `account_nickname` varchar(150) DEFAULT NULL,
  `masked_identifier` varchar(60) DEFAULT NULL COMMENT 'masked only, e.g. ****1234',
  `reference_id` varchar(100) DEFAULT NULL COMMENT 'customer / reference ID, not a secret',
  `notes` text DEFAULT NULL,
  `last_verified_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vault_user` (`user_id`),
  CONSTRAINT `fk_vault_metadata_user` FOREIGN KEY (`user_id`)
    REFERENCES `adminusers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Accounting core
-- -----------------------------------------------------------------------------
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('asset','liability','equity','income','expense') NOT NULL,
  `subtype` varchar(60) DEFAULT NULL COMMENT 'e.g. current_asset, cash, revenue',
  `parent_id` int(11) DEFAULT NULL,
  `opening_balance` decimal(18,2) NOT NULL DEFAULT 0,
  `opening_balance_type` enum('debit','credit') NOT NULL DEFAULT 'debit',
  `is_cash_account` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_accounts_code` (`code`),
  KEY `idx_accounts_type` (`type`),
  KEY `idx_accounts_parent` (`parent_id`),
  CONSTRAINT `fk_accounts_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_date` date NOT NULL,
  `reference` varchar(60) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `source_type` enum('manual','income','expense','investment','billing','opening','other') NOT NULL DEFAULT 'manual',
  `source_id` int(11) DEFAULT NULL,
  `status` enum('draft','posted','void') NOT NULL DEFAULT 'posted',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_journal_date` (`entry_date`),
  KEY `idx_journal_source` (`source_type`, `source_id`),
  KEY `idx_journal_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(18,2) NOT NULL DEFAULT 0,
  `credit` decimal(18,2) NOT NULL DEFAULT 0,
  `line_memo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lines_entry` (`journal_entry_id`),
  KEY `idx_lines_account` (`account_id`),
  CONSTRAINT `fk_journal_lines_entry` FOREIGN KEY (`journal_entry_id`)
    REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_journal_lines_account` FOREIGN KEY (`account_id`)
    REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fiscal_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fiscal_label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail for accounting mutations.
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Key/value application settings (company profile, base currency, etc).
CREATE TABLE `app_settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe OTP storage for optional email verification. Stores a hash, never the code.
CREATE TABLE `auth_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminuser_id` int(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `purpose` varchar(40) NOT NULL DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `consumed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_auth_otp_user` (`adminuser_id`),
  CONSTRAINT `fk_auth_otp_user` FOREIGN KEY (`adminuser_id`)
    REFERENCES `adminusers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Reporting view: unified investments
-- -----------------------------------------------------------------------------
DROP VIEW IF EXISTS `vw_all_investments`;
CREATE VIEW `vw_all_investments` AS
SELECT
  `i`.`id`                AS `id`,
  `i`.`investment_type`   AS `investment_type`,
  `i`.`invested_amount`   AS `invested_amount`,
  `i`.`current_value`     AS `current_value`,
  `i`.`investment_date`   AS `investment_date`,
  `i`.`status`            AS `status`,
  `i`.`remarks`           AS `remarks`,
  COALESCE(`s`.`company_name`, `b`.`business_name`, `l`.`borrower_name`, `r`.`property_location`) AS `investment_name`,
  `i`.`created_at`        AS `created_at`,
  `i`.`updated_at`        AS `updated_at`
FROM `investments` `i`
LEFT JOIN `stock_investments` `s`       ON `s`.`investment_id` = `i`.`id` AND `i`.`investment_type` = 'stock'
LEFT JOIN `business_investments` `b`    ON `b`.`investment_id` = `i`.`id` AND `i`.`investment_type` = 'business'
LEFT JOIN `loan_investments` `l`        ON `l`.`investment_id` = `i`.`id` AND `i`.`investment_type` = 'loan'
LEFT JOIN `real_estate_investments` `r` ON `r`.`investment_id` = `i`.`id` AND `i`.`investment_type` = 'real_estate';

-- =============================================================================
-- DYNAMIC SYSTEM ARCHITECTURE
-- =============================================================================
-- Everything below turns previously hard-coded behaviour into data:
--   * transaction categories + their ledger mapping
--   * financial accounts (wallets) + transfers + reconciliation
--   * NEPSE exchanges / sectors / securities / historical prices
--   * transaction-based stock ledger + corporate actions
--   * budgets, recurring transactions, goals, tags, fee rules
--   * configurable classification options
-- `app_settings` remains scalar-only (never a database dump).
-- =============================================================================

-- ---------------------------------------------------------------------------
-- Transaction categories (replaces hard-coded income/expense dropdowns)
-- ---------------------------------------------------------------------------
CREATE TABLE `transaction_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ledger_account_id` int(11) DEFAULT NULL COMMENT 'posting account for this category',
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_txn_category_slug` (`type`, `slug`),
  KEY `idx_txn_category_type` (`type`),
  CONSTRAINT `fk_txn_category_account` FOREIGN KEY (`ledger_account_id`)
    REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Financial accounts (wallets). NEVER store credentials here.
-- ---------------------------------------------------------------------------
CREATE TABLE `financial_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `type` enum('cash','bank','e_wallet','broker','credit_card','loan','other') NOT NULL DEFAULT 'cash',
  `institution` varchar(150) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'NPR',
  `opening_balance` decimal(18,2) NOT NULL DEFAULT 0,
  `masked_reference` varchar(60) DEFAULT NULL COMMENT 'masked only, e.g. ****1234',
  `ledger_account_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_financial_account_name` (`name`),
  KEY `idx_financial_account_type` (`type`),
  CONSTRAINT `fk_financial_account_ledger` FOREIGN KEY (`ledger_account_id`)
    REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Transfers between financial accounts (not income / not expense)
-- ---------------------------------------------------------------------------
CREATE TABLE `transfers` (
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
  KEY `idx_transfer_from` (`from_account_id`),
  KEY `idx_transfer_to` (`to_account_id`),
  CONSTRAINT `fk_transfer_from` FOREIGN KEY (`from_account_id`) REFERENCES `financial_accounts` (`id`),
  CONSTRAINT `fk_transfer_to` FOREIGN KEY (`to_account_id`) REFERENCES `financial_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Account reconciliation
-- ---------------------------------------------------------------------------
CREATE TABLE `reconciliations` (
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
  UNIQUE KEY `uq_reconciliation` (`financial_account_id`, `statement_date`),
  CONSTRAINT `fk_reconciliation_account` FOREIGN KEY (`financial_account_id`)
    REFERENCES `financial_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Market master data (NEPSE)
-- ---------------------------------------------------------------------------
CREATE TABLE `market_exchanges` (
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

CREATE TABLE `market_sectors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exchange_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_market_sector` (`exchange_id`, `slug`),
  CONSTRAINT `fk_market_sector_exchange` FOREIGN KEY (`exchange_id`)
    REFERENCES `market_exchanges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `securities` (
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
  UNIQUE KEY `uq_security_symbol` (`exchange_id`, `symbol`),
  KEY `idx_security_sector` (`sector_id`),
  KEY `idx_security_status` (`listing_status`),
  CONSTRAINT `fk_security_exchange` FOREIGN KEY (`exchange_id`)
    REFERENCES `market_exchanges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_security_sector` FOREIGN KEY (`sector_id`)
    REFERENCES `market_sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only historical prices. NEVER truncate this table.
CREATE TABLE `security_prices` (
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
  UNIQUE KEY `uq_security_price` (`security_id`, `price_date`, `source`),
  KEY `idx_security_price_date` (`price_date`),
  CONSTRAINT `fk_security_price_security` FOREIGN KEY (`security_id`)
    REFERENCES `securities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Portfolios and the transaction-based stock ledger
-- ---------------------------------------------------------------------------
CREATE TABLE `portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portfolio_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_transactions` (
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
  KEY `idx_stock_txn_portfolio` (`portfolio_id`),
  CONSTRAINT `fk_stock_txn_security` FOREIGN KEY (`security_id`) REFERENCES `securities` (`id`),
  CONSTRAINT `fk_stock_txn_portfolio` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stock_txn_account` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `corporate_actions` (
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
  KEY `idx_corp_action_date` (`action_date`),
  CONSTRAINT `fk_corp_action_security` FOREIGN KEY (`security_id`) REFERENCES `securities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Effective-dated fee / tax rules
-- ---------------------------------------------------------------------------
CREATE TABLE `fee_rules` (
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

-- ---------------------------------------------------------------------------
-- Budgets / recurring / goals / tags
-- ---------------------------------------------------------------------------
CREATE TABLE `budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `period_year` smallint(4) NOT NULL,
  `period_month` tinyint(2) NOT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_period` (`period_year`, `period_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `budget_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_item` (`budget_id`, `category_id`),
  CONSTRAINT `fk_budget_item_budget` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budget_item_category` FOREIGN KEY (`category_id`) REFERENCES `transaction_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recurring_transactions` (
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

CREATE TABLE `goals` (
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

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transaction_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `transaction_type` enum('income','expense','transfer','stock') NOT NULL,
  `transaction_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaction_tag` (`tag_id`, `transaction_type`, `transaction_id`),
  KEY `idx_transaction_tag_lookup` (`transaction_type`, `transaction_id`),
  CONSTRAINT `fk_transaction_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Configurable classification options (replaces hard-coded HTML enums)
-- ---------------------------------------------------------------------------
CREATE TABLE `classification_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_key` varchar(50) NOT NULL,
  `label` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classification_option` (`group_key`, `slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Benchmarks (advanced, schema-ready) and import audit log
-- ---------------------------------------------------------------------------
CREATE TABLE `benchmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_benchmark_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `benchmark_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `benchmark_id` int(11) NOT NULL,
  `price_date` date NOT NULL,
  `value` decimal(18,4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_benchmark_price` (`benchmark_id`, `price_date`),
  CONSTRAINT `fk_benchmark_price_benchmark` FOREIGN KEY (`benchmark_id`) REFERENCES `benchmarks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_batches` (
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

-- ---------------------------------------------------------------------------
-- Additive columns on existing tables (links, not replacements)
-- ---------------------------------------------------------------------------
ALTER TABLE `income`
  ADD COLUMN `financial_account_id` int(11) DEFAULT NULL AFTER `customer_id`,
  ADD KEY `idx_income_financial_account` (`financial_account_id`);

ALTER TABLE `expenses`
  ADD COLUMN `financial_account_id` int(11) DEFAULT NULL AFTER `remarks`,
  ADD KEY `idx_expenses_financial_account` (`financial_account_id`);

ALTER TABLE `stock_investments`
  ADD COLUMN `security_id` int(11) DEFAULT NULL AFTER `company_symbol`,
  ADD COLUMN `portfolio_id` int(11) DEFAULT NULL AFTER `security_id`,
  ADD KEY `idx_stock_inv_security` (`security_id`),
  ADD KEY `idx_stock_inv_portfolio` (`portfolio_id`);
