-- =============================================================================
-- Capistra - FICTIONAL demo seed
-- =============================================================================
-- EVERYTHING in this file is invented for local demonstration only.
-- Names, companies, emails, phones, addresses, account numbers and financial
-- figures are not real. Emails use the reserved `example.test` domain and
-- phone numbers use the reserved 555 range.
--
-- Demo login (LOCAL DEMO ONLY):
--   Username: demo_admin
--   Email:    demo@example.test
--   Password: Demo@12345   (stored as a password_hash, never plaintext)
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- Demo users
-- -----------------------------------------------------------------------------
INSERT INTO `adminusers` (`id`, `username`, `email`, `password`, `phone`, `full_name`, `address`, `profile_image`, `role`, `is_active`, `username_edit_count`) VALUES
(1, 'demo_admin', 'demo@example.test', '$2y$10$w4U61hTEOKaKecQGHAKCIuKJYP7EGDvs.ZW7.f2EMOSmdhJoWBxPq', '+1-555-0100', 'Demo Administrator', '1 Example Street, Demo City', NULL, 'admin', 1, 0),
(2, 'demo_accountant', 'accountant@example.test', '$2y$10$w4U61hTEOKaKecQGHAKCIuKJYP7EGDvs.ZW7.f2EMOSmdhJoWBxPq', '+1-555-0101', 'Demo Accountant', '2 Example Street, Demo City', NULL, 'accountant', 1, 0);

-- -----------------------------------------------------------------------------
-- Application settings (company profile - configurable)
-- -----------------------------------------------------------------------------
INSERT INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'Capistra Demo Company'),
('company_address', 'Demo Tower, Example Road, Demo City'),
('company_tax_id', 'DEMO-0000000'),
('base_currency', 'NPR'),
('fiscal_year_start', '07-01');

-- -----------------------------------------------------------------------------
-- Fiscal periods
-- -----------------------------------------------------------------------------
INSERT INTO `fiscal_periods` (`id`, `label`, `start_date`, `end_date`, `status`) VALUES
(1, 'FY 2025/26', '2025-07-01', '2026-06-30', 'open'),
(2, 'FY 2024/25', '2024-07-01', '2025-06-30', 'closed');

-- -----------------------------------------------------------------------------
-- Chart of accounts
-- -----------------------------------------------------------------------------
INSERT INTO `accounts` (`id`, `code`, `name`, `type`, `subtype`, `parent_id`, `opening_balance`, `opening_balance_type`, `is_cash_account`, `is_active`, `description`) VALUES
(1,  '1000', 'Cash and Bank',              'asset',     'current_asset', NULL, 0, 'debit',  1, 1, 'Primary cash and bank balances'),
(2,  '1010', 'Accounts Receivable',        'asset',     'current_asset', NULL, 0, 'debit',  0, 1, 'Amounts owed by customers'),
(3,  '1200', 'Investments - Stocks',       'asset',     'investment',    NULL, 0, 'debit',  0, 1, 'Equity holdings in listed companies'),
(4,  '1210', 'Investments - Business',     'asset',     'investment',    NULL, 0, 'debit',  0, 1, 'Equity / debt in private businesses'),
(5,  '1220', 'Investments - Loans',        'asset',     'investment',    NULL, 0, 'debit',  0, 1, 'Loan receivables'),
(6,  '1230', 'Investments - Real Estate',  'asset',     'investment',    NULL, 0, 'debit',  0, 1, 'Investment properties'),
(7,  '1500', 'Office Equipment',           'asset',     'fixed_asset',   NULL, 0, 'debit',  0, 1, 'Equipment and fixtures'),
(8,  '2000', 'Accounts Payable',           'liability', 'current_liability', NULL, 0, 'credit', 0, 1, 'Amounts owed to suppliers'),
(9,  '2100', 'Loans Payable',              'liability', 'long_term_liability', NULL, 0, 'credit', 0, 1, 'Borrowings'),
(10, '2200', 'Investor Capital',           'liability', 'capital',       NULL, 0, 'credit', 0, 1, 'Capital contributed by investors'),
(11, '3000', "Owner's Equity",             'equity',    'equity',        NULL, 0, 'credit', 0, 1, 'Owner equity'),
(12, '3100', 'Retained Earnings',          'equity',    'equity',        NULL, 0, 'credit', 0, 1, 'Accumulated results'),
(13, '4000', 'Operating Income',           'income',    'revenue',       NULL, 0, 'credit', 0, 1, 'Income from services and trading'),
(14, '4100', 'Investment Returns',         'income',    'revenue',       NULL, 0, 'credit', 0, 1, 'Dividends, interest and profit share'),
(15, '4200', 'Other Income',               'income',    'revenue',       NULL, 0, 'credit', 0, 1, 'Miscellaneous income'),
(16, '5000', 'Office Rent',                'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Rent expense'),
(17, '5100', 'Salaries and Wages',         'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Payroll expense'),
(18, '5200', 'Utilities',                  'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Utilities expense'),
(19, '5300', 'Marketing',                  'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Marketing expense'),
(20, '5400', 'Professional Fees',          'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Legal and accounting fees'),
(21, '5900', 'Other Expenses',             'expense',   'operating_expense', NULL, 0, 'debit', 0, 1, 'Other operating expenses');

