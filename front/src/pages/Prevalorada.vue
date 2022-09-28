<template>
<q-page>
<div class="row">
  <div class="col-12">
    <div class="row">
      <div class="col-3"><q-input dense type="date" outlined label="fechaInicio" v-model="fechaInicio" /></div>
      <div class="col-3"><q-input dense type="date" outlined label="fechaFin" v-model="fechaFin" /></div>
      <div class="col-3 flex flex-center"><q-btn color="primary" :loading="loading" label="Consultar" class="full-width"  icon="search" @click="prevaloradaConsulta" /></div>
      <div class="col-3 flex flex-center"><q-btn color="green" label="AGREGAR " class="full-width"  icon="add_circle_outline" @click="prevaloradaClickCreate" /></div>
    </div>
  </div>
  <div class="col-12">
    <q-table label="FACTURA DE ALQUILER DE AMBIENTES" :rows="prevaloradas" :columns="columns">
      <template v-slot:body-cell-Opciones="props">
        <q-td :props="props" auto-width>
          <q-btn-dropdown color="primary" label="Opciones">
            <q-list>
              <q-item clickable v-close-popup>
                <q-item-section>
                  <q-btn icon="print" color="primary" class="full-width" label="Imprimir" no-caps @click="printFactura(props.row)" v-if="props.row.siatAnulado==0"/>
                </q-item-section>
              </q-item>
              <q-item clickable v-close-popup>
                <q-item-section>
                  <q-btn icon="print" color="primary" class="full-width" label="blue" no-caps @click="printButton" />
                </q-item-section>
              </q-item>
              <q-item clickable v-close-popup>
                <q-item-section>
                  <q-btn icon="cancel_presentation" color="red" class="full-width" label="Anular" no-caps @click="anularSale(props.row)" v-if="props.row.siatAnulado==0"/>
                </q-item-section>
              </q-item>
              <q-item clickable v-close-popup>
                <q-item-section>
                  <q-btn type="a" label="Imp Impuestos " class="full-width" color="info" target="_blank" :href="`${cine.url2}consulta/QR?nit=${cine.nit}&cuf=${props.row.cuf}&numero=${props.row.numeroFactura }&t=2`" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>
  </div>
  <q-dialog full-width v-model="saleDialog">
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Realizar venta</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-form @submit.prevent="prevaloradaInsert">
<!--        <q-card-section>-->
<!--          <div class="row">-->
<!--            <div class="col-3">-->
<!--              <q-input outlined label="NIT/CARNET" @keyup="searchClient" required v-model="client.numeroDocumento" />-->
<!--            </div>-->
<!--            <div class="col-3">-->
<!--              <q-input outlined label="Complemento"  @keyup="searchClient" v-model="client.complemento" />-->
<!--            </div>-->
<!--            <div class="col-3">-->
<!--              <q-input outlined label="nombreRazonSocial" required v-model="client.nombreRazonSocial" />-->
<!--            </div>-->
<!--            <div class="col-3">-->
<!--              <q-select v-model="document" outlined :options="documents"/>-->
<!--            </div>-->
<!--          </div>-->
<!--        </q-card-section>-->
<!--        <q-separator/>-->
        <q-card-section>
          <div class="row">
            <div class="col-3">
             <q-select outlined v-model="vehiculo" :options="vehiculos" label="Tipo"/>
            </div>
            <div class="col-3">
              <q-input outlined v-model="cantidad" label="Cantidad" type="number"/>
            </div>
<!--            <div class="col-3">
              <q-input outlined label="TOTAL A PAGAR:" v-model="prevalorada.montoTotal" step="0.01" type="number" required />
            </div>
            <div class="col-3">
              <q-input outlined label="Descripcion:" v-model="prevalorada.descripcion" required />
            </div>-->
          </div>
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <div class="row">
            <div class="col-6">
              <q-btn type="submit" class="full-width" icon="o_add_circle" label="Realizar venta" :loading="loading" no-caps color="green" />
            </div>
            <div class="col-6">
              <q-btn class="full-width" icon="undo" @click="saleDialog=false" label="Atras" no-caps color="red" />
            </div>
          </div>
        </q-card-section>
      </q-form>
    </q-card>
  </q-dialog>
  <div id="myelement" class="hidden"></div>

</div>
</q-page>
</template>

<script>
import {date} from "quasar";
import conversor from "conversor-numero-a-letras-es-ar";
import QRCode from "qrcode";
import {Printd} from "printd";

