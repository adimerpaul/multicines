<template>
<q-page>
<q-card>
  <q-card-section class="q-py-xs bg-green-7 text-white text-bold" >
    <div class="row">
      <div class="col-2 flex flex-center">
        PANEL DE VENTAS
      </div>
      <div class="col-2">
        <q-input label="fecha" @update:model-value="myMovies" :min="now" type="date" label-color="white" outlined dense v-model="fecha"/>
      </div>
    </div>
  </q-card-section>
  <q-card-section class="q-py-none">
    <div class="row">
      <div class="col-6"><div class="text-h6"><q-icon name="o_confirmation_number"/> PELICULAS</div></div>
      <div class="col-6 text-right"><div class="text-subtitle2">Venta de boletos vendidos: 000</div></div>
    </div>
  </q-card-section>
  <q-separator />
  <q-card-section>
    <div class="row">
      <div class="col-2" v-for="m in movies" :key="m.id">
        <q-card @click="myHours(m.movie)" style="height: 100px" class="q-ma-xs">
          <q-img :src="url+'../imagen/'+m.movie.imagen" height="100px">
            <div class="absolute-bottom text-subtitle2 text-center" style="padding: 0px;margin:0px;border: 0px">
              <div class="row">
                <div class="col-6 q-pa-none q-ma-none">
                  {{m.movie.nombre}}
                </div>
                <div class="col-6 text-right flex flex-center">0</div>
              </div>
            </div>
          </q-img>
        </q-card>
      </div>

    </div>
  </q-card-section>
  <q-separator />
  <q-card-section>

  </q-card-section>
  <q-separator />
  <q-card-section>
    <div class="row">
      <div class="col-3">
        <div class="text-bold text-center">{{movie.nombre}}</div>
        <q-btn :loading="loading" size="12px" class="q-ma-xs full-width flex flex-center" v-for="h in hours" color="primary" :key="h.id" >
          <q-icon name="schedule" left/>
          <q-badge color="red">S{{h.sala.nro}}</q-badge> {{h.horaInicio.substring(10,16)}} <q-badge color="secondary">{{h.formato}}</q-badge> {{h.price.precio+'Bs'}}
        </q-btn>
        <pre>{{hours}}</pre>
      </div>
      <div class="col-3"></div>
      <div class="col-6"></div>
    </div>

  </q-card-section>
</q-card>
</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `Sale`,
  data() {
    return {
      url:process.env.API,
      now:date.formatDate(new Date(), "YYYY-MM-DD"),
      fecha: date.formatDate(new Date(), "YYYY-MM-DD"),
      movies:[],
      movie:{},
      hours:[],
      hour:{},
      loading:false
    }
  },
  created() {
    this.myMovies(this.fecha)
  },
  methods: {
    myMovies(fecha) {
      this.$api.post('movies',{fecha:fecha}).then(res => {
        this.movies = res.data
        this.movie = this.movies[0].movie
        this.myHours(this.movie)
      });
    },
    myHours(movie) {
      this.movie = movie
      this.loading= true
      this.$api.post('hours',{fecha:this.fecha,id:movie.id}).then(res => {
        this.loading=false
        this.hours = res.data
      });
    }
  }
}
</script>

<style scoped>

</style>
