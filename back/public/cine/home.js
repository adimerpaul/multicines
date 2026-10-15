/* MultiCines Plaza - pagina de inicio: carrusel y filtros de cartelera */
(function () {
  'use strict';

  // Carrusel
  var slides = document.querySelectorAll('[data-slide]');
  var dots = document.querySelectorAll('[data-dot]');
  var actual = 0, timer = null;

  function ir(i) {
    if (!slides.length) return;
    actual = (i + slides.length) % slides.length;
    slides.forEach(function (s, k) { s.classList.toggle('on', k === actual); });
    dots.forEach(function (d, k) { d.classList.toggle('on', k === actual); });
  }
  function auto() {
    clearInterval(timer);
    if (slides.length > 1) timer = setInterval(function () {
      if (document.getElementById('trailer-modal').hidden) ir(actual + 1);
    }, 6500);
  }
  dots.forEach(function (d) {
    d.addEventListener('click', function () { ir(+d.getAttribute('data-dot')); auto(); });
  });
  var prev = document.querySelector('[data-prev]'), next = document.querySelector('[data-next]');
  if (prev) prev.addEventListener('click', function () { ir(actual - 1); auto(); });
  if (next) next.addEventListener('click', function () { ir(actual + 1); auto(); });
  auto();

  // Cartelera: filtro por dia y por formato
  var cartelera = document.querySelector('[data-cartelera]');
  if (!cartelera) return;
  var movies = cartelera.querySelectorAll('[data-movie]');
  var sinResultados = cartelera.querySelector('[data-sin-resultados]');
  var diaOn = cartelera.querySelector('[data-dia].on');
  var dia = diaOn ? diaOn.getAttribute('data-dia') : '';
  var filtro = 'Todos';

  function aplicar() {
    var visibles = 0;
    movies.forEach(function (m) {
      var formatos = (m.getAttribute('data-formatos') || '').split(',');
      var ok = filtro === 'Todos' ||
        (filtro === 'Estrenos' ? m.getAttribute('data-estreno') === '1' : formatos.indexOf(filtro) !== -1);

      var horasDia = 0;
      m.querySelectorAll('[data-fecha]').forEach(function (h) {
        var mostrar = h.getAttribute('data-fecha') === dia;
        h.hidden = !mostrar;
        if (mostrar) horasDia++;
      });
      m.querySelector('[data-sin-funciones]').hidden = horasDia > 0;
      m.querySelector('[data-poster-link]').href = m.querySelector('[data-poster-link]').href.split('?')[0] + '?dia=' + dia;

      m.hidden = !ok;
      if (ok) visibles++;
    });
    if (sinResultados) sinResultados.hidden = visibles > 0;
  }

  cartelera.querySelectorAll('[data-dia]').forEach(function (b) {
    b.addEventListener('click', function () {
      cartelera.querySelectorAll('[data-dia]').forEach(function (x) { x.classList.toggle('on', x === b); });
      dia = b.getAttribute('data-dia');
      aplicar();
    });
  });
  cartelera.querySelectorAll('[data-filtro]').forEach(function (b) {
    b.addEventListener('click', function () {
      cartelera.querySelectorAll('[data-filtro]').forEach(function (x) { x.classList.toggle('on', x === b); });
      filtro = b.getAttribute('data-filtro');
      aplicar();
    });
  });
  aplicar();
})();
