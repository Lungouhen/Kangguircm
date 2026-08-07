<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\Validation;

class EmailController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function subscribers(): void
    {
        $subscribers = $this->db->fetchAll(
            "SELECT * FROM email_subscribers ORDER BY subscribed_at DESC"
        );

        View::display('emails.subscribers', ['subscribers' => $subscribers]);
    }

    public function addSubscriber(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'email' => 'required|email|unique:email_subscribers,email'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /email/subscribers');
            exit;
        }

        $this->db->insert('email_subscribers', [
            'email' => $_POST['email'],
            'name' => $_POST['name'] ?? null,
            'confirmation_token' => bin2hex(random_bytes(32))
        ]);

        header('Location: /email/subscribers');
        exit;
    }

    public function lists(): void
    {
        $lists = $this->db->fetchAll(
            "SELECT l.*, COUNT(ls.subscriber_id) as subscriber_count
             FROM email_lists l
             LEFT JOIN email_list_subscribers ls ON l.id = ls.list_id
             GROUP BY l.id
             ORDER BY l.created_at DESC"
        );

        View::display('emails.lists', ['lists' => $lists]);
    }

    public function createList(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:2|max:255'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /email/lists');
            exit;
        }

        $this->db->insert('email_lists', [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'created_by' => Session::get('user_id')
        ]);

        header('Location: /email/lists');
        exit;
    }

    public function campaigns(): void
    {
        $campaigns = $this->db->fetchAll(
            "SELECT * FROM email_campaigns ORDER BY created_at DESC"
        );

        View::display('emails.campaigns', ['campaigns' => $campaigns]);
    }

    public function createCampaign(): void
    {
        $lists = $this->db->fetchAll("SELECT * FROM email_lists ORDER BY name");
        $templates = $this->db->fetchAll("SELECT * FROM email_templates ORDER BY name");

        View::display('emails.create-campaign', [
            'lists' => $lists,
            'templates' => $templates
        ]);
    }

    public function storeCampaign(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:3|max:255',
            'subject' => 'required|min:3|max:255',
            'template' => 'required'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /email/campaigns/create');
            exit;
        }

        $this->db->beginTransaction();

        try {
            $campaignId = $this->db->insert('email_campaigns', [
                'name' => $_POST['name'],
                'subject' => $_POST['subject'],
                'template' => $_POST['template'],
                'status' => $_POST['status'] ?? 'draft',
                'scheduled_at' => $_POST['scheduled_at'] ?? null,
                'created_by' => Session::get('user_id')
            ]);

            if (!empty($_POST['lists'])) {
                foreach ($_POST['lists'] as $listId) {
                    $this->db->insert('email_campaign_lists', [
                        'campaign_id' => $campaignId,
                        'list_id' => $listId
                    ]);
                }
            }

            $this->db->commit();
            header('Location: /email/campaigns');
            exit;

        } catch (\Exception $e) {
            $this->db->rollBack();
            Session::flash('error', 'Failed to create campaign');
            header('Location: /email/campaigns/create');
            exit;
        }
    }
}
