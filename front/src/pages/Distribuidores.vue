<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Distribuidor" :rows-per-page-options="[0]" :rows="store.distributors" :columns="distributorColumns" :filter="distributorFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="distributorDialog=true"/>
            <q-input outlined dense debounce="300" v-model="distributorFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="distributorUpdateDialog=true;distributor2=props.row;this.store.distributor=props.row.distributor">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="distributorDelete(props.row.id,props.pageIndex)">
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>

      </div>
    </div>
    <q-dialog v-model="distributorDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Distribuidor</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="distributorCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="distributor.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Direccion" v-model="distributor.dir" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Localidad" v-model="distributor.loc" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="NIT" v-model="distributor.nit" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Telefono" v-model="distributor.tel" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Email" v-model="distributor.email" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Responsable" v-model="distributor.responsable" />
              </div>

              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="distributorUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Distribuidor</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="distributorUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="distributor2.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Direccion" v-model="distributor2.dir" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Localidad" v-model="distributor2.loc" />
              </div>

              <div class="col-3">
                <q-input dense outlined label="NIT" v-model="distributor2.nit" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Telefono" v-model="distributor2.tel" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Email" v-model="distributor2.email" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Responsable" v-model="distributor2.responsable" />
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
  name: `Distribuidores`,
  data() {
    return {
      loading: false,
      distributorFilter:'',
      distributor:{
      },
      distributor2:{},
      distributorDialog:false,
      distributorUpdateDialog:false,
      store: globalStore(),
      distributorColumns:[
        {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'NOMBRE',field:'nombre',name:'nombre',sortable:true},
        {label:'DIRECCION',field:'dir',name:'dir',sortable:true},
        {label:'LOCALIZACION',field:'loc',name:'loc',sortable:true},
        {label:'NIT',field:'nit',name:'nit',sortable:true},
        {label:'TELEFONO',field:'tel',name:'tel',sortable:true},
        {label:'EMAIL',field:'email',name:'email',sortable:true},
        {label:'RESPONSABLE',field:'responsable',name:'responsable',sortable:true},
      ]
    }
  },
  created() {
    if(!this.store.distributorSingleTon) {
      this.$q.loading.show()
      this.store.distributorSingleTon=true
      this.$api.get('distributor').then(res=>{
        this.store.distributors=res.data
        this.$q.loading.hide()
      })

    }
  },
  methods: {
    distributorCreate(){
      this.loading=true
      this.$api.post('distributor',this.distributor).then(res=>{
        this.loading=false
        this.store.distributors.push(res.data)
        this.distributorDialog=false
      })
    },
    distributorUpdate(){
      this.$q.loading.show()
      this.$api.put('distributor/'+this.distributor2.id,this.distributor2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.distributor2={}
        let index = this.store.distributors.findIndex(m => m.id == res.data.id);
        this.store.distributors[index]=res.data
        this.distributorUpdateDialog=false
      })
    },
    distributorDelete(id,pageIndex){
      this.$q.dialog({
        title: 'Eliminar Distribuidor',
        message: '¿Esta seguro de eliminar la distribuidor?',
        ok: {
          push: true
        },
        cancel: {
          push: true,
          color: 'negative'
        },
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('distributor/'+id).then(res=>{
          this.store.distributors.splice(pageIndex,1)
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Distribuidor eliminada',
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
