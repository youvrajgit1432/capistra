<?php
declare(strict_types=1);

/**
 * Capistra - transaction-based stock portfolio accounting.
 *
 * The legacy model stored a single position (`invested_amount`), which cannot
 * represent buys, sells, bonuses, splits or dividends. This library stores an
 * append-only `stock_transactions` ledger and derives every position from it.
 *
 * All position maths lives in pure functions so it is fully unit-testable.
 */

require_once dirname(__DIR__) . '/config/settings.php';
require_once __DIR__ . '/nepse.php';

if (!function_exists('capistra_stock_transaction_types')) {
    /** @return array<string,string> value => label */
    function capistra_stock_transaction_types(): array
    {
        return [
            'BUY'              => 'Buy',
            'SELL'             => 'Sell',
            'IPO'              => 'IPO Allotment',
            'FPO'              => 'FPO Allotment',
            'RIGHT_BUY'        => 'Rights Purchase',
            'BONUS'            => 'Bonus Shares',
            'CASH_DIVIDEND'    => 'Cash Dividend',
            'STOCK_DIVIDEND'   => 'Stock Dividend',
            'SPLIT'            => 'Stock Split',
            'MERGER_ADJUSTMENT'=> 'Merger Adjustment',
            'MANUAL_ADJUSTMENT'=> 'Manual Adjustment',
        ];
    }
}

if (!function_exists('capistra_stock_units_delta')) {
    /**
     * Signed unit movement for a transaction type.
     * SPLIT / CONSOLIDATION are handled via ratio, not delta.
     */
    function capistra_stock_units_delta(string $type, float $units): float
    {
        return match ($type) {
            'SELL' => -abs($units),
            'SPLIT' => 0.0, // ratio-based
            default => abs($units),
        };
    }
}

if (!function_exists('capistra_cash_amount')) {
    /**
     * Net cash for a transaction. Positive = money spent (buys), negative =
     * money received (sells/dividends).
     */
    function capistra_cash_amount(array $t): float
    {
        $units = abs((float) ($t['units'] ?? 0));
        $price = (float) ($t['price_per_unit'] ?? 0);
        $fees  = (float) ($t['fees'] ?? 0);
        $tax   = (float) ($t['tax'] ?? 0);
        $gross = (float) ($t['gross_amount'] ?? 0);
        if ($gross == 0.0) {
            $gross = $units * $price;
        }

        return match ((string) $t['transaction_type']) {
            'SELL' => -($gross - $fees - $tax),
            'CASH_DIVIDEND' => -($gross - $fees - $tax),
            default => ($gross + $fees + $tax),
        };
    }
}

