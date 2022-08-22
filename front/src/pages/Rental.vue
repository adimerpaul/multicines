<template>
<q-page>
<div class="row">
  <div class="col-12">
    <div class="row">
      <div class="col-3"><q-input dense type="date" outlined label="fechaInicio" v-model="fechaInicio" /></div>
      <div class="col-3"><q-input dense type="date" outlined label="fechaFin" v-model="fechaFin" /></div>
      <div class="col-3 flex flex-center"><q-btn color="primary" label="Consultar" class="full-width"  icon="search" @click="rentalConsulta" /></div>
    </div>
  </div>
</div>
</q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `Rental`,
  data(){
    return{
      fechaInicio:date.formatDate(new Date(),'YYYY-MM-DD'),
      fechaFin:date.formatDate(new Date(),'YYYY-MM-DD'),
      rental:{
        montoTotal:100,
        periodoFacturado:'ENERO 2022',
      }
    }
  },
  mounted() {

  },
  methods:{
    rentalConsulta(){
      this.$api.post('rentalConsulta',{
        fechaInicio:this.fechaInicio,
        fechaFin:this.fechaFin,
      }).then(res=>{
        console.log(res.data)
      })
    }
  }
}
</script>

<style scoped>

</style>
