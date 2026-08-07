<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Validation;
use App\Services\EmailService;

/**
 * Email marketing controller.
 *
 * Manages subscribers, mailing lists, and campaigns.
 * Uses EmailService for business logic delegation.
 */
class EmailController
{
    /**
     * @param EmailService $emailService Email business logic
     */
    public function __construct(
        private readonly EmailService $emailService = new EmailService(),
    ) {}

    /**
     * Display all subscribers.
     */
    public function subscribers(): void
    {
        $db = \App\Core\Database::getInstance();
        $subscribers = $db->fetchAll(
            "SELECT id, email, name, status, subscribed_at
             FROM email_subscribers
             ORDER BY subscribed_at DESC"
        );

        View::display('emails.subscribers', ['subscribers' => $subscribers]);
    }

    /**
     * Add a new subscriber.
     */
    public function addSubscriber(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'email' => 'required|email|unique:email_subscribers,email',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/email/subscribers');
            exit;
        }

        $this->emailService->addSubscriber(
            email: $_POST['email'],
            name: $_POST['name'] ?? null,
        );

        Response::redirect('/email/subscribers');
        exit;
    }

    /**
     * Display all mailing lists.
     */
    public function lists(): void
    {
        $lists = $this->emailService->getListsWithCounts();
        View::display('emails.lists', ['lists' => $lists]);
    }

    /**
     * Create a new mailing list.
     */
    public function createList(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:2|max:255',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/email/lists');
            exit;
        }

        $this->emailService->createList(
            name: $_POST['name'],
            description: $_POST['description'] ?? null,
            createdBy: (int) Session::get('user_id'),
        );

        Response::redirect('/email/lists');
        exit;
    }

    /**
     * Display all campaigns.
     */
    public function campaigns(): void
    {
        $campaigns = $this->emailService->getAllCampaigns();
        View::display('emails.campaigns', ['campaigns' => $campaigns]);
    }

    /**
     * Display the campaign creation form.
     */
    public function createCampaign(): void
    {
        $db = \App\Core\Database::getInstance();
        $lists = $db->fetchAll("SELECT id, name FROM email_lists ORDER BY name");
        $templates = $db->fetchAll("SELECT id, name FROM email_templates ORDER BY name");

        View::display('emails.create-campaign', [
            'lists' => $lists,
            'templates' => $templates,
        ]);
    }

    /**
     * Store a new campaign.
     */
    public function storeCampaign(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:3|max:255',
            'subject' => 'required|min:3|max:255',
            'template' => 'required',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/email/campaigns/create');
            exit;
        }

        try {
            $this->emailService->createCampaign(
                data: [
                    'name' => $_POST['name'],
                    'subject' => $_POST['subject'],
                    'template' => $_POST['template'],
                    'status' => $_POST['status'] ?? 'draft',
                    'scheduled_at' => $_POST['scheduled_at'] ?? null,
                ],
                listIds: array_map('intval', $_POST['lists'] ?? []),
                createdBy: (int) Session::get('user_id'),
            );

            Response::redirect('/email/campaigns');
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to create campaign');
            Response::redirect('/email/campaigns/create');
            exit;
        }
    }
}