if (!function_exists('capistra_position_from_transactions')) {
    /**
     * Derive a position from an ordered (or unordered) list of transactions.
     *
     * Returns:
     *   units          units currently held
     *   total_cost     cost basis of units still held
     *   average_cost   total_cost / units
     *   realized_gain  profit/loss on sold units (fees & tax deducted)
     *   dividends      cash dividends received
     *   invested       total cash spent on units (buys)
     *   proceeds       total cash received from sells
     *
     * Bonuses and stock dividends increase units without changing cost basis
     * (diluting average cost). Splits/consolidations adjust units by ratio
     * without changing cost basis. Nothing is double-counted.
     *
     * @param list<array<string,mixed>> $transactions
     * @return array<string,float>
     */
    function capistra_position_from_transactions(array $transactions): array
    {
        // Stable chronological ordering.
        usort($transactions, static function (array $a, array $b): int {
            return strcmp((string) ($a['transaction_date'] ?? ''), (string) ($b['transaction_date'] ?? ''))
                ?: ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0));
        });

        $units = 0.0;
        $totalCost = 0.0;
        $realized = 0.0;
        $dividends = 0.0;
        $invested = 0.0;
        $proceeds = 0.0;

        foreach ($transactions as $t) {
            $type = (string) ($t['transaction_type'] ?? 'BUY');
            $u     = abs((float) ($t['units'] ?? 0));
            $fees  = (float) ($t['fees'] ?? 0);
            $tax   = (float) ($t['tax'] ?? 0);

            switch ($type) {
                case 'SELL':
                    $avg = $units > 0 ? $totalCost / $units : 0.0;
                    $soldUnits = min($u, $units);
                    $costOut = $avg * $soldUnits;
                    $proceedsNet = capistra_cash_amount($t) * -1.0; // positive
                    $realized += $proceedsNet - $costOut;
                    $proceeds += $proceedsNet;
                    $units -= $soldUnits;
                    $totalCost -= $costOut;
                    break;

                case 'CASH_DIVIDEND':
                    $dividends += capistra_cash_amount($t) * -1.0;
                    break;

                case 'BONUS':
                case 'STOCK_DIVIDEND':
                case 'MERGER_ADJUSTMENT':
                    $units += $u; // no cost basis change
                    break;

                case 'SPLIT':
                    $from = (float) ($t['ratio_from'] ?? 0);
                    $to   = (float) ($t['ratio_to'] ?? 0);
                    if ($from > 0 && $to > 0) {
                        $units = $units * ($to / $from);
                    }
                    break;

                case 'MANUAL_ADJUSTMENT':
                    // A manual adjustment may add or remove units.
                    $delta = (float) ($t['units'] ?? 0);
                    if ($delta < 0) {
                        $avg = $units > 0 ? $totalCost / $units : 0.0;
                        $totalCost += $avg * $delta; // delta negative reduces cost
                    }
                    $units += $delta;
                    break;

                case 'BUY':
                case 'IPO':
                case 'FPO':
                case 'RIGHT_BUY':
                default:
                    $spent = capistra_cash_amount($t);
                    $units += $u;
                    $totalCost += $spent;
                    $invested += $spent;
                    break;
            }
        }

        $units = round($units, 4);
        $totalCost = round($totalCost, 2);

        return [
            'units'        => $units,
            'total_cost'   => $totalCost,
            'average_cost' => $units > 0.0001 ? round($totalCost / $units, 4) : 0.0,
            'realized_gain'=> round($realized, 2),
            'dividends'    => round($dividends, 2),
            'invested'     => round($invested, 2),
            'proceeds'     => round($proceeds, 2),
        ];
    }
}

if (!function_exists('capistra_position_with_market')) {
    /**
     * Combine a derived position with a latest market price.
     *
     * @return array<string,float>
     */
    function capistra_position_with_market(array $position, ?float $latestPrice): array
    {
        $units  = (float) $position['units'];
        $cost   = (float) $position['total_cost'];
        $market = $latestPrice !== null ? round($units * $latestPrice, 2) : $cost;

        return $position + [
            'latest_price'  => $latestPrice !== null ? round($latestPrice, 2) : null,
            'market_value'  => $market,
            'unrealized'    => round($market - $cost, 2),
            'total_return'  => round(($market - $cost) + (float) $position['realized_gain'] + (float) $position['dividends'], 2),
        ];
    }
}

// ---------------------------------------------------------------------------
// Database-backed helpers
// ---------------------------------------------------------------------------

