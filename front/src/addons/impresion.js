import { Printd } from 'printd'
import conversor from 'conversor-numero-a-letras-es-ar'
import QRCode from 'qrcode'

const COLOR_MARCA = '#7a1049'

// Iconos en SVG inline: el iframe de impresion no tiene la fuente de iconos.
const ICONOS = {
  fecha: '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M7.5 14h1M11.5 14h1M15.5 14h1M7.5 17.5h1M11.5 17.5h1"/>',
  precio: '<path d="M4 8a2 2 0 0 0 0 4v0a2 2 0 0 1 0 4v2h16v-2a2 2 0 0 1 0-4v0a2 2 0 0 0 0-4V6H4z" transform="rotate(-35 12 12)"/><path d="M10 9.5l4 5" transform="rotate(-35 12 12)" stroke-dasharray="1.5 1.5"/>',
  butaca: '<path d="M7 11V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v4"/><path d="M4 12a1.5 1.5 0 0 1 3 0v2h10v-2a1.5 1.5 0 0 1 3 0v4H4z"/><path d="M6 16v4M18 16v4"/>',
  hora: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12h3.5"/>',
}

function iconoBoleto(nombre) {
  return `<div class="bi-icono"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${ICONOS[nombre]}</svg></div>`
}

// Separa el formato del final del titulo ("AVENGERS BONUS 2D" -> "AVENGERS BONUS" + "2D")
// para resaltarlo con el color de la marca.
function tituloYFormato(titulo) {
  const t = (titulo || '').trim()
  const m = t.match(/^(.*?)\s+(2D|3D|4D|4DX|IMAX)$/i)
  return m ? { nombre: m[1], formato: m[2].toUpperCase() } : { nombre: t, formato: '' }
}

/**
 * Boleto compacto (para la billetera): imagen de la pelicula al azar arriba,
 * con el logo montado en la onda, y los datos debajo. trans: numero de "Trans:".
 */
