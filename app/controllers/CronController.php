<?php
namespace App\Controllers;

use App\Core\Billing;

class CronController extends Controller
{
    /** Full automation: billing + suspension + reminders + cancellations. */
    public function billing(): void { $this->run(); }

    public function run(): void
    {
        $secret = setting('cron_secret', '');
        $key = $_GET['key'] ?? '';
        header('Content-Type: application/json; charset=utf-8');
        if ($secret === '' || !hash_equals($secret, (string)$key)) {
            $this->json(['error' => 'Gecersiz cron anahtari'], 403);
        }
        $summary = ['billing' => Billing::runDueServices(), 'suspended' => $this->suspendOverdue(), 'reminders' => $this->sendReminders(), 'cancellations' => $this->processCancellations()];
        $this->json(['success' => true, 'summary' => $summary]);
    }

    private function suspendOverdue(): int
    {
        $stmt = db()->prepare("SELECT id, user_id, domain FROM services WHERE status = 'active' AND next_due_date IS NOT NULL AND next_due_date < date('now','-3 days')");
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll() as $s) {
            db()->prepare("UPDATE services SET status='suspended' WHERE id=?")->execute([$s['id']]);
            \App\Core\Mailer::sendTemplate('service_suspended', $this->userEmail((int)$s['user_id']), ['domain' => $s['domain'] ?: ('Hizmet #'.$s['id'])]);
            $count++;
        }
        return $count;
    }

    private function sendReminders(): int
    {
        $stmt = db()->prepare("SELECT id,user_id,domain,next_due_date FROM services WHERE status='active' AND next_due_date = date('now','+7 days')");
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll() as $s) {
            \App\Core\Mailer::send($this->userEmail((int)$s['user_id']), 'Yenileme Hatirlatmasi - '.setting('site_name','RCVXTR'), 'Merhaba,\n\n'.($s['domain'] ?: 'Hizmetiniz').' icin yenileme tarihiniz '.$s['next_due_date'].'. Lutfen zamaninda odeme yapin.\n\n'.setting('site_name','RCVXTR'));
            $count++;
        }
        return $count;
    }

    private function processCancellations(): int
    {
        $stmt = db()->prepare("SELECT cr.id, cr.service_id FROM cancellation_requests cr JOIN services s ON s.id=cr.service_id WHERE cr.status='approved' AND cr.type='end_of_period' AND s.status='active' AND s.next_due_date < date('now')");
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll() as $r) {
            db()->prepare("UPDATE services SET status='cancelled' WHERE id=?")->execute([$r['service_id']]);
            $count++;
        }
        return $count;
    }

    private function userEmail(int $userId): string
    {
        $stmt = db()->prepare('SELECT email FROM users WHERE id=?');
        $stmt->execute([$userId]);
        return (string)$stmt->fetchColumn();
    }
}
