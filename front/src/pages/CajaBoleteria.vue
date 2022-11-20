<template>
    <q-page padding>
      <div class="row">
        <div class="col-3"><q-select square outlined v-model="user" :options="users" label="Usuario" /></div>
        <div class="col-3"><q-input square outlined v-model="ini" label="fecha ini" type="date" /></div>
        <div class="col-3"><q-input square outlined v-model="fin" label="fecha fin" type="date" /></div>
        <div class="col-3"> <q-btn color="green"  label="Consultar" @click="consultar"/></div>
        <div class="col-12">
          <q-table dense title="Listado Venta Boleteria" :rows-per-page-options="[20,50,100,0]" :rows="reporte" :columns="columna" :filter="productoFilter">
            <template v-slot:top-right>
       
              <q-input outlined dense debounce="300" v-model="productoFilter" placeholder="Buscar">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </template>

>
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
  import {Printd} from "printd";

  export default {
    name: `Peliculas`,
    data() {
      return {
        ini:date.formatDate(new Date(), "YYYY-MM-DD"),
        fin:date.formatDate(new Date(), "YYYY-MM-DD"),
        loading: false,
        productoFilter:'',
        url:process.env.API,
        dialog_img:false,
        producto:{},
        producto2:{},
        productoDialog:false,
        productoUpdateDialog:false,
        reporte:[],
        user:{},
        users:[],
        store: globalStore(),
        foto:'',
        tvip:0,
        tcredito:0,
        tefectivo:0,
        total:0,
        columna:[
          {label:'NOMBRE',field:'descripcion',name:'descripcion',sortable:true},
          {label:'cantidad',field:'cantidad',name:'cantidad',sortable:true},
          {label:'SUBTOTAL',field:'subtotal',name:'subtotal',sortable:true},
        ]
      }
    },
    created() {
        this.listuser()
    },
    methods: {
        listuser(){
            this.users=[{id:0,label:'TODOS'}]
            this.$api.get('user').then(res=>{
                console.log(res.data)
                res.data.forEach(r => {
                    r.label=r.name
                    this.users.push(r)
                });
                this.user=this.users[0]
            })

        },

      consultar(){
          this.loading=true
        this.$api.post('cajaBol',{ini:this.ini,fin:this.fin,id:this.user.id}).then(res=>{
            console.log(res.data)
          this.loading=false
          this.reporte=res.data
        })
      },
      resumen(){
          this.loading=true
        this.$api.post('resumenBol',{ini:this.ini,fin:this.fin,id:this.user.id}).then(res=>{
            console.log(res.data)
            if(res.data.credito==null) res.data.credito=0
            if(res.data.vip==null) res.data.vip=0
            if(res.data.efectivo==null) res.data.efectivo=0
            this.tcredito=res.data.credito
            this.tvip=res.data.vip
            this.tefectivo=res.data.efectivo

          this.loading=false
        })
      },

      imprimir(){
        
      }


    }
    ,
    computed:{
        ventatotal(){
            let suma=0
            this.reporte.forEach(r=>{
                suma+=r.subotal
            })
            return suma
        },

    }
  }
  </script>
  
  <style scoped>
  
  </style>
  