-- -----------------------------------------------------------------------------
-- Customers (fictional)
-- -----------------------------------------------------------------------------
INSERT INTO `customers` (`id`, `name`, `company`, `email`, `phone`, `billing_address`, `notes`, `status`) VALUES
(1, 'Example Customer',    'Demo Consulting Pvt. Ltd.', 'customer1@example.test', '+1-555-0110', '10 Client Avenue, Demo City', 'Retainer client (fictional).', 'active'),
(2, 'Sample Client Two',   'Example Traders LLC',       'customer2@example.test', '+1-555-0111', '22 Sample Road, Demo City',   'Project-based client (fictional).', 'active'),
(3, 'Demo Organization',   'Sample Holdings',           'customer3@example.test', '+1-555-0112', '5 Example Plaza, Demo City',  'Inactive demo record.', 'inactive');

-- -----------------------------------------------------------------------------
-- Employees (fictional)
-- -----------------------------------------------------------------------------
INSERT INTO `employees` (`id`, `employee_id`, `full_name`, `email`, `mobile`, `position`, `department`, `join_date`, `employment_status`, `nationality`, `notes`) VALUES
(1, 'EMP-DEMO-001', 'Example Employee',  'employee1@example.test', '+1-555-0120', 'Accountant', 'Finance', '2024-01-15', 'active',   'Demo', 'Fictional employee record.'),
(2, 'EMP-DEMO-002', 'Sample Employee',   'employee2@example.test', '+1-555-0121', 'Analyst',    'Investment', '2024-06-01', 'active', 'Demo', 'Fictional employee record.'),
(3, 'EMP-DEMO-003', 'Demo Contractor',   'employee3@example.test', '+1-555-0122', 'Consultant', 'Operations', '2023-09-10', 'inactive', 'Demo', 'Fictional employee record.');

-- -----------------------------------------------------------------------------
-- Income (fictional) - linked to journal entries below
-- -----------------------------------------------------------------------------
INSERT INTO `income` (`id`, `category`, `date`, `amount`, `bill_file`, `remarks`, `customer_id`, `journal_entry_id`) VALUES
(1, 'Consulting',          '2025-07-05', 250000.00, NULL, 'Demo consulting engagement', 1, 2),
(2, 'Investment Returns',  '2025-08-10', 180000.00, NULL, 'Demo dividend and interest', 2, 3);

-- -----------------------------------------------------------------------------
-- Expenses (fictional) - linked to journal entries below
-- -----------------------------------------------------------------------------
INSERT INTO `expenses` (`id`, `category`, `date`, `amount`, `bill_file`, `remarks`, `journal_entry_id`) VALUES
(1, 'Office Rent',        '2025-07-05',  45000.00, NULL, 'Demo monthly office rent', 4),
(2, 'Salaries and Wages', '2025-07-28', 160000.00, NULL, 'Demo monthly payroll',     5),
(3, 'Utilities',          '2025-08-03',  12500.00, NULL, 'Demo utilities bill',      6);

-- -----------------------------------------------------------------------------
-- Investments (unified) + per-asset detail (fictional)
-- -----------------------------------------------------------------------------
INSERT INTO `investments` (`id`, `investment_type`, `invested_amount`, `investment_date`, `current_value`, `valuation_date`, `status`, `remarks`) VALUES
(1, 'stock',       300000.00, '2025-07-12', 345000.00, '2025-09-30', 'active', 'Demo listed equity portfolio'),
(2, 'loan',        200000.00, '2025-07-20', 210000.00, '2025-09-30', 'active', 'Demo loan receivable'),
(3, 'real_estate', 800000.00, '2025-08-01', 860000.00, '2025-09-30', 'active', 'Demo commercial property'),
(4, 'business',    150000.00, '2025-08-15', 165000.00, '2025-09-30', 'active', 'Demo equity in a private business');

