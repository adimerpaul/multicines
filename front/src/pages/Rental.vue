<template>
<q-page>
<div class="row">
  <div class="col-12">
    <div class="row">
      <div class="col-3"><q-input dense type="date" outlined label="fechaInicio" v-model="fechaInicio" /></div>
      <div class="col-3"><q-input dense type="date" outlined label="fechaFin" v-model="fechaFin" /></div>
      <div class="col-3 flex flex-center"><q-btn color="primary" :loading="loading" label="Consultar" class="full-width"  icon="search" @click="rentalConsulta" /></div>
      <div class="col-3 flex flex-center"><q-btn color="green" label="AGREGAR " class="full-width"  icon="add_circle_outline" @click="rentalClickCreate" /></div>
    </div>
  </div>
  <div class="col-12">
    <q-table label="FACTURA DE ALQUILER DE AMBIENTES" :rows="rentals" :columns="columns">
      <template v-slot:body-cell-Opciones="props">
        <q-td :props="props" auto-width>
          <q-btn-dropdown color="primary" label="Opciones" >
            <q-list>
              <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                <q-item-section>
                  <q-btn icon="print" color="primary" class="full-width" label="Imprimir" no-caps @click="printFactura(props.row)" v-if="props.row.siatAnulado==0"/>
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
      <q-form @submit.prevent="rentalInsert">
        <q-card-section>
          <div class="row">
            <div class="col-2">
              <q-input outlined label="NIT/CARNET" @keyup="searchClient" required v-model="client.numeroDocumento" />
            </div>
            <div class="col-2">
              <q-input outlined label="Complemento"  @keyup="searchClient" v-model="client.complemento" />
            </div>
            <div class="col-3">
              <q-input outlined label="nombreRazonSocial" required v-model="client.nombreRazonSocial" />
            </div>
            <div class="col-3">
              <q-select v-model="document" outlined :options="documents"/>
            </div>
            <div class="col-2">
              <q-input outlined label="Email"  v-model="client.email" type="email"/>
            </div>
          </div>
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <div class="row">
            <div class="col-3">
              <q-input outlined label="TOTAL A PAGAR:" v-model="rental.montoTotal" step="0.01" type="number" required />
            </div>
            <div class="col-3">
              <q-select outlined label="Mes:" v-model="rental.mes" :options="meses" />
            </div>
            <div class="col-3">
              <q-select outlined label="Periodo:" v-model="rental.gestion" :options="gestiones" />
            </div>
            <div class="col-3">
              <q-input outlined label="Descripcion:" v-model="rental.descripcion" required />
            </div>
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
        <q-btn flat label="ANULAR" @click="enviarAnular" :loading="loading"/>
      </q-card-actions>
    </q-card>
  </q-dialog>
</div>
  <div id="myelement" class="hidden"></div>

</q-page>
</template>

<script>
import {date} from "quasar";
import conversor from "conversor-numero-a-letras-es-ar";
import QRCode from "qrcode";
import {Printd} from "printd";