export default {
  name: `Prevalorada`,

  data(){
    return{
      loading:false,
      fechaInicio:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      prevaloradas:[],
      cantidad:0,
      vehiculos:[],
      meses:['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'],
      gestiones:[
        parseInt(date.formatDate(new Date(),'YYYY')) -2,
        parseInt(date.formatDate(new Date(),'YYYY')) - 1,
        parseInt(date.formatDate(new Date(),'YYYY')),
        parseInt(date.formatDate(new Date(),'YYYY')) + 1,
        parseInt(date.formatDate(new Date(),'YYYY')) + 2,
      ],
      prevalorada:{
        montoTotal:0,
        periodoFacturado:'ENERO 2022',
        mes:'ENERO',
        descripcion:'',
        gestion:date.formatDate(new Date(),'YYYY'),
      },
      client:{},
      documents:[],
      document:'',
      saleDialog:false,
      vehiculo:{},
      columns:[
        {label:'Opciones',name:'Opciones',field:'Opciones'},
        {label:"Factura",name:"numeroFactura",field:"numeroFactura",sortable:"true"},
        {label:'siatEnviado',name:'siatEnviado',field:'siatEnviado',sortable:true},
        {label:"Fecha Emision",name:"fechaEmision",field:"fechaEmision",sortable:"true"},
        // {label:"Periodo Facturado",name:"periodoFacturado",field:"periodoFacturado",sortable:"true"},
        {label:"Descripcion",name:"descripcion",field:"descripcion",sortable:"true"},
        {label:"Monto",name:"montoTotal",field:"montoTotal",sortable:"true"},
        {label:"Usuario",name:"usuario",field:"usuario",sortable:"true"},
        // {label:'client_id',name:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {label:'siatAnulado',name:'siatAnulado',field:'siatAnulado',sortable:true},
        {label:'id',name:'id',field:'id',sortable:true},
      ],
      cine:{},
      leyendas:[],
      opts : {
        errorCorrectionLevel: 'M',
        type: 'png',
        quality: 0.95,
        width: 60,
        margin: 1,
        color: {
          dark: '#000000',
          light: '#FFF',
        },
      }

    }
  },
  mounted() {
    this.$api.get('datocine').then(res => {
      this.cine = res.data;
    })
    this.$api.get('vehiculo').then(res => {
      res.data.forEach(r=>{
        r.label=r.descripcion
      })
      this.vehiculos = res.data;
      this.vehiculo=this.vehiculos[0]
    })
    this.prevaloradaConsulta();
    this.$api.get('document').then(res=>{
      res.data.forEach(r=>{
        r.label=r.descripcion
      })
      this.documents=res.data
      this.document=this.documents[0]
    })

  },
  methods:{
    prevaloradaInsert(){
      if(this.cantidad<=0 || this.cantidad==undefined || this.cantidad==''){
          return false
      }
      this.loading=true
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.$api.post('prevalorada',{
        client:this.client,
        vehiculo:this.vehiculo,
        cantidad:this.cantidad,
        // periodoFacturado:this.prevalorada.mes+' '+this.prevalorada.gestion,
      }).then(res=>{
        console.log(res.data)
        res.data.forEach(r=>{
          this.printFactura(r);
        })
        this.prevaloradaConsulta()
        this.cantidad=1;
        this.saleDialog=false
        this.loading=false
        // this.eventSearch()
      }).finally(()=>{
        this.loading=false
      })
        .catch(err=>{
        console.log(err)
        this.loading=false
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response.data.message,
          position: 'top',
          timeout: 5000,
        })
      })
    },
    async printFactura(factura) {
      let max=this.leyendas.length - 1;
      this.facturadetalle = factura
      let ClaseConversor = conversor.conversorNumerosALetras;
      let miConversor = new ClaseConversor();
      let a = miConversor.convertToText( parseInt(factura.montoTotal));
      this.qrImage = await QRCode.toDataURL(this.cine.url2+"consulta/QR?nit="+this.cine.nit+"&cuf="+factura.cuf+"&numero="+factura.numeroFactura+"&t=2", this.opts)
      let cadena = "<style>\
      .titulo{\
      font-size: 8px;\
      text-align: center;\
      font-weight: bold;\
      }\
      .titulo2{\
      font-size: 7px;\
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
      font-size: 6px;\
      text-align: right;\
      }\
      .titder{\
      font-size: 6px;\
      text-align: right;\
      font-weight: bold;\
      }\
      .titizq{\
      font-size: 14px;\
      text-align: left;\
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
    <table>\
    <tr>\
    <td style='width:50%'>      \
    <div class='titulo2'>"+this.cine.razon+"<br>\
        Casa Matriz<br>\
        No. Punto de Venta "+factura.codigoPuntoVenta+"<br>\
        "+this.cine.direccion.substring(0,38)+"<br>"+this.cine.direccion.substring(38)+"<br>\
        Tel. "+this.cine.telefono+"<br>\
        Oruro\
      </div></td>\
    <td style='width: 50%'>\
      <div class='titulo'>NIT</div>\
      <div class='titulo2'>"+this.cine.nit+"</div>\
      <div class='titulo'>FACTURA N°</div>\
      <div class='titulo2'>"+factura.numeroFactura+"</div>\
      <div class='titulo'>CÓD. AUTORIZACIÓN</div>\
      <div class='titulo2' style='text-align: left;' >"+factura.cuf.substring(0,17)+"<br>"+factura.cuf.substring(17,34)+"<br>"+factura.cuf.substring(34,51)+"<br>"+factura.cuf.substring(51)+"</div> </td>\
    </tr>\
    </table>\
    <div class='titder'>OTRAS ACTIVIDADES DE TRANSPORTE COMPLEMENTARIA <br>(TERMINAL DE BUSES, PLAYAS DE ESTACIONAMIENTO, ETC)</div>"
      cadena += "<table>\
      <tr><td class='titizq'>FACTURA</td><td style='text-align: right'><span style='font-size: 12px'>Bs. " + parseFloat(factura.montoTotal).toFixed(2) + "</span><br><span style='font-size: 6px'>Son " + a + " " + (parseFloat(factura.montoTotal).toFixed(2) - Math.floor(parseFloat(factura.montoTotal).toFixed(2))) * 100 + "/100 Bolivianos</span></td></tr>\
      </table>\
            <div style='font-size: 10px; text-align: center'>Oruro, " + factura.fechaEmision.substring(0,10) +"</div><br>\
      <div style='font-size: 6px'>'ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY'<br>\
      "+factura.leyenda+" \
      </div><br>\
     </div>\
       <img src="+this.qrImage+" >\
      "
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

    searchClient(){
      // console.log(this.client)
      this.document=this.documents[0]
      this.client.nombreRazonSocial=''
      this.client.id=undefined
      this.$api.post('searchClient',this.client).then(res=>{
        // console.log(res.data)
        if (res.data.nombreRazonSocial!=undefined) {
          this.client.nombreRazonSocial=res.data.nombreRazonSocial
          this.client.id=res.data.id
          let documento=this.documents.find(r=>r.codigoClasificador==res.data.codigoTipoDocumentoIdentidad)
          documento.label=documento.descripcion
          this.document=documento
        }
      })
    },
    prevaloradaClickCreate(){
      this.prevalorada.montoTotal=''
      this.prevalorada.descripcion=''
      this.cantidad=1
      this.saleDialog=true
      this.client={complemento:''}
    },
    printButton () {
      navigator.bluetooth.requestDevice(
          {
            filters: [
              {
                name: 'BlueTooth Printer',
                services: ['000018f0-0000-1000-8000-00805f9b34fb']
              }
            ]
          },
          {
            optionalServices: ['00002af1-0000-1000-8000-00805f9b34fb']
          }
        )
        .then(device => {
          console.log('device', device)
          if (device.gatt.connected) {
            device.gatt.disconnect()
          }
          console.log('connect')
          return this.connect(device)
        })
        .catch(this.handleError)
    },
    connect (device) {
      const self = this
      device.addEventListener('gattserverdisconnected', this.onDisconnected)
      return device.gatt
        .connect()
        .then(server =>
          server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb')
        )
        .then(service =>
          service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb')
        )
        .then(characteristic => {
          console.log('characteristic', characteristic)
          self.printCharacteristic = characteristic
          self.sendTextData(device)
        })
        .catch(error => {
          this.handleError(error, device)
        })
    },
    handleError (error, device) {
      console.error('handleError => error', error)
      if (device != null) {
        device.gatt.disconnect()
      }
      let erro = JSON.stringify({
        code: error.code,
        message: error.message,
        name: error.name
      })
      console.log('handleError => erro', error)
      if (erro.code !== 8) {
        this.$q.notify('Could not connect with the printer. Try it again')
      }
    },
    getBytes (text) {
      console.log('text', text)
      let br = '\u000A\u000D'
      text = text === undefined ? br : text
      let replaced = this.$languages.replace(text)
      console.log('replaced', replaced)
      let bytes = new TextEncoder('utf-8').encode(replaced + br)
      console.log('bytes', bytes)
      return bytes
    },
    addText (arrayText) {
      let text = 'cadena de prueba'
      arrayText.push(text)
      if (this.isMobile) {
        while (text.length >= 20) {
          let text2 = text.substring(20)
          arrayText.push(text2)
          text = text2
        }
      }
    },
    sendTextData (device) {
      let arrayText = []
      this.addText(arrayText)
      console.log('sendTextData => arrayText', arrayText)
      this.loop(0, arrayText, device)
    },
    loop (i, arrayText, device) {
      let arrayBytes = this.getBytes(arrayText[i])
      this.write(device, arrayBytes, () => {
        i++
        if (i < arrayText.length) {
          this.loop(i, arrayText, device)
        } else {
          let arrayBytes = this.getBytes()
          this.write(device, arrayBytes, () => {
            device.gatt.disconnect()
          })
        }
      })
    },
    write (device, array, callback) {
      this.printCharacteristic
        .writeValue(array)
        .then(() => {
          console.log('Printed Array: ' + array.length)
          setTimeout(() => {
            if (callback) {
              callback()
            }
          }, 250)
        })
        .catch(error => {
          this.handleError(error, device)
        })
    },
    prevaloradaConsulta(){
      this.loading=true
      this.$api.post('prevaloradaConsulta',{
        fechaInicio:this.fechaInicio,
        fechaFin:this.fechaFin,
      }).then(res=>{
        this.loading=false
        // console.log(res.data)
        this.prevaloradas=res.data
      })
    }
  }
}
</script>

<style scoped>

</style>
