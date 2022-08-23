<template>
<q-page>
<div class="row">
  <div class="col-12">
    <div class="row">
      <div class="col-3"><q-input dense type="date" outlined label="fechaInicio" v-model="fechaInicio" /></div>
      <div class="col-3"><q-input dense type="date" outlined label="fechaFin" v-model="fechaFin" /></div>
      <div class="col-3 flex flex-center"><q-btn color="primary" label="Consultar" class="full-width"  icon="search" @click="rentalConsulta" /></div>
      <div class="col-3 flex flex-center"><q-btn color="green" label="AGREGAR " class="full-width"  icon="send" @click="" /></div>
    </div>


  </div>
  <div class="col-12">
  <q-table label="FACTURA DE ALQUILER DE AMBIENTES" :rows="rentals" :columns="columns"/>


  </div>
  <q-dialog full-width v-model="saleDialog">
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Realizar venta</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-form @submit.prevent="saleInsert">
        <q-card-section>
          <div class="row">
            <div class="col-3">
              <q-input outlined label="NIT/CARNET" @keyup="searchClient" required v-model="client.numeroDocumento" />
            </div>
            <div class="col-3">
              <q-input outlined label="Complemento"  @keyup="searchClient" v-model="client.complemento" />
            </div>
            <div class="col-3">
              <q-input outlined label="nombreRazonSocial" required v-model="client.nombreRazonSocial" />
            </div>
            <div class="col-3">
              <q-select v-model="document" outlined :options="documents"/>
            </div>
          </div>
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <div class="row">
            <div class="col-3">
              <q-input outlined label="TOTAL A PAGAR:" disable v-model="total" />
            </div>
            <div class="col-3">
              <q-input outlined label="EFECTIVO BS."  @keyup="cambio" v-model="efectivo" />
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

</div>
</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `Rental`,

  data(){
    return{
      fechaInicio:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      rentals:[],
      mes:['ENERO','FEBRERO','MARZO','ABRIL',],
      rental:{
        montoTotal:100,
        periodoFacturado:'ENERO 2022',
      },
      saleDialog:false,
      columns:[
        {label:'Opciones',name:'Opciones',field:'Opciones'},
        {label:"Factura",name:"numeroFactura",field:"numeroFactura",sortable:"true"},
        {label:'siatEnviado',name:'siatEnviado',field:'siatEnviado',sortable:true},

        {label:"Fecha Emision",name:"fechaEmision",field:"fechaEmision",sortable:"true"},
        {label:"Periodo Facturado",name:"periodoFacturado",field:"periodoFacturado",sortable:"true"},
        {label:"Monto",name:"montoTotal",field:"montoTotal",sortable:"true"},
        {label:"Usuario",name:"usuario",field:"usuario",sortable:"true"},
        {label:'client_id',name:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {label:'siatAnulado',name:'siatAnulado',field:'siatAnulado',sortable:true},
        {label:'id',name:'id',field:'id',sortable:true},
      ]

    }
  },
  mounted() {

  },
  methods:{
    saleCreate(){
      this.saleDialog=true
      this.client={complemento:''}
    },
    rentalConsulta(){
      this.$api.post('rentalConsulta',{
        fechaInicio:this.fechaInicio,
        fechaFin:this.fechaFin,
      }).then(res=>{
        console.log(res.data)
        this.rentals=res.data
      })
    }
  }
}
</script>

<style scoped>

</style>
