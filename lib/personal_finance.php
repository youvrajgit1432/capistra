<?php
declare(strict_types=1);

/**
 * Capistra - personal finance capabilities: budgets, recurring transactions,
 * goals, tags, reconciliation and net worth, plus configurable classification
 * options and deterministic (non-AI) financial insights.
 */

require_once dirname(__DIR__) . '/config/settings.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/financial_accounts.php';

// ---------------------------------------------------------------------------
// Pure calculations (unit-tested)
// ---------------------------------------------------------------------------

if (!function_exists('capistra_budget_usage')) {
    /**
     * @return array{remaining:float,percent:float,over:bool}
     */
    function capistra_budget_usage(float $budget, float $actual): array
    {
        $remaining = round($budget - $actual, 2);
        $percent   = $budget > 0 ? round(($actual / $budget) * 100, 2) : ($actual > 0 ? 100.0 : 0.0);
        return [
            'remaining' => $remaining,
            'percent'   => $percent,
            'over'      => $actual > $budget + 0.001,
        ];
    }
}

if (!function_exists('capistra_recurring_frequencies')) {
    function capistra_recurring_frequencies(): array
    {
        return ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'];
    }
}

if (!function_exists('capistra_recurring_next_date')) {
    /**
     * Advance a date by one frequency. Uses calendar-correct month arithmetic.
     */
    function capistra_recurring_next_date(string $frequency, string $from): string
    {
        $ts = strtotime($from);
        if ($ts === false) {
            throw new InvalidArgumentException('Invalid date.');
        }
        return match ($frequency) {
            'weekly'    => date('Y-m-d', strtotime('+7 days', $ts)),
            'quarterly' => capistra_add_months($from, 3),
            'yearly'    => capistra_add_months($from, 12),
            default     => capistra_add_months($from, 1),
        };
    }
}

if (!function_exists('capistra_add_months')) {
    /** Add whole months without the PHP "31 Feb" overflow bug. */
    function capistra_add_months(string $date, int $months): string
    {
        [$y, $m, $d] = array_map('intval', explode('-', substr($date, 0, 10)));
        $total = ($y * 12 + ($m - 1)) + $months;
        $ny = intdiv($total, 12);
        $nm = ($total % 12) + 1;
        $last = (int) date('t', mktime(0, 0, 0, $nm, 1, $ny));
        return sprintf('%04d-%02d-%02d', $ny, $nm, min($d, $last));
    }
}

if (!function_exists('capistra_goal_progress')) {
    /**
     * @return array{remaining:float,percent:float,months_left:int,required_monthly:float,achieved:bool}
     */
    function capistra_goal_progress(float $target, float $current, ?string $targetDate, ?string $today = null): array
    {
        $today = $today ?? date('Y-m-d');
        $remaining = round(max(0.0, $target - $current), 2);
        $percent = $target > 0 ? round(min(100.0, ($current / $target) * 100), 2) : 0.0;
        $months = 0;
        if ($targetDate !== null && $targetDate !== '') {
            [$ty, $tm] = array_map('intval', explode('-', substr($targetDate, 0, 10)));
            [$cy, $cm] = array_map('intval', explode('-', substr($today, 0, 10)));
            $months = max(0, ($ty * 12 + $tm) - ($cy * 12 + $cm));
        }
        $required = ($months > 0 && $remaining > 0) ? round($remaining / $months, 2) : ($remaining > 0 ? $remaining : 0.0);

        return [
            'remaining'        => $remaining,
            'percent'          => $percent,
            'months_left'      => $months,
            'required_monthly' => $required,
            'achieved'         => $target > 0 && $current >= $target - 0.001,
        ];
    }
}

if (!function_exists('capistra_net_worth_totals')) {
    /**
     * @param array<string,float> $assets      class => value
     * @param array<string,float> $liabilities class => value
     * @return array{assets:float,liabilities:float,net:float}
     */
    function capistra_net_worth_totals(array $assets, array $liabilities): array
    {
        $a = round(array_sum(array_map('floatval', $assets)), 2);
        $l = round(array_sum(array_map('floatval', $liabilities)), 2);
        return ['assets' => $a, 'liabilities' => $l, 'net' => round($a - $l, 2)];
    }
}

