<?php
declare(strict_types=1);

/**
 * Capistra - transfers between financial accounts.
 *
 * A transfer moves money between two wallets and is neither income nor expense.
 * It posts a balanced double-entry journal: debit destination, credit source.
 */

require_once __DIR__ . '/financial_accounts.php';
require_once __DIR__ . '/periods.php';
require_once dirname(__DIR__) . '/accounting/lib/ledger.php';

if (!function_exists('capistra_transfer_validate')) {
    /**
     * Pure validation of a transfer request (unit-testable).
     *
     * @throws InvalidArgumentException
     */
    function capistra_transfer_validate(int $fromAccountId, int $toAccountId, float $amount, ?string $date): void
    {
        if ($fromAccountId <= 0 || $toAccountId <= 0) {
            throw new InvalidArgumentException('Both source and destination accounts are required.');
        }
        if ($fromAccountId === $toAccountId) {
            throw new InvalidArgumentException('Source and destination accounts must be different.');
        }
        if (round($amount, 2) <= 0) {
            throw new InvalidArgumentException('Transfer amount must be greater than zero.');
        }
        if ($date === null || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException('A valid transfer date is required.');
        }
    }
}

if (!function_exists('capistra_post_transfer')) {
    /**
     * Record and post a transfer.
     *
     * @param array{from_account_id:int,to_account_id:int,transfer_date:string,amount:float,reference?:?string,notes?:?string} $data
     * @return int transfer id
     */
    function capistra_post_transfer(array $data): int
    {
        $pdo    = capistra_pdo();
        $from   = (int) $data['from_account_id'];
        $to     = (int) $data['to_account_id'];
        $amount = round((float) $data['amount'], 2);
        $date   = (string) $data['transfer_date'];

        capistra_transfer_validate($from, $to, $amount, $date);
        capistra_assert_period_open($date);

        $fromAcc = capistra_financial_account_get($from);
        $toAcc   = capistra_financial_account_get($to);
        if (!$fromAcc || !$toAcc) {
            throw new RuntimeException('One of the selected accounts no longer exists.');
        }
        if (empty($fromAcc['ledger_account_id']) || empty($toAcc['ledger_account_id'])) {
            throw new RuntimeException('Both accounts need a linked ledger account before transferring.');
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO transfers
                (from_account_id, to_account_id, transfer_date, amount, reference, notes)
                VALUES (:f, :t, :d, :a, :ref, :notes)');
            $stmt->execute([
                ':f'     => $from,
                ':t'     => $to,
                ':d'     => $date,
                ':a'     => $amount,
                ':ref'   => $data['reference'] ?? null,
                ':notes' => $data['notes'] ?? null,
            ]);
            $transferId = (int) $pdo->lastInsertId();

            $entryId = ledger_post_entry($pdo, [
                'entry_date'  => $date,
                'reference'   => $data['reference'] ?? ('TRF-' . $transferId),
                'memo'        => 'Transfer: ' . $fromAcc['name'] . ' → ' . $toAcc['name'],
                'source_type' => 'other',
                'source_id'   => $transferId,
            ], [
                ['account_id' => (int) $toAcc['ledger_account_id'],   'debit'  => $amount, 'credit' => 0,      'line_memo' => 'Transfer in'],
                ['account_id' => (int) $fromAcc['ledger_account_id'], 'debit'  => 0,       'credit' => $amount, 'line_memo' => 'Transfer out'],
            ]);

            $pdo->prepare('UPDATE transfers SET journal_entry_id = ? WHERE id = ?')->execute([$entryId, $transferId]);
            $pdo->commit();

            return $transferId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

if (!function_exists('capistra_transfers')) {
    /** @return list<array<string,mixed>> */
    function capistra_transfers(int $limit = 100): array
    {
        $sql = 'SELECT t.*, f.name AS from_name, ta.name AS to_name
                FROM transfers t
                JOIN financial_accounts f ON f.id = t.from_account_id
                JOIN financial_accounts ta ON ta.id = t.to_account_id
                ORDER BY t.transfer_date DESC, t.id DESC
                LIMIT ' . max(1, min(1000, $limit));
        return capistra_pdo()->query($sql)->fetchAll();
    }
}
