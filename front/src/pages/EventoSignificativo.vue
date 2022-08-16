<template>
<q-page>

</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `EventoSignificativo`,
    data() {
    return {
      loading:false,
      eventoSignificativoDialog:false,
      fechaHoraInicioEvento:date.formatDate(new Date(),'YYYY-MM-DD HH:mm:ss'),
      fechaHoraFinEvento:date.formatDate(new Date(),'YYYY-MM-DD HH:mm:ss'),
      cufd:{},
      cufdEvento:{},
      cufds:[],
      events:[],
      event:{},
      cui:{
        codigoPuntoVenta:0,
        codigoSucursal:0
      },
      cuiDialog:false,
      cudfFilter: '',
      cufdsColums:[
        {name:'Opciones',label:'Opciones',field:'Opciones'},
        {name:'codigoPuntoVenta',label:'codigoPuntoVenta',field:'codigoPuntoVenta',sortable:true},
        {name:'codigoSucursal',label:'codigoSucursal',field:'codigoSucursal',sortable:true},
        {name:'fechaCreacion',label:'fechaCreacion',field:'fechaCreacion',sortable:true},
        {name:'fechaVigencia',label:'fechaVigencia',field:'fechaVigencia',sortable:true},
        {name:'id',label:'id',field:'id',sortable:true},
        {name:'codigo',label:'codigo',field:'codigo',sortable:true},
        {name:'codigoControl',label:'codigoControl',field:'codigoControl',sortable:true},
      ]
    }
  },
  created() {
    this.cufdGet();
    this.$api.get('event').then(response => {
      response.data.forEach(r=>{
        r.label= r.codigoClasificador+' '+r.descripcion;
      })
      this.events = response.data
      this.event=this.events[0]
    });
  },
}
</script>

<style scoped>

</style>
