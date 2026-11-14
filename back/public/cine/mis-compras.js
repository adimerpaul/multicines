/* MultiCines Plaza - Mis compras: abrir/cerrar el QR de cada entrada */
(function () {
  'use strict';

  function cerrar() {
    document.querySelectorAll('[data-modal-entrada]').forEach(function (m) { m.hidden = true; });
  }

  document.addEventListener('click', function (e) {
    var ver = e.target.closest('[data-ver-entrada]');
    if (ver) {
      var m = document.querySelector('[data-modal-entrada="' + ver.getAttribute('data-ver-entrada') + '"]');
      if (m) m.hidden = false;
      return;
    }
    if (e.target.closest('[data-cerrar-entrada]') || e.target.hasAttribute('data-modal-entrada')) cerrar();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });

  // Al ingresar con Google se recarga para mostrar sus compras
  document.addEventListener('cine:login', function () { location.reload(); });
})();
