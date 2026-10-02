<?php
declare(strict_types=1);

/**
 * Capistra - financial accounts (real-world money containers).
 *
 * A financial account is a wallet/bank/broker/card and is distinct from the
 * chart of accounts. Each financial account links to a ledger account so that
 * money movement posts correctly. Never store online-banking credentials here.
 */

require_once dirname(__DIR__) . '/config/settings.php';

if (!function_exists('capistra_account_types')) {
    /** @return array<string,string> value => label */
    function capistra_account_types(): array
    {
        return [
            'cash'        => 'Cash',
            'bank'        => 'Bank',
            'e_wallet'    => 'E-Wallet',
            'broker'      => 'Broker / Demat',
            'credit_card' => 'Credit Card',
            'loan'        => 'Loan / Credit Line',
            'other'       => 'Other',
        ];
    }
}

if (!function_exists('capistra_financial_accounts')) {
    /**
     * @return list<array<string,mixed>>
     */
    function capistra_financial_accounts(bool $activeOnly = false): array
    {
        $sql = 'SELECT f.*, a.code AS ledger_code, a.name AS ledger_name
                FROM financial_accounts f
                LEFT JOIN accounts a ON a.id = f.ledger_account_id';
        if ($activeOnly) {
            $sql .= ' WHERE f.is_active = 1';
        }
        $sql .= ' ORDER BY f.is_active DESC, f.name';
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_financial_account_get')) {
    function capistra_financial_account_get(int $id): ?array
    {
        $stmt = capistra_pdo()->prepare('SELECT * FROM financial_accounts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('capistra_financial_account_save')) {
    /**
     * @param array<string,mixed> $data
     * @return int account id
     */
    function capistra_financial_account_save(array $data): int
    {
        $pdo  = capistra_pdo();
        $id   = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Account name is required.');
        }

        $types = array_keys(capistra_account_types());
        $type  = in_array(($data['type'] ?? ''), $types, true) ? (string) $data['type'] : 'cash';
        $ledger = (int) ($data['ledger_account_id'] ?? 0);

        $fields = [
            ':name'    => $name,
            ':type'    => $type,
            ':inst'    => ($data['institution'] ?? '') !== '' ? $data['institution'] : null,
            ':curr'    => strtoupper((string) ($data['currency'] ?? capistra_base_currency())),
            ':open'    => round((float) ($data['opening_balance'] ?? 0), 2),
            ':masked'  => ($data['masked_reference'] ?? '') !== '' ? $data['masked_reference'] : null,
            ':ledger'  => $ledger > 0 ? $ledger : null,
            ':active'  => !empty($data['is_active']) ? 1 : 0,
            ':notes'   => ($data['notes'] ?? '') !== '' ? $data['notes'] : null,
        ];

        if ($id > 0) {
            $fields[':id'] = $id;
            $pdo->prepare('UPDATE financial_accounts SET
                    name = :name, type = :type, institution = :inst, currency = :curr,
                    opening_balance = :open, masked_reference = :masked,
                    ledger_account_id = :ledger, is_active = :active, notes = :notes
                WHERE id = :id')->execute($fields);
            return $id;
        }

        $pdo->prepare('INSERT INTO financial_accounts
                (name, type, institution, currency, opening_balance, masked_reference, ledger_account_id, is_active, notes)
            VALUES (:name, :type, :inst, :curr, :open, :masked, :ledger, :active, :notes)')->execute($fields);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_financial_account_balance')) {
    /**
     * Current balance of a financial account = opening + posted ledger movement
     * of its linked ledger account. Returns null if the account has no ledger link.
     */
    function capistra_financial_account_balance(int $id, ?string $asOf = null): ?float
    {
        $account = capistra_financial_account_get($id);
        if (!$account || empty($account['ledger_account_id'])) {
            return null;
        }

        $sql = 'SELECT COALESCE(SUM(l.debit - l.credit),0)
                FROM journal_lines l
                JOIN journal_entries e ON e.id = l.journal_entry_id
                WHERE l.account_id = :aid AND e.status = \'posted\'';
        $params = [':aid' => (int) $account['ledger_account_id']];
        if ($asOf !== null) {
            $sql .= ' AND e.entry_date <= :asof';
            $params[':asof'] = $asOf;
        }
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        $movement = (float) $stmt->fetchColumn();

        // Liability-style accounts (credit cards / loans) reduce a positive balance.
        $opening = (float) $account['opening_balance'];
        if (in_array($account['type'], ['credit_card', 'loan'], true)) {
            $opening = -$opening;
        }

        return round($opening + $movement, 2);
    }
}

if (!function_exists('capistra_financial_account_default')) {
    /** The first active cash/bank account, used as a fallback selector value. */
    function capistra_financial_account_default(): ?int
    {
        $row = capistra_pdo()->query(
            'SELECT id FROM financial_accounts WHERE is_active = 1 ORDER BY (type = \'cash\') DESC, id LIMIT 1'
        )->fetchColumn();
        return $row !== false ? (int) $row : null;
    }
}