export default {
  name: `Rental`,

  data(){
    return{
      dialogAnular:false,
      loading:false,
      fechaInicio:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      rentals:[],
      meses:['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'],
      gestiones:[
        parseInt(date.formatDate(new Date(),'YYYY')) -2,
        parseInt(date.formatDate(new Date(),'YYYY')) - 1,
        parseInt(date.formatDate(new Date(),'YYYY')),
        parseInt(date.formatDate(new Date(),'YYYY')) + 1,
        parseInt(date.formatDate(new Date(),'YYYY')) + 2,
      ],
      rental:{
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
      qrImage:'',
      cine:{},

      columns:[
        {label:'Opciones',name:'Opciones',field:'Opciones'},
        {label:"Factura",name:"numeroFactura",field:"numeroFactura",sortable:"true"},
        {label:'siatEnviado',name:'siatEnviado',field:'siatEnviado',sortable:true},
        {label:"Fecha Emision",name:"fechaEmision",field:"fechaEmision",sortable:"true"},
        {label:"Periodo Facturado",name:"periodoFacturado",field:"periodoFacturado",sortable:"true"},
        {label:"Descripcion",name:"descripcion",field:"descripcion",sortable:"true"},
        {label:"Monto",name:"montoTotal",field:"montoTotal",sortable:"true"},
        {label:"Usuario",name:"usuario",field:"usuario",sortable:"true"},
        {label:'client_id',name:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {label:'siatAnulado',name:'siatAnulado',field:'siatAnulado',sortable:true},
        {label:'id',name:'id',field:'id',sortable:true},
      ],
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
    this.encabezado()
    this.rental.mes=this.meses[parseInt(date.formatDate(new Date(),'M')-1)]
    this.$api.get('datocine').then(res => {
      this.cine = res.data;
    })
    this.rentalConsulta();
    this.$api.get('document').then(res=>{
      res.data.forEach(r=>{
        r.label=r.descripcion
      })
      this.documents=res.data
      this.document=this.documents[0]
    })
    this.cargarMotivo()
  },
  methods:{
    anularSale(factura){
      //console.log(factura)
      this.factura=factura
      this.dialogAnular=true
    },
    enviarAnular(){
      this.loading=true

      this.$api.post('anularRental',{rental:this.factura,motivo:this.motivo}).then(res => {
        console.log(res.data)
        this.rentalConsulta()
        this.dialogAnular=false
        this.loading=false

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
    encabezado(){
      this.$api.get('datocine').then(res => {
        this.cine = res.data;
        // console.log(this.cine)
      })
    },
    rentalInsert(){
      this.loading=true
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.client.email=this.client.email==undefined?'':this.client.email

      this.$api.post('rental',{
        client:this.client,
        montoTotal:this.rental.montoTotal,
        descripcion:this.rental.descripcion,
        periodoFacturado:this.rental.mes+' '+this.rental.gestion,
      }).then(res=>{
        console.log(res.data)
        // this.printFactura(res.data.sale)
        // res.data.tickets.forEach(r=>{
        //   this.boletoprint(r)
        // })
        // this.momentaneoDeleteAll()
        // this.tventa()
        // this.client={complemento:''}
        // this.saleDialog=false
        // this.myMovies(this.fecha)
        this.saleDialog=false
        if(res.data.rental.siatEnviado==1){
          this.printFactura(res.data.rental)
        }
        this.rentalConsulta()
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
      console.log(factura)
      this.facturadetalle = factura
      let ClaseConversor = conversor.conversorNumerosALetras;
      let miConversor = new ClaseConversor();
      let a = miConversor.convertToText( parseInt(factura.montoTotal));
      this.qrImage = await QRCode.toDataURL(this.cine.url2+"consulta/QR?nit="+this.cine.nit+"&cuf="+factura.cuf+"&numero="+factura.numeroFactura+"&t=2", this.opts)
      //console.log(this.qrImage)
      // return false
      let cadena = "<style>\
      .titulo{\
      font-size: 8px;\
      text-align: center;\
      font-weight: bold;\
      }\
      .titulo2{\
      font-size: 8px;\
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
      .titizq{\
      font-size: 12px;\
      text-align: left;\
      font-weight: bold;\
      }\
      hr{\
  border-top: 1px dashed   ;\
}\
  table{\
    width:100%\
    border-collapse: collapse;\
  }\
  .tabenc>tr>td{\
  font-size: 12px;border: 1px solid;\
  font-weight: bold;\
  text-align: center;\
  border-collapse: collapse;\
  }\
  \  .tabcont>tr>td{\
  font-size: 10px;border: 1px solid;\
  text-align: center;\
  border-collapse: collapse;\
  }\
  h1 {\
    color: black;\
    font-family: sans-serif;\
  }</style>\
    <div id='myelement'>\
    <table>\
    <tr><td style='width: 50%'>\
          <div class='titulo2'>"+this.cine.razon+"<br>\
        Casa Matriz<br>\
        No. Punto de Venta "+factura.codigoPuntoVenta+"<br>\
        "+this.cine.direccion.substring(0,33)+"<br>"+this.cine.direccion.substring(33)+"<br>\
        Tel. "+this.cine.telefono+"<br>\
        Oruro\
      </div>\
        </td><td style='vertical-align:top; text-align: right;width: 50%;'>\
        <div class='titulo' style='text-align: right'>NIT</div>\
        <div class='titulo' style='text-align: right'>FACTURA N°</div>\
        <div class='titulo' style='text-align: right'>CÓD. AUTORIZACIÓN</div>\
        </td><td style='vertical-align:top; '>\
        <div class='titulo2' style='text-align: left;'>"+this.cine.nit+"</div>\
        <div class='titulo2' style='text-align: left;'>"+factura.numeroFactura+"</div>\
        <div class='titulo2' style='text-align: left;' >"+factura.cuf.substring(0,17)+"<br>"+factura.cuf.substring(17,34)+"<br>"+factura.cuf.substring(34,51)+"<br>"+factura.cuf.substring(51)+"</div>\
      <td></tr>\
    </table>\
      <div class='titulo'><span style='font-size: 12px'>FACTURA DE ALQUILER</span><br>\(Con Derecho a Crédito Fiscal) </div>\
      <table >\
        <tr><td class='titizq'>FECHA DE EMISIÓN:</td><td class='contenido'>" + factura.fechaEmision + "</td><td style='width: 20%'></td></tc><td class='titder'>NIT/CI/CEX:</td><td class='contenido'>" + factura.client.numeroDocumento + "-" + factura.client.complemento + "</td></tr>\
        <tr><td class='titizq'>NOMBRE/RAZÓN SOCIAL:</td><td class='contenido'>" + factura.client.nombreRazonSocial + "</td><td style='width: 20%'></td><td class='titder'>Cod. Cliente:</td ><td class='contenido'>" + factura.client.id + "</td></tr>\
        <tr><td class='titizq'></td><td class='contenido'></td><td style='width: 20%'></td><td class='titder'>Periodo Facturado:</td><td class='contenido'>" + factura.periodoFacturado + "</td></tr>\
      </table><br>\
      <table style='width: 100%;border-collapse: collapse;'>\
      <thead class='tabenc'>\
      <tr><td>CÓDIGO SERVICIO</td><td>CANTIDAD</td><td>UNIDAD DE MEDIDA</td><td>DESCRIPCIÓN</td><td>PRECIO UNITARIO</td><td>DESCUENTO</td><td>SUBTOTAL</td></tr>\
      </thead>\
      <tbody class='tabcont'>\
      <tr><td>"+factura.codigoProducto+"</td><td>"+factura.cantidad+"</td><td>"+factura.medida+"</td><td>"+factura.descripcion+"</td><td>"+factura.precioUnitario+"</td><td>"+factura.montoDescuento+"</td><td>"+factura.subTotal+"</td></tr>\
      <tr><td colspan='4' style='border: 0'></td><td class='titder' colspan='2'>SUBTOTAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      <tr><td colspan='4' style='border: 0'></td><td class='titder' colspan='2'>DESCUENTO Bs</td><td class='conte2'>0.00</td></tr>\
      <tr><td colspan='4' style='border: 0'></td><td class='titder' colspan='2'>TOTAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      <tr><td colspan='4' style='border: 0'></td><td class='titder' style='font-size: 8px' colspan='2'>IMPORTE BASE CRÉDITO FISCAL Bs</td><td class='conte2'>" + parseFloat(factura.montoTotal).toFixed(2) + "</td></tr>\
      </tbody></table><br>\
      <div>Son " + a + " " + (parseFloat(factura.montoTotal).toFixed(2) - Math.floor(parseFloat(factura.montoTotal).toFixed(2))) * 100 + "/100 Bolivianos</div>\
      <table><tr><td>\
      <div class='titulo2 col-10' style='font-size: 9px'>ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY<br><br>\
"+factura.leyenda+" <br>\
“Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea”</div></td><td>\
<div style='display: flex;justify-content: center;' class='col-2'>\
  <img src="+this.qrImage+" >\
      </div></td></tr>\
              </table>"
      document.getElementById('myelement').innerHTML = cadena
      const d = new Printd()
      d.print( document.getElementById('myelement') )

    },

    searchClient(){
      // console.log(this.client)
      this.document=this.documents[0]
      this.client.nombreRazonSocial=''
      this.client.email=''
      this.client.id=undefined
      this.$api.post('searchClient',this.client).then(res=>{
        // console.log(res.data)
        if (res.data.nombreRazonSocial!=undefined) {
          this.client.nombreRazonSocial=res.data.nombreRazonSocial
          this.client.email=res.data.email
          this.client.id=res.data.id
          let documento=this.documents.find(r=>r.codigoClasificador==res.data.codigoTipoDocumentoIdentidad)
          documento.label=documento.descripcion
          this.document=documento
        }
      })
    },
    rentalClickCreate(){
      this.rental.montoTotal=''
      this.rental.descripcion=''
      this.saleDialog=true
      this.client={complemento:''}
    },
    rentalConsulta(){
      this.loading=true
      this.$api.post('rentalConsulta',{
        fechaInicio:this.fechaInicio,
        fechaFin:this.fechaFin,
      }).then(res=>{
        this.loading=false
        // console.log(res.data)
        this.rentals=res.data
      })
    }
  }
}
</script>

<style scoped>

</style>
