/* MultiCines Plaza - utilidades comunes de la web publica */
(function () {
  'use strict';

  function meta(name) {
    var el = document.querySelector('meta[name="' + name + '"]');
    return el ? el.getAttribute('content') : '';
  }

  // Llamada al backend con los dos tokens: CSRF de Laravel y el token de la
  // web (X-Cine-Token). Sin ellos el servidor responde 403/419.
  function api(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': meta('csrf-token'),
        'X-Cine-Token': meta('cine-token')
      },
      body: JSON.stringify(body || {})
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        if (!res.ok) {
          var err = new Error(data.message || (res.status === 419 ? 'La sesión venció, recarga la página' : 'Error ' + res.status));
          err.status = res.status;
          throw err;
        }
        return data;
      });
    });
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }

  // QR de muestra (solo diseno): patron aleatorio con los 3 cuadros de esquina.
  function hash(s) {
    var h = 2166136261;
    for (var i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
    return h >>> 0;
  }
  function fakeQr(container, seed) {
    var n = 25, r = hash(String(seed)) || 1;
    function rnd() { r ^= r << 13; r >>>= 0; r ^= r >> 17; r ^= r << 5; r >>>= 0; return r / 4294967296; }
    function finder(x, y) {
      var f = [[0, 0], [n - 7, 0], [0, n - 7]];
      for (var i = 0; i < f.length; i++) {
        var dx = x - f[i][0], dy = y - f[i][1];
        if (dx >= 0 && dx < 7 && dy >= 0 && dy < 7) {
          if (dx === 0 || dx === 6 || dy === 0 || dy === 6) return 1;
          if (dx >= 2 && dx <= 4 && dy >= 2 && dy <= 4) return 1;
          return 0;
        }
        if (dx >= -1 && dx <= 7 && dy >= -1 && dy <= 7) return 0;
      }
      return -1;
    }
    container.textContent = '';
    for (var y = 0; y < n; y++) {
      for (var x = 0; x < n; x++) {
        var f = finder(x, y);
        container.appendChild(el('span', (f === -1 ? rnd() > 0.52 : f === 1) ? 'on' : ''));
      }
    }
  }

  // Modal del trailer (YouTube sin cookies)
  var modal = document.getElementById('trailer-modal');
  function abrirTrailer(id, titulo) {
    if (!modal || !/^[A-Za-z0-9_-]{11}$/.test(id)) return;
    document.getElementById('trailer-titulo').textContent = titulo || '';
    var iframe = document.createElement('iframe');
    iframe.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0';
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
    iframe.allowFullscreen = true;
    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
    var box = document.getElementById('trailer-video');
    box.textContent = '';
    box.appendChild(iframe);
    modal.hidden = false;
  }
  function cerrarTrailer() {
    if (!modal) return;
    modal.hidden = true;
    document.getElementById('trailer-video').textContent = '';
  }
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-trailer]');
    if (t) {
      e.preventDefault();
      e.stopPropagation();
      abrirTrailer(t.getAttribute('data-trailer'), t.getAttribute('data-titulo'));
      return;
    }
    if (e.target === modal || e.target.closest('[data-cerrar-trailer]')) cerrarTrailer();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarTrailer();
  });

  // ---------- Cuenta del cliente (login con Google) ----------
  // El login real lo hace auth.js (modulo con Firebase); aqui solo el estado,
  // el modal y la cola de acciones que esperan a que el cliente ingrese.
  var authData = JSON.parse((document.getElementById('cine-auth') || {}).textContent || '{}');
  var loginModal = document.getElementById('login-modal');
  var pendientes = [];

  function pintarCuenta() {
    var c = authData.cliente;
    var cuenta = document.querySelector('[data-cuenta]');
    var ingresar = document.querySelector('[data-abrir-login]');
    if (!cuenta || !ingresar) return;
    cuenta.hidden = !c;
    ingresar.hidden = !!c;
    if (!c) return;
    var avatar = document.querySelector('[data-cuenta-avatar]');
    avatar.textContent = '';
    if (c.foto) {
      var img = el('img');
      img.src = c.foto;
      img.alt = '';
      img.referrerPolicy = 'no-referrer';
      avatar.appendChild(img);
    } else {
      avatar.textContent = (c.nombre || c.email || '?').charAt(0).toUpperCase();
    }
    document.querySelector('[data-cuenta-nombre]').textContent = (c.nombre || c.email || '').split(' ')[0];
  }
  function abrirLogin() {
    if (!loginModal) return;
    var err = loginModal.querySelector('[data-login-error]');
    if (err) err.hidden = true;
    loginModal.hidden = false;
  }
  function cerrarLogin() {
    if (loginModal) loginModal.hidden = true;
    pendientes = [];
  }
  // Ejecuta fn si hay sesion; si no, pide login y la ejecuta al ingresar.
  function requerirLogin(fn) {
    if (authData.cliente) { if (fn) fn(authData.cliente); return; }
    if (fn) pendientes.push(fn);
    abrirLogin();
  }
  // Lo llama auth.js cuando el backend acepto el login
  function loginOk(cliente) {
    authData.cliente = cliente;
    pintarCuenta();
    if (loginModal) loginModal.hidden = true;
    var cola = pendientes;
    pendientes = [];
    cola.forEach(function (fn) { fn(cliente); });
    document.dispatchEvent(new CustomEvent('cine:login', { detail: cliente }));
  }
  function loginError(msg) {
    var err = loginModal && loginModal.querySelector('[data-login-error]');
    if (!err) return;
    err.textContent = msg;
    err.hidden = false;
  }
  function logout() {
    api(authData.logoutUrl).catch(function () {}).then(function () { location.reload(); });
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-abrir-login]')) { abrirLogin(); return; }
    if (e.target.closest('[data-logout]')) { logout(); return; }
    if (e.target === loginModal || e.target.closest('[data-cerrar-login]')) cerrarLogin();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && loginModal && !loginModal.hidden) cerrarLogin();
  });

  window.Cine = {
    api: api, el: el, fakeQr: fakeQr, hash: hash,
    auth: authData, requerirLogin: requerirLogin, loginOk: loginOk, loginError: loginError,
    cliente: function () { return authData.cliente; }
  };
})();
