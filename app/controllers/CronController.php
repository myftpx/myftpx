<?php
namespace App\Controllers;

use App\Core\Billing;

class CronController extends Controller
{
    /**
     * Run automatic/recurring billing.
     * Access: /cron/billing?key=<cron_secret>
     * Schedule via crontab (every few minutes or daily).
     */
    public function billing(): void
    {
        $secret = setting('cron_secret', '');
        $key = $_GET['key'] ?? '';

        header('Content-Type: application/json; charset=utf-8');

        if ($secret === '' || !hash_equals($secret, (string)$key)) {
            $this->json(['error' => 'Geçersiz cron anahtarı'], 403);
        }

        $summary = Billing::runDueServices();
        $this->json(['success' => true, 'summary' => $summary]);
    }
}