INSERT INTO `stock_investments` (`id`, `investment_id`, `company_name`, `company_symbol`, `investment_term`, `base_price`, `total_units`, `agreement_pdf`) VALUES
(1, 1, 'Example Hydro Ltd',     'EXHYD', 'long_term',  250.00, 800.0000, NULL),
(2, 1, 'Sample Micro Finance',  'SMPMF', 'medium_term', 500.00, 200.0000, NULL);

INSERT INTO `loan_investments` (`id`, `investment_id`, `borrower_name`, `loan_type`, `interest_rate`, `loan_duration`, `repayment_schedule`, `collateral`, `collateral_details`, `agreement_pdf`) VALUES
(1, 2, 'Example Borrower Co.', 'Term Loan', 12.00, 24, 'Monthly', 'Property', 'Fictional collateral on demo property.', NULL);

INSERT INTO `real_estate_investments` (`id`, `investment_id`, `property_type`, `property_location`, `property_size`, `ownership_type`, `property_description`, `purchase_document`) VALUES
(1, 3, 'commercial', 'Demo City Central', 1200.00, 'freehold', 'Fictional commercial unit used for demonstration.', NULL);

INSERT INTO `business_investments` (`id`, `investment_id`, `business_name`, `business_type`, `investment_model`, `equity_percentage`, `shareholder_rights`, `profit_share_percentage`, `distribution_schedule`, `agreement_pdf`) VALUES
(1, 4, 'Example Ventures Pvt. Ltd.', 'Technology', 'equity', 15.00, 'Standard minority rights', 15.00, 'Annual', NULL);

-- Public market data cache (fictional symbols).
INSERT INTO `stock_prices` (`id`, `symbol`, `ltp`, `price_change`, `percent_change`, `volume`, `fetched_at`) VALUES
(1, 'EXHYD', 275.00, 2.50, '0.92', 1200, '2025-09-30 15:00:00'),
(2, 'SMPMF', 512.00, -1.20, '-0.23', 800, '2025-09-30 15:00:00');

-- -----------------------------------------------------------------------------
-- Investors / fund management (fictional)
-- -----------------------------------------------------------------------------
INSERT INTO `investors` (`id`, `name`, `profile_photo`, `contact_phone`, `contact_email`, `address`, `nationality`, `date_of_investment`, `investment_type`, `investment_amount`, `agreement_files`, `kyc_status`, `bank_name`, `bank_account_masked`, `nominee_name`, `relationship`, `investment_risk`, `deleted`) VALUES
(1, 'Sample Investor One', NULL, '+1-555-0130', 'investor1@example.test', '30 Investor Lane, Demo City', 'Demo', '2024-03-01', 'Equity',         500000.00, NULL, 'Verified', 'Example Bank', '****1111', 'Sample Nominee', 'Relative', 'Medium', 0),
(2, 'Sample Investor Two', NULL, '+1-555-0131', 'investor2@example.test', '31 Investor Lane, Demo City', 'Demo', '2024-09-15', 'Profit-sharing', 300000.00, NULL, 'Pending',  'Example Bank', '****2222', 'Demo Nominee',   'Relative', 'High',   0),
(3, 'Demo Investor Three', NULL, '+1-555-0132', 'investor3@example.test', '32 Investor Lane, Demo City', 'Demo', '2025-01-10', 'Debt',           200000.00, NULL, 'Verified', 'Example Bank', '****3333', NULL,             NULL,       'Low',    0);

INSERT INTO `equity_details` (`id`, `investor_id`, `equity_percentage`, `share_price`, `total_shares`, `dividend_policy`, `voting_rights`, `resale_strategy`, `share_transfer`) VALUES
(1, 1, 12.50, 100.00, 5000, 'Annual dividend', 'Yes', 'Long-term hold', 'Allowed');

INSERT INTO `debt_details` (`id`, `investor_id`, `debt_duration`, `interest_rate`, `repayment_schedule`, `collateral`, `total_interest`) VALUES
(1, 3, '3 years', 9.50, 'Monthly', 'Fictional collateral', 57000.00);

INSERT INTO `investor_returns` (`id`, `investor_id`, `return_date`, `amount`, `return_type`, `notes`) VALUES
(1, 1, '2025-06-30', 60000.00, 'dividend',     'Demo annual dividend'),
(2, 3, '2025-09-30', 15833.00, 'interest',     'Demo quarterly interest'),
(3, 2, '2025-09-30', 22500.00, 'profit_share', 'Demo profit share');

