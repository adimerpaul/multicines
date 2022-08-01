<template>
<q-page>
<q-card>
  <q-card-section class="q-py-xs bg-green-7 text-white text-bold" >
    <div class="row">
      <div class="col-2 flex flex-center">
        <q-icon name="live_tv" left/> PANEL DE VENTAS
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
        <q-btn @click="clickSala(h)" :loading="loading" size="12px" class="q-ma-xs full-width flex flex-center" v-for="h in hours" color="primary" :key="h.id" >
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
<q-dialog full-width full-height v-model="salaDialog">
<q-card>
  <q-card-section >
    <div class="row">
      <div class="col-12">
        <div class="text-bold">{{movie.nombre}} <q-icon name="schedule" left/><q-badge color="red">{{hour.sala.nombre}}</q-badge> {{hour.horaInicio.substring(10,16)}} <q-badge color="secondary">{{hour.formato}}</q-badge> {{hour.price.precio+'Bs'}}</div>
      </div>
      <div class="col-12">
        <table>
          <thead>
          <tr>
            <th :colspan="parseInt(hour.sala.columnas)+1" class="bg-blue-10 text-bold text-white">PANTALLA</th>
          </tr>
          <tr>
            <th></th>
            <th v-for="(c,i) in parseInt(hour.sala.columnas)" :key="i">{{hour.sala.columnas-c+1}}</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(f,i) in parseInt(hour.sala.filas)" :key="i">
            <th>{{letra[i+1]}}</th>
            <td v-for="(c,j) in parseInt(hour.sala.columnas)" click="cambio(f,c)" :key="j" class="text-center tdx" style="padding: 0px;margin: 0px;border: 0px">
              <q-btn color="green-6" class="full-width" :label="letra[i+1]+'-'+(hour.sala.columnas-c+1).toString()"/>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div class="col-12">
        <pre>{{hour.sala}}</pre>
      </div>
    </div>
  </q-card-section>
</q-card>
</q-dialog>
</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `Sale`,
  data() {
    return {
      letra:['','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB'],
      url:process.env.API,
      salaDialog:false,
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
    clickSala(h){
      this.hour=h
      this.salaDialog=true
    },
    myMovies(fecha) {
      this.movie={}
      this.hours=[]
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
        this.hour = this.hours[0]
        this.clickSala(this.hour)
      });
    }
  }
}
</script>

<style scoped>
table{
  width: 100%;
}
table, .tdx, th {
  border-collapse: collapse;
  border: 1px solid #ddd;
  padding: 5px;
}
input{
  border: 1px solid #ddd;
}
</style>