if (!function_exists('capistra_portfolios')) {
    function capistra_portfolios(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM portfolios' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY name';
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_stock_transactions')) {
    /**
     * @return list<array<string,mixed>>
     */
    function capistra_stock_transactions(?int $securityId = null, ?int $portfolioId = null, int $limit = 500): array
    {
        $sql = 'SELECT t.*, s.symbol, s.company_name, p.name AS portfolio_name,
                       f.name AS account_name
                FROM stock_transactions t
                JOIN securities s ON s.id = t.security_id
                LEFT JOIN portfolios p ON p.id = t.portfolio_id
                LEFT JOIN financial_accounts f ON f.id = t.financial_account_id';
        $where = [];
        $params = [];
        if ($securityId !== null) { $where[] = 't.security_id = :sid'; $params[':sid'] = $securityId; }
        if ($portfolioId !== null) { $where[] = 't.portfolio_id = :pid'; $params[':pid'] = $portfolioId; }
        if ($where !== []) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY t.transaction_date DESC, t.id DESC LIMIT ' . max(1, min(5000, $limit));
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_add_stock_transaction')) {
    /**
     * Append a stock transaction. Never overwrites price history and never
     * mutates existing rows (an audit trail, not a position store).
     *
     * @param array<string,mixed> $data
     * @return int transaction id
     */
    function capistra_add_stock_transaction(array $data): int
    {
        $pdo = capistra_pdo();
        $types = array_keys(capistra_stock_transaction_types());
        $type = in_array(($data['transaction_type'] ?? ''), $types, true) ? (string) $data['transaction_type'] : 'BUY';

        $units = (float) ($data['units'] ?? 0);
        $price = (float) ($data['price_per_unit'] ?? 0);
        $fees  = (float) ($data['fees'] ?? 0);
        $tax   = (float) ($data['tax'] ?? 0);
        $gross = (float) ($data['gross_amount'] ?? 0);
        if ($gross == 0.0) {
            $gross = $units * $price;
        }
        $net = (float) ($data['net_amount'] ?? 0);
        if ($net == 0.0) {
            $net = capistra_cash_amount([
                'transaction_type' => $type, 'units' => $units, 'price_per_unit' => $price,
                'fees' => $fees, 'tax' => $tax, 'gross_amount' => $gross,
            ]);
        }

        $stmt = $pdo->prepare('INSERT INTO stock_transactions
            (portfolio_id, security_id, transaction_type, transaction_date, units, price_per_unit,
             gross_amount, fees, tax, net_amount, financial_account_id, reference, notes, ratio_from, ratio_to)
            VALUES (:p, :s, :t, :d, :u, :price, :gross, :fees, :tax, :net, :acct, :ref, :notes, :rf, :rt)');
        $stmt->execute([
            ':p'     => !empty($data['portfolio_id']) ? (int) $data['portfolio_id'] : null,
            ':s'     => (int) $data['security_id'],
            ':t'     => $type,
            ':d'     => (string) $data['transaction_date'],
            ':u'     => round($units, 4),
            ':price' => round($price, 4),
            ':gross' => round($gross, 2),
            ':fees'  => round($fees, 2),
            ':tax'   => round($tax, 2),
            ':net'   => round($net, 2),
            ':acct'  => !empty($data['financial_account_id']) ? (int) $data['financial_account_id'] : null,
            ':ref'   => $data['reference'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':rf'    => isset($data['ratio_from']) && $data['ratio_from'] !== '' ? (float) $data['ratio_from'] : null,
            ':rt'    => isset($data['ratio_to']) && $data['ratio_to'] !== '' ? (float) $data['ratio_to'] : null,
        ]);

        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_stock_positions')) {
    /**
     * Derive current positions for every security that has transactions.
     * Groups by security (optionally by portfolio) and attaches latest price.
     *
     * @return list<array<string,mixed>>
     */
    function capistra_stock_positions(?string $asOf = null): array
    {
        $pdo = capistra_pdo();
        $rows = $pdo->query('SELECT t.*, s.symbol, s.company_name, sec.name AS sector_name
                             FROM stock_transactions t
                             JOIN securities s ON s.id = t.security_id
                             LEFT JOIN market_sectors sec ON sec.id = s.sector_id
                             ORDER BY t.transaction_date, t.id')->fetchAll();

        $bySecurity = [];
        foreach ($rows as $row) {
            $bySecurity[(int) $row['security_id']]['meta'] = [
                'security_id'  => (int) $row['security_id'],
                'symbol'       => (string) $row['symbol'],
                'company_name' => (string) $row['company_name'],
                'sector_name'  => (string) ($row['sector_name'] ?? ''),
            ];
            $bySecurity[(int) $row['security_id']]['txns'][] = $row;
        }

        $positions = [];
        foreach ($bySecurity as $sid => $group) {
            $position = capistra_position_from_transactions($group['txns']);
            $latest = capistra_latest_price($sid, $asOf);
            $withMarket = capistra_position_with_market($position, $latest ? (float) $latest['close'] : null);
            $positions[] = $group['meta'] + $withMarket;
        }

        usort($positions, static fn($a, $b) => $b['market_value'] <=> $a['market_value']);
        return $positions;
    }
}

if (!function_exists('capistra_portfolio_summary')) {
    /**
     * @return array<string,mixed>
     */
    function capistra_portfolio_summary(?string $asOf = null): array
    {
        $positions = capistra_stock_positions($asOf);
        $totals = ['cost' => 0.0, 'market' => 0.0, 'unrealized' => 0.0, 'realized' => 0.0, 'dividends' => 0.0];
        $bySector = [];
        foreach ($positions as $p) {
            if ((float) $p['units'] <= 0.0001) { continue; }
            $totals['cost']       += (float) $p['total_cost'];
            $totals['market']     += (float) $p['market_value'];
            $totals['unrealized'] += (float) $p['unrealized'];
            $totals['realized']   += (float) $p['realized_gain'];
            $totals['dividends']  += (float) $p['dividends'];
            $sector = $p['sector_name'] !== '' ? $p['sector_name'] : 'Unclassified';
            $bySector[$sector] = ($bySector[$sector] ?? 0.0) + (float) $p['market_value'];
        }
        arsort($bySector);

        return [
            'positions'       => $positions,
            'total_cost'      => round($totals['cost'], 2),
            'market_value'    => round($totals['market'], 2),
            'unrealized'      => round($totals['unrealized'], 2),
            'realized'        => round($totals['realized'], 2),
            'dividends'       => round($totals['dividends'], 2),
            'total_return'    => round($totals['unrealized'] + $totals['realized'] + $totals['dividends'], 2),
            'allocation_sector'=> $bySector,
        ];
    }
}

if (!function_exists('capistra_corporate_action_types')) {
    function capistra_corporate_action_types(): array
    {
        return [
            'cash_dividend' => 'Cash Dividend',
            'bonus'         => 'Bonus Shares',
            'rights'        => 'Rights Issue',
            'split'         => 'Stock Split',
            'consolidation' => 'Reverse Split / Consolidation',
            'merger'        => 'Merger Adjustment',
            'other'         => 'Other Adjustment',
        ];
    }
}

if (!function_exists('capistra_apply_corporate_action')) {
    /**
     * Record a corporate action and materialise it as a stock transaction so
     * holdings adjust correctly. Returns the corporate action id.
     *
     * @param array<string,mixed> $data
     */
    function capistra_apply_corporate_action(array $data): int
    {
        $pdo  = capistra_pdo();
        $type = (string) ($data['action_type'] ?? 'other');
        $sid  = (int) ($data['security_id'] ?? 0);
        $date = (string) ($data['action_date'] ?? date('Y-m-d'));
        if ($sid <= 0) {
            throw new InvalidArgumentException('A security is required.');
        }

        $txnData = [
            'security_id'      => $sid,
            'portfolio_id'     => $data['portfolio_id'] ?? null,
            'transaction_date' => $date,
            'reference'        => 'CA-' . $type,
            'notes'            => (string) ($data['notes'] ?? ''),
        ];

        switch ($type) {
            case 'cash_dividend':
                $txnData += [
                    'transaction_type' => 'CASH_DIVIDEND',
                    'units'            => 0,
                    'price_per_unit'   => 0,
                    'gross_amount'     => (float) ($data['amount'] ?? 0),
                ];
                break;
            case 'bonus':
            case 'rights':
                $txnData += [
                    'transaction_type' => $type === 'bonus' ? 'BONUS' : 'RIGHT_BUY',
                    'units'            => (float) ($data['units'] ?? 0),
                    'price_per_unit'   => (float) ($data['price_per_unit'] ?? 0),
                ];
                break;
            case 'split':
            case 'consolidation':
                $txnData += [
                    'transaction_type' => 'SPLIT',
                    'units'            => 0,
                    'ratio_from'       => (float) ($data['ratio_from'] ?? 1),
                    'ratio_to'         => (float) ($data['ratio_to'] ?? 1),
                ];
                break;
            case 'merger':
                $txnData += [
                    'transaction_type' => 'MERGER_ADJUSTMENT',
                    'units'            => (float) ($data['units'] ?? 0),
                ];
                break;
            default:
                $txnData += [
                    'transaction_type' => 'MANUAL_ADJUSTMENT',
                    'units'            => (float) ($data['units'] ?? 0),
                ];
        }

        $txnId = capistra_add_stock_transaction($txnData);

        $stmt = $pdo->prepare('INSERT INTO corporate_actions
            (security_id, portfolio_id, action_type, action_date, ratio_from, ratio_to,
             dividend_per_share, units_basis, amount, stock_transaction_id, notes)
            VALUES (:s, :p, :t, :d, :rf, :rt, :dps, :ub, :amt, :txn, :notes)');
        $stmt->execute([
            ':s'    => $sid,
            ':p'    => !empty($data['portfolio_id']) ? (int) $data['portfolio_id'] : null,
            ':t'    => $type,
            ':d'    => $date,
            ':rf'   => isset($data['ratio_from']) && $data['ratio_from'] !== '' ? (float) $data['ratio_from'] : null,
            ':rt'   => isset($data['ratio_to']) && $data['ratio_to'] !== '' ? (float) $data['ratio_to'] : null,
            ':dps'  => isset($data['dividend_per_share']) && $data['dividend_per_share'] !== '' ? (float) $data['dividend_per_share'] : null,
            ':ub'   => isset($data['units_basis']) && $data['units_basis'] !== '' ? (float) $data['units_basis'] : null,
            ':amt'  => isset($data['amount']) && $data['amount'] !== '' ? (float) $data['amount'] : null,
            ':txn'  => $txnId,
            ':notes'=> (string) ($data['notes'] ?? ''),
        ]);

        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_corporate_actions')) {
    function capistra_corporate_actions(int $limit = 200): array
    {
        $sql = 'SELECT ca.*, s.symbol, s.company_name
                FROM corporate_actions ca
                JOIN securities s ON s.id = ca.security_id
                ORDER BY ca.action_date DESC, ca.id DESC
                LIMIT ' . max(1, min(1000, $limit));
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_fee_rules')) {
    function capistra_fee_rules(?string $context = null, bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM fee_rules';
        $where = [];
        $params = [];
        if ($context !== null) { $where[] = 'transaction_context = :c'; $params[':c'] = $context; }
        if ($activeOnly)       { $where[] = 'is_active = 1'; }
        if ($where !== []) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY name';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_compute_fees')) {
    /**
     * Pure fee computation over effective-dated rules.
     *
     * @param list<array<string,mixed>> $rules
     * @return array{fees:float,total:float,breakdown:list<array{name:string,amount:float}>}
     */
    function capistra_compute_fees(array $rules, float $amount, string $date): array
    {
        $fees = 0.0;
        $breakdown = [];
        foreach ($rules as $rule) {
            $from = $rule['effective_from'] ?? null;
            $to   = $rule['effective_to'] ?? null;
            if ($from !== null && $from !== '' && strcmp($date, (string) $from) < 0) { continue; }
            if ($to !== null && $to !== '' && strcmp($date, (string) $to) > 0) { continue; }
            if (empty($rule['is_active'])) { continue; }

            if (($rule['calculation_type'] ?? 'percentage') === 'fixed') {
                $value = (float) ($rule['fixed_amount'] ?? 0);
            } else {
                $value = $amount * ((float) ($rule['rate'] ?? 0) / 100.0);
            }
            $min = isset($rule['min_amount']) && $rule['min_amount'] !== null ? (float) $rule['min_amount'] : null;
            $max = isset($rule['max_amount']) && $rule['max_amount'] !== null ? (float) $rule['max_amount'] : null;
            if ($min !== null) { $value = max($value, $min); }
            if ($max !== null) { $value = min($value, $max); }

            $value = round($value, 2);
            $fees += $value;
            $breakdown[] = ['name' => (string) $rule['name'], 'amount' => $value];
        }

        return ['fees' => round($fees, 2), 'total' => round($fees, 2), 'breakdown' => $breakdown];
    }
}
