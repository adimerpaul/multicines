/* MultiCines Plaza - compra de entradas: funcion, asientos, datos, QR y entrada.
   Por ahora solo los asientos vienen del servidor; QR, pago y factura son diseno. */
(function () {
  'use strict';

  var MAX_ASIENTOS = 10;
  var QR_MINUTOS = 5;
  var REFRESCO_SALA_MS = 20000;
  var PASOS = 5;

  var data = JSON.parse(document.getElementById('cine-data').textContent);
  var el = Cine.el;
  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return document.querySelectorAll(s); };

  var estado = {
    paso: 0,
    dia: null,
    funcion: null,
    precio: 0,
    sala: null,
    asientos: [],      // [{f,c,l,e}] desde el servidor
    seleccion: [],     // ['A16', ...]
    ventaOnline: data.ventaOnline,
    codigo: ''
  };
  var timerQr = null, timerSala = null, cargaId = 0;

  // ---------- Resumen ----------
  function precioTxt(n) { return 'Bs. ' + (Math.round(n * 100) / 100); }
  function total() { return estado.seleccion.length * estado.precio; }
  function diaLargo(fecha) {
    var b = document.querySelector('[data-dia="' + fecha + '"]');
    return b ? b.getAttribute('data-largo') : fecha;
  }
  function setSum(campo, valor) {
    $$('[data-sum="' + campo + '"]').forEach(function (e) { e.textContent = valor; });
  }
  function resumen() {
    var f = estado.funcion;
    setSum('fecha', f ? diaLargo(f.fecha) : '—');
    setSum('hora', f ? f.hora + ' · ' + f.formato + (f.subtitulada ? ' · Sub' : '') : '—');
    setSum('sala', f ? (estado.sala ? estado.sala.nombre : f.sala) : '—');
    setSum('asientos', estado.seleccion.length ? estado.seleccion.join(', ') : '—');
    setSum('total', precioTxt(total()));
  }

  // ---------- Paso 1: dia y horario ----------
  function funcionesDelDia(fecha) {
    return data.funciones.filter(function (f) { return f.fecha === fecha; });
  }
  function elegirDia(fecha) {
    estado.dia = fecha;
    $$('[data-paso="0"] [data-dia]').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-dia') === fecha); });
    var cont = $('[data-horarios]');
    cont.textContent = '';
    funcionesDelDia(fecha).forEach(function (f) {
      var b = el('button', 'funcion' + (estado.funcion && estado.funcion.id === f.id ? ' on' : '') + (f.ventaOnline === false ? ' funcion-bol' : ''));
      b.type = 'button';
      b.appendChild(el('span', 'funcion-h', f.hora));
      b.appendChild(el('span', 'funcion-d', f.formato + ' · ' + precioTxt(f.precio) + ' · ' + (f.sala || '')));
      if (f.ventaOnline === false) b.appendChild(el('span', 'funcion-tag', 'Solo boletería'));
      b.addEventListener('click', function () { elegirFuncion(f); });
      cont.appendChild(b);
    });
  }
  function elegirFuncion(f) {
    if (!estado.funcion || estado.funcion.id !== f.id) {
      estado.seleccion = [];
      estado.sala = null;
      estado.asientos = [];
    }
    estado.funcion = f;
    estado.precio = f.precio;
    estado.ventaOnline = data.ventaOnline && f.ventaOnline !== false;
    elegirDia(f.fecha);
    render();
  }

  // ---------- Paso 2: asientos ----------
  function cargarSala(silencioso) {
    if (!estado.funcion) return;
    var id = ++cargaId;
    var err = $('[data-sala-error]'), cargando = $('[data-sala-cargando]');
    if (!silencioso) { cargando.hidden = false; err.hidden = true; $('[data-butacas]').textContent = ''; }

    Cine.api(data.asientosUrl, { programa_id: estado.funcion.id }).then(function (res) {
      if (id !== cargaId) return;
      cargando.hidden = true;
      estado.sala = res.sala;
      estado.precio = res.precio || estado.funcion.precio;
      estado.asientos = res.asientos || [];
      estado.ventaOnline = data.ventaOnline && estado.funcion.ventaOnline !== false && res.venta_online !== false;
      // Si alguien compro un asiento que tenia seleccionado, se quita
      var libres = {};
      estado.asientos.forEach(function (a) { if (a.e === 'L') libres[a.l + a.c] = true; });
      var antes = estado.seleccion.length;
      estado.seleccion = estado.seleccion.filter(function (s) { return libres[s]; });
      if (silencioso && estado.seleccion.length < antes) {
        err.textContent = 'Uno de tus asientos acaba de ser ocupado; elige otro.';
        err.hidden = false;
      }
      dibujarSala();
      render();
    }).catch(function (e) {
      if (id !== cargaId) return;
      cargando.hidden = true;
      if (!silencioso) {
        err.textContent = e.message || 'No se pudo cargar la sala';
        err.hidden = false;
      }
    });
  }

  function dibujarSala() {
    var grid = $('[data-butacas]');
    grid.textContent = '';
    if (!estado.sala) return;
    var filas = estado.sala.filas, cols = estado.sala.columnas;
    var mapa = {}, letras = {};
    estado.asientos.forEach(function (a) { mapa[a.f + '-' + a.c] = a; letras[a.f] = a.l; });
    grid.style.gridTemplateColumns = '18px repeat(' + cols + ', 26px) 18px';

    for (var f = 1; f <= filas; f++) {
      var letra = letras[f] || String.fromCharCode(64 + f);
      grid.appendChild(el('span', 'fila-letra', letra));
      for (var c = cols; c >= 1; c--) {
        var a = mapa[f + '-' + c];
        if (!a || a.e === 'I') { grid.appendChild(el('span', 'seat-gap')); continue; }
        grid.appendChild(botonAsiento(a));
      }
      grid.appendChild(el('span', 'fila-letra', letra));
    }
  }

  function botonAsiento(a) {
    var id = a.l + a.c;
    var b = el('button', 'seat', String(a.c));
    b.type = 'button';
    b.title = id;
    if (a.e === 'O') { b.classList.add('ocu'); b.disabled = true; b.title = id + ' · ocupado'; }
    else if (a.e === 'R') { b.classList.add('res'); b.disabled = true; b.title = id + ' · reservado'; }
    else {
      if (estado.seleccion.indexOf(id) !== -1) b.classList.add('sel');
      b.addEventListener('click', function () {
        var i = estado.seleccion.indexOf(id);
        if (i !== -1) estado.seleccion.splice(i, 1);
        else if (estado.seleccion.length < MAX_ASIENTOS) estado.seleccion.push(id);
        b.classList.toggle('sel', estado.seleccion.indexOf(id) !== -1);
        render();
      });
    }
    return b;
  }

  // ---------- Paso 3: datos de factura (diseno) ----------
  var form = $('[data-form-factura]');
  function campo(n) { return form.elements[n]; }
  function datosValidos() {
    var nit = campo('nit').value.trim(), nombre = campo('nombre').value.trim(), email = campo('email').value.trim();
    var emailOk = !email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    return /^\d{1,20}$/.test(nit) && nombre.length > 0 && emailOk;
  }
  form.addEventListener('input', render);
  form.addEventListener('submit', function (e) { e.preventDefault(); });
  $('[data-sin-nombre]').addEventListener('click', function () {
    campo('nit').value = '0';
    campo('nombre').value = 'S/N';
    render();
  });

  // ---------- Paso 4: QR (diseno) ----------
  function iniciarQr() {
    clearInterval(timerQr);
    var restante = QR_MINUTOS * 60;
    var t = $('[data-timer]');
    var pintar = function () { t.textContent = Math.floor(restante / 60) + ':' + String(restante % 60).padStart(2, '0'); };
    pintar();
    timerQr = setInterval(function () {
      restante = Math.max(0, restante - 1);
      pintar();
      if (!restante) clearInterval(timerQr);
    }, 1000);
    $$('[data-qr]').forEach(function (q) { Cine.fakeQr(q, estado.funcion.id + estado.seleccion.join('')); });
  }
  $('[data-simular-pago]').addEventListener('click', function () {
    clearInterval(timerQr);
    estado.codigo = 'MP-' + (Cine.hash(Date.now() + '') % 900000 + 100000);
    llenarEntrada();
    irPaso(4);
  });

  // ---------- Paso 5: entrada + factura (diseno) ----------
  function llenarEntrada() {
    $('[data-codigo]').textContent = estado.codigo;
    $$('[data-qr]').forEach(function (q) { Cine.fakeQr(q, estado.codigo); });
    var fac = function (k, v) { $$('[data-fac="' + k + '"]').forEach(function (e) { e.textContent = v; }); };
    fac('nit', campo('nit').value.trim() + (campo('complemento').value.trim() ? '-' + campo('complemento').value.trim() : ''));
    fac('nombre', campo('nombre').value.trim());
    fac('email', campo('email').value.trim() || 'tu correo');
    fac('emision', new Date().toLocaleString('es-BO'));
    fac('total', total().toFixed(2));
    var det = $('[data-fac-detalle]');
    det.textContent = '';
    estado.seleccion.forEach(function (s) {
      var tr = el('tr');
      tr.appendChild(el('td', '', 'Entrada ' + s + ' · ' + estado.funcion.hora));
      tr.appendChild(el('td', 'r', estado.precio.toFixed(2)));
      det.appendChild(tr);
    });
  }

  // ---------- Navegacion ----------
  var HINTS = ['Elige el día y el horario', 'Selecciona hasta ' + MAX_ASIENTOS + ' asientos', 'La factura se emite con estos datos', 'Escanea el QR para continuar', ''];

  function pasoCompleto(i) {
    switch (i) {
      case 0: return !!estado.funcion;
      case 1: return estado.seleccion.length > 0 && estado.ventaOnline;
      case 2: return datosValidos();
      case 3: return false;
      default: return true;
    }
  }
  function puedeSeguir() { return pasoCompleto(estado.paso); }

  // Desde las pestanas: atras siempre (salvo ya pagado); adelante solo si los
  // pasos intermedios estan completos. A la entrada solo se llega pagando.
  function pasoAlcanzable(n) {
    if (estado.paso === 4) return n === 4;
    if (n <= estado.paso) return true;
    for (var i = 0; i < n; i++) if (!pasoCompleto(i)) return false;
    return true;
  }

  function irPaso(n) {
    estado.paso = Math.max(0, Math.min(PASOS - 1, n));
    clearInterval(timerSala);
    if (estado.paso !== 3) clearInterval(timerQr);
    if (estado.paso === 1) {
      cargarSala(false);
      timerSala = setInterval(function () { if (!document.hidden) cargarSala(true); }, REFRESCO_SALA_MS);
    }
    if (estado.paso === 3) iniciarQr();
    render();
    irACompra();
  }

  // Lleva la vista al inicio de los pasos (no a la ficha de la pelicula, que en
  // movil queda arriba). Si los pasos ya estan a la vista, no se mueve.
  function irACompra() {
    var pasos = $('.pasos-col');
    if (!pasos) return;
    var header = $('.header');
    var alto = header ? header.getBoundingClientRect().height : 0;
    var rect = pasos.getBoundingClientRect();
    if (rect.top >= alto && rect.top < window.innerHeight * 0.4) return;
    var top = rect.top + window.pageYOffset - alto - 8;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
  }

  function render() {
    $$('[data-paso]').forEach(function (p) { p.hidden = +p.getAttribute('data-paso') !== estado.paso; });
    $$('[data-tab]').forEach(function (t) {
      var i = +t.getAttribute('data-tab');
      t.classList.toggle('on', i === estado.paso);
      t.classList.toggle('done', i < estado.paso);
      t.disabled = i === estado.paso || !pasoAlcanzable(i);
    });
    resumen();

    var ok = puedeSeguir();
    var sig = $('[data-siguiente]');
    sig.disabled = !ok;
    sig.textContent = estado.paso === 4 ? 'Volver al inicio' : estado.paso === 2 ? 'Ir a pagar' : 'Continuar';
    sig.hidden = estado.paso === 3;
    $('[data-atras]').classList.toggle('invisible', !(estado.paso > 0 && estado.paso < 4));

    // Aviso por funcion (el de la pelicula completa ya viene del servidor)
    var soloBoleteria = !!estado.funcion && !estado.ventaOnline;
    $('[data-aviso-funcion]').hidden = !(soloBoleteria && data.ventaOnline && estado.paso <= 1);

    var hint = HINTS[estado.paso];
    if (estado.paso === 1 && !estado.ventaOnline) hint = data.ventaOnline ? 'Esta función se vende solo en boletería' : 'Esta película se vende solo en boletería';
    else if (estado.paso === 1 && estado.seleccion.length) hint = estado.seleccion.length + ' de ' + MAX_ASIENTOS + ' asientos';
    $('[data-hint]').textContent = hint;
  }

  $('[data-siguiente]').addEventListener('click', function () {
    if (!puedeSeguir()) return;
    if (estado.paso === 4) { location.href = data.inicioUrl; return; }
    irPaso(estado.paso + 1);
  });
  $('[data-atras]').addEventListener('click', function () { irPaso(estado.paso - 1); });
  $$('[data-tab]').forEach(function (t) {
    t.addEventListener('click', function () {
      var n = +t.getAttribute('data-tab');
      if (n !== estado.paso && pasoAlcanzable(n)) irPaso(n);
    });
  });
  $$('[data-paso="0"] [data-dia]').forEach(function (b) {
    b.addEventListener('click', function () { elegirDia(b.getAttribute('data-dia')); render(); });
  });

  // ---------- Inicio ----------
  var inicial = data.funciones.filter(function (f) { return f.id === data.funcionInicial; })[0];
  if (inicial) {
    elegirFuncion(inicial);
    irPaso(1);
  } else if (data.funciones.length) {
    var dia = funcionesDelDia(data.diaInicial).length ? data.diaInicial : data.funciones[0].fecha;
    elegirDia(dia);
    render();
  } else {
    render();
  }
})();
