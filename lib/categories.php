<?php
declare(strict_types=1);

/**
 * Capistra - transaction category master.
 *
 * Categories are rows in `transaction_categories`; each income/expense
 * category is mapped to a chart-of-accounts ledger account so ledger posting is
 * data-driven instead of hard-coded name→code maps.
 */

require_once dirname(__DIR__) . '/config/settings.php';

if (!function_exists('capistra_slugify')) {
    function capistra_slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}

if (!function_exists('capistra_categories')) {
    /**
     * @param 'income'|'expense'|null $type
     * @return list<array<string,mixed>>
     */
    function capistra_categories(?string $type = null, bool $activeOnly = false): array
    {
        $sql = 'SELECT c.*, a.code AS ledger_code, a.name AS ledger_name
                FROM transaction_categories c
                LEFT JOIN accounts a ON a.id = c.ledger_account_id';
        $where = [];
        $params = [];
        if ($type !== null) {
            $where[] = 'c.type = :type';
            $params[':type'] = $type;
        }
        if ($activeOnly) {
            $where[] = 'c.is_active = 1';
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY c.type, c.sort_order, c.name';

        $stmt = capistra_pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('capistra_category_get')) {
    function capistra_category_get(int $id): ?array
    {
        $stmt = capistra_pdo()->prepare('SELECT * FROM transaction_categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('capistra_category_save')) {
    /**
     * Create or update a category.
     *
     * @param array{id?:int,type:string,name:string,description?:?string,ledger_account_id?:?int,color?:?string,icon?:?string,is_active?:bool,sort_order?:int} $data
     * @return int category id
     */
    function capistra_category_save(array $data): int
    {
        $pdo  = capistra_pdo();
        $id   = (int) ($data['id'] ?? 0);
        $type = in_array(($data['type'] ?? ''), ['income', 'expense'], true) ? (string) $data['type'] : 'expense';
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Category name is required.');
        }

        $ledger = $data['ledger_account_id'] ?? null;
        $ledger = ($ledger === null || $ledger === '' || (int) $ledger === 0) ? null : (int) $ledger;

        $fields = [
            ':type'        => $type,
            ':name'        => $name,
            ':slug'        => capistra_slugify($name),
            ':description' => $data['description'] ?? null,
            ':ledger'      => $ledger,
            ':color'       => $data['color'] ?? null,
            ':icon'        => $data['icon'] ?? null,
            ':active'      => !empty($data['is_active']) ? 1 : 0,
            ':sort'        => (int) ($data['sort_order'] ?? 0),
        ];

        if ($id > 0) {
            $fields[':id'] = $id;
            $sql = 'UPDATE transaction_categories SET
                        type = :type, name = :name, slug = :slug, description = :description,
                        ledger_account_id = :ledger, color = :color, icon = :icon,
                        is_active = :active, sort_order = :sort
                    WHERE id = :id';
            $pdo->prepare($sql)->execute($fields);
            return $id;
        }

        $sql = 'INSERT INTO transaction_categories
                    (type, name, slug, description, ledger_account_id, color, icon, is_active, sort_order)
                VALUES (:type, :name, :slug, :description, :ledger, :color, :icon, :active, :sort)';
        $pdo->prepare($sql)->execute($fields);
        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('capistra_category_set_active')) {
    function capistra_category_set_active(int $id, bool $active): void
    {
        capistra_pdo()->prepare('UPDATE transaction_categories SET is_active = ? WHERE id = ?')
            ->execute([$active ? 1 : 0, $id]);
    }
}

if (!function_exists('capistra_category_resolve_ledger')) {
    /**
     * Resolve the ledger account id for a category.
     *
     * Resolution order (documented precedence):
     *   1. explicit category id / name match in `transaction_categories`
     *   2. fallback account code (4000 income / 5900 expense)
     *
     * Legacy rows whose category text no longer exists therefore still post,
     * using the documented fallback — no data is rejected.
     *
     * @param 'income'|'expense' $type
     */
    function capistra_category_resolve_ledger(string $category, string $type): int
    {
        $pdo = capistra_pdo();

        $stmt = $pdo->prepare('SELECT ledger_account_id FROM transaction_categories
                               WHERE type = ? AND (name = ? OR slug = ?)
                               ORDER BY is_active DESC, id LIMIT 1');
        $stmt->execute([$type, $category, capistra_slugify($category)]);
        $ledger = $stmt->fetchColumn();
        if ($ledger !== false && $ledger !== null && (int) $ledger > 0) {
            return (int) $ledger;
        }

        $fallbackCode = $type === 'income' ? '4000' : '5900';
        $stmt = $pdo->prepare('SELECT id FROM accounts WHERE code = ? LIMIT 1');
        $stmt->execute([$fallbackCode]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            // Last resort: first account of the right type.
            $stmt = $pdo->prepare('SELECT id FROM accounts WHERE type = ? ORDER BY code LIMIT 1');
            $stmt->execute([$type]);
            $id = $stmt->fetchColumn();
        }
        if ($id === false) {
            throw new RuntimeException("No ledger account available for $type category '$category'.");
        }
        return (int) $id;
    }
}

if (!function_exists('capistra_category_map')) {
    /** @return array<string,int> name => ledger_account_id (for a type) */
    function capistra_category_map(string $type): array
    {
        $map = [];
        foreach (capistra_categories($type) as $row) {
            if ($row['ledger_account_id']) {
                $map[(string) $row['name']] = (int) $row['ledger_account_id'];
            }
        }
        return $map;
    }
}
