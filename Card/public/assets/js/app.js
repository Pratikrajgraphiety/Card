(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const appUrl = (document.querySelector('meta[name="app-url"]')?.content || window.location.origin).replace(/\/$/, '');

    const iconRefresh = () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    };

    const post = (path, body) => fetch(`${appUrl}/${path.replace(/^\//, '')}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrf
        },
        body: new URLSearchParams(body)
    }).catch(() => null);

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);

    const renderFields = (target, fields) => {
        if (!target) return;
        if (!fields.length) {
            target.innerHTML = '';
            return;
        }

        target.innerHTML = `<div class="row g-3">${fields.map((field) => {
            const type = field.type || 'text';
            const accept = field.accept ? ` accept="${escapeHtml(field.accept)}"` : '';
            if (type === 'textarea') {
                return `<div class="col-md-6"><label class="form-label">${escapeHtml(field.label)}</label><textarea class="form-control" name="${escapeHtml(field.name)}" rows="3"></textarea></div>`;
            }
            if (type === 'file') {
                return `<div class="col-md-6"><label class="form-label">${escapeHtml(field.label)}</label><input class="form-control" type="file" name="${escapeHtml(field.name)}"${accept}></div>`;
            }
            return `<div class="col-md-6"><label class="form-label">${escapeHtml(field.label)}</label><input class="form-control" type="${escapeHtml(type)}" name="${escapeHtml(field.name)}"></div>`;
        }).join('')}</div>`;
    };

    document.querySelectorAll('[data-category-select]').forEach((select) => {
        select.addEventListener('change', async () => {
            const target = document.querySelector(select.dataset.fieldsTarget);
            if (!select.value) {
                renderFields(target, []);
                return;
            }
            const response = await fetch(`${appUrl}/api/category-fields/${encodeURIComponent(select.value)}`);
            if (!response.ok) return;
            const payload = await response.json();
            renderFields(target, payload.fields || []);
        });
    });

    const reindexGroup = (group) => {
        group.querySelectorAll('[data-repeat-row]').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/\[\d+]/, `[${index}]`);
            });
        });
    };

    document.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-add-row]');
        if (addButton) {
            const group = document.querySelector(addButton.dataset.addRow);
            const row = group?.querySelector('[data-repeat-row]:last-child');
            if (!group || !row) return;
            const clone = row.cloneNode(true);
            clone.querySelectorAll('input, textarea, select').forEach((input) => {
                if (input.type === 'number') {
                    input.value = input.name.includes('[level]') ? '80' : '';
                } else {
                    input.value = '';
                }
            });
            group.appendChild(clone);
            reindexGroup(group);
            iconRefresh();
            return;
        }

        const removeButton = event.target.closest('[data-remove-row]');
        if (removeButton) {
            const group = removeButton.closest('[data-repeat-group]');
            const rows = group?.querySelectorAll('[data-repeat-row]');
            if (!group || !rows || rows.length <= 1) return;
            removeButton.closest('[data-repeat-row]').remove();
            reindexGroup(group);
        }
    });

    document.querySelectorAll('[data-dark-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const html = document.documentElement;
            const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem('smartprofile-theme-mode', next);
        });
    });

    const savedMode = localStorage.getItem('smartprofile-theme-mode');
    if (savedMode) {
        document.documentElement.setAttribute('data-bs-theme', savedMode);
    }

    document.addEventListener('click', async (event) => {
        const shareButton = event.target.closest('[data-share-url]');
        if (!shareButton) return;
        const shareUrl = shareButton.dataset.shareUrl;
        const profileId = shareButton.dataset.profileId;
        if (shareButton.dataset.track && profileId) {
            post('/api/track', { profile_id: profileId, event: shareButton.dataset.track, label: shareUrl });
        }
        if (shareButton.tagName === 'A') return;
        if (navigator.share) {
            await navigator.share({ title: document.title, url: shareUrl }).catch(() => null);
        } else if (navigator.clipboard) {
            await navigator.clipboard.writeText(shareUrl).catch(() => null);
            shareButton.classList.add('active');
            setTimeout(() => shareButton.classList.remove('active'), 900);
        }
    });

    document.addEventListener('click', (event) => {
        const tracked = event.target.closest('[data-track]');
        if (!tracked || tracked.dataset.shareUrl) return;
        const profileId = tracked.dataset.profileId;
        if (profileId) {
            post('/api/track', { profile_id: profileId, event: tracked.dataset.track, label: tracked.href || '' });
        }
    });

    document.querySelectorAll('[data-print-qr]').forEach((button) => {
        button.addEventListener('click', () => {
            const image = document.querySelector('.qr-image');
            if (!image) return;
            const win = window.open('', '_blank', 'width=520,height=640');
            if (!win) return;
            win.document.write(`<html><head><title>Print QR</title><style>body{display:grid;place-items:center;min-height:100vh;margin:0}img{width:360px;height:auto}</style></head><body><img src="${image.src}" alt="QR code"></body></html>`);
            win.document.close();
            win.focus();
            win.print();
        });
    });

    const chartEl = document.getElementById('analyticsChart');
    if (chartEl && window.Chart) {
        const series = JSON.parse(chartEl.dataset.series || '[]');
        const labels = [...new Set(series.map((row) => row.date))];
        const events = [...new Set(series.map((row) => row.event_type))];
        const colors = ['#4f46e5', '#059669', '#e11d48', '#d97706', '#0891b2', '#7c3aed'];
        const datasets = events.map((name, index) => ({
            label: name.replaceAll('_', ' '),
            borderColor: colors[index % colors.length],
            backgroundColor: colors[index % colors.length],
            data: labels.map((date) => {
                const found = series.find((row) => row.date === date && row.event_type === name);
                return found ? Number(found.total) : 0;
            }),
            tension: .3
        }));
        new Chart(chartEl, {
            type: 'line',
            data: { labels, datasets },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    iconRefresh();
})();