if (!function_exists('capistra_reconciliation_result')) {
    /**
     * @return array{difference:float,status:string}
     */
    function capistra_reconciliation_result(float $statement, float $system): array
    {
        $diff = round($statement - $system, 2);
        return [
            'difference' => $diff,
            'status'     => abs($diff) < 0.01 ? 'balanced' : 'discrepancy',
        ];
    }
}

// ---------------------------------------------------------------------------
// Budgets
// ---------------------------------------------------------------------------

if (!function_exists('capistra_budget_periods')) {
    function capistra_budgets(?int $year = null, ?int $month = null): array
    {
        $sql = 'SELECT * FROM budgets';
        $where = [];
        $params = [];
        if ($year !== null)  { $where[] = 'period_year = :y'; $params[':y'] = $year; }
        if ($month !== null) { $where[] = 'period_month = :m'; $params[':m'] = $month; }
        if ($where !== []) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY period_year DESC, period_month DESC';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        $budgets = $stmt->fetchAll();

        foreach ($budgets as &$b) {
            $b['items'] = capistra_budget_items((int) $b['id']);
            $b['total'] = array_sum(array_map(static fn($i) => (float) $i['amount'], $b['items']));
        }
        return $budgets;
    }
}

if (!function_exists('capistra_budget_items')) {
    function capistra_budget_items(int $budgetId): array
    {
        $stmt = capistra_pdo()->prepare(
            'SELECT bi.*, c.name AS category_name FROM budget_items bi
             JOIN transaction_categories c ON c.id = bi.category_id
             WHERE bi.budget_id = ? ORDER BY c.name'
        );
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_budget_save')) {
    /** Create or update a budget period with category limits. */
    function capistra_budget_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id  = (int) ($data['id'] ?? 0);
        $year  = (int) ($data['period_year'] ?? date('Y'));
        $month = (int) ($data['period_month'] ?? date('n'));
        $name  = trim((string) ($data['name'] ?? sprintf('Budget %04d-%02d', $year, $month)));

        if ($id > 0) {
            $pdo->prepare('UPDATE budgets SET name=:n, period_year=:y, period_month=:m, notes=:notes WHERE id=:id')
                ->execute([':n' => $name, ':y' => $year, ':m' => $month, ':notes' => $data['notes'] ?? null, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO budgets (name, period_year, period_month, notes)
                                   VALUES (:n, :y, :m, :notes)');
            $stmt->execute([':n' => $name, ':y' => $year, ':m' => $month, ':notes' => $data['notes'] ?? null]);
            $id = (int) $pdo->lastInsertId();
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $cid = (int) ($item['category_id'] ?? 0);
                $amt = round((float) ($item['amount'] ?? 0), 2);
                if ($cid <= 0) { continue; }
                if ($amt <= 0) {
                    $pdo->prepare('DELETE FROM budget_items WHERE budget_id = ? AND category_id = ?')->execute([$id, $cid]);
                    continue;
                }
                $pdo->prepare('INSERT INTO budget_items (budget_id, category_id, amount) VALUES (:b, :c, :a)
                               ON DUPLICATE KEY UPDATE amount = VALUES(amount)')
                    ->execute([':b' => $id, ':c' => $cid, ':a' => $amt]);
            }
        }

        return $id;
    }
}

if (!function_exists('capistra_budget_actuals')) {
    /** Actual expense per category for a given year/month. */
    function capistra_budget_actuals(int $year, int $month): array
    {
        $stmt = capistra_pdo()->prepare(
            "SELECT category, COALESCE(SUM(amount),0) AS actual
             FROM expenses WHERE YEAR(date) = ? AND MONTH(date) = ?
             GROUP BY category"
        );
        $stmt->execute([$year, $month]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(string) $row['category']] = (float) $row['actual'];
        }
        return $out;
    }
}

if (!function_exists('capistra_budget_report')) {
    /** Budget vs actual rows for a period. */
    function capistra_budget_report(int $budgetId): array
    {
        $pdo = capistra_pdo();
        $stmt = $pdo->prepare('SELECT * FROM budgets WHERE id = ? LIMIT 1');
        $stmt->execute([$budgetId]);
        $budget = $stmt->fetch();
        if (!$budget) {
            return ['budget' => null, 'rows' => [], 'totals' => []];
        }

        $actuals = capistra_budget_actuals((int) $budget['period_year'], (int) $budget['period_month']);
        $rows = [];
        $tb = 0.0; $ta = 0.0;
        foreach (capistra_budget_items($budgetId) as $item) {
            $budgetAmt = (float) $item['amount'];
            $actualAmt = $actuals[(string) $item['category_name']] ?? 0.0;
            $usage = capistra_budget_usage($budgetAmt, $actualAmt);
            $tb += $budgetAmt; $ta += $actualAmt;
            $rows[] = [
                'category' => (string) $item['category_name'],
                'budget'   => $budgetAmt,
                'actual'   => round($actualAmt, 2),
            ] + $usage;
        }
        foreach ($actuals as $cat => $actual) {
            if (!in_array($cat, array_column($rows, 'category'), true)) {
                $rows[] = ['category' => $cat, 'budget' => 0.0, 'actual' => round($actual, 2)] + capistra_budget_usage(0.0, $actual);
                $ta += $actual;
            }
        }

        return [
            'budget' => $budget,
            'rows'   => $rows,
            'totals' => capistra_budget_usage(round($tb, 2), round($ta, 2)) + ['budget' => round($tb, 2), 'actual' => round($ta, 2)],
        ];
    }
}

// ---------------------------------------------------------------------------
// Recurring transactions
// ---------------------------------------------------------------------------

if (!function_exists('capistra_recurring_rules')) {
    function capistra_recurring_rules(bool $activeOnly = false): array
    {
        $sql = 'SELECT r.*, c.name AS category_name, f.name AS account_name, t.name AS to_account_name
                FROM recurring_transactions r
                LEFT JOIN transaction_categories c ON c.id = r.category_id
                LEFT JOIN financial_accounts f ON f.id = r.financial_account_id
                LEFT JOIN financial_accounts t ON t.id = r.to_account_id';
        if ($activeOnly) { $sql .= ' WHERE r.is_active = 1'; }
        $sql .= ' ORDER BY r.next_date, r.id';
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_recurring_save')) {
    function capistra_recurring_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id  = (int) ($data['id'] ?? 0);
        $types = ['income', 'expense', 'transfer'];
        $type = in_array(($data['transaction_type'] ?? ''), $types, true) ? (string) $data['transaction_type'] : 'expense';
        $freqs = array_keys(capistra_recurring_frequencies());
        $freq = in_array(($data['frequency'] ?? ''), $freqs, true) ? (string) $data['frequency'] : 'monthly';
        $mode = ($data['mode'] ?? 'reminder') === 'auto' ? 'auto' : 'reminder';

        $fields = [
            ':type'   => $type,
            ':cat'    => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':acct'   => !empty($data['financial_account_id']) ? (int) $data['financial_account_id'] : null,
            ':to'     => !empty($data['to_account_id']) ? (int) $data['to_account_id'] : null,
            ':amount' => round((float) ($data['amount'] ?? 0), 2),
            ':freq'   => $freq,
            ':next'   => (string) ($data['next_date'] ?? date('Y-m-d')),
            ':end'    => ($data['end_date'] ?? '') !== '' ? $data['end_date'] : null,
            ':mode'   => $mode,
            ':active' => !empty($data['is_active']) ? 1 : 0,
            ':notes'  => $data['notes'] ?? null,
        ];

        if ($id > 0) {
            $fields[':id'] = $id;
            $pdo->prepare('UPDATE recurring_transactions SET
                    transaction_type=:type, category_id=:cat, financial_account_id=:acct, to_account_id=:to,
                    amount=:amount, frequency=:freq, next_date=:next, end_date=:end, mode=:mode,
                    is_active=:active, notes=:notes WHERE id=:id')->execute($fields);
            return $id;
        }

        $pdo->prepare('INSERT INTO recurring_transactions
                (transaction_type, category_id, financial_account_id, to_account_id, amount,
                 frequency, next_date, end_date, mode, is_active, notes)
            VALUES (:type, :cat, :acct, :to, :amount, :freq, :next, :end, :mode, :active, :notes)')->execute($fields);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_recurring_due')) {
    /** Active rules whose next date is on/before a reference date. */
    function capistra_recurring_due(?string $asOf = null): array
    {
        $asOf = $asOf ?? date('Y-m-d');
        $stmt = capistra_pdo()->prepare('SELECT * FROM recurring_transactions
            WHERE is_active = 1 AND next_date <= ? ORDER BY next_date');
        $stmt->execute([$asOf]);
        return $stmt->fetchAll();
    }
}

// ---------------------------------------------------------------------------
// Goals
// ---------------------------------------------------------------------------

if (!function_exists('capistra_goals')) {
    function capistra_goals(bool $activeOnly = false): array
    {
        $sql = 'SELECT g.*, f.name AS account_name FROM goals g
                LEFT JOIN financial_accounts f ON f.id = g.financial_account_id';
        if ($activeOnly) { $sql .= " WHERE g.status = 'active'"; }
        $sql .= ' ORDER BY g.target_date IS NULL, g.target_date, g.name';
        $goals = capistra_pdo()->query($sql)->fetchAll();
        foreach ($goals as &$g) {
            $g['progress'] = capistra_goal_progress((float) $g['target_amount'], (float) $g['current_amount'], $g['target_date']);
        }
        return $goals;
    }
}

if (!function_exists('capistra_goal_save')) {
    function capistra_goal_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id  = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Goal name is required.');
        }
        $statuses = ['active', 'achieved', 'paused', 'cancelled'];
        $status = in_array(($data['status'] ?? ''), $statuses, true) ? (string) $data['status'] : 'active';

        $fields = [
            ':name'    => $name,
            ':target'  => round((float) ($data['target_amount'] ?? 0), 2),
            ':current' => round((float) ($data['current_amount'] ?? 0), 2),
            ':date'    => ($data['target_date'] ?? '') !== '' ? $data['target_date'] : null,
            ':acct'    => !empty($data['financial_account_id']) ? (int) $data['financial_account_id'] : null,
            ':status'  => $status,
            ':notes'   => $data['notes'] ?? null,
        ];
        if ($id > 0) {
            $fields[':id'] = $id;
            $pdo->prepare('UPDATE goals SET name=:name, target_amount=:target, current_amount=:current,
                    target_date=:date, financial_account_id=:acct, status=:status, notes=:notes WHERE id=:id')->execute($fields);
            return $id;
        }
        $pdo->prepare('INSERT INTO goals (name, target_amount, current_amount, target_date, financial_account_id, status, notes)
                VALUES (:name, :target, :current, :date, :acct, :status, :notes)')->execute($fields);
        return (int) $pdo->lastInsertId();
    }
}

// ---------------------------------------------------------------------------
// Tags
// ---------------------------------------------------------------------------

if (!function_exists('capistra_tags')) {
    function capistra_tags(): array
    {
        return capistra_pdo()->query('SELECT * FROM tags ORDER BY name')->fetchAll();
    }
}

if (!function_exists('capistra_tag_save')) {
    function capistra_tag_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Tag name is required.');
        }
        if ($id > 0) {
            $pdo->prepare('UPDATE tags SET name=:n, slug=:s, color=:c WHERE id=:id')
                ->execute([':n' => $name, ':s' => capistra_slugify($name), ':c' => $data['color'] ?? null, ':id' => $id]);
            return $id;
        }
        $pdo->prepare('INSERT INTO tags (name, slug, color) VALUES (:n, :s, :c)')
            ->execute([':n' => $name, ':s' => capistra_slugify($name), ':c' => $data['color'] ?? null]);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_tag_transaction')) {
    function capistra_tag_transaction(string $type, int $transactionId, int $tagId): void
    {
        capistra_pdo()->prepare('INSERT IGNORE INTO transaction_tags (tag_id, transaction_type, transaction_id)
                                 VALUES (:t, :ty, :id)')
            ->execute([':t' => $tagId, ':ty' => $type, ':id' => $transactionId]);
    }
}

if (!function_exists('capistra_tags_for')) {
    function capistra_tags_for(string $type, int $transactionId): array
    {
        $stmt = capistra_pdo()->prepare('SELECT t.* FROM tags t
            JOIN transaction_tags tt ON tt.tag_id = t.id
            WHERE tt.transaction_type = ? AND tt.transaction_id = ? ORDER BY t.name');
        $stmt->execute([$type, $transactionId]);
        return $stmt->fetchAll();
    }
}

// ---------------------------------------------------------------------------
// Reconciliation
// ---------------------------------------------------------------------------

if (!function_exists('capistra_reconciliations')) {
    function capistra_reconciliations(int $limit = 100): array
    {
        $sql = 'SELECT r.*, f.name AS account_name FROM reconciliations r
                JOIN financial_accounts f ON f.id = r.financial_account_id
                ORDER BY r.statement_date DESC, r.id DESC LIMIT ' . max(1, min(1000, $limit));
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_reconcile_save')) {
    function capistra_reconcile_save(array $data): int
    {
        $pdo = capistra_pdo();
        $accountId = (int) ($data['financial_account_id'] ?? 0);
        $date = (string) ($data['statement_date'] ?? date('Y-m-d'));
        $statement = round((float) ($data['statement_balance'] ?? 0), 2);
        $system = capistra_financial_account_balance($accountId, $date) ?? 0.0;
        $result = capistra_reconciliation_result($statement, $system);

        $pdo->prepare('INSERT INTO reconciliations
                (financial_account_id, statement_date, statement_balance, system_balance, difference, status, notes)
            VALUES (:a, :d, :s, :sys, :diff, :status, :notes)
            ON DUPLICATE KEY UPDATE statement_balance=VALUES(statement_balance), system_balance=VALUES(system_balance),
                difference=VALUES(difference), status=VALUES(status), notes=VALUES(notes)')
            ->execute([
                ':a' => $accountId, ':d' => $date, ':s' => $statement, ':sys' => $system,
                ':diff' => $result['difference'], ':status' => $result['status'], ':notes' => $data['notes'] ?? null,
            ]);

        return (int) ($pdo->lastInsertId() ?: 0);
    }
}

// ---------------------------------------------------------------------------
// Net worth
// ---------------------------------------------------------------------------

if (!function_exists('capistra_net_worth_report')) {
    /**
     * Assets and liabilities derived from financial accounts and investments.
     *
     * @return array{assets:array<string,float>,liabilities:array<string,float>,total_assets:float,total_liabilities:float,net:float}
     */
    function capistra_net_worth_report(?string $asOf = null): array
    {
        $pdo = capistra_pdo();
        $assets = [];
        $liabilities = [];

        // Financial accounts (cash, bank, wallets, broker) vs credit/loan.
        foreach (capistra_financial_accounts(true) as $acc) {
            $balance = capistra_financial_account_balance((int) $acc['id'], $asOf);
            if ($balance === null) {
                $balance = (float) $acc['opening_balance'];
            }
            $label = (string) $acc['name'];
            if (in_array($acc['type'], ['credit_card', 'loan'], true)) {
                $liabilities[$label] = round(abs($balance), 2);
            } else {
                $assets[$label] = round($balance, 2);
            }
        }

        // Investments (latest locally known valuation).
        $inv = $pdo->query("SELECT investment_type, COALESCE(SUM(COALESCE(current_value, invested_amount)),0) AS v
                            FROM investments WHERE status <> 'closed' GROUP BY investment_type")->fetchAll();
        foreach ($inv as $row) {
            $label = 'Investments - ' . ucwords(str_replace('_', ' ', (string) $row['investment_type']));
            $assets[$label] = round((float) $row['v'], 2);
        }

        // Derive stock portfolio market value from transactions when available.
        require_once __DIR__ . '/portfolio.php';
        try {
            $summary = capistra_portfolio_summary($asOf);
            if ($summary['market_value'] > 0) {
                // Replace the position-based stock value with ledger-derived value.
                $assets['Investments - Stock Portfolio'] = (float) $summary['market_value'];
                if (isset($assets['Investments - Stock'])) {
                    unset($assets['Investments - Stock']);
                }
            }
        } catch (Throwable $e) {
            error_log('net worth portfolio: ' . $e->getMessage());
        }

        $totals = capistra_net_worth_totals($assets, $liabilities);
        return [
            'assets'            => $assets,
            'liabilities'       => $liabilities,
            'total_assets'      => $totals['assets'],
            'total_liabilities' => $totals['liabilities'],
            'net'               => $totals['net'],
        ];
    }
}

// ---------------------------------------------------------------------------
// Classification options (configurable enums)
// ---------------------------------------------------------------------------

if (!function_exists('capistra_classification_groups')) {
    function capistra_classification_groups(): array
    {
        return [
            'stock_term'     => 'Stock Investment Horizon',
            'business_type'  => 'Business Type',
            'investment_model' => 'Business Investment Model',
            'loan_type'      => 'Loan Type',
            'repayment_frequency' => 'Repayment Frequency',
            'collateral_type'=> 'Collateral Type',
            'property_type'  => 'Property Type',
            'ownership_type' => 'Ownership Type',
        ];
    }
}

if (!function_exists('capistra_classification_options')) {
    function capistra_classification_options(string $group, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM classification_options WHERE group_key = :g';
        if ($activeOnly) { $sql .= ' AND is_active = 1'; }
        $sql .= ' ORDER BY sort_order, label';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute([':g' => $group]);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_classification_save')) {
    function capistra_classification_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id = (int) ($data['id'] ?? 0);
        $group = (string) ($data['group_key'] ?? '');
        $label = trim((string) ($data['label'] ?? ''));
        if ($group === '' || $label === '') {
            throw new InvalidArgumentException('Group and label are required.');
        }
        if ($id > 0) {
            $pdo->prepare('UPDATE classification_options SET label=:l, slug=:s, is_active=:a, sort_order=:o WHERE id=:id')
                ->execute([':l' => $label, ':s' => capistra_slugify($label), ':a' => !empty($data['is_active']) ? 1 : 0, ':o' => (int) ($data['sort_order'] ?? 0), ':id' => $id]);
            return $id;
        }
        $pdo->prepare('INSERT INTO classification_options (group_key, label, slug, is_active, sort_order)
                VALUES (:g, :l, :s, :a, :o)')
            ->execute([':g' => $group, ':l' => $label, ':s' => capistra_slugify($label), ':a' => !empty($data['is_active']) ? 1 : 0, ':o' => (int) ($data['sort_order'] ?? 0)]);
        return (int) $pdo->lastInsertId();
    }
}

// ---------------------------------------------------------------------------
// Deterministic insights (rules, not "AI")
// ---------------------------------------------------------------------------

if (!function_exists('capistra_insights')) {
    /**
     * @return list<array{level:string,title:string,detail:string}>
     */
    function capistra_insights(): array
    {
        $pdo = capistra_pdo();
        $insights = [];
        $thisMonth = date('Y-m'); $lastMonth = date('Y-m', strtotime('first day of last month'));

        $cur = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE DATE_FORMAT(date,'%Y-%m')='" . $thisMonth . "'")->fetchColumn();
        $prev = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE DATE_FORMAT(date,'%Y-%m')='" . $lastMonth . "'")->fetchColumn();
        if ($prev > 0 && $cur > $prev) {
            $pct = round((($cur - $prev) / $prev) * 100, 1);
            if ($pct >= 10) {
                $insights[] = ['level' => 'warning', 'title' => 'Spending increased', 'detail' => "Expenses are up {$pct}% versus last month."];
            }
        }

        // Budget overruns for the current month.
        $stmt = $pdo->query("SELECT id FROM budgets WHERE period_year = YEAR(CURDATE()) AND period_month = MONTH(CURDATE()) LIMIT 1");
        $budgetId = $stmt->fetchColumn();
        if ($budgetId !== false) {
            foreach (capistra_budget_report((int) $budgetId)['rows'] as $row) {
                if ($row['over']) {
                    $insights[] = ['level' => 'error', 'title' => 'Budget exceeded', 'detail' => "{$row['category']} is over budget by " . capistra_currency($row['remaining'] * -1) . '.'];
                }
            }
        }

        $threshold = (float) capistra_setting('low_cash_threshold', '0');
        if ($threshold > 0) {
            foreach (capistra_financial_accounts(true) as $acc) {
                if (!in_array($acc['type'], ['cash', 'bank', 'e_wallet'], true)) { continue; }
                $bal = capistra_financial_account_balance((int) $acc['id']);
                if ($bal !== null && $bal < $threshold) {
                    $insights[] = ['level' => 'warning', 'title' => 'Low balance', 'detail' => "{$acc['name']} is below the configured cash-reserve target."];
                }
            }
        }

        // Upcoming recurring items.
        foreach (capistra_recurring_due(date('Y-m-d', strtotime('+7 days'))) as $rule) {
            $insights[] = ['level' => 'info', 'title' => 'Upcoming recurring', 'detail' => "{$rule['transaction_type']} of " . capistra_currency((float) $rule['amount']) . " due {$rule['next_date']}."];
        }

        return $insights;
    }
}
