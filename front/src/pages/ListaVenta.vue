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
      <div class="col-12">
        <pre>{{listaVentaBoleteria}}</pre>
      </div>
    </div>
    <div id="myelement">
      sjkdsada
    </div>
  </q-page>
</template>
<script>
import {date} from "quasar";
import { Printd } from 'printd'
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
        {name:'numeroFactura',label:'numeroFactura',field:'numeroFactura',sortable:true},
        {name:'siatEnviado',label:'siatEnviado',field:'siatEnviado',sortable:true},
        {name:'fechaEmision',label:'fechaEmision',field:'fechaEmision',sortable:true},
        {name:'client_id',label:'client_id',field:row=>row.client.nombreRazonSocial,sortable:true},
        {name:'user_id',label:'user_id',field:row=>row.user.name,sortable:true},
        {name:'montoTotal',label:'montoTotal',field:'montoTotal',sortable:true},
        {name:'siatAnulado',label:'siatAnulado',field:'siatAnulado',sortable:true},
        {name:'id',label:'id',field:'id',sortable:true},
      ]
    }
  },
  mounted() {
    // this.printFactura()
    this.listaVentaBoleteriaGet();
  },
  methods: {
    printFactura(){
      const cssText = `
  h1 {
    color: black;
    font-family: sans-serif;
  }
`

      const d = new Printd()
      d.print( document.getElementById('myelement'), [ cssText ] )
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