export async function printBoletoDiseno(bol, cine, trans) {
  const logo = new URL('multicines-logo-ticket.png', window.location.href.split('#')[0]).href
  const { nombre, formato } = tituloYFormato(bol.titulo)
  const hora = (bol.horaFuncion || '').substring(11)
  const imagenes = bol.footer_images || []
  const base = process.env.API + '../imagen/'

  const estilos = `<style>
    @page { margin: 0; }
    * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bi { font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; padding: 0 0.2cm 0.05cm; }
    .bi-sinimg { padding-top: 0.3cm; }
    .bi-head { display: flex; align-items: center; justify-content: center; gap: 0.15cm; }
    .bi-head img { height: 1.05cm; }
    .bi-razon { color: ${COLOR_MARCA}; font-size: 15px; font-weight: 800; border-bottom: 1.5px solid ${COLOR_MARCA}; line-height: 1.1; }
    .bi-nit { font-size: 10px; text-align: center; margin-top: 1px; }
    .bi-hr { border: 0; border-top: 1px dashed #333; margin: 0.18cm 0; }
    .bi-titulo { text-align: center; font-size: 20px; font-weight: 900; line-height: 1.1; text-transform: uppercase; margin-top: 0.2cm; }
    .bi-formato { color: ${COLOR_MARCA}; }
    .bi-sala { display: flex; align-items: center; gap: 0.12cm; margin: 0.15cm 0 0.2cm; }
    .bi-sala .linea { flex: 1; border-top: 1px solid ${COLOR_MARCA}; }
    .bi-sala .estrella { color: ${COLOR_MARCA}; font-size: 10px; }
    .bi-sala .pill { background: ${COLOR_MARCA}; color: #fff; font-weight: 800; font-size: 15px; padding: 2px 12px; border-radius: 4px; white-space: nowrap; }
    .bi-grid { display: grid; grid-template-columns: 1fr auto; gap: 0.2cm 0.2cm; }
    .bi-dato { display: flex; align-items: center; gap: 0.1cm; }
    .bi-icono { width: 0.75cm; height: 0.75cm; border-radius: 4px; background: ${COLOR_MARCA}; display: flex; align-items: center; justify-content: center; flex: none; }
    .bi-label { font-size: 11px; line-height: 1; }
    .bi-valor { font-size: 18px; font-weight: 800; line-height: 1.1; }
    .bi-cod { font-size: 12px; line-height: 1.3; display: flex; justify-content: space-between; }
    .bi-cod b { font-weight: 700; }
    .bi-talon { text-align: center; font-size: 11px; line-height: 1.35; }
    /* Portada: la imagen ocupa siempre el mismo recuadro (se estira si hace
       falta) para que todos los boletos midan igual. Abajo cierra con una
       onda blanca que continua el boleto. */
    .bi-portada { position: relative; height: 1.3cm; margin: 0 -0.2cm; overflow: hidden; border-radius: 0.35cm 0.35cm 0 0; }
    .bi-portada-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill; display: block; }
    .bi-ola { position: absolute; bottom: 0; left: 0; width: 100%; height: 0.4cm; display: block; filter: drop-shadow(0 -1px 1.5px rgba(0,0,0,.35)); }
    .bi-portada + .bi-head { margin-top: 0.02cm; }
  </style>`

  // Una sola imagen por boleto, elegida al azar entre las de la pelicula:
  // cada boleto (y cada reimpresion) puede salir con una distinta.
  let imgUrl = ''
  if (imagenes.length) {
    const img = imagenes[Math.floor(Math.random() * imagenes.length)]
    imgUrl = base + img.imagen
  }
  const cabecera = `<div class='bi-head'>
      <img src="${logo}" />
      <div>
        <div class='bi-razon'>${cine.razon}</div>
        <div class='bi-nit'>NIT:${cine.nit}</div>
      </div>
    </div>`
  // Borde superior del boleto: sube por los costados hasta una meseta al
  // centro, recortando la imagen.
  const ola = `<svg class='bi-ola' viewBox='0 0 100 40' preserveAspectRatio='none'>
    <path fill='#fff' d='M0,40 H100 V30 C92,28 86,8 70,5 H30 C14,8 8,28 0,30 Z'/>
  </svg>`
  const portada = imgUrl
    ? `<div class='bi-portada'><img class='bi-portada-img' src="${imgUrl}" />${ola}</div>${cabecera}`
    : cabecera

  const html = `${estilos}<div class='bi${imgUrl ? '' : ' bi-sinimg'}'>
    ${portada}
    <div class='bi-titulo'>${nombre}${formato ? ` <span class='bi-formato'>${formato}</span>` : ''}</div>
    <div class='bi-sala'><span class='linea'></span><span class='estrella'>&#9733;</span><span class='pill'>${bol.nombreSala}</span><span class='estrella'>&#9733;</span><span class='linea'></span></div>
    <div class='bi-grid'>
      <div class='bi-dato'>${iconoBoleto('fecha')}<div><div class='bi-label'>Fecha:</div><div class='bi-valor'>${bol.fechaFuncion}</div></div></div>
      <div class='bi-dato'>${iconoBoleto('precio')}<div class='bi-valor'>Bs. ${bol.costo}</div></div>
      <div class='bi-dato'>${iconoBoleto('butaca')}<div><div class='bi-label'>Butaca:</div><div class='bi-valor'>${bol.letra}-${bol.columna}</div></div></div>
      <div class='bi-dato'>${iconoBoleto('hora')}<div><div class='bi-label'>Hora:</div><div class='bi-valor'>${hora}</div></div></div>
    </div>
    <hr class='bi-hr'>
    <div class='bi-cod'><span><b>Cod:</b> ${bol.numboc}</span><span>Trans: ${trans}</span></div>
    <hr class='bi-hr'>
    <div class='bi-talon'>${bol.fechaFuncion} ${hora}<br><b>${bol.titulo}</b><br>${bol.nombreSala} ${bol.letra}-${bol.columna}</div>
    <div>_</div>
  </div>`

  // Si se imprime antes de que la imagen termine de descargarse, el boleto
  // sale sin ella. Se espera a que el logo y la imagen esten en cache.
  await precargarImagenes([logo, imgUrl].filter(Boolean))

  // Elemento propio y no #myelement: printFactura (asincrona) lo reescribe
  // mientras este boleto espera las imagenes.
  const el = document.createElement('div')
  el.innerHTML = html
  const d = new Printd()
  d.print(el)
}

function precargarImagenes(urls, espera = 8000) {
  return Promise.all(urls.map(url => new Promise(resolve => {
    const img = new Image()
    const listo = () => resolve()
    img.onload = listo
    img.onerror = listo
    setTimeout(listo, espera)
    img.src = url
  })))
}

export function printPromo(info, cine) {
  const promo = `
  <style>
    .titulo { font-size: 12px; text-align: center; font-weight: bold; }
    .titulo2 { font-size: 10px; text-align: center; }
    .textpeq { font-size: small; text-align: left; }
    hr { border-top: 1px dashed; }
  </style>
  <div>
    <div class='titulo2'>${cine.razon}<br>NIT ${cine.nit}<br>Casa Matriz<br>Oruro</div>
    <hr>
    <div class='titulo'>COMBO DUO</div>
    <hr>
    <div class='titulo'>Reclame su Combo Duo <br> 1 PIPOCA + 2 REFRESCOS</div>
    <hr>
    <div class='textpeq'>FECHA DE EMISIÓN: ${info.fechaEmision}</div>
    <div class='textpeq'>Usuario: ${info.usuario}</div>
    <div class='textpeq'>Venta: ${info.id}</div>
  </div>
  `;

  document.getElementById('myelement').innerHTML = promo;
  const d = new Printd();
  d.print(document.getElementById('myelement'));
}

