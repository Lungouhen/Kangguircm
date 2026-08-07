<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\SubscriberStatus;

/**
 * Email subscriber data access layer.
 *
 * Handles all database operations for the email_subscribers table.
 */
class SubscriberRepository extends BaseRepository
{
    protected readonly string $table = 'email_subscribers';

    /** @var list<string> */
    protected readonly array $columns = [
        'id', 'email', 'name', 'status',
        'subscribed_at', 'unsubscribed_at', 'confirmed_at',
    ];

    /**
     * Find a subscriber by email address.
     *
     * @param string $email
     * @return array<string, mixed>|false
     */
    public function findByEmail(string $email): array|false
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetch(
            "SELECT {$columnList} FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    /**
     * Get all active subscribers.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findActive(int $limit = 50, int $offset = 0): array
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetchAll(
            "SELECT {$columnList} FROM {$this->table} WHERE status = ? ORDER BY subscribed_at DESC LIMIT ? OFFSET ?",
            [SubscriberStatus::Active->value, $limit, $offset]
        );
    }

    /**
     * Get all records ordered by subscription date.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetchAll(
            "SELECT {$columnList} FROM {$this->table} ORDER BY subscribed_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Count subscribers by status.
     *
     * @param SubscriberStatus $status
     */
    public function countByStatus(SubscriberStatus $status): int
    {
        return $this->count('status = ?', [$status->value]);
    }
}
