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

  window.Cine = { api: api, el: el, fakeQr: fakeQr, hash: hash };
})();
