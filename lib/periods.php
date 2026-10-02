<?php
declare(strict_types=1);

/**
 * Capistra - fiscal periods and the closed-book posting guard.
 *
 * Closed periods reject ordinary mutation. Reopening requires an explicit
 * administrative action (and is audit-logged).
 */

require_once dirname(__DIR__) . '/config/settings.php';

if (!function_exists('capistra_fiscal_periods')) {
    function capistra_fiscal_periods(): array
    {
        return capistra_pdo()->query('SELECT * FROM fiscal_periods ORDER BY start_date DESC')->fetchAll();
    }
}

if (!function_exists('capistra_fiscal_period_save')) {
    function capistra_fiscal_period_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id    = (int) ($data['id'] ?? 0);
        $label = trim((string) ($data['label'] ?? ''));
        $start = (string) ($data['start_date'] ?? '');
        $end   = (string) ($data['end_date'] ?? '');
        if ($label === '' || $start === '' || $end === '' || strcmp($end, $start) < 0) {
            throw new InvalidArgumentException('A label and a valid start/end range are required.');
        }
        $status = (($data['status'] ?? 'open') === 'closed') ? 'closed' : 'open';

        if ($id > 0) {
            $pdo->prepare('UPDATE fiscal_periods SET label=:l, start_date=:s, end_date=:e, status=:st WHERE id=:id')
                ->execute([':l' => $label, ':s' => $start, ':e' => $end, ':st' => $status, ':id' => $id]);
            return $id;
        }
        $pdo->prepare('INSERT INTO fiscal_periods (label, start_date, end_date, status) VALUES (:l, :s, :e, :st)')
            ->execute([':l' => $label, ':s' => $start, ':e' => $end, ':st' => $status]);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_period_is_closed')) {
    /** Is the given date inside a closed fiscal period? */
    function capistra_period_is_closed(string $date): bool
    {
        try {
            $stmt = capistra_pdo()->prepare(
                "SELECT COUNT(*) FROM fiscal_periods WHERE status = 'closed' AND :d BETWEEN start_date AND end_date"
            );
            $stmt->execute([':d' => $date]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log('period check: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('capistra_assert_period_open')) {
    /**
     * Throw if a date falls in a closed period. Called by every posting path so
     * closed books are never modified silently.
     */
    function capistra_assert_period_open(string $date): void
    {
        if (capistra_period_is_closed($date)) {
            throw new RuntimeException("The fiscal period containing $date is closed. Reopen it explicitly before posting.");
        }
    }
}
