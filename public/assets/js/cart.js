/*
FILE: public/assets/js/cart.js
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Logika keranjang sisi klien — add/update/remove, badge realtime,
         mini-cart drawer, render keranjang penuh. Semua via API JSON.
*/
(function () {
  'use strict';

  const BASE = (window.APP_BASE || '');
  const API = BASE + '/index.php?page=api/cart';

  const rupiah = (n) =>
    'Rp ' + (Number(n) || 0).toLocaleString('id-ID');

  // ---- Toast (SweetAlert2) ----
  function toast(message, icon = 'success') {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 2200,
        showConfirmButton: false,
        icon,
        title: message,
        background: '#FFFFFF',
        color: '#2C2018',
        iconColor: icon === 'success' ? '#6FA773' : '#D9776A',
      });
    }
  }

  // ---- Request helper ----
  async function cartRequest(action, payload = {}) {
    const body = new URLSearchParams({ action, ...payload });
    const opts =
      action === 'get'
        ? { method: 'GET' }
        : { method: 'POST', body };
    const url = action === 'get' ? API + '&action=get' : API;
    const res = await fetch(url, opts);
    if (!res.ok && res.status !== 200) {
      let msg = 'Terjadi kesalahan.';
      try { msg = (await res.json()).message || msg; } catch (e) {}
      throw new Error(msg);
    }
    return res.json();
  }

  // ---- Badge ----
  function updateBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    badge.textContent = count;
    if (count > 0) {
      badge.classList.remove('hidden');
      badge.classList.remove('badge-pop');
      void badge.offsetWidth; // restart animasi
      badge.classList.add('badge-pop');
    } else {
      badge.classList.add('hidden');
    }
  }

  // ---- Render mini-cart drawer ----
  function renderDrawer(data) {
    const wrap = document.getElementById('cartItems');
    const sub = document.getElementById('cartSubtotal');
    const checkoutBtn = document.getElementById('cartCheckoutBtn');
    if (!wrap) return;

    if (!data.items || data.items.length === 0) {
      wrap.innerHTML =
        '<div class="flex flex-col items-center justify-center text-center py-16 text-body/70">' +
        '<i class="fa-solid fa-bag-shopping text-4xl text-line mb-4"></i>' +
        '<p class="font-medium text-heading">Keranjang masih kosong</p>' +
        '<p class="text-sm mt-1">Yuk pilih kukis favoritmu!</p>' +
        '<a href="' + BASE + '/index.php?page=catalog" class="mt-5 inline-block bg-primary hover:bg-hover text-cream text-sm font-semibold px-5 py-2.5 rounded-xl transition">Lihat Katalog</a>' +
        '</div>';
      if (checkoutBtn) checkoutBtn.classList.add('pointer-events-none', 'opacity-50');
    } else {
      wrap.innerHTML = data.items
        .map(
          (it) => `
        <div class="cart-row flex gap-3 bg-card rounded-2xl p-3 border border-line shadow-soft" data-id="${it.id}">
          <img src="${it.image}" alt="" class="w-16 h-16 rounded-xl object-cover bg-muted">
          <div class="flex-1 min-w-0">
            <p class="font-medium text-heading text-sm leading-snug truncate">${it.name}</p>
            <p class="text-primary font-semibold text-sm mt-0.5">${rupiah(it.price)}</p>
            <div class="flex items-center gap-2 mt-2">
              <div class="flex items-center border border-line rounded-full overflow-hidden">
                <button class="qty-btn w-7 h-7 grid place-items-center hover:bg-muted transition" data-act="dec" data-id="${it.id}"><i class="fa-solid fa-minus text-[10px]"></i></button>
                <span class="w-7 text-center text-sm font-medium">${it.qty}</span>
                <button class="qty-btn w-7 h-7 grid place-items-center hover:bg-muted transition" data-act="inc" data-id="${it.id}"><i class="fa-solid fa-plus text-[10px]"></i></button>
              </div>
              <button class="remove-btn ml-auto text-body/60 hover:text-danger transition text-sm" data-id="${it.id}"><i class="fa-solid fa-trash-can"></i></button>
            </div>
          </div>
        </div>`
        )
        .join('');
      if (checkoutBtn) checkoutBtn.classList.remove('pointer-events-none', 'opacity-50');
    }
    if (sub) sub.textContent = rupiah(data.subtotal);
  }

  // ---- Render full cart page (jika ada) ----
  function renderFullCart(data) {
    const container = document.getElementById('fullCartItems');
    if (!container) return;
    const summary = document.getElementById('fullCartSummary');

    if (!data.items || data.items.length === 0) {
      container.innerHTML =
        '<div class="bg-card border border-line rounded-2xl p-12 text-center shadow-soft">' +
        '<i class="fa-solid fa-bag-shopping text-5xl text-line mb-4"></i>' +
        '<p class="font-display text-2xl text-heading">Keranjang masih kosong</p>' +
        '<p class="text-body mt-2">Belum ada produk yang ditambahkan.</p>' +
        '<a href="' + BASE + '/index.php?page=catalog" class="mt-6 inline-block bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Mulai Belanja</a>' +
        '</div>';
      if (summary) summary.classList.add('hidden');
      return;
    }
    if (summary) summary.classList.remove('hidden');

    container.innerHTML = data.items
      .map(
        (it) => `
      <div class="flex gap-4 bg-card border border-line rounded-2xl p-4 shadow-soft items-center" data-id="${it.id}">
        <img src="${it.image}" alt="" class="w-20 h-20 rounded-xl object-cover bg-muted">
        <div class="flex-1 min-w-0">
          <p class="font-medium text-heading truncate">${it.name}</p>
          <p class="text-primary font-semibold mt-1">${rupiah(it.price)}</p>
        </div>
        <div class="flex items-center border border-line rounded-full overflow-hidden">
          <button class="qty-btn w-9 h-9 grid place-items-center hover:bg-muted transition" data-act="dec" data-id="${it.id}"><i class="fa-solid fa-minus text-xs"></i></button>
          <span class="w-10 text-center font-medium">${it.qty}</span>
          <button class="qty-btn w-9 h-9 grid place-items-center hover:bg-muted transition" data-act="inc" data-id="${it.id}"><i class="fa-solid fa-plus text-xs"></i></button>
        </div>
        <div class="text-right w-28 hidden sm:block">
          <p class="font-semibold text-heading">${rupiah(it.subtotal)}</p>
        </div>
        <button class="remove-btn w-9 h-9 grid place-items-center rounded-full hover:bg-danger/10 text-body/60 hover:text-danger transition" data-id="${it.id}"><i class="fa-solid fa-trash-can"></i></button>
      </div>`
      )
      .join('');

    const subEl = document.getElementById('summarySubtotal');
    const totEl = document.getElementById('summaryTotal');
    if (subEl) subEl.textContent = rupiah(data.subtotal);
    if (totEl) totEl.textContent = rupiah(data.subtotal);
  }

  function renderAll(data) {
    updateBadge(data.count || 0);
    renderDrawer(data);
    renderFullCart(data);
  }

  // ---- Drawer open/close ----
  function openDrawer() {
    const overlay = document.getElementById('cartOverlay');
    const drawer = document.getElementById('cartDrawer');
    if (!overlay || !drawer) return;
    overlay.classList.remove('hidden');
    requestAnimationFrame(() => {
      overlay.querySelector('[data-cart-close]').classList.add('opacity-100');
      drawer.classList.remove('translate-x-full');
    });
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    const overlay = document.getElementById('cartOverlay');
    const drawer = document.getElementById('cartDrawer');
    if (!overlay || !drawer) return;
    overlay.querySelector('[data-cart-close]').classList.remove('opacity-100');
    drawer.classList.add('translate-x-full');
    setTimeout(() => overlay.classList.add('hidden'), 300);
    document.body.style.overflow = '';
  }

  // ---- Public: add to cart ----
  async function addToCart(id, qty, btn) {
    try {
      if (btn) { btn.disabled = true; btn.classList.add('opacity-70'); }
      const data = await cartRequest('add', { id, qty: qty || 1 });
      if (!data.ok) throw new Error(data.message || 'Gagal menambah produk.');
      renderAll(data);
      toast(data.message || 'Ditambahkan ke keranjang', 'success');
      openDrawer();
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove('opacity-70'); }
    }
  }

  async function changeQty(id, qty) {
    try {
      const data = await cartRequest('update', { id, qty });
      renderAll(data);
    } catch (err) { toast(err.message, 'error'); }
  }

  async function removeItem(id) {
    try {
      const data = await cartRequest('remove', { id });
      renderAll(data);
      toast(data.message || 'Item dihapus', 'success');
    } catch (err) { toast(err.message, 'error'); }
  }

  // ---- Event wiring ----
  document.addEventListener('DOMContentLoaded', () => {
    // Sinkron awal
    cartRequest('get').then(renderAll).catch(() => {});

    // Tombol cart di navbar -> buka drawer
    const cartButton = document.getElementById('cartButton');
    if (cartButton) cartButton.addEventListener('click', openDrawer);

    // Tutup drawer
    document.querySelectorAll('[data-cart-close]').forEach((el) =>
      el.addEventListener('click', closeDrawer)
    );
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

    // Delegasi: tombol add-to-cart di seluruh halaman
    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('[data-add-to-cart]');
      if (addBtn) {
        e.preventDefault();
        const id = parseInt(addBtn.getAttribute('data-id'), 10);
        const qtyInput = addBtn.getAttribute('data-qty-input');
        let qty = 1;
        if (qtyInput) {
          const el = document.querySelector(qtyInput);
          if (el) qty = Math.max(1, parseInt(el.value, 10) || 1);
        }
        addToCart(id, qty, addBtn);
        return;
      }

      // qty +/- (drawer & full cart)
      const qtyBtn = e.target.closest('.qty-btn');
      if (qtyBtn) {
        const id = parseInt(qtyBtn.getAttribute('data-id'), 10);
        const act = qtyBtn.getAttribute('data-act');
        const row = qtyBtn.closest('[data-id]');
        const span = row ? row.querySelector('span') : null;
        let cur = span ? parseInt(span.textContent, 10) : 1;
        cur = act === 'inc' ? cur + 1 : cur - 1;
        changeQty(id, cur);
        return;
      }

      // remove
      const rmBtn = e.target.closest('.remove-btn');
      if (rmBtn) {
        const id = parseInt(rmBtn.getAttribute('data-id'), 10);
        removeItem(id);
        return;
      }
    });
  });

  // Ekspor minimal ke global (opsional dipakai halaman lain)
  window.LumiereCart = { add: addToCart, open: openDrawer, refresh: () => cartRequest('get').then(renderAll) };
})();
