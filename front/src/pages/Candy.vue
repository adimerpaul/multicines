<template>
  <q-page>
    <q-card>
      <q-card-section class="q-py-xs bg-purple-7 text-white text-bold" >
        <div class="row">
          <div class="col-12 flex flex-center text-h5">
            <q-icon name="storefront" left/> CANDY BAR
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-py-none">
        <div class="row">
          <div class="col-6"><div class="text-h6"><q-icon name="store"/> RUBROS</div></div>
        </div>
      </q-card-section>
      <q-separator />
<div class="row">
<div class="col-12 col-md-8">
      <q-card-section>
        <div class="row">
          <div v-for="r in rubros" :key="r.id" class="col-2 col-md-2">
              <q-card @click="productos=r.productos" class="q-pa-xs">
                <q-img
                  :style="'background: '+r.color"
                  :src="r.imagen!=null?url+'../imagen/'+r.imagen:''"
                  :alt="r.nombre"
                  basic
                  style="height: 140px"
                >
                  <div class="absolute-bottom text-subtitle2 text-center">
                    {{r.nombre}}
                  </div>
                </q-img>
              </q-card>
          </div>

        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row">
          <div class="col-6"><div class="text-h6"><q-icon name="shopping_cart"/> PRODUCTOS</div></div>
        </div>
        <div class="row">
          <div class="col-2 col-md-2" v-for="p in productos" :key="p.id">
            <q-card @click="miventa(p)"  class="q-ma-xs" :style="'background: '+p.color">
              <q-img
                :style="'background: '+p.color"
                :src="p.imagen!=null?url+'../imagen/'+p.imagen:''"
                :alt="p.nombre"
                basic
                style="height: 140px"
              >
                <div class="absolute-bottom text-subtitle2 text-center">

                  <div class="row">
                    <div class="col-6"> {{p.nombre}}</div>

                    <div class="col-6">{{p.precio}}Bs</div>
                  </div>
                </div>
              </q-img>
            </q-card>
          </div>

        </div>

      </q-card-section>
      <q-separator />
</div>
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section class="bg-accent text-white q-pa-xs">
            <div class="row">
              <div class="col-4 flex flex-center" ><q-icon name="point_of_sale"></q-icon> VENTA </div>
              <div class="col-4 text-center" ><q-btn @click="reset"  color="negative" size="14px" icon="restart_alt" label="cancelar"/></div>
              <div class="col-4 text-center" ><q-btn @click="icon = true;tienerebaja=false; booltarjeta=false; tarjeta=false;"  color="positive" label="Venta" size="14px" icon="add_circle"/></div>
            </div>

          </q-card-section>
          <q-card-section class="q-pa-xs">
            <table >
              <thead>
              <tr class="bg-accent text-white">
                <th>#</th>
                <th>Nombre producto</th>
                <th>Cant.</th>
                <th>Subt.</th>
                <th>Opc</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(i,index) in store.detallecandy" :key="index">
                <td>{{index+1}}</td>
                <td>{{i.nombre}}</td>
                <td class="text-center">{{i.cantidad}}</td>
                <td class="text-right">{{i.subtotal}}Bs</td>
                <td>
                  <q-btn @click="agregar(index)" size="12px" icon="add_circle_outline" color="green"></q-btn>
                  <q-btn @click="disminuir(index)" size="12px" icon="remove_circle_outline" color="warning"></q-btn>
                  <q-btn @click="quitar(index)" size="12px" icon="o_delete" color="negative"></q-btn>
                </td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <th></th>
                <th></th>
                <th>TOTAL</th>
                <th class="text-right">{{total}}Bs</th>
              </tr>
              </tfoot>
            </table>
          </q-card-section>
        </q-card>
      </div>
