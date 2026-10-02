<?php
declare(strict_types=1);

/**
 * Capistra - market master data: exchanges, sectors, securities, prices.
 *
 * Replaces the hard-coded JavaScript company list. Relationships use IDs, so
 * renaming a sector never breaks a holding. Price history is append-only.
 */

require_once dirname(__DIR__) . '/config/settings.php';

if (!function_exists('capistra_security_types')) {
    function capistra_security_types(): array
    {
        return [
            'equity'       => 'Equity Share',
            'mutual_fund'  => 'Mutual Fund',
            'debenture'    => 'Debenture / Bond',
            'preference'   => 'Preference Share',
            'other'        => 'Other / Unit Scheme',
        ];
    }
}

if (!function_exists('capistra_listing_statuses')) {
    function capistra_listing_statuses(): array
    {
        return [
            'unverified' => 'Unverified',
            'listed'     => 'Listed',
            'suspended'  => 'Suspended',
            'delisted'   => 'Delisted',
        ];
    }
}

if (!function_exists('capistra_default_exchange_id')) {
    /** Get (or lazily create) the default NEPSE exchange. */
    function capistra_default_exchange_id(): int
    {
        $pdo = capistra_pdo();
        $id = $pdo->query("SELECT id FROM market_exchanges WHERE code = 'NEPSE' LIMIT 1")->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }
        $pdo->prepare("INSERT INTO market_exchanges (code, name, country, currency, is_active)
                       VALUES ('NEPSE', 'Nepal Stock Exchange', ?, ?, 1)")
            ->execute([(string) capistra_setting('nepse_country', 'Nepal'), capistra_base_currency()]);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_exchanges')) {
    function capistra_exchanges(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM market_exchanges' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY name';
        return capistra_pdo()->query($sql)->fetchAll();
    }
}

if (!function_exists('capistra_sectors')) {
    /** @return list<array<string,mixed>> */
    function capistra_sectors(?int $exchangeId = null, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM market_sectors';
        $where = [];
        $params = [];
        if ($exchangeId !== null) { $where[] = 'exchange_id = :e'; $params[':e'] = $exchangeId; }
        if ($activeOnly)          { $where[] = 'is_active = 1'; }
        if ($where !== []) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY sort_order, name';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_sector_save')) {
    function capistra_sector_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id  = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Sector name is required.');
        }
        $exchangeId = (int) ($data['exchange_id'] ?? capistra_default_exchange_id());
        $slug = capistra_slugify($name);
        $active = !empty($data['is_active']) ? 1 : 0;
        $sort = (int) ($data['sort_order'] ?? 0);

        if ($id > 0) {
            $pdo->prepare('UPDATE market_sectors SET exchange_id=:e, name=:n, slug=:s, is_active=:a, sort_order=:o WHERE id=:id')
                ->execute([':e' => $exchangeId, ':n' => $name, ':s' => $slug, ':a' => $active, ':o' => $sort, ':id' => $id]);
            return $id;
        }
        $pdo->prepare('INSERT INTO market_sectors (exchange_id, name, slug, is_active, sort_order)
                       VALUES (:e, :n, :s, :a, :o)')
            ->execute([':e' => $exchangeId, ':n' => $name, ':s' => $slug, ':a' => $active, ':o' => $sort]);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_securities')) {
    /**
     * @param array{sector_id?:int,q?:string,status?:string,active_only?:bool} $filter
     * @return list<array<string,mixed>>
     */
    function capistra_securities(array $filter = []): array
    {
        $sql = 'SELECT s.*, e.code AS exchange_code, e.name AS exchange_name,
                       sec.name AS sector_name, sec.slug AS sector_slug
                FROM securities s
                JOIN market_exchanges e ON e.id = s.exchange_id
                LEFT JOIN market_sectors sec ON sec.id = s.sector_id';
        $where = [];
        $params = [];
        if (!empty($filter['sector_id'])) {
            $where[] = 's.sector_id = :sector';
            $params[':sector'] = (int) $filter['sector_id'];
        }
        if (!empty($filter['status'])) {
            $where[] = 's.listing_status = :status';
            $params[':status'] = (string) $filter['status'];
        }
        if (!empty($filter['active_only'])) {
            $where[] = 's.is_active = 1';
        }
        if (!empty($filter['q'])) {
            $where[] = '(s.symbol LIKE :q1 OR s.company_name LIKE :q2)';
            $params[':q1'] = '%' . $filter['q'] . '%';
            $params[':q2'] = '%' . $filter['q'] . '%';
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY s.symbol';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_security_by_symbol')) {
    function capistra_security_by_symbol(string $symbol, ?int $exchangeId = null): ?array
    {
        $sql = 'SELECT * FROM securities WHERE symbol = :s';
        $params = [':s' => strtoupper(trim($symbol))];
        if ($exchangeId !== null) { $sql .= ' AND exchange_id = :e'; $params[':e'] = $exchangeId; }
        $sql .= ' LIMIT 1';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('capistra_security_save')) {
    /**
     * @param array<string,mixed> $data
     * @return int security id
     */
    function capistra_security_save(array $data): int
    {
        $pdo = capistra_pdo();
        $id  = (int) ($data['id'] ?? 0);
        $symbol = strtoupper(trim((string) ($data['symbol'] ?? '')));
        $name   = trim((string) ($data['company_name'] ?? ''));
        if ($symbol === '' || $name === '') {
            throw new InvalidArgumentException('Symbol and company name are required.');
        }

        $exchangeId = (int) ($data['exchange_id'] ?? capistra_default_exchange_id());
        $sectorId   = (int) ($data['sector_id'] ?? 0);
        $types      = array_keys(capistra_security_types());
        $type       = in_array(($data['security_type'] ?? ''), $types, true) ? (string) $data['security_type'] : 'equity';
        $statuses   = array_keys(capistra_listing_statuses());
        $status     = in_array(($data['listing_status'] ?? ''), $statuses, true) ? (string) $data['listing_status'] : 'unverified';

        $fields = [
            ':e'       => $exchangeId,
            ':sector'  => $sectorId > 0 ? $sectorId : null,
            ':symbol'  => $symbol,
            ':name'    => $name,
            ':type'    => $type,
            ':face'    => ($data['face_value'] ?? '') !== '' ? (float) $data['face_value'] : null,
            ':status'  => $status,
            ':listed'  => ($data['listing_date'] ?? '') !== '' ? $data['listing_date'] : null,
            ':delisted'=> ($data['delisted_date'] ?? '') !== '' ? $data['delisted_date'] : null,
            ':mdsym'   => ($data['market_data_symbol'] ?? '') !== '' ? $data['market_data_symbol'] : null,
            ':notes'   => ($data['notes'] ?? '') !== '' ? $data['notes'] : null,
            ':active'  => !empty($data['is_active']) ? 1 : 0,
        ];

        if ($id > 0) {
            $fields[':id'] = $id;
            $pdo->prepare('UPDATE securities SET
                    exchange_id=:e, sector_id=:sector, symbol=:symbol, company_name=:name,
                    security_type=:type, face_value=:face, listing_status=:status,
                    listing_date=:listed, delisted_date=:delisted, market_data_symbol=:mdsym,
                    notes=:notes, is_active=:active
                WHERE id=:id')->execute($fields);
            return $id;
        }

        $pdo->prepare('INSERT INTO securities
                (exchange_id, sector_id, symbol, company_name, security_type, face_value,
                 listing_status, listing_date, delisted_date, market_data_symbol, notes, is_active)
            VALUES (:e, :sector, :symbol, :name, :type, :face, :status, :listed, :delisted, :mdsym, :notes, :active)')
            ->execute($fields);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_record_price')) {
    /**
     * Append a price point (idempotent per security/date/source).
     *
     * @param array{security_id:int,price_date:string,close:float,open?:?float,high?:?float,low?:?float,volume?:?int,source?:string} $data
     */
    function capistra_record_price(array $data): void
    {
        $pdo = capistra_pdo();
        $stmt = $pdo->prepare('INSERT INTO security_prices
                (security_id, price_date, open, high, low, close, volume, source, fetched_at)
            VALUES (:sid, :d, :o, :h, :l, :c, :v, :src, NOW())
            ON DUPLICATE KEY UPDATE
                open = VALUES(open), high = VALUES(high), low = VALUES(low),
                close = VALUES(close), volume = VALUES(volume), fetched_at = NOW()');
        $stmt->execute([
            ':sid' => (int) $data['security_id'],
            ':d'   => (string) $data['price_date'],
            ':o'   => isset($data['open']) ? (float) $data['open'] : null,
            ':h'   => isset($data['high']) ? (float) $data['high'] : null,
            ':l'   => isset($data['low']) ? (float) $data['low'] : null,
            ':c'   => (float) $data['close'],
            ':v'   => isset($data['volume']) ? (int) $data['volume'] : null,
            ':src' => (string) ($data['source'] ?? 'manual'),
        ]);
    }
}

if (!function_exists('capistra_latest_price')) {
    /** Latest close for a security as of a date (inclusive), or null. */
    function capistra_latest_price(int $securityId, ?string $asOf = null): ?array
    {
        $sql = 'SELECT * FROM security_prices WHERE security_id = :sid';
        $params = [':sid' => $securityId];
        if ($asOf !== null) {
            $sql .= ' AND price_date <= :d';
            $params[':d'] = $asOf;
        }
        $sql .= ' ORDER BY price_date DESC, id DESC LIMIT 1';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

if (!function_exists('capistra_price_history')) {
    function capistra_price_history(int $securityId, ?string $from = null, ?string $to = null): array
    {
        $sql = 'SELECT * FROM security_prices WHERE security_id = :sid';
        $params = [':sid' => $securityId];
        if ($from !== null) { $sql .= ' AND price_date >= :from'; $params[':from'] = $from; }
        if ($to !== null)   { $sql .= ' AND price_date <= :to';   $params[':to'] = $to; }
        $sql .= ' ORDER BY price_date ASC';
        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
