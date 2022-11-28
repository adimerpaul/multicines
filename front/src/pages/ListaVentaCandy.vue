<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-form @submit.prevent="listaVentaCandyGet">
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
        <q-table :rows="listaVentaCandy" :columns="listaColums">
          <template v-slot:body-cell-siatAnulado="props">
            <q-td :props="props" auto-width>
              <q-badge :color="props.row.siatAnulado?'red':'green'" :label="props.row.siatAnulado?'A':'V'"/>
            </q-td>
          </template>
          <template v-slot:body-cell-Opciones="props">
            <q-td :props="props" auto-width>
              <q-btn-dropdown color="primary" label="Opciones">
                <q-list>
                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                    <q-item-section>
                      <q-btn icon="print" color="primary" class="full-width" label="Imprimir" no-caps @click="printFactura(props.row)" v-if="props.row.siatAnulado==0"/>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                    <q-item-section>
                      <q-btn icon="list" color="green" class="full-width" label="Comanda" no-caps @click="printComanda(props.row)" v-if="props.row.siatAnulado==0"/>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                    <q-item-section>
                      <q-btn icon="cancel_presentation" color="red" class="full-width" label="Anular" no-caps @click="anularSale(props.row)" v-if="props.row.siatAnulado==0"/>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup>
                    <q-item-section>
                      <q-btn type="a" label="Imp Impuestos " class="full-width" color="info" target="_blank" :href="`${cine.url2}consulta/QR?nit=${cine.nit}&cuf=${props.row.cuf}&numero=${props.row.numeroFactura }&t=2`" />
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                    <q-item-section>
                      <q-btn label="Enviar Correo " class="full-width" color="teal" @click="correo(props.row)"/>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>




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
    <q-dialog v-model="ticketsDialog" full-width>
      <q-card >
        <q-card-section>
          <div class="text-h6">Boletos</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-table label="Boletos" :columns="boletoColums" :rows="tickets">
            <template v-slot:body-cell-opcion="props">
              <q-td :props="props">
                <q-btn color="info" icon="print" @click="boletoprint(props.row)" v-if="props.row.devuelto==0"/>
              </q-td>
            </template>
          </q-table>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <div>
      <img :src="qrImage" style="visibility: hidden">
    </div>
    <div id="myelement" class="hidden">{{lorem}}</div>

    <q-dialog v-model="dialogAnular" >
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">ANULAR FACTURA</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-select label="motivo" :options="motivos" v-model="motivo"/>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="ANULAR" @click="enviarAnular" />
        </q-card-actions>
      </q-card>
    </q-dialog>

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
      ticketsDialog:false,
      loading:false,
      fechaIni:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      listaVentaCandy:[],
      facturadetalle:{},
      factura:{},
      cine:{},
      dialogAnular:false,
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
      boletoColums:[
        {name:'opcion',label:'Opcion',field:'opcion'},
        {name:'titulo',label:'titulo',field:'titulo',sortable:true},
        {name:'nombreSala',label:'nombreSala',field:'nombreSala',sortable:true},
        {name:'horaFuncion',label:'horaFuncion',field:'horaFuncion',sortable:true},
        {name:'letra',label:'letra',field:'letra',sortable:true},
        {name:'columna',label:'columna',field:'columna',sortable:true},
        {name:'costo',label:'costo',field:'costo',sortable:true},
      ],
      tickets:[],
      leyendas:[],
      qrImage:'',
      motivos:[],
      motivo:{},
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
    this.listaVentaCandyGet();
    this.encabezado();
    this.cargarLeyenda();
    this.cargarMotivo()
  },
  methods: {
    correo(venta){
      console.log(venta)
      this.$api.post('enviarCorreo',{client:venta.client,sale:venta}).then(res => {
        if(res.data){
          this.$q.notify({
            color: 'green',
            textColor: 'white',
            message: 'enviado al correo',
          })}
      })

    },
    async printComanda(factura) {
      this.facturadetalle = factura
      let ClaseConversor = conversor.conversorNumerosALetras;
      let miConversor = new ClaseConversor();
      let a = miConversor.convertToText( parseInt(factura.montoTotal));
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
      .campotd{\
      text-align: center;\
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
      <div class='titulo2'>"+this.cine.razon+"<br>\
        Casa Matriz<br>\
        No. Punto de Venta "+factura.codigoPuntoVenta+"<br>\
        Oruro\
      </div>\
      <hr>\
      <table>\
        <tr><td class='titder'>FECHA DE EMISIÓN:</td><td class='contenido'>" + factura.fechaEmision + "</td></tr>\
      </table>\
      <hr>\
      <div class='titulo'>DETALLE</div>\
      <table style='font-size: 10px;'><thead>\
      <tr><th>CANT</th><th>PROD</th><th>P.U.</th><th>SubT</th></tr>\
      </thead><tbody>"
      factura.details.forEach(r => {
        cadena += "<tr><td class='campotd'>" + r.cantidad + "</td><td class='campotd'>  " +r.descripcion+"</td><td class='campotd'>"+ parseFloat(r.precioUnitario).toFixed(2) + " </td><td class='campotd'>" + parseFloat(r.subTotal).toFixed(2) + "</td></tr>"
      })
      cadena += "</tbody></table><hr>\
      <table style='font-size: 8px;'>\
      <tr><td class='titder'>TOTAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      </table><br>\
      <div>Son " + a + " " + (parseFloat(factura.montoTotal).toFixed(2) - Math.floor(parseFloat(factura.montoTotal).toFixed(2))) * 100 + "/100 Bolivianos</div>\
      <div>Usuario: "+factura.usuario+"</div>\
      <div>Venta: "+factura.id+"</div>\
      ";
      document.getElementById('myelement').innerHTML = cadena
      const d = new Printd()
      d.print( document.getElementById('myelement') )

    },

    enviarAnular(){
      this.$q.loading.show()
      this.$api.post('anularSale',{sale:this.factura,motivo:this.motivo}).then(res => {
        // console.log(res.data)
        this.$q.loading.hide()
        this.listaVentaCandyGet();
        this.dialogAnular = false
      })
    },
    cargarMotivo(){
      this.$api.get('motivoanular').then(res => {
        res.data.forEach(r=>{
          r.label=r.descripcion
        })
        this.motivos=res.data;

        this.motivo=this.motivos[0]
      })

    },
    cargarLeyenda(){
      this.$api.post('listleyenda',{codigo:'590000'}).then(res => {
          this.leyendas=res.data;
      })

        },
    encabezado(){
      this.$api.get('datocine').then(res => {
        this.cine = res.data;
        // console.log(this.cine)
      })
    },
    boletoprint(bol){
      console.log(bol)

      let ticket=""
      ticket+="<style>\
        .titulo{\
        font-size: 14px;\
        text-align: center;\
        font-weight: bold;\
      }\
      \        .tifecha{\
        font-size: 14px;\
      }\
      \        .titnit{\
        font-size: 10px;\
        text-align: center;\
      }\
      \      hr{\
      border-top: 1px dashed   ;\
    }\
         .titpelicula{\
        font-size: 18px;\
        text-align: center;\
        font-weight: bold;\
      }\
        </style>"
      ticket+="<div style='padding-right: 0.5cm;padding-left: 0.5cm'>"
      ticket+="<div class='titulo'>"+this.cine.razon+"</div>"
      ticket+="<div class='titnit'>NIT:"+this.cine.nit+"</div>";
      ticket+="<hr>";
      ticket+="<div class='titpelicula'>" + bol.titulo+"<br> " + bol.nombreSala+"</div><br>";
      ticket+="<div class='tifecha'>Fecha:  <span style='font-size: 20px;'>"+bol.fechaFuncion+"</span>  <span style='float:right'>Bs. "+bol.costo+"</span></div>";
      ticket+="<div class='tifecha'>Butaca: <span style='font-size:22px;'>"+bol.letra +" - "+ bol.columna+"</span><div style='float:right'> Hora: <span style='font-size:20px;'>"+bol.horaFuncion.substring(11)+ "</span></div></div>";
      ticket+="<hr>";
      ticket+="<div style='font-size: 12px' >Cod: "+bol.numboc + "<br>"
      ticket+="Trans: "+bol.id+"</div>";
      ticket+="</div >"
      document.getElementById('myelement').innerHTML = ticket
      const d = new Printd()
      d.print( document.getElementById('myelement') )

    },
    anularSale(factura){
        //console.log(factura)
        this.factura=factura
        this.dialogAnular=true
    },
    detalleimp(factura){
      console.log(factura.tickets)
      this.tickets=factura.tickets
      this.ticketsDialog=true
      // let myWindow = window.open("", "Imprimir", "width=1000,height=1000");
       //myWindow.document.write(this.boletoprint(factura.tickets[0]));
        //myWindow.document.close();
    //   setTimeout(() => {
     //    myWindow.print();
      //   myWindow.close();
       //}, 10);
    },
    async printFactura(factura) {
      console.log(factura)
      let max=this.leyendas.length - 1;
      //let pos=Math.round(Math.random() * (max - 0) + 0)
      //let ley=this.leyendas[pos].descripcionLeyenda
      this.facturadetalle = factura
      let ClaseConversor = conversor.conversorNumerosALetras;
      let miConversor = new ClaseConversor();
      let a = miConversor.convertToText( parseInt(factura.montoTotal));
      this.qrImage = await QRCode.toDataURL(this.cine.url2+"consulta/QR?nit="+this.cine.nit+"&cuf="+factura.cuf+"&numero="+factura.numeroFactura+"&t=2", this.opts)
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
    <div id='myelement' style='padding-left: 0.5cm;padding-right: 0.5cm'>\
      <div class='titulo'>FACTURA <br>CON DERECHO A CREDITO FISCAL</div>\
      <div class='titulo2'>"+this.cine.razon+"<br>\
        Casa Matriz<br>\
        No. Punto de Venta "+factura.codigoPuntoVenta+"<br>\
        "+this.cine.direccion.substring(0,38)+"<br>"+this.cine.direccion.substring(38)+"<br>\
        Tel. "+this.cine.telefono+"<br>\
        Oruro\
      </div>\
      <hr>\
      <div class='titulo'>NIT</div>\
      <div class='titulo2'>"+this.cine.nit+"</div>\
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
        cadena += "<div style='font-size: 12px'><b>" + r.product_id + " - " + r.descripcion + "</b></div>"
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
"+factura.leyenda+" <br><br>\
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
    listaVentaCandyGet() {
      this.loading= true
      this.$api.post('listaVentaCandy',{
        fechaIni:this.fechaIni,
        fechaFin:this.fechaFin,
      }).then(res => {
        this.loading= false;
        this.listaVentaCandy=res.data
      })
    },
  }
}
</script>
