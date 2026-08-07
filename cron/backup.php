<?php

declare(strict_types=1);

/**
 * Automated Database Backup Script
 * Setup cron: 0 2 * * * php /path/to/cron/backup.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$backupDir = __DIR__ . '/../../storage/backups';
$timestamp = date('Y-m-d_H-i-s');
$filename = "backup_{$timestamp}.sql.gz";
$filepath = "{$backupDir}/{$filename}";

// Ensure backup directory exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0750, true);
}

echo "Starting database backup...\n";

try {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    
    // Use mysqldump for reliable backup
    $command = sprintf(
        'mysqldump -h %s -P %s -u %s %s %s 2>&1 | gzip > %s',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        $password ? '-p' . escapeshellarg($password) : '',
        escapeshellarg($database),
        escapeshellarg($filepath)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new \RuntimeException("mysqldump failed: " . implode("\n", $output));
    }
    
    $fileSize = filesize($filepath);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    
    echo "✅ Backup completed: {$filename} ({$fileSizeMB} MB)\n";
    
    // Clean old backups (keep last 30 days)
    $retentionDays = 30;
    $cutoffTime = time() - ($retentionDays * 86400);
    $deletedCount = 0;
    
    foreach (glob("{$backupDir}/backup_*.sql.gz") as $file) {
        if (filemtime($file) < $cutoffTime) {
            unlink($file);
            $deletedCount++;
        }
    }
    
    if ($deletedCount > 0) {
        echo "🗑️  Cleaned {$deletedCount} old backup(s)\n";
    }
    
    echo "Backup saved to: {$filepath}\n";
    
} catch (\Exception $e) {
    echo "❌ Backup failed: " . $e->getMessage() . "\n";
    exit(1);
}
