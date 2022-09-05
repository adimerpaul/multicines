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
<!--          <q-btn icon="print" @click="printFactura(props.row)"/>-->
<!--          <q-btn icon="list" @click="detalleimp(props.row)"/>-->
          <q-btn type="a" label="CLick" target="_blank" :href="`${cine.url2}consulta/QR?nit=${cine.nit}&cuf=${props.row.cuf}&numero=${props.row.numeroFactura }&t=2`" />
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

</div>
</q-page>
</template>

<script>
import {date} from "quasar";

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
      ]

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
      if(this.cantidad==0 || this.cantidad==undefined || this.cantidad==''){
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
        // this.printFactura(res.data.sale)
        // res.data.tickets.forEach(r=>{
        //   this.boletoprint(r)
        // })
        // this.momentaneoDeleteAll()
        // this.tventa()
        // this.client={complemento:''}
        // this.saleDialog=false
        // this.myMovies(this.fecha)
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
