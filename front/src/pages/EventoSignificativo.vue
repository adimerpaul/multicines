<template>
<q-page>
<div class="row">
  <div class="col-12">
    <q-table dense title="Control de eventos" :rows="eventoSignificativos" :columns="eventoSignificativoColumns">
      <template v-slot:body-cell-Opciones="props">
        <q-td :props="props" auto-width>
          <q-btn @click="recepcionPaqueteFactura(props.row)" label="Enviar a impuestos" color="primary" icon="send" size="10px" />
        </q-td>
      </template>
    </q-table>
  </div>
  <div class="col-12">
    <pre>{{eventoSignificativos}}</pre>
  </div>
</div>
</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `EventoSignificativo`,
    data() {
    return {
      loading: false,
      eventoSignificativos:[],
      eventoSignificativoColumns:[
        {label:'Opciones',name:'Opciones',field:'Opciones'},
        {label:'codigoPuntoVenta',name:'codigoPuntoVenta',field:'codigoPuntoVenta'},
        {label:'codigoRecepcionEventoSignificativo',name:'codigoRecepcionEventoSignificativo',field:'codigoRecepcionEventoSignificativo'},
        {label:'codigoSucursal',name:'codigoSucursal',field:'codigoSucursal'},
        {label:'fechaHoraInicioEvento',name:'fechaHoraInicioEvento',field:'fechaHoraInicioEvento'},
        {label:'fechaHoraFinEvento',name:'fechaHoraFinEvento',field:'fechaHoraFinEvento'},
        {label:'codigoMotivoEvento',name:'codigoMotivoEvento',field:'codigoMotivoEvento'},
        {label:'descripcion',name:'descripcion',field:'descripcion'},
      ],
    }
  },
  created() {
    this.eventoSignificativoGet();
  },
  methods:{
    recepcionPaqueteFactura(evento){
      this.$api.post('cantidadFacturas',{
        fechaInicio:evento.fechaHoraInicioEvento,
        fechaFin:evento.fechaHoraFinEvento,
      }).then(res=>{
        console.log(res.data)
      })
      // this.$api.post('recepcionPaqueteFactura',evento).then(res=>{
      //   console.log(res.data)
      // })
    },
    eventoSignificativoGet(){
      this.loading = true;
      this.$api.get('eventoSignificativo')
        .then(response => {
          this.loading = false;
          this.eventoSignificativos = response.data;
        })
        .catch(error => {
          this.loading = false;
          this.error = error;
        });
    },
  }
}
</script>

<style scoped>

</style>
