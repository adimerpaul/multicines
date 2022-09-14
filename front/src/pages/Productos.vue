<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Productos" :rows-per-page-options="[20,50,100,0]" :rows="store.productos" :columns="productoColumns" :filter="productoFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="productoDialog=true"/>
            <q-input outlined dense debounce="300" v-model="productoFilter" placeholder="Buscar">
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
                  <q-item clickable v-close-popup @click="productoUpdateDialog=true;producto2=props.row;this.store.rubro=props.row.rubro">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="dialog_img=true;producto2=props.row;">
                    <q-item-section>
                      <q-item-label>Actualizar Imagen</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="productoDelete(props.row.id,props.pageIndex)">
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

    <q-dialog v-model="productoDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="productoCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="producto.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="producto.descripcion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="precio" v-model="producto.precio" type="number" step="0.01" />
              </div>

              <div class="col-3">
                <q-select dense outlined label="rubro_id" v-model="store.rubro" :options="store.rubros" option-label="nombre"/>
              </div>
              <div class="col-3">
                <q-input
                  label="Color Fondo"
                  outlined
                  dense
                  v-model="producto.color"
                  class="Color Fondo"
                  :rules="[val => val.length>0 || 'Seleccionar color']"
                >
                  <template v-slot:append>
                    <q-icon name="colorize" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-color v-model="producto.color" />
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

    <q-dialog v-model="productoUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="productoUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="producto2.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="producto2.descripcion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Precio" v-model="producto2.precio"  type="number" step="0.01"/>
              </div>

              <div class="col-3">
                <q-select dense outlined label="rubro_id" v-model="store.rubro" :options="store.rubros" option-label="nombre"/>
              </div>
              <div class="col-3">
                <q-toggle v-model="producto2.activo" color="green" :label="producto2.activo" size="xl"  false-value="INACTVO" true-value="ACTIVO"/>

              </div>
              <div class="col-3">
                <q-input
                  label="Color Fondo"
                  outlined
                  dense
                  v-model="producto2.color"
                  class="Color Fondo"
                  :rules="[val => val.length>0 || 'Seleccionar color']"
                >
                  <template v-slot:append>
                    <q-icon name="colorize" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-color v-model="producto2.color" />
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
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
  name: `Peliculas`,
  data() {
    return {
      loading: false,
      productoFilter:'',
      url:process.env.API,
      dialog_img:false,
      producto:{},
      producto2:{},
      productoDialog:false,
      productoUpdateDialog:false,
      store: globalStore(),
      foto:'',
      productoColumns:[
        {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'NOMBRE',field:'nombre',name:'nombre',sortable:true},
        {label:'DESCRIPCION',field:'descripcion',name:'descripcion',sortable:true},
        {label:'PRECIO',field:'precio',name:'precio',sortable:true},
        {label:'IMAGEN',field:'imagen',name:'imagen',sortable:true},
        {label:'RUBRO',field:row=>row.rubro.nombre,name:'rubro',sortable:true},
        {label:'ACTIVO',field:'activo',name:'activo',sortable:true},
      ]
    }
  },
  created() {
    if(!this.store.productoSingleTon) {
      this.$q.loading.show()
      this.store.productoSingleTon=true
      this.$api.get('producto').then(res=>{
        this.store.productos=res.data
        this.$q.loading.hide()
      })
    }
    if(!this.store.rubroSingleTon){
      this.$q.loading.show()
      this.store.rubroSingleTon=true
      this.$api.get('rubro').then(res=>{
        this.store.rubros=res.data
        this.store.rubro=res.data[0]
        this.$q.loading.hide()
      })
    }
  },
  methods: {
    onModImagen(){
      this.producto2.imagen=this.foto
      this.$q.loading.show()
      this.$api.post('upimagenproducto',this.producto2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.producto2={}
        this.foto=''
        let index = this.store.productos.findIndex(m => m.id == res.data.id);
        this.store.productos[index]=res.data
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
    productoCreate(){
      this.producto.imagen=this.foto
      this.loading=true
      this.producto.rubro_id=this.store.rubro.id
      this.$api.post('producto',this.producto).then(res=>{
        this.loading=false
        this.store.productos.push(res.data)
        this.foto=''
        this.productoDialog=false
        this.producto={}
      })
    },
    productoUpdate(){
      this.$q.loading.show()
      this.$api.put('producto/'+this.producto2.id,this.producto2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.producto2={}
        let index = this.store.productos.findIndex(m => m.id == res.data.id);
        this.store.productos[index]=res.data
        this.productoUpdateDialog=false
        this.producto2={}
      })
    },
    productoDelete(id,pageIndex){
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
        this.$api.delete('producto/'+id).then(res=>{
          this.store.productos.splice(pageIndex,1)
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
