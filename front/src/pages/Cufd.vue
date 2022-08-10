<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table :rows="cufds" dense title="Gestionar cufd" :columns="cufdsColums" :rows-per-page-options="[0]" :filter="cudfFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="cuiDialog=true"/>
            <q-input outlined dense debounce="300" v-model="cudfFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-Opciones="props">
            <q-td :props="props" auto-width>
              <q-btn v-if="props.pageIndex == 0" size="10px" icon="add_circle_outline" @click="eventoSignificativoDialog=true;cufd=props.row;cufdEvento=cufds[props.pageIndex+1]" color="primary" label="Evento significativo" no-caps/>
            </q-td>
          </template>
        </q-table>
        <pre>{{cufd}}</pre>
        <pre>{{cufdEvento}}</pre>
      </div>
    </div>
    <q-dialog v-model="cuiDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Cufd</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="cuiCreate">
            <div class="row">
              <div class="col-12">
                <q-select :options="[0,1,2]" dense outlined label="codigoPuntoVenta" v-model="cui.codigoPuntoVenta" />
              </div>
              <div class="col-12">
                <q-select :options="[0,1,2]" dense outlined label="codigoSucursal" v-model="cui.codigoSucursal" />
              </div>
              <div class="col-12 flex flex-center">
                <q-btn dense class="full-width" :loading="loading" type="submit" color="green" icon="check" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="eventoSignificativoDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar evento significativo</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="cuiCreate">
            <div class="row">
              <div class="col-12 flex flex-center">
                <q-btn dense class="full-width" :loading="loading" type="submit" color="green" icon="check" label="Crear evento significativo" />
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
      eventoSignificativoDialog:false,
      cufd:{},
      cufdEvento:{},
      cufds:[],
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
  },
  methods: {
    cufdGet() {
      this.$api.get('cufd').then(res => {
        res.data.forEach(r=>{
          r.label='Punto venta'+r.codigoPuntoVenta+' ID:'+r.id;
        })
        this.cufds = res.data
      })
    },
    cuiCreate() {
      this.loading=true
      this.$api.post('cufd', this.cui).then(res => {
        this.loading=false
        this.cuiDialog = false
        this.cufdGet()
        this.$q.notify({
          color: 'positive',
          message: 'Cui registrado correctamente',
          icon: 'done'
        })
      })
        .catch(err=>{
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
