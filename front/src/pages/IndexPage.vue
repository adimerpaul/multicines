<template>
  <q-page>
  <div>  <q-btn color="green" icon="check_circle" label="Verificar Comunicacion" @click="verifica"/>
    <div class="text-h5">{{mensaje}}</div>
  </div>
  <q-page class="flex flex-center">
    <img
      alt="Quasar logo"
      src="~assets/quasar-logo-vertical.svg"
      style="width: 200px; height: 200px"
    >
  </q-page>
</q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'IndexPage',
  data() {
    return {
      valida:false,
      mensaje:''
    }
  },

  methods: {
    verifica() {
      this.$api.get('verificarComunicacion').then(res => {
        console.log(res.data)
        this.valida=res.data
        if (this.valida)
          this.mensaje='Existe comunicacion'
        else
          this.mensaje='Existe un Problema con SIAT'  
      })
    }
  }
})
</script>
