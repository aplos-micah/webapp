<?php

class Article
{
    private DB $db;

    private const FIELDS = [
        'title', 'category', 'content', 'status', 'tags', 'author_id',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Constants
    // =========================================================================

    const SORTABLE   = ['title', 'category', 'status', 'view_count', 'created_at', 'updated_at'];
    const STATUSES   = ['Draft', 'Published', 'Archived'];
    const CATEGORIES = ['Procedure', 'Troubleshooting', 'FAQ', 'Policy', 'Reference', 'Other'];

    // =========================================================================
    // Queries
    // =========================================================================

    public function count(
        string $search   = '',
        string $status   = '',
        string $category = '',
        ?int   $authorId = null
    ): int {
        [$where, $params] = $this->buildSearch($search, $status, $category, $authorId);
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM kb_articles a {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int     $limit    = 20,
        int     $offset   = 0,
        string  $search   = '',
        string  $sort     = 'created_at',
        string  $dir      = 'desc',
        string  $status   = '',
        string  $category = '',
        ?int    $authorId = null
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        [$where, $params] = $this->buildSearch($search, $status, $category, $authorId);

        return $this->db->query(
            "SELECT a.id, a.title, a.category, a.status, a.tags,
                    a.view_count, a.author_id, a.created_at, a.updated_at,
                    u.name AS author_name
               FROM kb_articles a
               LEFT JOIN users u ON u.id = a.author_id
              {$where}
              ORDER BY a.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT a.id, a.title, a.category, a.content, a.status, a.tags,
                    a.view_count, a.author_id, a.created_at, a.updated_at,
                    u.name AS author_name
               FROM kb_articles a
               LEFT JOIN users u ON u.id = a.author_id
              WHERE a.id = ?
              LIMIT 1',
            [$id]
        ) ?: null;
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function create(array $data): array
    {
        $title = trim($data['title'] ?? '');
        if ($err = Validator::required($title, 'Title')) {
            return ['ok' => false, 'id' => null, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO kb_articles ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $title = trim($data['title'] ?? '');
        if ($err = Validator::required($title, 'Title')) {
            return ['ok' => false, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE kb_articles SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    public function incrementViewCount(int $id): void
    {
        $this->db->execute(
            'UPDATE kb_articles SET view_count = view_count + 1 WHERE id = ?',
            [$id]
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function search(string $q): array
    {
        if ($q === '') return [];
        $like = '%' . $q . '%';
        $rows = $this->db->query(
            "SELECT id, title FROM kb_articles
              WHERE status = 'Published'
                AND (title LIKE ? OR content LIKE ? OR tags LIKE ? OR category LIKE ?)
              ORDER BY view_count DESC, updated_at DESC LIMIT 10",
            [$like, $like, $like, $like]
        );
        return array_map(fn($r) => [
            'id'   => $r['id'],
            'name' => $r['title'],
        ], $rows);
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(
        string $search,
        string $status,
        string $category,
        ?int   $authorId
    ): array {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $like         = '%' . $search . '%';
            $conditions[] = '(a.title LIKE ? OR a.content LIKE ? OR a.tags LIKE ?)';
            $params       = array_merge($params, [$like, $like, $like]);
        }
        if ($status !== '') {
            $conditions[] = 'a.status = ?';
            $params[]     = $status;
        }
        if ($category !== '') {
            $conditions[] = 'a.category = ?';
            $params[]     = $category;
        }
        if ($authorId !== null) {
            $conditions[] = 'a.author_id = ?';
            $params[]     = $authorId;
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    private function buildValues(array $data): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $raw = $data[$field];
            if ($raw === '') {
                $raw = null;
            }
            $values[$field] = $raw;
        }
        return $values;
    }
}
