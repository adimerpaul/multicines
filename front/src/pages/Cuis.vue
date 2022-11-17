<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table :rows="cuis" dense title="Gestionar cuis" :columns="cuisColums" :rows-per-page-options="[0]">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              :loading="loading"
              @click="cuiDialog=true"/>
            <q-input outlined dense debounce="300" v-model="cuiFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
        </q-table>
      </div>
    </div>
    <q-dialog v-model="cuiDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Cuis</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="cuiCreate">
            <div class="row">
              <div class="col-12">
                <q-select :options="[0]" dense outlined label="codigoPuntoVenta" v-model="cui.codigoPuntoVenta" />
              </div>
<!--              <div class="col-12">-->
<!--                <q-select :options="[0,1,2]" dense outlined label="codigoSucursal" v-model="cui.codigoSucursal" />-->
<!--              </div>-->
              <div class="col-12 flex flex-center">
                <q-btn dense class="full-width" :loading="loading" type="submit" color="green" icon="check" label="Guardar" />
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
  name: `Cuis`,
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
    this.getCuis();
  },
  methods: {
    getCuis() {
      this.loading=true;
      this.$api.get('cui').then(res => {
        this.loading=false;
        this.cuis = res.data
      })
    },
    cuiCreate() {
      this.loading=true
      this.$api.post('cui', this.cui).then(res => {
        this.loading=false
        this.cuiDialog = false
        this.getCuis()
        this.$q.notify({
          color: 'positive',
          message: 'Cui registrado correctamente',
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
