<template>
    <q-page padding>
      <div class="row">
        <div class="col-3"><q-select square outlined v-model="user" :options="users" label="Usuario" /></div>
        <div class="col-3"><q-input square outlined v-model="ini" label="fecha ini" type="date" /></div>
        <div class="col-3"><q-input square outlined v-model="fin" label="fecha fin" type="date" /></div>
        <div class="col-3"> <q-btn color="green"  label="Consultar" @click="consultar"/></div>
        <div class="col-3"> <q-btn color="info"  label="Imprimir" @click="impresion"/></div>
        <div class="col-12">
          <q-table dense title="Listado Venta Boleteria" :rows-per-page-options="[20,50,100,0]" :rows="reporte" :columns="columna" :filter="productoFilter">
            <template v-slot:top-right>

              <q-input outlined dense debounce="300" v-model="productoFilter" placeholder="Buscar">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </template>

>
          </q-table>
        </div>
      </div>


  <div id="myelement" class="hidden"></div>

    </q-page>
  </template>

  <script>
  import {globalStore} from "stores/globalStore";
  import {date} from "quasar";
  import {Printd} from "printd";

  export default {
    name: `CajaBoleteria`,
    data() {
      return {
        ini:date.formatDate(new Date(), "YYYY-MM-DD"),
        fin:date.formatDate(new Date(), "YYYY-MM-DD"),
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
        this.listuser()
    },
    methods: {
        listuser(){
            this.users=[{id:0,label:'TODOS'}]
            this.$api.get('user').then(res=>{
                console.log(res.data)
                res.data.forEach(r => {
                    r.label=r.name
                    this.users.push(r)
                });
                this.user=this.users[0]
            })

        },

      consultar(){
          this.loading=true
        this.$api.post('cajaBol',{ini:this.ini,fin:this.fin,id:this.user.id}).then(res=>{
            console.log(res.data)
          this.loading=false
          this.reporte=res.data
          this.resumen()
        })
      },
      resumen(){
          this.loading=true
        this.$api.post('resumenBol',{ini:this.ini,fin:this.fin,id:this.user.id}).then(res=>{
            console.log(res.data)

            this.tcredito=res.data[0].tarjeta==null?0:res.data[0].tarjeta
            this.tvip=res.data[0].vip==null?0:res.data[0].vip
            this.tefectivo=res.data[0].efectivo==null?0:res.data[0].efectivo

          this.loading=false
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
        </table>\
        <div style='text-align:right;'><span class='titulo3'>Total: </span> "+this.ventatotal+" Bs</div>\
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