export async function printFactura(factura, cine, leyenda, qrOptions) {
  const ClaseConversor = conversor.conversorNumerosALetras;
  const miConversor = new ClaseConversor();
  const literal = miConversor.convertToText(parseInt(factura.montoTotal));
  const qr = await QRCode.toDataURL(`${cine.url2}consulta/QR?nit=${cine.nit}&cuf=${factura.cuf}&numero=${factura.numeroFactura}&t=2`, qrOptions);
  const online = factura.online ? 'en' : 'fuera de';

  let html = `<style>
    .titulo { font-size: 12px; text-align: center; font-weight: bold; }
    .titulo2 { font-size: 10px; text-align: center; }
    .contenido { font-size: 10px; text-align: left; }
    .conte2 { font-size: 10px; text-align: right; }
    .titder { font-size: 12px; text-align: right; font-weight: bold; }
    hr { border-top: 1px dashed; }
  </style>
  <div style='padding: 0.5cm'>
    <div class='titulo'>FACTURA CON DERECHO A CREDITO FISCAL</div>
    <div class='titulo2'>
      ${cine.razon}<br>Casa Matriz<br>No. Punto de Venta ${factura.codigoPuntoVenta}<br>
      ${cine.direccion}<br>Tel. ${cine.telefono}<br>Oruro
    </div>
    <hr>
    <div class='titulo'>NIT</div><div class='titulo2'>${cine.nit}</div>
    <div class='titulo'>FACTURA N°</div><div class='titulo2'>${factura.numeroFactura}</div>
    <div class='titulo'>CÓD. AUTORIZACIÓN</div><div class='titulo2'>${factura.cuf}</div>
    <hr>
    <table>
      <tr><td class='titder'>NOMBRE/RAZÓN SOCIAL:</td><td class='contenido'>${factura.client.nombreRazonSocial}</td></tr>
      <tr><td class='titder'>NIT/CI/CEX:</td><td class='contenido'>${factura.client.numeroDocumento}-${factura.client.complemento}</td></tr>
      <tr><td class='titder'>COD. CLIENTE:</td><td class='contenido'>${factura.client.id}</td></tr>
      <tr><td class='titder'>FECHA DE EMISIÓN:</td><td class='contenido'>${factura.fechaEmision}</td></tr>
    </table>
    <hr>
    <div class='titulo'>DETALLE</div>`;

  factura.details.forEach(r => {
    html += `<div style='font-size: 12px'><b>${r.programa_id} - ${r.descripcion}</b></div>
             <div>${r.cantidad} ${parseFloat(r.precioUnitario).toFixed(2)} 0.00
             <span style='float:right'>${parseFloat(r.subTotal).toFixed(2)}</span></div>`;
  });

  html += `<hr>
    <table style='font-size: 8px;'>
      <tr><td class='titder'>SUBTOTAL Bs</td><td class='conte2'>${parseFloat(factura.montoTotal).toFixed(2)}</td></tr>
      <tr><td class='titder'>DESCUENTO Bs</td><td class='conte2'>0.00</td></tr>
      <tr><td class='titder'>TOTAL Bs</td><td class='conte2'>${parseFloat(factura.montoTotal).toFixed(2)}</td></tr>
      <tr><td class='titder'>MONTO GIFT CARD Bs</td><td class='conte2'>0.00</td></tr>
      <tr><td class='titder'>MONTO A PAGAR Bs</td><td class='conte2'>${parseFloat(factura.montoTotal).toFixed(2)}</td></tr>
      <tr><td class='titder'>IMPORTE BASE CRÉDITO FISCAL Bs</td><td class='conte2'>${parseFloat(factura.montoTotal).toFixed(2)}</td></tr>
    </table><br>
    <div>Son ${literal} ${((parseFloat(factura.montoTotal) - Math.floor(factura.montoTotal)) * 100).toFixed(0)}/100 Bolivianos</div>
    <hr>
    <div class='titulo2' style='font-size: 9px'>ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,<br>
    EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY<br><br>
    ${leyenda}<br><br>
    “Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación ${online} línea”</div>
    <div style='display: flex; justify-content: center;'>
      <img src="${qr}" />
    </div>
  </div>`;

  document.getElementById('myelement').innerHTML = html;
  const d = new Printd();
  d.print(document.getElementById('myelement'));
}
