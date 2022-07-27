<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Salas" :rows-per-page-options="[0]" :rows="store.salas" :columns="salaColumns" :filter="salaFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="salaDialog=true"/>
            <q-input outlined dense debounce="300" v-model="salaFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-opciones="props">
            <td auto-width :props="props">
              <q-btn-dropdown color="info" label="Opciones" dropdown-icon="change_history">
                <q-list>
                  <q-item clickable v-close-popup @click="salaUpdateDialog=true;sala2=props.row;this.store.sala=props.row.sala">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="salaDelete(props.row.id,props.pageIndex)">
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </td>
          </template>
        </q-table>
        <pre>{{store.movies}}</pre>
      </div>
    </div>

    <q-dialog v-model="salaDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Sala</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="salaCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="NRO" v-model="sala.nro" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="sala.nombre" />
              </div>
              <div class="col-2">
                <q-input dense outlined label="Filas" v-model="filas" />
              </div>

              <div class="col-2">
                <q-input dense outlined label="Columnas" v-model="columnas" v-on:keyup="invertir"/>
              </div>
              <div class="col-2">
                <q-input dense outlined label="Capacidad" v-model="sala.capacidad" v-on:change="tablacine"/>
              </div>
              <div class="row " style="text-align:center;">
                <table class="flex-center" >
                  <th>
                    <tr>
                      <td v-for="c in colnuminv" :key="c">{{c}}</td>

                    </tr>

                  </th>
                  <tr v-for="f in parseInt(filas)" :key="f">{{letra[f-1]}}
                    <td v-for="c in parseInt(columnas)" :key="c" >{{ingresar=parseInt(ingresar) + 1 }}--{{ingresar}}

                      <div v-on:click="activar(asientos[f + c -2])" v-if="asientos[f + c -2].activo=='ACTIVO'">
                        <img src="../assets/img/1.png" alt="" style="width: 30px;height:30px;" >

                      </div>
                      <div v-on:click="activar(asientos[f + c -2])" v-else>
                        <img src="../assets/img/0.png" alt="" style="width: 30px;height:30px;" >
                      </div>
                    </td>
                  </tr>
                </table>

              </div>


              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="salaUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Distribuidor</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="salaUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="sala2.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Direccion" v-model="sala2.dir" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Localidad" v-model="sala2.loc" />
              </div>

              <div class="col-3">
                <q-input dense outlined label="NIT" v-model="sala2.nit" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Telefono" v-model="sala2.tel" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Email" v-model="sala2.email" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Responsable" v-model="sala2.responsable" />
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
import VueTableDynamic from 'vue-table-dynamic';

export default {
  name: `Salas`,
  data() {
    return {
      loading: false,
      salaFilter:'',
      filas:0,
      columnas:0,
      colnuminv:[],
      ingresar:0,
      sala:{
      },
      sala2:{},
      salaDialog:false,
      salaUpdateDialog:false,
      store: globalStore(),
      asientos:[],
      seat:{'fila':0,'columna':0,'letra':'','activo':''},
      letra:['A','B','C','D','E','F','G','H','I','J'],
      salaColumns:[
        {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'NRO',field:'nro',name:'nro',sortable:true},
        {label:'NOMBRE',field:'nombre',name:'nombre',sortable:true},
        {label:'FILAS',field:'filas',name:'filas',sortable:true},
        {label:'COLUMNAS',field:'columnas',name:'columnas',sortable:true},
        {label:'CAPACIDAD',field:'capacidad',name:'capacidad',sortable:true},
      ],
    }
  },
  created() {
    if(!this.store.salaSingleTon) {
      this.$q.loading.show()
      this.store.salaSingleTon=true
      this.$api.get('sala').then(res=>{
        this.store.salas=res.data
        this.$q.loading.hide()
      })
    }
  },
  methods: {
    cargarimagen(pp){
      if(pp.activo=='ACTIVO')
        return '../assets/img/1.png'
      else
        return '../assets/img/0.png'

    },
    activar(butaca){
      this.asientos.forEach(function( m ) {
          if(m.fila==butaca.fila && m.columna==butaca.columna){

            if(m.activo=='ACTIVO')
              m.activo='INACTIVO'
            else
              m.activo='ACTIVO'

          }

      })
    },
    invertir(){
      this.colnuminv=[]
      for(let i=parseInt(this.columnas);i>=1;i--){
        console.log(i)

        this.colnuminv.push(i)
      }
      console.log(this.columnas)
      this.tablacine()
    },
    tablacine(){
        this.asientos=[]
       this.ingresar=0
        let head=[]
        if(this.filas>0 && this.columnas>0){
          for (let f=1;f<=this.filas;f++){
            for(let i=this.columnas;i>=1;i--){
              this.asientos.push({'fila':parseInt(f),'columna':parseInt(i),'letra':this.letra[f-1],'activo':'ACTIVO'})
            }
          }
          console.log(this.asientos)
        }
    },
    salaCreate(){
      this.loading=true
      this.$api.post('sala',this.sala).then(res=>{
        this.loading=false
        this.store.salas.push(res.data)
        this.salaDialog=false
      })
    },
    salaUpdate(){
      this.$q.loading.show()
      this.$api.put('sala/'+this.sala2.id,this.sala2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.sala2={}
        let index = this.store.salas.findIndex(m => m.id == res.data.id);
        this.store.salas[index]=res.data
        this.salaUpdateDialog=false
      })
    },
    salaDelete(id,pageIndex){
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
        this.$api.delete('sala/'+id).then(res=>{
          this.store.salas.splice(pageIndex,1)
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
