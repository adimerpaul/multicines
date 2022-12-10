<template>
    <q-page padding>
      <div class="row">
        <div class="col-4"><q-input dense outlined v-model="ini" label="fecha ini" type="date" /></div>
        <div class="col-4"><q-input dense outlined v-model="fin" label="fecha fin" type="date" /></div>
        <div class="col-4 flex flex-center"> <q-btn icon="check" color="green"  label="Consultar" @click="consultar"/></div>
        <div class="col-12">
          <q-table dense title="Listado Venta Funcion" :rows-per-page-options="[20,50,100,0]" :rows="reporte"  :filter="productoFilter">
            <template v-slot:top-right>
              <q-btn icon="download" color="green"  label="Excel"  @click="exportar" />
              <q-input outlined dense debounce="300" v-model="productoFilter" placeholder="Buscar">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </template>
          </q-table>
<!--          <pre>{{reporte}}</pre>-->
        </div>
      </div>
      <div id="myelement" class="hidden"></div>
    </q-page>
  </template>

  <script>
  import {globalStore} from "stores/globalStore";
  import {date} from "quasar";
  import {Printd} from "printd";
  import xlsx from "json-as-xlsx"

  export default {
    name: `CajaBoleteria`,
    data() {
      return {
        ini:date.formatDate(new Date(), "YYYY-MM-DD"),
        fin:date.formatDate(new Date(), "YYYY-MM-DD"),
        infouser:[],
        loading: false,
        productoFilter:'',
        url:process.env.API,
        dialog_img:false,
        producto:{},
        producto2:{},
        productoDialog:false,
        productoUpdateDialog:false,
        reporte:[],
        user:{},
        users:[],
        columns:[],
        store: globalStore(),
        foto:'',
        tvip:0,
        tcredito:0,
        tefectivo:0,
        total:0,
        columna:[
          {label:'NOMBRE',field:'descripcion',name:'descripcion',sortable:true},
          {label:'cantidad',field:'cantidad',name:'cantidad',sortable:true},
          {label:'SUBTOTAL',field:'total',name:'subtotal',sortable:true},
        ]
      }
    },
    created() {
    },
    methods: {
      exportar(){

        let data = [
          {
            sheet: "Adults",
            columns: [
              { label: "id", value: "id" },
              { label: "fec", value: "fec" },
              { label: "titulo", value: "titulo" },
              { label: "sala", value: "sala" },
              { label: "horaInicio", value: "horaInicio" },
              { label: "ff", value: "ff" },
              { label: "serie", value: "serie" },
              { label: "precio", value: "precio" },
              { label: "cantf", value: "cantf" },
              { label: "total", value: "total" },
              { label: "cantr", value: "cantr" },
              { label: "cantc", value: "cantc" },
              { label: "canttotal", value: "canttotal" },
              // { label: "Age", value: (row) => row.age + " years" }, // Custom format
              // { label: "Phone", value: (row) => (row.more ? row.more.phone || "" : "") }, // Run functions
            ],
            content: this.reporte,
          },
        ]

        let settings = {
          fileName: "ventasfuncion" + date.formatDate(new Date(), "YYYY-MM-DD"),
          // extraLength: 3, // A bigger number means that columns will be wider
          // writeMode: "writeFile", // The available parameters are 'WriteFile' and 'write'. This setting is optional. Useful in such cases https://docs.sheetjs.com/docs/solutions/output#example-remote-file
          writeOptions: {}, // Style options from https://docs.sheetjs.com/docs/api/write-options
          // RTL: true, // Display the columns from right-to-left (the default value is false)
        }
        xlsx(data, settings) // Will download the excel file
      },
      consultar(){
          this.loading=true
        this.$api.post('reporteFuncion',{ini:this.ini,fin:this.fin}).then(res=>{
            console.log(res.data)
          this.loading=false
          this.reporte=res.data
        })
      },

      impresion(){
        if(this.reporte.length==0){
         return false;
        }

        let cadena="<style>\
        *{font-size:10px;}\
        .titulo{text-align:center;\
          font-weight:bold;}\
        .titulo2{font-weight:bold;}\
        .titulo3{font-weight:bold; text-align:right;}\
        table{width:100%; border:0.2px solid;}\
        </style>\
        <div class='titulo'>MULTISALAS S.R.L.</div>\
        <div class='titulo'>ORURO - BOLIVIA</div>\
        <div class='titulo'>VENTAS DE BOLETERIA</div>\
        <hr>\
        <div><span class='titulo2'>Fecha: </span> "+date.formatDate(new Date(), 'DD/MM/YYYY HH:mm:ss')+"</div>\
        <div><span class='titulo2'>Fecha Caja: </span> "+this.ini+" al "+this.fin+"</div>\
        <div><span  class='titulo2'>Usuario: </span> "+this.user.label+"</div>\
        <hr>\
        <table>\
        <thead><tr><th>DESCRIPCION</th><th>CANTIDAD</th><th>TOTAL</th></tr></thead>\
        <tbody>"
          this.reporte.forEach(r => {
            cadena+="<tr><td>"+r.descripcion+"</td><td>"+r.cantidad+"</td><td>"+r.total+"</td></tr>"
          });
        cadena+="</tbody>\
        </table>"
        if(this.user.id==0){
        cadena+="<table><tr><th>USUARIO</th><th>TOTAL</th></tr>"
          this.infouser.forEach(r => {
          cadena+="<tr><td>"+r.usuario+"</td> <td>"+r.total+"</td><\tr>"
          })
        cadena+="</table>"}

        cadena+="<div style='text-align:right;'><span class='titulo3'>Total: </span> "+this.ventatotal+" Bs</div>\
        <div style='text-align:right;'><span class='titulo3'>Total VIP: </span> "+this.tvip+" Bs</div>\
        <div style='text-align:right;'><span class='titulo3'>Total Credito: </span> "+this.tcredito+" Bs</div>\
        <div style='text-align:right;'><span class='titulo3'>Total Efectivo: </span> "+this.tefectivo+" Bs</div>\
        "
        document.getElementById('myelement').innerHTML = cadena
        const d = new Printd()
        d.print( document.getElementById('myelement') )

      }


    }
    ,
    computed:{
        ventatotal(){
            let suma=0
            this.reporte.forEach(r=>{
                suma+=r.total
            })
            return suma.toFixed(2)
        },

    }
  }
  </script>

  <style scoped>

  </style>
