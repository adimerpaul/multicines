<template>
<q-page>
    <div class="row">

      <div class="col-3"> <q-input v-model="client.numeroDocumento" label="NIT/CI/CEX" outlined/></div>
      <div class="col-3"> <q-input v-model="client.complemento" label="COMPLEMENTO" outlined/></div>
      <div class="col-3"> <q-btn label="Buscar Cliente" icon="search" @click="searchClient" color="info"/></div>

      <div class="col-6" v-if="client.id!=undefined">
        <div class="text-h5" >DATOS DE CLIENTE</div>

        <q-select v-model="document" outlined :options="documents"/>
        <q-input v-model="client.nombreRazonSocial" label="Nombre RazonSocial" outlined/>
        <q-input v-model="client.email" label="Email" type="email" outlined/>
        <q-btn label="Modificar" icon="edit" @click="updateClient" color="yellow"/>
      </div>



    </div>


</q-page>

</template>

<script>
export default {
  name: `Cliente`,
  data() {
    return {
      client:{},
      document:{},
      documents:[]

    }
  },
  created() {
    this.$api.get('document').then(res=>{
      res.data.forEach(r=>{
        r.label=r.descripcion
      })
      this.documents=res.data
      this.document=this.documents[0]
    })
  },
  methods: {
    searchClient(){
      // console.log(this.client)
      if(this.client.numeroDocumento==undefined){
        return false
      }
      this.document=this.documents[0]
      this.client.nombreRazonSocial=''
      this.client.email=''
      this.client.id=undefined
      this.$api.post('searchClient',this.client).then(res=>{
        // console.log(res.data)
        if (res.data.nombreRazonSocial!=undefined) {
          this.client.nombreRazonSocial=res.data.nombreRazonSocial
          this.client.email=res.data.email
          this.client.id=res.data.id
          let documento=this.documents.find(r=>r.codigoClasificador==res.data.codigoTipoDocumentoIdentidad)
          documento.label=documento.descripcion
          this.document=documento
        }
      })
    },
    updateClient(){

      if(this.client.id==undefined){
        return false
      }
      this.client.codigoTipoDocumentoIdentidad=this.document.codigoClasificador
      this.client.email=this.client.email==undefined?'':this.client.email
      this.$api.put('client/'+this.client.id,this.client).then(res=>{

      })



      }
  }
}
</script>

<style scoped>

</style>
