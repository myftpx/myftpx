<?php
namespace App\Controllers;

class PublicController extends Controller
{
    // ---- Announcements ----
    public function announcements(): void
    {
        $announcements = db()->query('SELECT * FROM announcements WHERE status = 1 ORDER BY published_at DESC')->fetchAll();
        echo $this->render('store/announcements', ['title' => 'Duyurular', 'announcements' => $announcements], 'store');
    }

    public function announcement(string $id): void
    {
        $stmt = db()->prepare('SELECT * FROM announcements WHERE id = ? AND status = 1');
        $stmt->execute([$id]);
        $a = $stmt->fetch();
        if (!$a) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'store'); return; }
        echo $this->render('store/announcement', ['title' => $a['title'], 'announcement' => $a], 'store');
    }

    // ---- Knowledgebase ----
    public function knowledgebase(): void
    {
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $stmt = db()->prepare('SELECT a.*, c.name cat FROM kb_articles a LEFT JOIN kb_categories c ON c.id = a.category_id WHERE a.status = 1 AND (a.title LIKE ? OR a.body LIKE ?) ORDER BY a.views DESC');
            $like = '%' . $q . '%';
            $stmt->execute([$like, $like]);
            $articles = $stmt->fetchAll();
            echo $this->render('store/kb_search', ['title' => 'Bilgi Bankası: ' . $q, 'articles' => $articles, 'q' => $q], 'store');
            return;
        }
        $categories = db()->query('SELECT c.*, (SELECT COUNT(*) FROM kb_articles a WHERE a.category_id = c.id AND a.status = 1) cnt FROM kb_categories c ORDER BY c.sort_order, c.id')->fetchAll();
        $popular = db()->query('SELECT * FROM kb_articles WHERE status = 1 ORDER BY views DESC LIMIT 5')->fetchAll();
        echo $this->render('store/knowledgebase', ['title' => 'Bilgi Bankası', 'categories' => $categories, 'popular' => $popular], 'store');
    }

    public function kbCategory(string $id): void
    {
        $stmt = db()->prepare('SELECT * FROM kb_categories WHERE id = ?');
        $stmt->execute([$id]);
        $cat = $stmt->fetch();
        if (!$cat) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'store'); return; }
        $stmt = db()->prepare('SELECT * FROM kb_articles WHERE category_id = ? AND status = 1 ORDER BY id DESC');
        $stmt->execute([$id]);
        echo $this->render('store/kb_category', ['title' => $cat['name'], 'category' => $cat, 'articles' => $stmt->fetchAll()], 'store');
    }

    public function kbArticle(string $id): void
    {
        $stmt = db()->prepare('SELECT a.*, c.name cat FROM kb_articles a LEFT JOIN kb_categories c ON c.id = a.category_id WHERE a.id = ? AND a.status = 1');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        if (!$article) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'store'); return; }
        db()->prepare('UPDATE kb_articles SET views = views + 1 WHERE id = ?')->execute([$id]);
        echo $this->render('store/kb_article', ['title' => $article['title'], 'article' => $article], 'store');
    }
    // ---- Domain search & registration ----
    public function domains(): void
    {
        $tlds = db()->query('SELECT * FROM tld_pricing WHERE status = 1 ORDER BY tld')->fetchAll();
        echo $this->render('store/domains', ['title' => 'Alan Adı Kaydı', 'tlds' => $tlds], 'store');
    }

    public function domainSearch(): void
    {
        $domain = trim($_GET['domain'] ?? $_POST['domain'] ?? '');
        $domain = preg_replace('/\s+/', '', $domain);
        if ($domain === '') redirect(url('domains'));

        $tlds = db()->query('SELECT * FROM tld_pricing WHERE status = 1 ORDER BY tld')->fetchAll();
        $results = [];
        foreach ($tlds as $t) {
            $full = $domain . '.' . ltrim($t['tld'], '.');
            $taken = (crc32($full) % 3 === 0);
            $results[] = [
                'domain' => $full, 'tld' => $t['tld'], 'available' => !$taken,
                'register_price' => $t['register_price'], 'renew_price' => $t['renew_price'],
            ];
        }
        echo $this->render('store/domain_results', ['title' => 'Sonuçlar: ' . $domain, 'query' => $domain, 'results' => $results], 'store');
    }

    public function domainRegister(): void
    {
        $this->validateCsrf();
        auth()->require();
        $uid = auth()->id();
        $domain = trim($this->input('domain', ''));
        $years = (int)$this->input('years', 1);
        $tld = trim($this->input('tld', ''));

        $stmt = db()->prepare('SELECT * FROM tld_pricing WHERE tld = ? AND status = 1');
        $stmt->execute([$tld]);
        $pricing = $stmt->fetch();
        if (!$pricing) { flash('error', 'Geçersiz uzantı.'); redirect(url('domains')); }

        $amount = (float)$pricing['register_price'] * $years;
        $invoice = (new StoreController())->createInvoice($uid, [
            ['description' => 'Alan adı kaydı: ' . $domain . ' (' . $years . ' yıl)', 'amount' => $amount],
        ]);

        db()->prepare('INSERT INTO domains (user_id, domain, tld, registration_period, status, expiry_date, nameservers, dns) VALUES (?, ?, ?, ?, "pending", ?, ?, ?)')
            ->execute([$uid, $domain, $tld, $years, date('Y-m-d', strtotime('+' . $years . ' years')), json_encode(['ns1.rcvxtr.com', 'ns2.rcvxtr.com']), json_encode([])]);

        flash('info', 'Alan adı siparişiniz oluşturuldu. Ödeme sonrası kayıt tamamlanacaktır.');
        redirect(url('client/invoices/' . $invoice));
    }

    public function networkStatus(): void
    {
        $services = db()->query('SELECT s.*, p.name pname FROM services s LEFT JOIN products p ON p.id = s.product_id WHERE s.status IN ("active","suspended") LIMIT 200')->fetchAll();
        echo $this->render('store/network_status', ['title' => 'Ağ Durumu', 'services' => $services], 'store');
    }
}