</div>
    </q-card>
    <q-dialog v-model="icon" full-width persistent>
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="row items-center q-pa-xs bg-green-14 text-white">
          <div class="text-h6"> <q-icon name="send"></q-icon> Realizar venta</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-12">
              <q-form
                @submit.prevent="saleInsert"
                class="q-gutter-md"
              >
                <div class="row">
                  <div class="col-2">
                    <q-input
                      required
                      @keyup="searchClient"
                      outlined
                      v-model="client.numeroDocumento"
                      label="CI / NIT *"
                      hint="Carnet o nit"
                      lazy-rules
                      :rules="[ val => val && val.length > 0 || 'Dato obligatorio']"
                    />
                  </div>
                  <div class="col-2">
                    <q-input
                      @keyup="searchClient"
                      outlined
                      v-model="client.complemento"
                      label="COMPLEMENTO"
                    />
                  </div>
                  <div class="col-3">
                    <q-input
                      required
                      outlined
                      v-model="client.nombreRazonSocial"
                      label="Nombre y razon *"
                      hint="Razon social"
                      style="text-transform: uppercase"
                      lazy-rules
                      :rules="[ val => val && val.length > 0 || 'Dato obligatorio']"
                    />
                  </div>

                  <div class="col-3">
                    <q-select v-model="document" outlined :options="documents" @update:model-value="validarnit"/>
                  </div>
                  <div class="col-2">
                    <q-input
                      outlined
                      v-model="client.email"
                      label="Email"
                      type="email"
                    />
                  </div>
                  <div class="col-3 q-pa-xs">
                    <q-input
                      required
                      outlined
                      disable
                      v-model="total"
                      label="Total*"
                      lazy-rules
                    />
                  </div>
                  <div class="col-3 q-pa-xs">
                    <q-input
                      v-model="efectivo"
                      outlined
                      label="Monto recibido"

                    />
                  </div>
                  <div class="col-3 q-pa-xs">
                    <q-input
                      outlined
                      disable
                      label="Cambio"
                      v-model="cambio"
                    />
                  </div>
                  <div class="row col-3">
                    <q-checkbox v-model="credito"  label="T Credito" dense/>
                    <q-checkbox  v-model="booltarjeta"  label="T VIP" dense @click="verificar"/>
                  </div>
                  <div class="col-12">
                  <template v-if="booltarjeta">
                      <q-form @submit.prevent="consultartarjeta">
                      <div class="row">
                      <div class="col-6"><q-input outlined label="Codigo" v-model="codigo"  @keyup="consultartarjeta"/></div>
                      <div class="col-6"><q-banner >Saldo :{{nombresaldo.saldo}} -- {{nombresaldo.nombre}}</q-banner></div>
                    </div>
                    </q-form>
                  </template>
                </div>
              </div>
                <div>
                  <q-btn  label="venta" :loading="loading" icon="send" type="submit" color="positive" :disable="btn"/>
                  <q-btn label="Cerrar" :loading="loading" type="button" size="md" icon="delete" color="negative" class="q-ml-sm" @click="cancelarVenta" />
                </div>
              </q-form>


            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <div id="myelement" class="hidden"></div>


  </q-page>
</template>

<script>
import {date} from "quasar";
import {globalStore} from "stores/globalStore";
import conversor from "conversor-numero-a-letras-es-ar";
import QRCode from "qrcode";
import {Printd} from "printd";

