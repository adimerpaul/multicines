import { Printd } from 'printd'
import conversor from 'conversor-numero-a-letras-es-ar'
import QRCode from 'qrcode'

export function printBoleto(bol, cine) {
  let ticket = "<style>\
    .titulo{ font-size: 14px; text-align: center; font-weight: bold; }\
    .tifecha{ font-size: 14px; }\
    .titnit{ font-size: 10px; text-align: center; }\
    hr{ border-top: 1px dashed; }\
    .titpelicula{ font-size: 18px; text-align: center; font-weight: bold; }\
    </style><div style='padding: 0.5cm'>";
  ticket += `<div class='titulo'>${cine.razon}</div>
             <div class='titnit'>NIT:${cine.nit}</div>
             <hr>
             <div class='titpelicula'>${bol.titulo} <br> ${bol.nombreSala}</div>
             <br>
             <div class='tifecha'>Fecha: <span style='font-size: 20px;'>${bol.fechaFuncion}</span>
             <span style='float:right'>Bs. ${bol.costo}</span></div>
             <div class='tifecha'>Butaca: <span style='font-size:22px;'>${bol.letra}-${bol.columna}</span>
             <div style='float:right'>Hora: <span style='font-size:20px;'>${bol.horaFuncion.substring(11)}</span></div></div>
             <hr>
             <div style='font-size: 12px'>Cod: ${bol.numboc}<br>Trans: ${bol.sale_id}</div>
             <hr><br>
             <div class='tifecha' style='font-size: 10px'>${bol.fechaFuncion} ${bol.horaFuncion.substring(11)}</div>
             <div class='titpelicula' style='font-size:10px'>${bol.titulo}<br>${bol.nombreSala} ${bol.letra}-${bol.columna}</div>
             <div>_</div><br>`;
  ticket += "</div>";

  document.getElementById('myelement').innerHTML = ticket;
  const d = new Printd();
  d.print(document.getElementById('myelement'));
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
