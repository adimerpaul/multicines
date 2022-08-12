<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-form @submit.prevent="listaVentaBoleteriaGet">
          <div class="row">
            <div class="col-4 q-pa-xs">
              <q-input outlined label="FechaIni" v-model="fechaIni" />
            </div>
            <div class="col-4 q-pa-xs">
              <q-input outlined label="FechaFin" v-model="fechaFin" />
            </div>
            <div class="col-4 q-pa-xs flex flex-center">
              <q-btn label="Buscar" icon="search" color="primary" type="submit" class="" :loading="loading" />
            </div>
          </div>
        </q-form>
      </div>
      <div class="col-12">
        <q-table :rows="listaVentaBoleteria" :columns="listaColums">

        </q-table>
      </div>
      <div class="col-12">
        <pre>{{listaVentaBoleteria}}</pre>
      </div>
    </div>
  </q-page>
</template>
<script>
import {date} from "quasar";

export default {
  name: `LisaVenta`,
  data() {
    return {
      loading:false,
      fechaIni:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      listaVentaBoleteria:[],
      listaColums:[
        {name:'Opciones',label:'Opciones',field:'Opciones'},
        {name:'id',label:'id',field:'id',sortable:true},
        {name:'numeroFactura',label:'numeroFactura',field:'numeroFactura',sortable:true},
        {name:'fechaEmision',label:'fechaEmision',field:'fechaEmision',sortable:true},
        {name:'client_id',label:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {name:'user_id',label:'user_id',field:row=>row.user.name,sortable:true},
        {name:'montoTotal',label:'montoTotal',field:'montoTotal',sortable:true},
        {name:'siatAnulado',label:'siatAnulado',field:'siatAnulado',sortable:true},
      ]
    }
  },
  created() {
    this.listaVentaBoleteriaGet();
  },
  methods: {
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
