<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriberStatus;
use App\Repositories\SubscriberRepository;
use App\Core\Database;

/**
 * Email marketing service.
 *
 * Handles subscriber management, mailing lists, and campaign operations.
 */
class EmailService
{
    private readonly Database $db;

    /**
     * @param SubscriberRepository $subscriberRepository Subscriber data access
     */
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository = new SubscriberRepository(),
    ) {
        $this->db = Database::getInstance();
    }

    /**
     * Add a new subscriber.
     *
     * @param string $email Subscriber email
     * @param string|null $name Subscriber name
     * @return int New subscriber ID
     */
    public function addSubscriber(string $email, ?string $name = null): int
    {
        return $this->subscriberRepository->create([
            'email' => $email,
            'name' => $name,
            'status' => SubscriberStatus::Active->value,
            'subscribed_at' => date('Y-m-d H:i:s'),
            'confirmation_token' => bin2hex(random_bytes(32)),
        ]);
    }

    /**
     * Check if an email is already subscribed.
     *
     * @param string $email
     */
    public function isSubscribed(string $email): bool
    {
        $subscriber = $this->subscriberRepository->findByEmail($email);
        return $subscriber !== false;
    }

    /**
     * Create a new mailing list.
     *
     * @param string $name List name
     * @param string|null $description List description
     * @param int $createdBy User ID of creator
     * @return int New list ID
     */
    public function createList(string $name, ?string $description, int $createdBy): int
    {
        return $this->db->insert('email_lists', [
            'name' => $name,
            'description' => $description,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get all mailing lists with subscriber counts.
     *
     * @return list<array<string, mixed>>
     */
    public function getListsWithCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT l.id, l.name, l.description, l.created_at,
                    COUNT(ls.subscriber_id) AS subscriber_count
             FROM email_lists l
             LEFT JOIN email_list_subscribers ls ON l.id = ls.list_id
             GROUP BY l.id, l.name, l.description, l.created_at
             ORDER BY l.created_at DESC"
        );
    }

    /**
     * Create a new email campaign.
     *
     * @param array<string, mixed> $data Campaign data
     * @param list<int> $listIds Target mailing list IDs
     * @param int $createdBy User ID of creator
     * @return int New campaign ID
     */
    public function createCampaign(array $data, array $listIds, int $createdBy): int
    {
        $this->db->beginTransaction();

        try {
            $data['created_by'] = $createdBy;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $campaignId = $this->db->insert('email_campaigns', $data);

            foreach ($listIds as $listId) {
                $this->db->insert('email_campaign_lists', [
                    'campaign_id' => $campaignId,
                    'list_id' => $listId,
                ]);
            }

            $this->db->commit();
            return $campaignId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all campaigns.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllCampaigns(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, subject, status, scheduled_at, sent_at, created_at
             FROM email_campaigns
             ORDER BY created_at DESC"
        );
    }

    /**
     * Get active subscriber count.
     */
    public function getActiveSubscriberCount(): int
    {
        return $this->subscriberRepository->countByStatus(SubscriberStatus::Active);
    }
}
