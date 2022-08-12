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
          <div v-for="r in rubros" :key="r.id" class="col-2 col-md-3">
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
          <div class="col-2 col-md-3" v-for="p in productos" :key="p.id">
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
            <q-icon name="point_of_sale"></q-icon> Venta <q-btn @click="reset"  color="negative" size="xs" icon="restart_alt" label="cancelar"/><q-btn @click="icon = true;tienerebaja=false;"  color="positive" label="Venta" size="xs" icon="add_circle"/>
          </q-card-section>
          <q-card-section class="q-pa-xs">
            <table style="width: 100%;border: 1px solid black" >
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
                <td>{{i.cantidad}}</td>
                <td>{{i.subtotal}}</td>
                <td>
                  <q-btn @click="agregar(index)" size="xs" icon="add" color="green"></q-btn>
                  <q-btn @click="disminuir(index)" size="xs" icon="remove" color="warning"></q-btn>
                  <q-btn @click="quitar(index)" size="xs" icon="delete" color="negative"></q-btn>
                </td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>TOTAL</th>
                <th>{{total}}</th>
              </tr>
              </tfoot>
            </table>
          </q-card-section>
        </q-card>
      </div>
</div>
    </q-card>
    <q-dialog v-model="icon" full-width>
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
                  <div class="col-3">
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
                  <div class="col-3">
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
                    <q-select v-model="document" outlined :options="documents"/>
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
                  <div class="col-2"><q-checkbox v-model="credito"  label="T Credito"/></div>
                  <div class="col-2"><q-checkbox @input="verificar" v-model="tarjeta"  label="Tarjeta"/></div>

                </div>
                <div>
                  <q-btn  label="venta" icon="send" type="submit" color="positive" :disable="btn"/>
                  <q-btn label="Cerrar" type="button" size="md" icon="delete" color="negative" class="q-ml-sm" @click="icon=false" />
                </div>
              </q-form>


            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>



  </q-page>
</template>

<script>
import {date} from "quasar";
import {globalStore} from "stores/globalStore";

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


    }
  },
  created() {
    this.listado()
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

    verificar(){
      // console.log(this.booltargeta)
      this.codigo=''
      this.nombresaldo=''
      if (!this.booltargeta){
        if (this.tienerebaja){
          this.$store.state.products.forEach(r=>{
            r.precio=(1.25*r.precio).toFixed(2)
            r.subtotal=(1.25*r.subtotal).toFixed(2)
          })
          this.btn=false
          this.tienerebaja=false
        }
      }
    },
    reset(){
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
      this.loading=true
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.$api.post('salecandy',{
        client:this.client,
        montoTotal:this.total,
        detalleVenta:this.store.detallecandy,
      }).then(res=>{
        console.log(res.data)
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