export default {
  name: `Sale`,

  data() {
    return {
      saleDialog:false,
      url:process.env.API,
      now:date.formatDate(new Date(), "YYYY-MM-DD"),
      efectivo:'',
      btn:false,
      rubros:[],
      codigo:'',
      productos:[],
      client:{complemento:''},
      temporal:[],
      loading:false,
      documents:[],
      document:{},
      credito:false,
      store: globalStore(),
      icon:false,
      tarjeta:false,
      cine:{},
      leyendas:[],
      nombresaldo:{},
      tienerebaja:false,
      booltarjeta:false,
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
  created() {
    this.listado()
    this.encabezado()
    this.cargarLeyenda()
    this.$api.get('document').then(res=>{
      res.data.forEach(r=>{
        r.label=r.descripcion
      })
      this.documents=res.data
      this.document=this.documents[0]
    });
      this.$api.get('document').then(res=>{
        res.data.forEach(r=>{
          r.label=r.descripcion
        })
        this.documents=res.data
        this.document=this.documents[0]
      })
  },
  methods: {
    cancelarVenta(){
      this.codigo=''
      this.booltarjeta=false
      this.nombresaldo={}
      this.icon=false
      this.verificar()
    },
    validarnit(){
      if(this.document==this.documents[4]){
        this.$api.get('validanit/'+this.client.numeroDocumento).then(res=>{
          console.log(res.data)
          this.$q.notify({
            message: res.data.RespuestaVerificarNit.mensajesList.descripcion,
            color: 'teal',
            icon: 'info'
          })
        })

      }
    },
    consultartarjeta(){
      if (this.codigo!='' || this.codigo!=undefined){
        this.nombresaldo=''
        this.codigo=this.codigo.replaceAll(' ','');
        if (this.tienerebaja){
          this.store.detallecandy.forEach(r=>{
            r.precio=(1.25*r.precio).toFixed(2)
            r.subtotal=(1.25*r.subtotal).toFixed(2)
          })
          this.btn=false
          this.tienerebaja=false
        }
        this.$api.get('validarTarjeta/'+this.codigo).then(res=>{
          console.log(res.data)
          this.$q.loading.hide()
          if (res.data=='0' || res.data==''){
          }else{
            this.nombresaldo=res.data
            if (!this.tienerebaja){
              this.store.detallecandy.forEach(r=>{
                r.precio=(0.8*r.precio).toFixed(2)
                r.subtotal=(0.8*r.subtotal).toFixed(2)
              })
              this.tienerebaja=true
              if ( parseFloat(this.total) <= parseFloat(this.nombresaldo.saldo)){
                this.btn=false
              }else {
                this.btn=true
              }
            }
          }
        })
      }
    },
    verificar(){
      this.codigo=''
      this.nombresaldo=''
      if (!this.booltarjeta){
        if (this.tienerebaja){
          this.store.detallecandy.forEach(r=>{
            r.precio=(1.25*r.precio).toFixed(2)
            r.subtotal=(1.25*r.subtotal).toFixed(2)
          })
          this.btn=false
          this.tienerebaja=false
        }
      }
    },
    reset(){
      this.client={complemento:''}
      this.store.detallecandy=[]
    },
    quitar(index){
      this.store.detallecandy.splice(index,1);
    },
    agregar(index){
      let product=this.store.detallecandy[index];
      this.store.detallecandy[index].cantidad++;
      this.store.detallecandy[index].subtotal= (parseFloat(product.precio)* parseFloat(this.store.detallecandy[index].cantidad)).toFixed(2);
    },
    disminuir(index){
      let product=this.store.detallecandy[index];
      this.store.detallecandy[index].cantidad--;
      this.store.detallecandy[index].subtotal= (parseFloat(product.precio)* parseFloat(this.store.detallecandy[index].cantidad)).toFixed(2);
      if( this.store.detallecandy[index].cantidad==0)
        this.store.detallecandy.splice(index,1);
    },
    miventa(product){
      let index=this.store.detallecandy.findIndex(p=>p.product_id===product.id);
      if (index==-1){
        this.store.detallecandy.push({'product_id':product.id,'nombre':product.nombre,'precio':product.precio,'cantidad':1,'subtotal':product.precio});
      }else{
        this.store.detallecandy[index].cantidad++;
        this.store.detallecandy[index].subtotal= (parseFloat(product.precio)* parseFloat(this.store.detallecandy[index].cantidad)).toFixed(2) ;
      }
    },
    listado(){
      this.$api.post('listadoprod').then(res=>{
        console.log(res.data)
          this.rubros=res.data
      })

        },
    saleInsert(){
      if (this.client.numeroDocumento==0) {
        this.$q.notify({
          color: 'red',
          textColor: 'white',
          message: 'Debe ingresar un numero de documento'
        })
        return false
      }
      this.error=''
      this.loading=true
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.client.email=this.client.email==undefined?'':this.client.email
      this.$api.post('salecandy',{
        client:this.client,
        montoTotal:this.total,
        detalleVenta:this.store.detallecandy,
        tarjeta:this.credito?'SI':'NO',
        codigoTarjeta:this.codigo,
        vip:this.booltarjeta?'SI':'NO',
      }).then(res=>{
        this.reset()
          if(res.data.sale.siatEnviado==1){
            this.printFactura(res.data.sale)
          }
        this.printComanda(res.data.sale)
        this.icon=false
        // console.log(res.data)
        this.loading=false
      }).catch(err=>{
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
        if(this.document.codigoClasificador==5) this.validarnit()
      })
    },
    async printFactura(factura) {
      // console.log(factura)
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
        <tr><td class='titder'>NIT/CI/CEX:</td><td class='contenido'>" + factura.client.numeroDocumento +'-'+factura.client.complemento + "</td></tr>\
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

    cargarLeyenda(){
      this.$api.post('listleyenda',{codigo:'590000'}).then(res => {
        // console.log(res.data)
        this.leyendas=res.data;
      })

    },
    encabezado(){
      this.$api.get('datocine').then(res => {
        this.cine = res.data;
        // console.log(this.cine)
      })
    },



  },
  computed:{
    cambio(){
      let cambio=parseFloat(this.efectivo==''?0:this.efectivo)- parseFloat(this.total)
      return  Math.round(cambio*100)/100
    },
      total:function (){
        let t=0;
        this.store.detallecandy.forEach(r=>{
          t+= parseFloat(r.precio)*parseFloat(r.cantidad);
        })
        return t.toFixed(2);
    }



  }
}
</script>

<style scoped>
table{
  width: 100%;
}
table, .tdx, th {
  border-collapse: collapse;
  border: 1px solid #ddd;
  padding: 5px;
}
input{
  border: 1px solid #ddd;
}
</style>
