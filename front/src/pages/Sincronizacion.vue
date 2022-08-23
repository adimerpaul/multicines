<template>
  <q-page>
    <div class="row">
      <div class="col-12">
      <q-btn
        class="q-mt-xs full-width"
        color="green"
        icon="add_circle"
        label="Sincronizar"
        :loading="loading"
        @click="cuiDialog=true"/>
<!--        <q-table :rows="cuis" dense title="Gestionar sincronizacion" :columns="cuisColums" :rows-per-page-options="[0]">-->
<!--          <template v-slot:top-right>-->
<!--            <q-btn-->
<!--              color="green"-->
<!--              icon="add_circle"-->
<!--              label="Agregar"-->
<!--              @click="cuiDialog=true"/>-->
<!--            <q-input outlined dense debounce="300" v-model="cuiFilter" placeholder="Buscar">-->
<!--              <template v-slot:append>-->
<!--                <q-icon name="search" />-->
<!--              </template>-->
<!--            </q-input>-->
<!--          </template>-->
<!--        </q-table>-->
      </div>
      <div class="col-12">
        <q-table :rows="activities" dense title="Actividades" />
      </div>
      <div class="col-12">
        <q-table :rows="documents" dense title="Documentos" />
      </div>
      <div class="col-12">
        <q-table :rows="documentsectors" dense title="Documento Sector" />
      </div>
      <div class="col-12">
        <q-table :rows="events" dense title="Eventos" />
      </div>
      <div class="col-12">
        <q-table :rows="leyendas" dense title="Leyendas" />
      </div>
      <div class="col-12">
        <q-table :rows="medidas" dense title="Medidas" />
      </div>
      <div class="col-12">
        <q-table :rows="messages" dense title="Mensajes" />
      </div>
      <div class="col-12">
        <q-table :rows="sectors" dense title="Sectores" />
      </div>
      <div class="col-12">
        <q-table :rows="servicios" dense title="Servicios" />
      </div>
      <div class="col-12">
        <q-table :rows="tipopagos" dense title="Tipo Pagos" />
      </div>
    </div>
    <q-dialog v-model="cuiDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Sincronizacion</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="activityCreate">
            <div class="row">
<!--              <div class="col-12">-->
<!--                <q-select :options="[0,1]" dense outlined label="codigoPuntoVenta" v-model="cui.codigoPuntoVenta" />-->
<!--              </div>-->
<!--              <div class="col-12">-->
<!--                <q-select :options="[0,1,2]" dense outlined label="codigoSucursal" v-model="cui.codigoSucursal" />-->
<!--              </div>-->
              <div class="col-12 flex flex-center">
                <q-btn dense class="full-width" :loading="loading" type="submit" color="green" icon="check" label="Sincronizar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>
<script>
export default {
  name: `Sincronizacion`,
  data() {
    return {
      loading:false,
      cuis:[],
      cui:{
        codigoPuntoVenta:0,
        codigoSucursal:0
      },
      cuiDialog:false,
      cuiFilter: '',
      activities:[],
      documents:[],
      documentsectors:[],
      events:[],
      leyendas:[],
      medidas:[],
      messages:[],
      motivos:[],
      sectors:[],
      servicios:[],
      tipopagos:[],
      cuisColums:[
        {name:'id',label:'id',field:'id',sortable:true},
        {name:'codigo',label:'codigo',field:'codigo',sortable:true},
        {name:'fechaVigencia',label:'fechaVigencia',field:'fechaVigencia',sortable:true},
        {name:'codigoPuntoVenta',label:'codigoPuntoVenta',field:'codigoPuntoVenta',sortable:true},
        {name:'codigoSucursal',label:'codigoSucursal',field:'codigoSucursal',sortable:true},
      ]
    }
  },
  created() {
    this.datosGet();
  },
  methods: {
    datosGet(){
      this.loading=true
      this.$api.get('activity').then(res => {
        this.loading=false
        this.activities=res.data.activities;
        this.documents=res.data.documents;
        this.documentsectors=res.data.documentsectors;
        this.events=res.data.events;
        this.leyendas=res.data.leyendas;
        this.medidas=res.data.medidas;
        this.messages=res.data.messages;
        this.motivos=res.data.motivos;
        this.sectors=res.data.sectors;
        this.servicios=res.data.servicios;
        this.tipopagos=res.data.tipopagos;
      })
    },
    activityCreate() {
      this.loading=true
      this.$api.post('activity', this.cui).then(res => {
        // console.log(res.data)
        this.datosGet()
        this.loading=false
        this.cuiDialog = false
        this.$q.notify({
          color: 'positive',
          message: 'Registro creado correctamente',
          icon: 'done'
        })
      }).catch(err=>{
        this.loading=false
        this.$q.notify({
          color: 'negative',
          message: err.response.data.message,
          position: 'top',
          icon: 'error',
          timeout: 5000
        })
      })
    }
  }
}
</script>
