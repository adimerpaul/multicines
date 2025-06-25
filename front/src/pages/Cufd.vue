<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table
          :rows="cufds"
          :columns="cufdsColums"
          row-key="id"
          :loading="loading"
          :rows-per-page-options="[5, 10, 20]"
          :pagination="pagination"
          @request="onRequest"
        >
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              :loading="loading"
              @click="cuiDialog=true"/>
            <q-input outlined dense debounce="300" v-model="cudfFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
            <q-pagination
              v-model="pagination.page"
              :max="pagination.last_page"
              @update:model-value="onRequest"
              input
            />
          </template>
<!--          <template v-slot:top>-->
<!--          </template>-->
          <template v-slot:body-cell-Opciones="props">
            <q-td :props="props" auto-width>
              <q-btn v-if="props.pageIndex==0" size="10px" icon="add_circle_outline" @click="eventoSignificativoClick(props.row)" color="primary" label="Evento significativo" no-caps/>
            </q-td>
          </template>
          <template v-slot:body-cell-sales="props">
            <q-td :props="props" auto-width>
              {{props.row.sales.length}}
            </q-td>
          </template>
        </q-table>
<!--        <pre>{{cufds}}</pre>-->
<!--        <pre>{{cufd}}</pre>-->
<!--        <pre>{{cufdEvento}}</pre>-->
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
    <q-dialog v-model="eventoSignificativoDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar evento significativo</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="eventoSignificativoCreate">
            <div class="row">
              <div class="col-12">
                <q-select :options="cufds" dense outlined label="CufdEvento" v-model="cufdEvento" />
              </div>
              <div class="col-12">
                <q-select :options="events" dense outlined label="codigoMotivoEvento" v-model="event" />
              </div>
              <div class="col-12">
                <q-input dense outlined label="fechaHoraInicioEvento" v-model="fechaHoraInicioEvento" />
              </div>
              <div class="col-12">
                <q-input dense outlined label="fechaHoraFinEvento" v-model="fechaHoraFinEvento" />
              </div>
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
import {date} from "quasar";

export default {
  name: `Cuis`,
  data() {
    return {
      loading:false,
      eventoSignificativoDialog:false,
      fechaHoraInicioEvento:date.formatDate(new Date(),'YYYY-MM-DD HH:mm:ss'),
      fechaHoraFinEvento:date.formatDate(new Date(),'YYYY-MM-DD HH:mm:ss'),
      cufd:{},
      cufdEvento:{},
      cufds: [],
      pagination: {
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0,
        last_page: 1
      },
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
        {name:'sales',label:'sales',field:'sales',sortable:true},
        {name:'fechaCreacion',label:'fechaCreacion',field:'fechaCreacion',sortable:true},
        {name:'fechaVigencia',label:'fechaVigencia',field:'fechaVigencia',sortable:true},
        {name:'id',label:'id',field:'id',sortable:true},
        {name:'codigoSucursal',label:'codigoSucursal',field:'codigoSucursal',sortable:true},
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
  methods: {
    onRequest() {
      this.cufdGet();
    },
    eventoSignificativoClick(cufdEvento){
      this.eventoSignificativoDialog=true;
      // this.cufd=props.row;
      this.cufdEvento=cufdEvento;
      this.fechaHoraInicioEvento=this.cufdEvento.fechaCreacion
      this.fechaHoraFinEvento=this.cufdEvento.fechaVigencia
    },
    cufdGet() {
      this.loading = true;
      const { page, rowsPerPage } = this.pagination;

      this.$api.get('cufd', {
        params: {
          page,
          per_page: rowsPerPage,
          filter: this.cudfFilter
        }
      }).then(res => {
        this.cufds = res.data.data;
        this.pagination.rowsNumber = res.data.total;
        this.pagination.last_page = res.data.last_page;
        this.pagination.page = res.data.current_page;
        this.loading = false;
      }).catch(() => {
        this.loading = false;
      });
    },
    cuiCreate() {
      this.loading = true
      this.$api.post('cufd', this.cui).then(res => {
        this.loading = false
        this.cuiDialog = false
        this.cufdGet()
        // this.cufdGet()
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
    },
    eventoSignificativoCreate() {
      this.loading=true
      this.$api.post('eventoSignificativo',{
        codigoPuntoVenta:this.cufdEvento.codigoPuntoVenta,
        codigoSucursal:this.cufdEvento.codigoSucursal,
        fechaHoraFinEvento:this.fechaHoraFinEvento,
        fechaHoraInicioEvento:this.fechaHoraInicioEvento,
        codigoMotivoEvento:this.event.codigoClasificador,
        descripcion:this.event.descripcion,
        // cufd:this.cufd.codigo,
        cufdEvento:this.cufdEvento.codigo,
        cufdEventoId:this.cufdEvento.id,
      }).then(res => {
        // console.log(res.data)
        this.loading=false
        this.cuiDialog = false
        this.eventoSignificativoDialog=false
        // this.cufdGet()
        this.$q.notify({
          color: 'positive',
          message: 'Evento significativo  registrado correctamente',
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
