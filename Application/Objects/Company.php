<?php

class Company
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, phone, email, address, city, state, zip, website, created_at, updated_at
               FROM company WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    public function create(array $fields): int
    {
        return (int) $this->db->insert(
            'INSERT INTO company (name, phone, email, address, city, state, zip, website)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                trim($fields['name']    ?? ''),
                trim($fields['phone']   ?? '') ?: null,
                trim($fields['email']   ?? '') ?: null,
                trim($fields['address'] ?? '') ?: null,
                trim($fields['city']    ?? '') ?: null,
                trim($fields['state']   ?? '') ?: null,
                trim($fields['zip']     ?? '') ?: null,
                trim($fields['website'] ?? '') ?: null,
            ]
        );
    }

    public function update(int $id, array $fields): void
    {
        $this->db->execute(
            'UPDATE company
                SET name = ?, phone = ?, email = ?, address = ?, city = ?, state = ?, zip = ?, website = ?, updated_at = ?
              WHERE id = ?',
            [
                trim($fields['name']    ?? ''),
                trim($fields['phone']   ?? '') ?: null,
                trim($fields['email']   ?? '') ?: null,
                trim($fields['address'] ?? '') ?: null,
                trim($fields['city']    ?? '') ?: null,
                trim($fields['state']   ?? '') ?: null,
                trim($fields['zip']     ?? '') ?: null,
                trim($fields['website'] ?? '') ?: null,
                date('Y-m-d H:i:s'),
                $id,
            ]
        );
    }
}
