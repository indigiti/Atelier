(() => {
  const root = document.documentElement;
  let stored = null;
  try { stored = localStorage.getItem('atelier-theme'); } catch (e) { stored = null; }
  if (stored === 'dark' || stored === 'light') root.dataset.theme = stored;
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => btn.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('atelier-theme', next); } catch (e) {}
  }));

  const menuBtn = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');
  if (menuBtn && menu) {
    menuBtn.addEventListener('click', () => {
      const open = menu.hasAttribute('hidden');
      if (open) menu.removeAttribute('hidden'); else menu.setAttribute('hidden','');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.classList.toggle('menu-open', open);
    });
    menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      menu.setAttribute('hidden',''); document.body.classList.remove('menu-open'); menuBtn.setAttribute('aria-expanded','false');
    }));
  }

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('in'); io.unobserve(entry.target); }
    }), {threshold:.08, rootMargin:'0px 0px -25px'});
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
  } else document.querySelectorAll('.reveal').forEach(el => el.classList.add('in'));

  const finder = document.querySelector('[data-finder]');
  if (finder) {
    const steps = [...finder.querySelectorAll('.finder-step')];
    const result = finder.querySelector('.finder-result');
    const bar = finder.querySelector('.finder-progress i');
    const status = finder.querySelector('[data-finder-status]');
    const state = {};
    let step = 0;
    const show = () => {
      steps.forEach((s,i) => s.classList.toggle('active', i === step));
      bar.style.width = `${Math.min(100, ((step+1)/steps.length)*100)}%`;
      status.textContent = step === 0 ? 'Choose a room to begin.' : step === 1 ? `Room: ${state.room}` : `Style: ${state.style}`;
    };
    finder.querySelectorAll('[data-choice]').forEach(btn => btn.addEventListener('click', () => {
      state[btn.dataset.choice] = btn.dataset.value;
      step++;
      if (step < steps.length) show(); else {
        steps.forEach(s=>s.classList.remove('active'));
        result.hidden = false;
        bar.style.width = '100%';
        status.textContent = 'Profile complete.';
        result.querySelector('[data-result-title]').textContent = `${state.style || 'Warm'} · ${state.material || 'Natural wood'}`;
        result.querySelector('[data-result-copy]').textContent = `For your ${state.room || 'space'}, start with ${state.material || 'natural wood'} pieces and keep the composition ${String(state.style || 'quiet').toLowerCase()}. We recommend one anchor piece, one useful wall object and one smaller ritual or plant detail.`;
      }
    }));
    finder.querySelector('[data-restart]')?.addEventListener('click', () => {
      Object.keys(state).forEach(k => delete state[k]); step=0; result.hidden=true; show();
    });
    show();
  }
})();

// Progressive confirmation for consequential browser forms.
document.addEventListener('submit', function(e){ const f=e.target.closest('form[data-confirm]'); if(f && !window.confirm(f.dataset.confirm || 'Continue?')) e.preventDefault(); });

// v0.4.0 — server-authoritative live product configurator.
(() => {
  const money = n => new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR', maximumFractionDigits:0}).format(Number(n || 0));
  document.querySelectorAll('[data-configurator]').forEach(shell => {
    const form = shell.querySelector('[data-config-form]');
    if (!form) return;
    const quoteUrl = shell.dataset.quoteUrl;
    const total = shell.querySelector('[data-config-total]');
    const base = shell.querySelector('[data-config-base]');
    const status = shell.querySelector('[data-config-status]');
    const adjustments = shell.querySelector('[data-config-adjustments]');
    const productPrice = document.querySelector('[data-config-price]');
    const addButton = shell.querySelector('[data-add-config]');
    const saveForm = shell.querySelector('[data-save-config]');
    let timer = null;

    const syncRange = input => {
      const out = input.closest('.range-field')?.querySelector('output');
      if (out) out.textContent = `${input.value} ${input.closest('.range-field')?.querySelector('small')?.textContent.match(/cm|mm|in/)?.[0] || 'cm'}`;
    };
    shell.querySelectorAll('[data-range]').forEach(input => { syncRange(input); input.addEventListener('input', () => syncRange(input)); });

    const syncSaveForm = () => {
      if (!saveForm) return;
      const bucket = saveForm.querySelector('[data-save-config-fields]');
      if (!bucket) return;
      bucket.innerHTML = '';
      const data = new FormData(form);
      for (const [key,value] of data.entries()) {
        if (['_csrf','slug','qty','configuration_guid'].includes(key)) continue;
        const input = document.createElement('input'); input.type='hidden'; input.name=key; input.value=String(value); bucket.appendChild(input);
      }
      // unchecked boolean options are intentionally absent and normalize to false server-side.
    };

    const renderQuote = quote => {
      if (!quote || !quote.ok) {
        status.textContent = (quote?.errors || ['Please review this configuration.']).join(' ');
        status.classList.add('error');
        if (addButton) addButton.disabled = true;
        return;
      }
      status.classList.remove('error');
      status.textContent = `Server-authoritative · pricing rules ${quote.pricing_version}`;
      if (base) base.textContent = money(quote.base_price);
      if (total) total.textContent = money(quote.unit_price);
      if (productPrice) productPrice.innerHTML = `${money(quote.unit_price)} <small>configured staging price</small>`;
      if (addButton) { addButton.disabled = false; const span=addButton.querySelector('span'); if(span) span.textContent=money(quote.unit_price); }
      if (adjustments) adjustments.innerHTML = (quote.adjustments || []).map(a => `<span>${String(a.label).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))} <b>${a.delta >= 0 ? '+' : ''}${money(a.delta)}</b></span>`).join('');
      syncSaveForm();
    };

    const quote = async () => {
      if (!quoteUrl) return;
      status.textContent = 'Updating price…'; status.classList.remove('error');
      try {
        const body = new FormData(form);
        const response = await fetch(quoteUrl, {method:'POST', body, headers:{'X-Requested-With':'XMLHttpRequest'}});
        const payload = await response.json(); renderQuote(payload);
      } catch (err) {
        status.textContent = 'Live pricing is temporarily unavailable. Refresh and try again.'; status.classList.add('error');
        if (addButton) addButton.disabled = true;
      }
    };
    const schedule = () => { clearTimeout(timer); timer = setTimeout(quote, 120); syncSaveForm(); };
    form.addEventListener('change', schedule); form.addEventListener('input', e => { if (e.target.matches('input[type="range"],input[type="text"]')) schedule(); });
    saveForm?.addEventListener('submit', syncSaveForm);
    syncSaveForm();
  });
})();

