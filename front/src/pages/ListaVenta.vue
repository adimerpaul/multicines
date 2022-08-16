<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-form @submit.prevent="listaVentaBoleteriaGet">
          <div class="row">
            <div class="col-4 q-pa-xs">
              <q-input outlined label="FechaIni" type="date" v-model="fechaIni" />
            </div>
            <div class="col-4 q-pa-xs">
              <q-input outlined label="FechaFin" type="date" v-model="fechaFin" />
            </div>
            <div class="col-4 q-pa-xs flex flex-center">
              <q-btn label="Buscar" icon="search" color="primary" type="submit" class="" :loading="loading" />
            </div>
          </div>
        </q-form>
      </div>
      <div class="col-12">
        <q-table :rows="listaVentaBoleteria" :columns="listaColums">
          <template v-slot:body-cell-siatAnulado="props">
            <q-td :props="props" auto-width>
              <q-badge :color="props.row.siatAnulado?'red':'green'" :label="props.row.siatAnulado?'A':'V'"/>
            </q-td>
          </template>
          <template v-slot:body-cell-Opciones="props">
            <q-td :props="props" auto-width>
              <q-btn icon="print" @click="printFactura(props.row)"/>

              <q-btn type="a" label="CLick" target="_blank" :href="`https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=329448023&cuf=${props.row.cuf}&numero=${props.row.numeroFactura }&t=2`" />
            </q-td>
          </template>
          <template v-slot:body-cell-siatEnviado="props">
            <q-td :props="props" auto-width>
              <q-badge :color="props.row.siatEnviado?'green':'red'" :label="props.row.siatEnviado?'V':'E'"/>
            </q-td>
          </template>
        </q-table>
      </div>

    </div>
    <div>
      <img :src="qrImage" style="visibility: hidden">
    </div>
    <div id="myelement" class="hidden">{{lorem}}</div>
  </q-page>
