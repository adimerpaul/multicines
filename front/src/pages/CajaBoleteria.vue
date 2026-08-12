<template>
    <q-page padding class="caja-boleteria-page">
      <div class="row q-col-gutter-xs items-center filtros-caja">
        <div class="col-12 col-md-2"><q-select dense outlined v-model="user" :options="users" label="Usuario" /></div>
        <div class="col-6 col-md-2"><q-input dense outlined v-model="ini" label="fecha ini" type="date" /></div>
        <div class="col-6 col-md-2"><q-input dense outlined v-model="fin" label="fecha fin" type="date" /></div>
        <div class="col-6 col-md-2"><q-btn class="full-width" :loading="loading" :disable="loading" icon="check" color="green" label="Consultar" @click="consultar"/></div>
        <div class="col-6 col-md-2"><q-btn class="full-width" icon="print" color="info" label="Imprimir" :disable="loading || reporte.length==0" @click="impresion"/></div>
        <div class="col-12 col-md-2">
          <q-input outlined dense debounce="300" v-model="productoFilter" placeholder="Buscar pelicula">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-12">
          <div class="resumen-caja">
            <div><span>Total</span><b>{{ ventatotal }} Bs</b></div>
            <div><span>Factura</span><b>{{ ventafact }} Bs</b></div>
            <div><span>Recibo</span><b>{{ ventarecibo }} Bs</b></div>
            <div><span>VIP</span><b>{{ tvip }} Bs</b></div>
            <div><span>Credito</span><b>{{ tcredito }} Bs</b></div>
            <div><span>QR</span><b>{{ tqr }} Bs</b></div>
            <div><span>Efectivo</span><b>{{ tefectivo }} Bs</b></div>
          </div>
        </div>
        <div class="col-12">
          <q-table
            class="tabla-compacta"
            dense
            flat
            bordered
            separator="cell"
            title="Listado Venta Boleteria"
            :loading="loading"
            :rows-per-page-options="[0]"
            :pagination="tablePagination"
            :rows="reporteDetalle"
            :columns="columna"
            :filter="productoFilter"
            row-key="pelicula_id"
          >
            <template v-slot:top-right>
              <span class="text-caption text-grey-8">{{ reporteDetalle.length }} peliculas</span>
            </template>
          </q-table>
        </div>
        <div class="col-12 col-md-6">
          <q-table class="tabla-compacta" dense flat bordered separator="cell" title="Ventas por usuario" :loading="loading" :rows-per-page-options="[0]" :pagination="tablePagination" :rows="infouser" :columns="columna2" row-key="usuario" />
        </div>
        <div class="col-12 col-md-6">
          <q-table class="tabla-compacta" dense flat bordered separator="cell" title="Anulados" :loading="loading" :rows-per-page-options="[0]" :pagination="tablePagination" :rows="anulados" :columns="columnaAnulados" row-key="usuario" />
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
        store: globalStore(),
        foto:'',
        tvip:0,
        tcredito:0,
        tqr:0,
        tbolR:0,
        tbolF:0,
        tbolRefectivo:0,
        tbolFefectivo:0,
        tcandyF:0,
        tcandyR:0,
        tefectivo:0,
        total:0,
        reportef:[],
        reporter:[],
        columna:[
          {label:'COD',field:'pelicula_id',name:'pelicula_id',sortable:true,align:'left'},
          {label:'NOMBRE',field:'descripcion',name:'descripcion',sortable:true,align:'left'},
          {label:'CANT.',field:'cantidad',name:'cantidad',sortable:true,align:'right'},
          {label:'TOTAL',field:'total',name:'total',sortable:true,align:'right',format: val => this.formatMoney(val)},
          {label:'CANT. F',field:'cantidadF',name:'cantidadF',sortable:true,align:'right'},
          {label:'FACTURA',field:'totalF',name:'totalF',sortable:true,align:'right',format: val => this.formatMoney(val)},
          {label:'CANT. R',field:'cantidadR',name:'cantidadR',sortable:true,align:'right'},
          {label:'RECIBO',field:'totalR',name:'totalR',sortable:true,align:'right',format: val => this.formatMoney(val)},
        ],
        columna2:[
          {label:'USUARIO',field:'usuario',name:'usuario',sortable:true,align:'left'},
          {label:'TOTAL',field:'total',name:'total',sortable:true,align:'right',format: val => this.formatMoney(val)},
        ],
        columnaAnulados:[
          {label:'USUARIO',field:'usuario',name:'usuario',sortable:true,align:'left'},
          {label:'TOTAL ANULADOS',field:'total',name:'total',sortable:true,align:'right'},
          {label:'MONTO',field:'monto',name:'monto',sortable:true,align:'right',format: val => this.formatMoney(val)},
        ],
        tablePagination:{rowsPerPage:0},
        anulados:[],
      }
    },
    created() {
        this.listuser()
    },
    methods: {
        formatMoney(value){
          return (parseFloat(value || 0)).toFixed(2)
        },
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
        this.$api.post('reporteCajaBoleteria',{ini:this.ini,fin:this.fin,id:this.user?.id || 0}).then(res=>{
          const resumen = res.data.resumen || {}
          const resumenRF = res.data.resumenRF || {}

          this.reporte=res.data.reporte || []
          this.reportef=res.data.reportef || []
          this.reporter=res.data.reporter || []
          this.infouser=res.data.infouser || []
          this.anulados=res.data.anulados || []
          this.tcredito=resumen.tarjeta==null?0:resumen.tarjeta
          this.tvip=resumen.vip==null?0:resumen.vip
          this.tqr=resumen.qr==null?0:resumen.qr
          this.tefectivo=resumen.efectivo==null?0:resumen.efectivo
          this.tbolF=resumenRF.tarjetaF==null?0:resumenRF.tarjetaF
          this.tbolR=resumenRF.tarjetaR==null?0:resumenRF.tarjetaR
          this.tbolFefectivo=resumenRF.efectivoF==null?0:resumenRF.efectivoF
          this.tbolRefectivo=resumenRF.efectivoR==null?0:resumenRF.efectivoR
        }).catch(err=>{
          this.$q.notify({
            color: 'negative',
            textColor: 'white',
            message: err.response?.data?.message || 'No se pudo cargar el reporte de caja boleteria',
            position: 'top',
            timeout: 5000,
          })
        }).finally(()=>{
          this.loading=false
        })
      },

      impresion(){
        if(this.reporte.length==0){
         return false;
        }

        let cadena="<style>\
        .f-10{font-size:10px;}\
        .centro{text-align:center;}\
        .titulo{text-align:center;\
          font-weight:bold;}\
        .titulo2{font-weight:bold;}\
        .titulo3{font-weight:bold; text-align:right;}\
        .table{width:100%; border:0.2px solid;}\
        </style>\
        <div class='f-10 titulo' >MULTISALAS S.R.L.</div>\
        <div class='f-10 titulo' >ORURO - BOLIVIA</div>\
        <div class='f-10 titulo' >VENTAS DE BOLETERIA TOTAL</div>\
        <hr>\
        <div class='f-10'><span class='titulo2'>Fecha: </span> "+date.formatDate(new Date(), 'DD/MM/YYYY HH:mm:ss')+"</div>\
        <div class='f-10'><span class='titulo2'>Fecha Caja: </span> "+this.ini+" al "+this.fin+"</div>\
        <div class='f-10'><span  class='titulo2'>Usuario: </span> "+this.user.label+"</div>\
        <hr>\
        <table class='table'>\
        <thead><tr><th class='f-10'>DESCRIPCION</th><th class='f-10'>CANTIDAD</th><th class='f-10'>TOTAL</th></tr></thead>\
        <tbody>"
          this.reporte.forEach(r => {
            cadena+="<tr><td class='f-10'>"+r.descripcion+"</td><td class='f-10'>"+r.cantidad+"</td><td class='f-10'>"+r.total+"</td></tr>"
          });
        cadena+="</tbody>\
        </table>"
        if(this.user.id==0){
        cadena+="<table class='table'><tr><th class='f-10'>USUARIO</th><th class='f-10'>TOTAL</th></tr>"
          this.infouser.forEach(r => {
          cadena+="<tr><td class='f-10'>"+r.usuario+"</td> <td class='f-10'>"+r.total+"</td></tr>"
          })
        cadena+="</table>"}
        if(this.anulados.length>0){
          cadena += "<table class='table'>\
          <thead><tr><th class='f-10'>USUARIO</th><th class='f-10'>TOTAL ANULADOS</th><th class='f-10'>MONTO</th></tr></thead>\
          <tbody>"
            this.anulados.forEach(r => {
              cadena+="<tr><td class='f-10 centro'>"+r.usuario+"</td><td class='f-10 centro'>"+r.total+"</td><td class='f-10 centro'>"+r.monto+"</td></tr>"
            });
          cadena+="</tbody>\
          </table>"
        }
        cadena+="<div class='f-10' style='text-align:right;'><span class='titulo3'>Total: </span> "+this.ventatotal+" Bs</div>\
        <div class='f-10' style='text-align:right;'><span class='titulo3'>Total VIP: </span> "+this.tvip+" Bs</div>\
        <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Credito: </span> "+this.tcredito+" Bs</div>\
        <div class='f-10' style='text-align:right;'><span class='titulo3'>Total QR: </span> "+this.tqr+" Bs</div>\
        <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Efectivo: </span> "+this.tefectivo+" Bs</div>\
        "
        cadena += "<div style='page-break-after:always'></div>"

        let cadena2="<style>\
        .f-10{font-size:10px;}\
        .titulo{text-align:center;\
          font-weight:bold;}\
        .titulo2{font-weight:bold;}\
        .titulo3{font-weight:bold; text-align:right;}\
        .table{width:100%; border:0.2px solid;}\
        </style>\
        <div class='f-10 titulo' >MULTISALAS S.R.L.</div>\
        <div class='f-10 titulo' >ORURO - BOLIVIA</div>\
        <div class='f-10 titulo' >VENTAS DE BOLETERIA FACTURA</div>\
        <hr>\
        <div class='f-10'><span class='titulo2'>Fecha: </span> "+date.formatDate(new Date(), 'DD/MM/YYYY HH:mm:ss')+"</div>\
        <div class='f-10'><span class='titulo2'>Fecha Caja: </span> "+this.ini+" al "+this.fin+"</div>\
        <div class='f-10'><span  class='titulo2'>Usuario: </span> "+this.user.label+"</div>\
        <hr>\
        <table class='table'>\
        <thead><tr><th class='f-10'>DESCRIPCION</th><th class='f-10'>CANTIDAD</th><th class='f-10'>TOTAL</th></tr></thead>\
        <tbody>"
          this.reportef.forEach(r => {
            cadena2+="<tr><td class='f-10'>"+r.descripcion+"</td><td class='f-10'>"+r.cantidad+"</td><td class='f-10'>"+r.total+"</td></tr>"
          });
        cadena2+="</tbody>\
        </table>"
        if(this.tbolF==null) this.tbolF=0
        if(this.tbolFefectivo==null) this.tbolFefectivo=0
        cadena2+="<div class='f-10' style='text-align:right;'><span class='titulo3'>Total: </span> "+this.ventafact+" Bs</div>\
                  <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Efectivo: </span> "+this.tbolFefectivo+" Bs</div>\
                  <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Tarjeta: </span> "+this.tbolF+" Bs</div>\
        "
        cadena2 += "<div style='page-break-after:always'></div>"

        let cadena3="<style>\
        .f-10{font-size:10px;}\
        .titulo{text-align:center;\
          font-weight:bold;}\
        .titulo2{font-weight:bold;}\
        .titulo3{font-weight:bold; text-align:right;}\
        .table{width:100%; border:0.2px solid;}\
        </style>\
        <div class='f-10 titulo' >MULTISALAS S.R.L.</div>\
        <div class='f-10 titulo' >ORURO - BOLIVIA</div>\
        <div class='f-10 titulo' >VENTAS DE BOLETERIA RECIBO</div>\
        <hr>\
        <div class='f-10'><span class='titulo2'>Fecha: </span> "+date.formatDate(new Date(), 'DD/MM/YYYY HH:mm:ss')+"</div>\
        <div class='f-10'><span class='titulo2'>Fecha Caja: </span> "+this.ini+" al "+this.fin+"</div>\
        <div class='f-10'><span  class='titulo2'>Usuario: </span> "+this.user.label+"</div>\
        <hr>\
        <table class='table'>\
        <thead><tr><th class='f-10'>DESCRIPCION</th><th class='f-10'>CANTIDAD</th><th class='f-10'>TOTAL</th></tr></thead>\
        <tbody>"
          this.reporter.forEach(r => {
            cadena3+="<tr><td class='f-10'>"+r.descripcion+"</td><td class='f-10'>"+r.cantidad+"</td><td class='f-10'>"+r.total+"</td></tr>"
          });
        cadena3+="</tbody>\
        </table>"
        if(this.tbolR==null) this.tbolR=0
        if(this.tbolRefectivo==null) this.tbolRefectivo=0
                cadena3+="<div class='f-10' style='text-align:right;'><span class='titulo3'>Total: </span> "+this.ventarecibo+" Bs</div>\
                <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Efectivo: </span> "+this.tbolRefectivo+" Bs</div>\
                <div class='f-10' style='text-align:right;'><span class='titulo3'>Total Tarjeta: </span> "+this.tbolR+" Bs</div>\
        "
        document.getElementById('myelement').innerHTML = cadena + cadena2 + cadena3
        const d = new Printd()
        d.print( document.getElementById('myelement') )
      }


    }
    ,
    computed:{
        reporteDetalle(){
            return this.reporte.map(r=>{
                const factura=this.reportef.find(f=>f.pelicula_id==r.pelicula_id) || {}
                const recibo=this.reporter.find(f=>f.pelicula_id==r.pelicula_id) || {}
                return {
                    pelicula_id:r.pelicula_id,
                    descripcion:r.descripcion,
                    cantidad:r.cantidad,
                    total:r.total,
                    cantidadF:factura.cantidad || 0,
                    totalF:factura.total || 0,
                    cantidadR:recibo.cantidad || 0,
                    totalR:recibo.total || 0,
                }
            })
        },
        ventatotal(){
            let suma=0
            this.reporte.forEach(r=>{
                suma+=r.total
            })
            return suma.toFixed(2)
        },
        ventarecibo(){
            let suma=0
            this.reporter.forEach(r=>{
          //    if(r.credito=="NO" && r.vip=="NO" && r.cortesia=="NO")
                suma+=r.total
            })
            return suma.toFixed(2)
        },
        ventafact(){
            let suma=0
            this.reportef.forEach(r=>{
          //    if(r.credito=="NO" && r.vip=="NO" && r.cortesia=="NO")
                suma+=r.total
            })
            return suma.toFixed(2)
        },

    }
  }
  </script>

  <style scoped>
  .caja-boleteria-page{
    min-height: 100%;
    background:
      linear-gradient(135deg, rgba(236, 253, 245, 0.88), rgba(248, 250, 252, 0.94)),
      repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.025) 0, rgba(0, 0, 0, 0.025) 1px, transparent 1px, transparent 18px);
  }

  .filtros-caja{
    background: rgba(255, 255, 255, 0.84);
    border: 1px solid rgba(31, 41, 55, 0.12);
    border-radius: 6px;
    padding: 6px;
  }

  .resumen-caja{
    display: grid;
    grid-template-columns: repeat(7, minmax(100px, 1fr));
    gap: 4px;
  }

  .resumen-caja div{
    background: #166534;
    color: white;
    border-radius: 4px;
    padding: 5px 8px;
    line-height: 1.15;
  }

  .resumen-caja span{
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    opacity: 0.82;
  }

  .resumen-caja b{
    display: block;
    font-size: 13px;
  }

  .tabla-compacta{
    background: rgba(255, 255, 255, 0.96);
  }

  .tabla-compacta :deep(.q-table__top){
    min-height: 30px;
    padding: 4px 8px;
    background: #1f2937;
    color: #fff;
  }

  .tabla-compacta :deep(.q-table__title){
    font-size: 13px;
    font-weight: 700;
  }

  .tabla-compacta :deep(th){
    height: 24px;
    padding: 2px 5px;
    font-size: 10px;
    font-weight: 700;
    color: #263238;
    background: #e1e8e6;
    text-transform: uppercase;
  }

  .tabla-compacta :deep(td){
    height: 22px;
    padding: 1px 5px;
    font-size: 11px;
    line-height: 1.1;
    color: #1f2933;
  }

  .tabla-compacta :deep(tbody tr:nth-child(even)){
    background: #f7faf8;
  }

  .tabla-compacta :deep(.q-table__bottom){
    min-height: 28px;
    padding: 2px 8px;
    font-size: 11px;
  }

  @media (max-width: 900px){
    .resumen-caja{
      grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
  }
  </style>
