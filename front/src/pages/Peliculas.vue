<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar peliculas" :rows-per-page-options="[0]" :rows="store.movies" :columns="movieColumns" :filter="movieFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="movieDialog=true"/>
            <q-input outlined dense debounce="300" v-model="movieFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="info" label="Opciones" dropdown-icon="change_history">
                <q-list>
                  <q-item clickable v-close-popup @click="movieUpdateDialog=true;movie2=props.row;this.store.distributor=props.row.distributor">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="movieDelete(props.row.id,props.pageIndex)">
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>
        <pre>{{store.movies}}</pre>
      </div>
    </div>
    <q-dialog v-model="movieDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="movieCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="nombre" v-model="movie.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="duracion" v-model="movie.duracion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="paisOrigen" v-model="movie.paisOrigen" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="genero" v-model="movie.genero" :options="['Accion','Animacion','Animada','Aventura','Aventuras','Ciencia Ficcion','Comedia','Deporte','Documental','Drama','Historica','Infantil','Musical','Romantica','Suspenso','Terror','Thiller','Western','Otros']"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="sipnosis" v-model="movie.sipnosis" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="urlTrailer" v-model="movie.urlTrailer" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="clasificacion" v-model="movie.clasificacion" :options="['+13','+13 C/R','+16','+18','ATP','ATP C/R','R',]"/>
              </div>
              <div class="col-3">
                <q-input dense outlined type="date" label="fechaEstreno" v-model="movie.fechaEstreno" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="distributor_id" v-model="store.distributor" :options="store.distributors" option-label="nombre"/>
              </div>
              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="movieUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="movieUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="nombre" v-model="movie2.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="duracion" v-model="movie2.duracion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="paisOrigen" v-model="movie2.paisOrigen" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="genero" v-model="movie2.genero" :options="['Accion','Animacion','Animada','Aventura','Aventuras','Ciencia Ficcion','Comedia','Deporte','Documental','Drama','Historica','Infantil','Musical','Romantica','Suspenso','Terror','Thiller','Western','Otros']"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="sipnosis" v-model="movie2.sipnosis" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="urlTrailer" v-model="movie2.urlTrailer" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="clasificacion" v-model="movie2.clasificacion" :options="['+13','+13 C/R','+16','+18','ATP','ATP C/R','R',]"/>
              </div>
              <div class="col-3">
                <q-input dense outlined type="date" label="fechaEstreno" v-model="movie2.fechaEstreno" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="distributor_id" v-model="store.distributor" :options="store.distributors" option-label="nombre"/>
              </div>
              <div class="col-12">
                <q-btn color="yellow-8" icon="edit" class="full-width" type="submit" label="Modificar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import {globalStore} from "stores/globalStore";
import {date} from "quasar";

export default {
  name: `Peliculas`,
  data() {
    return {
      loading: false,
      movieFilter:'',
      movie:{
        clasificacion:'+13',
        genero:'Accion',
        fechaEstreno:date.formatDate(new Date(),'YYYY-MM-DD'),
      },
      movie2:{},
      movieDialog:false,
      movieUpdateDialog:false,
      store: globalStore(),
      movieColumns:[
        {label:'opciones',field:'opciones',name:'opciones'},
        {label:'nombre',field:'nombre',name:'nombre',sortable:true},
        {label:'duracion',field:'duracion',name:'duracion',sortable:true},
        {label:'paisOrigen',field:'paisOrigen',name:'paisOrigen',sortable:true},
        {label:'genero',field:'genero',name:'genero',sortable:true},
        {label:'clasificacion',field:'clasificacion',name:'clasificacion',sortable:true},
        {label:'fechaEstreno',field:'fechaEstreno',name:'fechaEstreno',sortable:true},
        {label:'distributor_id',field:row=>row.distributor.nombre,name:'distributor_id',sortable:true},
      ]
    }
  },
  created() {
    if(!this.store.movieSingleTon) {
      this.$q.loading.show()
      this.store.movieSingleTon=true
      this.$api.get('movie').then(res=>{
        this.store.movies=res.data
        this.$q.loading.hide()
      })
    }
    if(!this.store.distributorSingleTon){
      this.$q.loading.show()
      this.store.distributorSingleTon=true
      this.$api.get('distributor').then(res=>{
        this.store.distributors=res.data
        this.store.distributor=res.data[0]
        this.$q.loading.hide()
      })
    }
  },
  methods: {
    movieCreate(){
      this.loading=true
      this.movie.distributor_id=this.store.distributor.id
      this.$api.post('movie',this.movie).then(res=>{
        this.loading=false
        this.store.movies.push(res.data)
        this.movieDialog=false
      })
    },
    movieUpdate(){
      this.$q.loading.show()
      this.$api.put('movie/'+this.movie2.id,this.movie2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.movie2={}
        let index = this.store.movies.findIndex(m => m.id == res.data.id);
        this.store.movies[index]=res.data
        this.movieUpdateDialog=false
      })
    },
    movieDelete(id,pageIndex){
      this.$q.dialog({
        title: 'Eliminar Pelicula',
        message: '¿Esta seguro de eliminar la pelicula?',
        ok: {
          push: true
        },
        cancel: {
          push: true,
          color: 'negative'
        },
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('movie/'+id).then(res=>{
          this.store.movies.splice(pageIndex,1)
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Pelicula eliminada',
            color: 'positive',
            icon: 'done'
          })
        })
      })
    }
  }
}
</script>

<style scoped>

</style>
