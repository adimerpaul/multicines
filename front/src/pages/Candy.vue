<template>
  <q-page>
    <q-card>
      <q-card-section class="q-py-xs bg-green-7 text-white text-bold" >
        <div class="row">
          <div class="col-2 flex flex-center">
            <q-icon name="live_tv" left/> PANEL DE VENTAS
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-py-none">
        <div class="row">
          <div class="col-6"><div class="text-h6"><q-icon name="store"/> RUBROS</div></div>
        </div>
      </q-card-section>
      <q-separator />

<div class="col-8">
      <q-card-section>
        <div class="row">
          <div class="col-2" v-for="r in rubros" :key="r.id">
            <q-card @click="productos=(r.productos)" class="q-ma-xs flex flex-center" :style="'background: '+r.color">
              <q-img :src="url+'../imagen/'+r.imagen" height="100px" width="100px">
                <div class="absolute-bottom text-subtitle2 text-center" style="padding: 0px;margin:0px;border: 0px">
                  <div class="row">
                    <div class="col-12 q-pa-none q-ma-none">
                      {{r.nombre}}
                    </div>
                  </div>
                </div>
              </q-img>
            </q-card>
          </div>

        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row">
          <div class="col-2" v-for="p in productos" :key="p.id">
            <q-card @click="agregar(p)"  class="q-ma-xs" :style="'background: '+p.color">
              <q-img :src="url+'../imagen/'+p.imagen" height="100px" width="100px"/>
                <div class="absolute-bottom text-subtitle2 text-center" style="padding: 0px;margin:0px;border: 0px">
                  <div class="row">
                    <div class="col-6 q-pa-none q-ma-none">
                      {{p.nombre}}
                    </div>
                    <div class="col-6 text-right flex flex-center">{{p.precio}} Bs</div>
                  </div>
                </div>
            </q-card>
          </div>

        </div>

      </q-card-section>
      <q-separator />
</div>
      <div class="col-4"></div>

    </q-card>



  </q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `Sale`,

  data() {
    return {
      saleDialog:false,
      url:process.env.API,
      now:date.formatDate(new Date(), "YYYY-MM-DD"),
      efectivo:'',
      rubros:[],
      productos:[],
      client:{complemento:''},
      temporal:[],
      loading:false,
      documents:[],
      document:{},
      credito:false,

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
    })
  },
  methods: {
    listado(){
      this.$api.post('listadoprod').then(res=>{
        console.log(res.data)
          this.rubros=res.data
      })

        },
    saleInsert(){
      this.loading=true
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.$api.post('sale',{
        client:this.client,
        montoTotal:this.total,
        detalleVenta:this.detalleVenta,
      }).then(res=>{
        console.log(res.data)
        // this.client={complemento:''}
        // this.saleDialog=false
        // this.myMomentaneo();
        // this.myMovies(this.fecha)
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
