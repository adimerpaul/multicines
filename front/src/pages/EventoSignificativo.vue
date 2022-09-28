<template>
<q-page>
<div class="row">
  <div class="col-12">
    <q-table dense title="Control de eventos" :rows="eventoSignificativos" :columns="eventoSignificativoColumns">
      <template v-slot:body-cell-Opciones="props">
        <q-td :props="props" auto-width>
          <q-btn @click="recepcionPaqueteFacturaClick(props.row)" label="Enviar a impuestos" color="primary" icon="send" size="10px" />
          <q-btn class="q-ml-lg" v-if="props.row.codigoRecepcion!=null" @click="validarPaquete(props.row)" label="Validar" color="yellow-9" icon="send" size="10px" />
        </q-td>
      </template>
    </q-table>
  </div>
</div>
  <q-dialog v-model="eventoSignificativoDialog" full-width>
    <q-card>
      <q-card-section class="row items-center">
        <div class="text-h6">
          Datos que se enviaran a impuestos
        </div>
        <q-space/>
        <q-btn @click="eventoSignificativoDialog = false" label="Cerrar" color="primary" icon="close" />
      </q-card-section>
      <q-card-section>
        <div class="row">
          <div class="col-12">
            <q-table :rows="eventoSignificativosDatos" :columns="eventoDetalle">
            </q-table>
          </div>
        </div>
        <div class="col-12">
          <q-btn @click="recepcionPaqueteFactura"  :disable="eventoSignificativosDatos.length==0" label="Enviar" color="primary" icon="send" class="full-width" :loading="loading" />
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
  <q-dialog v-model="recepcionPaqueteFacturaDialog">
    <q-card >
      <q-card-section class="row text-center">
        <div class="text-h6">
          RECEPCION DE PAQUETE
        </div>
        <q-space/>
        <q-btn @click="recepcionPaqueteFacturaDialog = false" label="Cerrar" color="primary" icon="close" />
      </q-card-section>
      <q-card-section>
        <pre>{{validarPaqueteRespuesta}}</pre>
      </q-card-section>
    </q-card>
  </q-dialog>
</q-page>
</template>

<script>
import {date} from "quasar";
import {globalStore} from "stores/globalStore";

export default {
  name: `EventoSignificativo`,
    data() {
    return {
      store:globalStore(),
      loading: false,
      eventoSignificativoDialog: false,
      eventoSignificativos:[],
      eventoSignificativo:{},
      eventoSignificativosDatos:[],
      eventoSignificativoColumns:[
        {label:'Opciones',name:'Opciones',field:'Opciones'},
        {label:'codigoPuntoVenta',name:'codigoPuntoVenta',field:'codigoPuntoVenta'},
        {label:'codigoRecepcionEventoSignificativo',name:'codigoRecepcionEventoSignificativo',field:'codigoRecepcionEventoSignificativo'},
        {label:'codigoRecepcion',name:'codigoRecepcion',field:'codigoRecepcion'},
        {label:'codigoSucursal',name:'codigoSucursal',field:'codigoSucursal'},
        {label:'fechaHoraInicioEvento',name:'fechaHoraInicioEvento',field:'fechaHoraInicioEvento'},
        {label:'fechaHoraFinEvento',name:'fechaHoraFinEvento',field:'fechaHoraFinEvento'},
        {label:'codigoMotivoEvento',name:'codigoMotivoEvento',field:'codigoMotivoEvento'},
        {label:'descripcion',name:'descripcion',field:'descripcion'},
      ],
      validarPaqueteRespuesta:"",
      recepcionPaqueteFacturaDialog:false,
      eventoDetalle:[
        {label:'numeroFactura',name:'numeroFactura',field:'numeroFactura'},
        {label:'codigoSucursal',name:'codigoSucursal',field:'codigoSucursal'},
        {label:'codigoPuntoVenta',name:'codigoPuntoVenta',field:'codigoPuntoVenta'},
        {label:'fechaEmision',name:'fechaEmision',field:'fechaEmision'},
        {label:'montoTotal',name:'montoTotal',field:'montoTotal'},
        {label:'usuario',name:'usuario',field:'usuario'},
      ]
    }
  },
  created() {
    this.eventoSignificativoGet();
  },
  methods:{
    validarPaquete(evento){
      console.log(evento);
      this.recepcionPaqueteFacturaDialog = true;
      this.$api.post('validarPaquete',{
        codigoRecepcion:evento.codigoRecepcion,
      }).then(res=>{
        this.validarPaqueteRespuesta=res.data;
      })
    },
    eventSearch(){

      this.$api.post('eventSearch').then(res=>{
        this.store.eventNumber=res.data
      })
    },
    recepcionPaqueteFactura(){
      this.eventoSignificativo.datos=this.eventoSignificativosDatos
      this.loading=true;
      this.$api.post('recepcionPaqueteFactura',this.eventoSignificativo).then(res=>{
        console.log(res.data)
        this.eventSearch()
        this.eventoSignificativoDialog=false;
        this.loading=false;
      }).catch(err=>{
        this.$q.notify({
          color: 'red-6',
          textColor: 'white',
          icon: 'error',
          message: err.response.data.message
        })
        this.loading=false;
      })
    },
    recepcionPaqueteFacturaClick(evento){
      this.eventoSignificativo=evento
      this.$api.post('cantidadFacturas',{
        fechaInicio:evento.fechaHoraInicioEvento,
        fechaFin:evento.fechaHoraFinEvento,
      }).then(res=>{
        this.eventoSignificativosDatos = res.data;
        this.eventoSignificativoDialog = true;

      })
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
