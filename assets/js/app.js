// RCVXTR — global scripts
document.addEventListener('DOMContentLoaded', function () {
    // Mobile sidebar toggle
    document.querySelectorAll('.menu-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sidebar = document.querySelector('.sidebar');
            if (sidebar) sidebar.classList.toggle('open');
        });
    });

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Emin misiniz?';
            if (!window.confirm(msg)) e.preventDefault();
        });
    });

    // Copy to clipboard
    document.querySelectorAll('[data-copy]').forEach(function (el) {
        el.addEventListener('click', function () {
            var target = document.querySelector(el.getAttribute('data-copy'));
            if (!target) return;
            var text = target.textContent || target.value;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    flashCopy(el);
                });
            } else {
                var ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta); ta.select();
                document.execCommand('copy'); document.body.removeChild(ta);
                flashCopy(el);
            }
        });
    });

    function flashCopy(el) {
        var original = el.textContent;
        el.textContent = 'Kopyalandı ✓';
        setTimeout(function () { el.textContent = original; }, 1500);
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(function (a) {
        setTimeout(function () {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(function () { a.remove(); }, 500);
        }, 6000);
    });

    // Toggle password visibility
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (input) input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});
