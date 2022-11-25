<template>
  <q-page>
    <div class="row q-pa-xs">
      <div class="col-12">
      <div class="text-h5">PROGRAMACION DE FUNCIONES</div>
        <q-btn label="Registrar" icon="add_circle" color="primary" @click="programaDialog=true; "/>
      <div class="col-12">
        <div class="row">
          <div class="col q-ma-xs"><q-btn  icon="list" class="full-width " label="TODOS" :color="color[0]" @click="listado(0)"/></div>
          <div class="col q-ma-xs" v-for="s in store.salas" :key="s">
            <q-btn class="full-width " icon="play_circle_outline"  :label="s.nombre" :color="color[s.nro]" @click="listado(s.id)"/>
          </div>
        </div>
      </div>
        <FullCalendar class="full-height" :options="calendarOptions" />

      </div>

      <q-dialog v-model="programaDialog" full-width>
        <q-card >
          <q-card-section>
            <div class="text-h6">REGISTRAR DE FUNCION PELICULA</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <div class="row">
              <div class="col-12"></div>
            <div class="col-4"><q-select outlined v-model="sala" label="SALAS" :options="store.salas2"  use-input input-debounce="0" @filter="filterSala">
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No results
                  </q-item-section>
                </q-item>
              </template>
              </q-select></div>
              <div class="col-4"><q-select outlined v-model="pelicula" label="PELICULAS" :options="store.movies2" use-input input-debounce="0" @filter="filterPelicula">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
              </q-select></div>
              <div class="col-4"><q-select outlined v-model="tarifa" label="TARIFAS"  :options="store.prices2" use-input input-debounce="0" @filter="filterTarifa">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
              </q-select></div>
            <div class="col-4"><q-input outlined v-model="programa.fechaini" @update:model-value="cambioFecha" label="Fecha Inicial" type="date"/></div>
            <div class="col-4"><q-input outlined v-model="programa.fechafin" label="Fecha Final" type="date"/></div>
            <div class="col-4"><q-input outlined v-model="programa.hora" required label="Hora" type="time"/></div>
              <div class="col-4"><q-checkbox v-model="programa.subtitulada" label="SUBTITULADA" size="xl" true-value="SI" false-value="NO"/></div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="text-primary">
            <q-btn color="red " icon="o_delete" label="CANCELAR" v-close-popup />
            <q-btn color="green" icon="o_check_circle" :loading="loading" label="REGISTRAR" @click="registrarFuncion" />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <q-dialog v-model="programaUpdateDialog" full-width>
        <q-card >
          <q-card-section>
            <div class="text-h6">MODIFICAR FUNCION</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <div class="row">

              <div class="col-4"><q-select outlined v-model="sala" label="SALAS" :options="store.salas2"  use-input input-debounce="0" @filter="filterSala">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
              </q-select></div>
              <div class="col-4"><q-select outlined v-model="pelicula" label="PELICULAS" :options="store.movies2" use-input input-debounce="0" @filter="filterPelicula">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
              </q-select></div>
              <div class="col-4"><q-select outlined v-model="tarifa" label="TARIFAS"  :options="store.prices2" use-input input-debounce="0" @filter="filterTarifa">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
              </q-select></div>
              <div class="col-6"><q-input outlined v-model="programa2.fecha" label="Fecha " type="date" readonly/></div>
              <div class="col-6"><q-input outlined v-model="programa2.hora" label="Hora" type="time"/></div>
              <div class="col-4"><q-checkbox v-model="programa2.subtitulada" label="SUBTITULADA" size="xl" true-value="SI" false-value="NO"/></div>
              <div class="col-4"><q-toggle v-model="programa2.activo" color="purple" :label="programa2.activo" size="xl"  false-value="INACTVO" true-value="ACTIVO"/> </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="text-primary">
            <q-btn color="red-10" label="Atras" :loading="loading" icon="arrow_back" v-close-popup />
            <q-btn color="red" label="Eliminar" :loading="loading" @click="eliminarFuncion" icon="delete" />
            <q-btn  label="Modificar" :loading="loading" @click="modificarFuncion" icon="edit" color="yellow"/>
          </q-card-actions>
        </q-card>
      </q-dialog>

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
import moment from "moment"
export default {
  name: `Programa`,
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },
  data() {
    return {
      loading: false,
      events: [],
      sala:{label:'',value:{}},
      pelicula:{label:'',value:{}},
      tarifa:{label:'',value:{}},
      funcion:[],
      programa:{'fechaini':date.formatDate(new Date(), "YYYY-MM-DD"),'fechafin':date.formatDate(new Date(), "YYYY-MM-DD"),'hora':'00:00','subtitulada':'NO'},
      programa2:{},
      listpelis:[],
      listsalas:[],
      filsala:[],
      filpelis:[],
      filtarifa:[],
      listtarifa:[],
      programaUpdateDialog:false,
      color: ["indigo", "blue", "green", "cyan-4","orange","light-green","lime","amber", "light-blue", "purple", "pink","teal","lime", "deep-orange", "#1b5e20", "#ff5722","#795548","#e65100","#827717","#01579b", "#006064", "#1b5e20", "#ff5722","#795548"],
      programaDialog:false,
      store: globalStore(),

      calendarOptions: {
        // slotMinTime: "09:00:00",

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
        firstDay:4,
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
  mounted() {
    this.cambioFecha(this.programa.fechaini)

    if(!this.store.salaSingleTon) {
      this.$q.loading.show()
      this.store.salaSingleTon=true
      this.$api.get('sala').then(res=>{
        res.data.forEach(p=>{
          p.label=p.nombre
        })
        this.store.salas2=res.data
        this.store.salas3=res.data
        this.store.salas=res.data
        this.$q.loading.hide()
      })
    }
    if(!this.store.priceSingleTon) {
      this.$q.loading.show()
      this.store.priceSingleTon=true
      this.$api.get('price').then(res=>{
        res.data.forEach(p=>{
          p.label=p.serie+' '+p.precio+' Bs.'
        })
        this.store.prices2=res.data
        this.store.prices3=res.data
        this.store.prices=res.data
        this.$q.loading.hide()
      })

    }
    // if(!this.store.movieSingleTon) {
      this.$q.loading.show()
      this.store.movieSingleTon=true
      this.$api.get('movie').then(res=>{
        res.data.forEach(p=>{
          p.label=p.nombre+' ' + p.formato
        })
        this.store.movies2=res.data
        this.store.movies3=res.data
        this.store.movies=res.data
        this.$q.loading.hide()
      })
    // }
    //if(!this.store.movieSingleTon) {
     // this.$q.loading.show()
      //this.store.movieSingleTon=true
    //  this.$api.get('movie').then(res=>{
     //   this.store.movies=res.data
      //  this.$q.loading.hide()
      //})
    //}
    this.listado(0)
    // this.cargar()
  },
  methods: {
    cambioFecha(fecha){
      this.programa.fechafin=(moment(fecha).add(7, 'days').format("YYYY-MM-DD"));
    },
    eventTitleClick: function(args) {
     // console.log(args.event.id)
      this.funcion.forEach(r=>{
        if(r.id==args.event.id)
          this.programa2=r
      })
      // console.log(this.programa2)
      this.pelicula=this.programa2.movie
      this.pelicula.label=this.pelicula.nombre
      this.tarifa=this.programa2.price
      this.tarifa.label=this.tarifa.serie+' '+this.tarifa.precio+' Bs'
      this.sala=this.programa2.sala
      this.sala.label=this.sala.nombre
      this.programa2.hora=date.formatDate(this.programa2.horaInicio, "HH:mm")
      this.programaUpdateDialog=true
      //console.log(date.formatDate(this.programa2.horaInicio, "H:mm"))
    },

    registrarFuncion(){
      this.programa.movie=this.pelicula
      this.programa.price=this.tarifa
      this.programa.sala=this.sala
      this.loading=true
      this.$api.post('programa',this.programa).then(res=>{
        console.log(res.data)
        this.listado(0)
        this.loading=false
        this.programa={'fechaini':date.formatDate(new Date(), "YYYY-MM-DD"),'fechafin':date.formatDate(new Date(), "YYYY-MM-DD"),'hora':'00:00','subtitulada':'NO'}
        this.programaDialog=false
      }).catch(err=>{
        console.error(err)
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          icon: 'report_problem',
          message: err.response.data.message,
          position: 'top'
        })
        this.loading=false
      })

    },
    modificarFuncion(){
      this.programa2.movie=this.pelicula
      this.programa2.price=this.tarifa
      this.programa2.sala=this.sala
      this.loading=true
      this.$api.put('programa/'+this.programa2.id,this.programa2).then(res=>{
        // console.log(res.data)
        this.listado(0);
        this.loading=false
        this.programaUpdateDialog=false
        })

    },
    eliminarFuncion(){
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Está seguro de eliminar la función?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.loading=true
        this.$api.delete('programa/'+this.programa2.id).then(res=>{
          // console.log(res.data)
          this.listado(0);
          this.loading=false
          this.programaUpdateDialog=false
          })
      }).onCancel(() => {
        console.log('Cancel')
      }).onDismiss(() => {
        console.log('Dismissed')
      })
      // this.$api.delete('programa/'+this.programa2.id).then(res=>{
      //   // console.log(res.data)
      //   this.listado(0)
      // })
    },
    cargar(){
      this.listtarifa=[]
      this.listsalas=[]
      this.listpelis=[]
      this.store.prices.forEach(p=>{
        p.label=p.serie+' '+p.precio+' Bs.'
        this.listtarifa.push(p);
      })
      this.store.movies.forEach(p=>{
        p.label=p.nombre
        this.listpelis.push(p);
      })
      this.store.salas.forEach(p=>{
        p.label=p.nombre
        this.listsalas.push(p);
      })
      this.filsala=this.listsalas
      this.filpelis=this.listpelis
      this.filtarifa=this.listtarifa
    },
    filterSala(val, update) {
        if (val === '') {
          update(() => {
            this.store.salas2 = this.store.salas3
            // here you have access to "ref" which
            // is the Vue reference of the QSelect
          })
          return
        }

        update(() => {
          const needle = val.toLowerCase()
          this.store.salas2 = this.store.salas3.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
        })

    },
    filterPelicula(val, update) {
      if (val === '') {
        update(() => {
          this.store.movies2 = this.store.movies3

          // here you have access to "ref" which
          // is the Vue reference of the QSelect
        })
        return
      }

      update(() => {
        const needle = val.toLowerCase()
        this.store.movies2 = this.store.movies3.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })

    },

    filterTarifa(val, update) {
      if (val === '') {
        update(() => {
          this.store.prices2 = this.store.prices3

          // here you have access to "ref" which
          // is the Vue reference of the QSelect
        })
        return
      }

      update(() => {
        const needle = val.toLowerCase()
        this.store.prices2 = this.store.prices3.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })

    },
    listado(num){
      this.events=[]
      this.funcion=[]
        this.$api.get('programa').then(res=>{
            // console.log(res.data)
          let col=0
          res.data.forEach(r=>{
            this.funcion.push(r)
            // console.log(r.sala.nro)
            col=r.sala.nro
            if(num==0){
              this.events.push({ title: r.movie.nombre+' '+r.movie.formato, start: r.horaInicio,end:r.horaFin,color:this.color[col],id:r.id })
              }
            else{
            if(num==r.sala.id)
              this.events.push({ title: r.movie.nombre+' '+r.movie.formato, start: r.horaInicio,end:r.horaFin,color:this.color[col],id:r.id })
            }

        })
          console.log(this.events)
          this.calendarOptions.events=this.events

        })

    },



  },
  computed:{
    listapelis(){
      return 0
    }
  }
}
</script>

<style scoped>

</style>
