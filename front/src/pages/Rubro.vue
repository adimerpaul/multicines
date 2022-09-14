<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Rubro" :rows-per-page-options="[20,50,100,0]" :rows="store.rubros" :columns="rubroColumns" :filter="rubroFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="rubroDialog=true"/>
            <q-input outlined dense debounce="300" v-model="rubroFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-imagen="props">
            <q-td auto-width :props="props">
              <div :style="'background: '+props.row.color" class="flex flex-center">
              <q-img :src="url+'../imagen/'+props.row.imagen" class="q-pa-lg" style="border:0px solid black;height: 50px; max-width: 50px;"/>
              </div>
            </q-td>

          </template>

          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="rubroUpdateDialog=true;rubro2=props.row;this.store.rubro=props.row.rubro">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="rubro2=props.row; dialog_img=true;">
                    <q-item-section>
                      <q-item-label>Cambiar Imagen</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="rubroDelete(props.row.id,props.pageIndex)">
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

    <q-dialog v-model="rubroDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Rubro</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="rubroCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="rubro.nombre" :rules="[val => val.length>0 || 'Ingresar Nombre']"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="rubro.descripcion" />
              </div>
              <div class="col-3">
                <q-input
                  label="Color Fondo"
                  outlined
                  dense
                  no-header
                  v-model="rubro.color"
                  class="Color Fondo"
                  :rules="[val => val.length>0 || 'Seleccionar color']"
                >
                  <template v-slot:append>
                    <q-icon name="colorize" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-color v-model="rubro.color" />
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>

              <div class="col-12 text-center flex flex-center">
                <q-uploader
                  accept=".jpg, .png"
                  @added="uploadFile"
                  auto-upload
                  max-files="1"
                  label="Ingresar Imagen"
                  flat
                  max-file-size="2000000"
                  @rejected="onRejected"
                  bordered
                />
              </div>

              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="rubroUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Rubro</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="rubroUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="rubro2.nombre" :rules="[val => val.length>0 || 'Ingresar Nombre']"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="rubro2.descripcion" />
              </div>


              <div class="col-3">
                <q-input
                  label="Color Fondo"
                  outlined
                  dense
                  v-model="rubro2.color"
                  class="Color Fondo"
                  :rules="[val => val.length>0 || 'Seleccionar color']"
                >
                  <template v-slot:append>
                    <q-icon name="colorize" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-color v-model="rubro2.color" />
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
              <div class="col-3">
                <q-toggle v-model="rubro2.activo" color="green" :label="rubro2.activo" size="xl"  false-value="INACTVO" true-value="ACTIVO"/>

              </div>

              <div class="col-12">
                <q-btn color="yellow-8" icon="edit" class="full-width" type="submit" label="Modificar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialog_img">
      <q-card>
        <q-card-section class="bg-amber-14 text-white">
          <div class="text-h6">Modificar imagen</div>
        </q-card-section>
        <q-card-section class="q-pt-xs">
          <q-form
            @submit="onModImagen"
            class="q-gutter-md"
          >
            <div class="col-12 text-center flex flex-center">
              <q-uploader
                accept=".jpg, .png"
                @added="uploadFile"
                auto-upload
                max-files="1"
                label="Ingresar Imagen"
                flat
                max-file-size="2000000"
                @rejected="onRejected"
                bordered
              />
            </div>
            <div>
              <q-btn label="Modificar" type="submit" color="positive" icon="add_circle"/>
              <q-btn  label="Cancelar" icon="delete" color="negative" v-close-popup />
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
      rubroFilter:'',
      rubro:{},
      url:process.env.API,
      rubro2:{},
      rubroDialog:false,
      rubroUpdateDialog:false,
      dialog_img:false,
      store: globalStore(),
      foto:'',
      rubroColumns:[
        {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'NOMBRE',field:'nombre',name:'nombre',sortable:true},
        {label:'DESCRIPCION',field:'descripcion',name:'descripcion',sortable:true},
        {label:'IMAGEN',field:'imagen',name:'imagen',sortable:true},
        {label:'ACTIVO',field:'activo',name:'activo'},
      ]
    }
  },
  created() {
    if(!this.store.rubroSingleTon) {
      this.$q.loading.show()
      this.store.rubroSingleTon=true
      this.$api.get('rubro').then(res=>{
        this.store.rubros=res.data
        this.$q.loading.hide()
      })

    }
  },
  methods: {
    onModImagen(){
      this.rubro2.imagen=this.foto
      this.$q.loading.show()
      this.$api.post('upimagenrubro',this.rubro2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.rubro2={}
        let index = this.store.rubros.findIndex(m => m.id == res.data.id);
        this.store.rubros[index]=res.data
        this.dialog_img=false
      })
    },
    uploadFile (file) {
      let dialog = this.$q.dialog({
        message: 'Subiendo... 0%',
      })
      let percentage = 0
      const fd = new FormData()
      fd.append('file', file[0])
      return new Promise((resolve, reject) => {
        this.$api.post('upload',
          fd,
          {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
              percentage = Math.round((progressEvent.loaded / progressEvent.total) * 100)
              dialog.update({
                message: `Subiendo... ${percentage}%`
              })
              if (percentage>=100){
                dialog.hide()
              }
            }
          })
          .then(res => {
            this.foto=res.data
            resolve(file)
          })
          .catch(err => reject(err))
      })
    },
    onRejected (rejectedEntries) {
      this.$q.notify({
        type: 'negative',
        message: `${rejectedEntries.length} el archivo paso las restricciones de validación`
      })
    },
    rubroCreate(){
      this.rubro.imagen=this.foto
      this.loading=true
      this.$api.post('rubro',this.rubro).then(res=>{
        this.loading=false
        this.store.rubros.push(res.data)
        this.rubroDialog=false
        this.rubro={}
      })
    },
    rubroUpdate(){
      this.$q.loading.show()
      this.$api.put('rubro/'+this.rubro2.id,this.rubro2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.rubro2={}
        let index = this.store.rubros.findIndex(m => m.id == res.data.id);
        this.store.rubros[index]=res.data
        this.rubroUpdateDialog=false
        this.rubro2={}
      })
    },
    rubroDelete(id,pageIndex){
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
        this.$api.delete('rubro/'+id).then(res=>{
          this.store.rubros.splice(pageIndex,1)
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