</template>
<script>
import {date} from "quasar";
import { Printd } from 'printd'
const conversor = require('conversor-numero-a-letras-es-ar');
const QRCode = require('qrcode')
export default {
  name: `LisaVenta`,
  data() {
    return {
      lorem: 'lorem impus',
      loading:false,
      fechaIni:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      listaVentaBoleteria:[],
      facturadetalle:{},
      listaColums:[
        {name:'Opciones',label:'Opciones',field:'Opciones'},
        {name:'numeroFactura',label:'numeroFactura',field:'numeroFactura',sortable:true},
        {name:'siatEnviado',label:'siatEnviado',field:'siatEnviado',sortable:true},
        {name:'fechaEmision',label:'fechaEmision',field:'fechaEmision',sortable:true},
        {name:'client_id',label:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {name:'user_id',label:'user_id',field:row=>row.user.name,sortable:true},
        {name:'montoTotal',label:'montoTotal',field:'montoTotal',sortable:true},
        {name:'siatAnulado',label:'siatAnulado',field:'siatAnulado',sortable:true},
        {name:'id',label:'id',field:'id',sortable:true},
      ],
      qrImage:'',
      opts : {
        errorCorrectionLevel: 'M',
        type: 'png',
        quality: 0.95,
        width: 100,
        margin: 1,
        color: {
          dark: '#000000',
          light: '#FFF',
        },
      }
    }
  },
  mounted() {
    // this.printFactura()
//     const cssText = `
//   h1 {
//     color: black;
//     font-family: sans-serif;
//   }
// `
//
//     const d = new Printd()
//     d.print( document.getElementById('myelement'), [ cssText ] )
    this.listaVentaBoleteriaGet();
  },
  methods: {
    async printFactura(factura) {
      console.log(factura)
      this.facturadetalle = factura
      let ClaseConversor = conversor.conversorNumerosALetras;
      let miConversor = new ClaseConversor();
      let a = miConversor.convertToText(factura.montoTotal);
      this.qrImage = await QRCode.toDataURL("https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=329448023&cuf="+factura.cuf+"&numero="+factura.numeroFactura+"&t=2", this.opts)
      //console.log(this.qrImage)
     // return false
      let cadena = "<style>\
      .titulo{\
      font-size: 12px;\
      text-align: center;\
      font-weight: bold;\
      }\
      .titulo2{\
      font-size: 10px;\
      text-align: center;\
      }\
            .titulo3{\
      font-size: 10px;\
      text-align: center;\
      width:70%;\
      }\
            .contenido{\
      font-size: 10px;\
      text-align: left;\
      }\
      .conte2{\
      font-size: 10px;\
      text-align: right;\
      }\
      .titder{\
      font-size: 12px;\
      text-align: right;\
      font-weight: bold;\
      }\
      hr{\
  border-top: 1px dashed   ;\
}\
  table{\
    width:100%\
  }\
  h1 {\
    color: black;\
    font-family: sans-serif;\
  }</style>\
    <div id='myelement'>\
      <div class='titulo'>FACTURA <br>CON DERECHO A CREDITO FISCAL</div>\
      <div class='titulo2'>MULTISALAS<br>\
        Casa Matriz<br>\
        No. Punto de Venta 0<br>\
        AVENIDA TACNA ENTRE TOMAS FRIAS Y JAEN<br>\
        Nª2337 ZONA ESTE<br>\
        Tel. 2846005<br>\
        Oruro\
      </div>\
      <hr>\
      <div class='titulo'>NIT</div>\
      <div class='titulo2'>329448023</div>\
      <div class='titulo'>FACTURA N°</div>\
      <div class='titulo2'>"+factura.numeroFactura+"</div>\
      <div class='titulo'>CÓD. AUTORIZACIÓN</div>\
      <div class='titulo2 ' >"+factura.cuf.substring(0,41)+"<br>"+factura.cuf.substring(41)+"</div>\
      <hr>\
      <table>\
        <tr><td class='titder'>NOMBRE/RAZÓN SOCIAL:</td><td class='contenido'>" + factura.client.nombreRazonSocial + "</td></tr>\
        <tr><td class='titder'>NIT/CI/CEX:</td><td class='contenido'>" + factura.client.numeroDocumento + "</td></tr>\
        <tr><td class='titder'>COD. CLIENTE:</td ><td class='contenido'>" + factura.client.id + "</td></tr>\
        <tr><td class='titder'>FECHA DE EMISIÓN:</td><td class='contenido'>" + factura.fechaEmision + "</td></tr>\
      </table>\
      <hr>\
      <div class='titulo'>DETALLE</div>"
      factura.details.forEach(r => {
        cadena += "<div><b>" + r.programa_id + " - " + r.descripcion + "</b></div>"
        cadena += "<div>" + r.cantidad + "  " + parseFloat(r.precioUnitario).toFixed(2) + " 0.00<span style='float:right'>" + parseFloat(r.subTotal).toFixed(2) + "</span></div>"
      })
      cadena += "<hr>\
      <table style='font-size: 8px;'>\
      <tr><td class='titder' style='width: 60%'>SUBTOTAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      <tr><td class='titder'>DESCUENTO Bs</td><td class='conte2'>0.00</td></tr>\
      <tr><td class='titder'>TOTAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      <tr><td class='titder'>MONTO GIFT CARD Bs</td ><td class='conte2'>0.00</td></tr>\
      <tr><td class='titder'>MONTO A PAGAR Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      <tr><td class='titder' style='font-size: 8px'>IMPORTE BASE CRÉDITO FISCAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      </table><br>\
      <div>Son " + a + " " + (parseFloat(factura.montoTotal).toFixed(2) - Math.floor(parseFloat(factura.montoTotal).toFixed(2))) * 100 + "/100 Bolivianos</div>\
      <hr>\
      <div class='titulo2' style='font-size: 9px'>ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,<br>\
EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE<br>\
ACUERDO A LEY<br><br>\
Ley N° 453: Tienes derecho a un trato equitativo sin discriminación en la <br>\
oferta de servicios. <br><br>\
“Este documento es la Representación Gráfica de un<br>\
Documento Fiscal Digital emitido en una modalidad de<br>\
facturación en línea”</div><br>\
<div style='display: flex;justify-content: center;'>\
  <img src="+this.qrImage+" >\
      </div>\
              </div>"
      //
      //const cuf = document.createElement('cuf')
      //cuf.setAttribute('html', factura.cuf)
      //const options = {
      //  parent: document.getElementById('myelement'),
      //  headElements: [ cuf ]
      //}
      // const d = new Printd()
      // d.print( options)

      // let myWindow = window.open("", "Imprimir", "width=1000,height=1000");
      // myWindow.document.write(cadena);
      // // myWindow.document.close();
      // setTimeout(() => {
      //   myWindow.print();
      //   myWindow.close();
      // }, 10);
      document.getElementById('myelement').innerHTML = cadena
      const d = new Printd()
      d.print( document.getElementById('myelement') )

    },
    listaVentaBoleteriaGet() {
      this.loading= true
      this.$api.post('listaVentaBoleteria',{
        fechaIni:this.fechaIni,
        fechaFin:this.fechaFin,
      }).then(res => {
        this.loading= false;
        this.listaVentaBoleteria=res.data
      })
    },
  }
}
</script>
