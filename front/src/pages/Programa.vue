<template>
  <q-page>
    <div class="row">
      <div class="col-12">
      <div class="text-h5">PROGRMACION DE FUNCIONES</div>

        <FullCalendar class="full-height" :options="calendarOptions" />

      </div>
    </div>
  </q-page>
</template>

<script>
import {globalStore} from "stores/globalStore";
import {date} from "quasar";
import FullCalendar, {formatDate} from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import esLocale from "@fullcalendar/core/locales/es";

export default {
  name: `Programa`,
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },
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
      ],
      calendarOptions: {
        selectable:true,
        plugins: [ dayGridPlugin, timeGridPlugin,interactionPlugin], headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        initialView: 'timeGridWeek',
        locale: esLocale,
        allDaySlot:false,
        editable: true,
        selectMirror: true,
        dayMaxEvents: true,
        // weekends: true,
        // select: this.handleDateSelect,
        // dateClick: this.handleDateClick,
        eventClick: this.eventTitleClick,
        dateClick:this.dateClick,
        // select:this.handleSelect,
        events: [
          // { title: 'event 1', date: '2022-02-01' },
          // { title: 'event 2', date: '2022-02-02' }
        ],
        height: "auto",
        weekends: true // initial value
      },
    }
  },
  created() {
    //if(!this.store.movieSingleTon) {
     // this.$q.loading.show()
      //this.store.movieSingleTon=true
    //  this.$api.get('movie').then(res=>{
     //   this.store.movies=res.data
      //  this.$q.loading.hide()
      //})
    //}
    this.listado()
  },
  methods: {
    listado(){
        this.$api.get('programa').then(res=>{
            console.log(res.data)
        })

    },
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
