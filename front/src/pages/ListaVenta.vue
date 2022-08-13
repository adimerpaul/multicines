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
              <q-btn icon="print" @click="printFactura(props.row)"/>

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
      <div class="titulo">FACTURA <br>CON DERECHO A CREDITO FISCAL</div>
      <div class="titulo2">MULTISALAS<br>
        Casa Matriz<br>
        No. Punto de Venta 0<br>
        AVENIDA TACNA ENTRE TOMAS FRIAS Y JAEN<br>
        Nª2337 ZONA ESTE<br>
        Tel. 2846005<br>
        Oruro
      </div>
      <hr>
      <div class="titulo">NIT</div>
      <div class="titulo2">329448023</div>
      <div class="titulo">FACTURA N°</div>
      <div class="titulo2">2</div>
      <div class="titulo">CÓD. AUTORIZACIÓN</div>
      <div class="titulo2 col-6" >168AB0075CF0FEC12741DDF938B3BBB63C339F8D472FEC0658B8B6D74</div>
      <hr>
      <table>
        <tr><td class="titder">NOMBRE/RAZÓN SOCIAL:</td><td class="contenido">JUAN PEREZ</td></tr>
        <tr><td class="titder">NIT/CI/CEX:</td><td class="contenido">1010</td></tr>
        <tr><td class="titder">COD. CLIENTE:</td ><td class="contenido">4</td></tr>
        <tr><td class="titder">FECHA DE EMISIÓN:</td><td class="contenido">12/08/2022 08:35 PM</td></tr>
      </table>
      <hr>
      <div class="titulo">DETALLE</div>


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
      facturadetalle:{},
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
    printFactura(factura){
      console.log(factura)
      this.facturadetalle=factura
      const cssText = `
      .titulo{
      font-size: 12px;
      text-align: center;
      font-weight: bold;
      }
      .titulo2{
      font-size: 10px;
      text-align: center;
      }
            .titulo3{
      font-size: 10px;
      text-align: center;
      width:70%;
      }
            .contenido{
      font-size: 10px;
      text-align: left;
      }
      .titder{
      font-size: 12px;
      text-align: right;
      font-weight: bold;
      }
      hr{
  border-top: 1px dashed   ;
}
  table{
    width:100%
  }
  h1 {
    color: black;
    font-family: sans-serif;
  }
`
      //
      const d = new Printd()
      d.print( document.getElementById('myelement'),[cssText] )
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
