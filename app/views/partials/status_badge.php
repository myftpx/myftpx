<?php
// Status badge helper for views
if (!function_exists('status_badge')) {
    function status_badge(string $status): string {
        $map = [
            'active' => 'success', 'paid' => 'success', 'open' => 'info', 'completed' => 'success',
            'pending' => 'warning', 'unpaid' => 'warning', 'suspended' => 'danger', 'closed' => 'default',
            'answered' => 'primary', 'cancelled' => 'danger', 'terminated' => 'danger', 'disabled' => 'default',
            'refunded' => 'warning', 'failed' => 'danger', 'medium' => 'warning', 'high' => 'danger',
            'low' => 'info', 'inactive' => 'default',
        ];
        $cls = $map[$status] ?? 'default';
        if ($cls === 'default') $cls = '';
        $labels = [
            'active' => 'Aktif', 'paid' => 'Ödendi', 'open' => 'Açık', 'completed' => 'Tamamlandı',
            'pending' => 'Beklemede', 'unpaid' => 'Ödenmedi', 'suspended' => 'Askıda', 'closed' => 'Kapalı',
            'answered' => 'Yanıtlandı', 'cancelled' => 'İptal', 'terminated' => 'Sonlandırıldı',
            'disabled' => 'Devre Dışı', 'refunded' => 'İade', 'failed' => 'Başarısız',
            'medium' => 'Orta', 'high' => 'Yüksek', 'low' => 'Düşük', 'inactive' => 'Pasif',
        ];
        $label = $labels[$status] ?? ucfirst($status);
        return '<span class="badge badge-' . $cls . '">' . e($label) . '</span>';
    }
}