-- -----------------------------------------------------------------------------
-- Safe account metadata (NO passwords / NO secrets)
-- -----------------------------------------------------------------------------
INSERT INTO `account_vault_metadata` (`id`, `user_id`, `category`, `provider`, `account_nickname`, `masked_identifier`, `reference_id`, `notes`, `last_verified_at`) VALUES
(1, 1, 'bank',   'Example Bank',   'Operating Account', '****4321', 'REF-DEMO-001', 'Fictional bank metadata.', '2025-09-01'),
(2, 1, 'broker', 'Demo Securities','Trading Account',   '****8765', 'REF-DEMO-002', 'Fictional broker metadata.', '2025-09-01'),
(3, 2, 'demat',  'Sample Depository','Demat Account',  '****2468', 'REF-DEMO-003', 'Fictional demat metadata.', '2025-08-15');

-- -----------------------------------------------------------------------------
-- Chart-of-accounts journal (double-entry, balanced)
-- Each entry: SUM(debit) = SUM(credit).
-- -----------------------------------------------------------------------------
INSERT INTO `journal_entries` (`id`, `entry_date`, `reference`, `memo`, `source_type`, `source_id`, `status`, `created_by`) VALUES
(1,  '2025-07-01', 'JE-0001', 'Opening balances',              'opening',    NULL, 'posted', 1),
(2,  '2025-07-05', 'JE-0002', 'Consulting income',             'income',     1,    'posted', 1),
(3,  '2025-08-10', 'JE-0003', 'Investment returns income',     'income',     2,    'posted', 1),
(4,  '2025-07-05', 'JE-0004', 'Office rent',                   'expense',    1,    'posted', 1),
(5,  '2025-07-28', 'JE-0005', 'Payroll',                       'expense',    2,    'posted', 1),
(6,  '2025-08-03', 'JE-0006', 'Utilities',                     'expense',    3,    'posted', 1),
(7,  '2025-07-02', 'JE-0007', 'Investor capital contribution', 'investment', NULL, 'posted', 1),
(8,  '2025-07-12', 'JE-0008', 'Purchase of stock investments', 'investment', 1,    'posted', 1),
(9,  '2025-07-20', 'JE-0009', 'Loan investment issued',        'investment', 2,    'posted', 1),
(10, '2025-08-01', 'JE-0010', 'Real estate acquisition',       'investment', 3,    'posted', 1),
(11, '2025-08-15', 'JE-0011', 'Business equity investment',    'investment', 4,    'posted', 1);

INSERT INTO `journal_lines` (`journal_entry_id`, `account_id`, `debit`, `credit`, `line_memo`) VALUES
-- 1 Opening: Debit Cash 2,000,000 / Credit Owner's Equity 2,000,000
(1, 1,  2000000.00, 0,          'Opening cash'),
(1, 11, 0,          2000000.00, 'Opening equity'),
-- 2 Consulting income: Debit Cash / Credit Operating Income
(2, 1,  250000.00,  0,          'Cash received'),
(2, 13, 0,          250000.00,  'Consulting income'),
-- 3 Investment returns: Debit Cash / Credit Investment Returns
(3, 1,  180000.00,  0,          'Cash received'),
(3, 14, 0,          180000.00,  'Investment returns'),
-- 4 Office rent: Debit Office Rent / Credit Cash
(4, 16, 45000.00,   0,          'Rent expense'),
(4, 1,  0,          45000.00,   'Cash paid'),
-- 5 Payroll: Debit Salaries / Credit Cash
(5, 17, 160000.00,  0,          'Payroll expense'),
(5, 1,  0,          160000.00,  'Cash paid'),
-- 6 Utilities: Debit Utilities / Credit Cash
(6, 18, 12500.00,   0,          'Utilities expense'),
(6, 1,  0,          12500.00,   'Cash paid'),
-- 7 Investor capital: Debit Cash / Credit Investor Capital
(7, 1,  500000.00,  0,          'Capital received'),
(7, 10, 0,          500000.00,  'Investor capital'),
-- 8 Stock purchase: Debit Investments-Stocks / Credit Cash
(8, 3,  300000.00,  0,          'Stock cost basis'),
(8, 1,  0,          300000.00,  'Cash paid'),
-- 9 Loan issued: Debit Investments-Loans / Credit Cash
(9, 5,  200000.00,  0,          'Loan principal'),
(9, 1,  0,          200000.00,  'Cash paid'),
-- 10 Real estate: Debit Investments-Real Estate / Credit Cash
(10, 6, 800000.00,  0,          'Property cost'),
(10, 1, 0,          800000.00,  'Cash paid'),
-- 11 Business equity: Debit Investments-Business / Credit Cash
(11, 4, 150000.00,  0,          'Business investment'),
(11, 1, 0,          150000.00,  'Cash paid');

-- -----------------------------------------------------------------------------
-- Audit trail (fictional)
-- -----------------------------------------------------------------------------
INSERT INTO `audit_log` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`) VALUES
(1, 'seed', 'system', NULL, 'Demo data seeded', '127.0.0.1');
