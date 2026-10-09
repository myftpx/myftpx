<div class="container" style="padding-top:40px;max-width:980px">
    <h1>API Dokümantasyonu</h1>
    <p class="text-muted mb-3">RCVXTR REST API — müşterileriniz hizmet, alan adı, fatura ve biletlerini programatik olarak yönetebilir.</p>

    <div class="card mb-3"><div class="card-body">
        <h3 class="mb-2">Kimlik Doğrulama</h3>
        <p>Her istekte aşağıdaki başlıkları gönderin:</p>
        <div class="key-box mb-2">X-Api-Key: rcvx_...</div>
        <div class="key-box mb-2">X-Auth-Key: (auth key)</div>
        <p class="text-muted small">Alternatif: <code class="mono">Authorization: Bearer &lt;api_key&gt;</code></p>
        <p class="text-muted small">IP izin listesi: boş bırakılırsa tüm IP'ler; aksi halde yalnızca listelenen IP'ler.</p>
    </div></div>

    <div class="card mb-3"><div class="card-body">
        <h3 class="mb-2">Uç Noktalar (Endpoints)</h3>
        <div class="table-wrap"><table class="table">
            <tr><th>Metod</th><th>Uç Nokta</th><th>Açıklama</th></tr>
            <tr><td>GET</td><td class="mono">/api/v1/me</td><td>Hesap bilgileri</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/services</td><td>Hizmetleri listele</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/services/{id}</td><td>Hizmet detayı</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/domains</td><td>Alan adlarını listele</td></tr>
            <tr><td>PUT</td><td class="mono">/api/v1/domains/{id}</td><td>Alan adı DNS güncelle</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/invoices</td><td>Faturaları listele</td></tr>
            <tr><td>POST</td><td class="mono">/api/v1/invoices/{id}</td><td>Faturayı bakiye ile öde</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/tickets</td><td>Biletleri listele</td></tr>
            <tr><td>POST</td><td class="mono">/api/v1/tickets</td><td>Yeni bilet oluştur</td></tr>
            <tr><td>GET</td><td class="mono">/api/v1/balance</td><td>Bakiye sorgula</td></tr>
            <tr><td>POST</td><td class="mono">/api/v1/orders</td><td>Bakiye ile sipariş ver</td></tr>
        </table></div>
    </div></div>

    <div class="card"><div class="card-body">
        <h3 class="mb-2">Örnek İstek</h3>
        <div class="key-box mb-2">curl -H "X-Api-Key: rcvx_..." -H "X-Auth-Key: ..." https://site.com/api/v1/me</div>
        <div class="key-box">curl -X POST -H "X-Api-Key: rcvx_..." -H "Content-Type: application/json" -d '{"product_id":1,"billing_cycle":"monthly","domain":"example.com"}' https://site.com/api/v1/orders</div>
    </div></div>
</div>
