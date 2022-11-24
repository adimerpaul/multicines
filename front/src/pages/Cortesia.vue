<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Cortesias" :rows-per-page-options="[20,50,100,0]" :rows="cortesias" :columns="cortesiaColumns" :filter="cortesiaFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="cortesiaDialog=true"/>
            <q-input outlined dense debounce="300" v-model="cortesiaFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-user="props">
            <q-td :props="props" >
              <div v-if="props.row.user!=null">{{props.row.user.name}}</div>
            </q-td>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="cortesiaUpdateDialog=true;cortesia2=props.row;this.store.cortesia=props.row.cortesia">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="cortesiaDelete(props.row.id,props.pageIndex)">
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
    <q-dialog v-model="cortesiaDialog">
      <q-card >
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Cortesia</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="cortesiaCreate">
            <div class="row">
              <div class="col-12">
                <q-input dense outlined label="Cantidad de cortesia" v-model="cortesia.cantidad" />
              </div>
              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="cortesiaUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Cortesia</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="cortesiaUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Serie" v-model="cortesia2.serie" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Precio" v-model="cortesia2.precio" type="number" step="0.01"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="cortesia2.descripcion" />
              </div>
              <div class="col-3">
                <q-toggle
                  false-value="NO"
                  :label="'Promo '+ cortesia2.promo"
                  true-value="SI"
                  color="green"
                  v-model="cortesia2.promo"
                />

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
  name: `Cortesia`,
  data() {
    return {
      loading: false,
      cortesiaFilter:'',
      cortesia2:{},
      cortesiaDialog:false,
      cortesiaUpdateDialog:false,
      store: globalStore(),
      cortesiaColumns:[
        // {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'ID',field:'id',name:'id',sortable:true},
        {label:'Hora',field:'time',name:'time',sortable:true},
        {label:'Fecha',field:'date',name:'date',sortable:true},
        {label:'user',field:'user',name:'user'},
      ],
      cortesias:[],
      cortesia:{},
    }
  },
  created() {
    this.cortesiaGet();
  },
  methods: {
    cortesiaGet(){
      this.$api.get('cortesia').then(response => {
        this.cortesias = response.data;
      }).catch(error => {
        console.log(error);
      });
    },
    cortesiaCreate(){
      this.loading=true
      this.$api.post('cortesia',this.cortesia).then(res=>{
        this.loading=false
        this.cortesiaGet()
        this.cortesiaDialog=false
      })
    },
    cortesiaUpdate(){
      this.$q.loading.show()
      this.$api.put('cortesia/'+this.cortesia2.id,this.cortesia2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.cortesia2={}
        let index = this.store.cortesias.findIndex(m => m.id == res.data.id);
        this.store.cortesias[index]=res.data
        this.cortesiaUpdateDialog=false
      })
    },
    cortesiaDelete(id,pageIndex){
      this.$q.dialog({
        title: 'Eliminar Cortesia',
        message: '¿Esta seguro de eliminar la Cortesia?',
        ok: {
          push: true
        },
        cancel: {
          push: true,
          color: 'negative'
        },
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('cortesia/'+id).then(res=>{
          this.store.cortesias.splice(pageIndex,1)
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Cortesia eliminada',
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
