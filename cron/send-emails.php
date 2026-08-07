<?php

declare(strict_types=1);

/**
 * Email Campaign Cron Job Runner
 * Processes scheduled email campaigns and sends emails
 * 
 * Setup cron: * * * * * php /path/to/cron/send-emails.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$logFile = __DIR__ . '/../../storage/logs/email-cron-' . date('Y-m-d') . '.log';

function logMessage(string $message): void {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    echo "[{$timestamp}] {$message}\n";
}

try {
    $db = Database::getInstance();
    
    logMessage("Starting email campaign processor...");
    
    // Find scheduled campaigns that are due
    $campaigns = $db->fetchAll(
        "SELECT * FROM email_campaigns 
         WHERE status = 'scheduled' 
         AND scheduled_at <= NOW()
         ORDER BY scheduled_at ASC 
         LIMIT 5"
    );
    
    if (empty($campaigns)) {
        logMessage("No scheduled campaigns due. Exiting.");
        exit(0);
    }
    
    foreach ($campaigns as $campaign) {
        logMessage("Processing campaign: {$campaign['name']} (ID: {$campaign['id']})");
        
        // Mark as sending
        $db->update('email_campaigns', ['status' => 'sending'], 'id = ?', [$campaign['id']]);
        
        // Get target subscribers from campaign lists
        $subscribers = $db->fetchAll(
            "SELECT DISTINCT s.email, s.name
             FROM email_subscribers s
             INNER JOIN email_list_subscribers ls ON s.id = ls.subscriber_id
             INNER JOIN email_campaign_lists cl ON ls.list_id = cl.list_id
             WHERE cl.campaign_id = ?
             AND s.status = 'active'
             AND s.confirmed_at IS NOT NULL",
            [$campaign['id']]
        );
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($subscribers as $subscriber) {
            try {
                $mail = new PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['MAIL_USERNAME'];
                $mail->Password = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
                $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
                
                $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
                $mail->addAddress($subscriber['email'], $subscriber['name'] ?? '');
                
                $mail->isHTML(true);
                $mail->Subject = $campaign['subject'];
                
                // Personalize template
                $body = str_replace(
                    ['{{name}}', '{{email}}', '{{unsubscribe_url}}'],
                    [$subscriber['name'] ?? 'Subscriber', $subscriber['email'], '#'],
                    $campaign['template']
                );
                
                $mail->Body = $body;
                $mail->AltBody = strip_tags($body);
                
                $mail->send();
                $sentCount++;
                
                logMessage("  ✓ Sent to: {$subscriber['email']}");
                
                // Rate limiting: small delay between sends
                usleep(100000); // 100ms
                
            } catch (Exception $e) {
                $failedCount++;
                logMessage("  ✗ Failed to {$subscriber['email']}: " . $e->getMessage());
            }
        }
        
        // Update campaign status
        $db->update('email_campaigns', [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$campaign['id']]);
        
        logMessage("Campaign '{$campaign['name']}' complete: {$sentCount} sent, {$failedCount} failed");
    }
    
    logMessage("Email campaign processor finished.");
    
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: " . $e->getMessage());
    exit(1);
}