// v0.9.8.1 — Marketplace Luxe interaction layer.
(() => {
  const megaBtn = document.querySelector('[data-mega-toggle]');
  const mega = document.querySelector('[data-mega]');
  const closeMega = () => { if (!megaBtn || !mega) return; mega.hidden = true; megaBtn.setAttribute('aria-expanded','false'); };
  if (megaBtn && mega) {
    megaBtn.addEventListener('click', e => { e.stopPropagation(); const open = mega.hidden; mega.hidden = !open; megaBtn.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    document.addEventListener('click', e => { if (!mega.hidden && !mega.contains(e.target) && e.target !== megaBtn) closeMega(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeMega(); megaBtn.focus(); } });
  }

  const bindSheet = (toggleSelector, sheetSelector) => {
    const toggle = document.querySelector(toggleSelector); const sheet = document.querySelector(sheetSelector);
    if (!toggle || !sheet) return;
    let previous = null;
    const open = () => { previous = document.activeElement; sheet.hidden = false; document.body.classList.add('sheet-open'); sheet.querySelector('button,[href],input,select')?.focus(); };
    const close = () => { sheet.hidden = true; document.body.classList.remove('sheet-open'); previous?.focus?.(); };
    toggle.addEventListener('click', open); sheet.querySelectorAll('[data-sheet-close]').forEach(el => el.addEventListener('click', close));
    document.addEventListener('keydown', e => { if (!sheet.hidden && e.key === 'Escape') close(); });
  };
  bindSheet('[data-filter-toggle]','[data-filter-sheet]');
  bindSheet('[data-sort-toggle]','[data-sort-sheet]');

  document.querySelectorAll('[data-pincode-widget]').forEach(widget => {
    const input = widget.querySelector('input'); const button = widget.querySelector('[data-pincode-check]'); const result = widget.querySelector('[data-pincode-result]');
    if (!input || !button || !result) return;
    const check = async () => {
      const pin = String(input.value || '').replace(/\D/g,'').slice(0,6); input.value = pin;
      result.className = ''; result.textContent = 'Checking delivery…';
      if (pin.length !== 6) { result.classList.add('error'); result.textContent = 'Enter a valid 6-digit pincode.'; return; }
      try {
        const r = await fetch(`${widget.dataset.shippingUrl || '/api/shipping'}?pincode=${encodeURIComponent(pin)}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
        const data = await r.json();
        if (!data.serviceable) { result.classList.add('error'); result.textContent = data.error || 'Delivery is unavailable.'; return; }
        result.classList.add('success'); result.textContent = `${data.eta} · ${data.installation_available ? 'Installation available' : 'Delivery available'}`;
      } catch (e) { result.classList.add('error'); result.textContent = 'Delivery check is temporarily unavailable.'; }
    };
    button.addEventListener('click', check); input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); check(); } });
  });

  // Keep the compact mobile purchase price aligned with server-authoritative live quote rendering.
  const mobilePrice = document.querySelector('[data-mobile-pdp-price]');
  const configuredPrice = document.querySelector('[data-config-price]');
  if (mobilePrice && configuredPrice && 'MutationObserver' in window) {
    const sync = () => { const text = configuredPrice.textContent.trim().split('configured')[0].trim(); if (text) mobilePrice.textContent = text; };
    new MutationObserver(sync).observe(configuredPrice,{childList:true,subtree:true,characterData:true}); sync();
  }
})();
