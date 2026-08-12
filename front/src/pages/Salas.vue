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
              @click="salaDialog=true; columnas=1; filas=1; tablacine()"/>
            <q-input outlined dense debounce="300" v-model="salaFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="salaUpdateDialog=true;sala2=props.row;columnas=props.row.columnas;filas=props.row.filas;asientos=props.row.seats;">
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
            </q-td>
          </template>
        </q-table>

      </div>
    </div>

    <q-dialog v-model="salaDialog" full-width>
      <q-card>
        <q-card-section class="row q-pb-none">
          <div class="text-h6">
            Registrar Sala
          </div>

          <q-space />
          <div class="text-h6 text-grey" >Capacidad: {{total}}</div>
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="salaCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Numero Sala (Opcional)" v-model="sala.nro" type="number"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Nombre" v-model="sala.nombre" required/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Filas" type="number" v-model="filas" @change="tablacine" />
              </div>

              <div class="col-3">
                <q-input dense outlined label="Columnas" type="number" v-model="columnas" @change="tablacine"/>
              </div>
              <div class="col-12">
                <table>
                  <thead>
                    <tr>
                      <th></th>
                      <th v-for="(c,i) in parseInt(columnas) " :key="i">{{columnas-c+1}}</th>
                    </tr>
                  </thead>
                  <tbody>
                  <tr v-for="(f,i) in parseInt(filas)" :key="i">
                    <th>{{letra[i+1]}}</th>
                    <td v-for="(c,j) in parseInt(columnas)" @click="cambio(f,c)" :key="j" class="text-center tdx">
                      <q-btn round icon="o_chair" :color="asientos[columnas*(f-1)+(c-1)]['activo']=='ACTIVO'?'green-6':'grey-9'" >
                        <q-badge color="primary" floating>{{letra[i+1]+'-'+(columnas-c+1).toString()}}</q-badge>
                      </q-btn>
                    </td>
                  </tr>
                  </tbody>
                </table>
<!--                <div class="row " style="text-align:center;">-->
                  <!--                <table class="flex-center" >-->
                  <!--                  <th>-->
                  <!--                    <tr>-->
                  <!--                      <td v-for="c in colnuminv" :key="c">{{c}}</td>-->

                  <!--                    </tr>-->

                  <!--                  </th>-->
                  <!--                  <tr v-for="f in parseInt(filas)" :key="f">{{letra[f-1]}}-->
                  <!--                    <td v-for="c in parseInt(columnas)" :key="c" >-->

                  <!--                      <div v-on:click="activar(asientos[f + c -2])" v-if="asientos[f + c -2].activo=='ACTIVO'">-->
                  <!--                        <img src="../assets/img/1.png" alt="" style="width: 30px;height:30px;" >-->

                  <!--                      </div>-->
                  <!--                      <div v-on:click="activar(asientos[f + c -2])" v-else>-->
                  <!--                        <img src="../assets/img/0.png" alt="" style="width: 30px;height:30px;" >-->
                  <!--                      </div>-->
                  <!--                    </td>-->
                  <!--                  </tr>-->
                  <!--                </table>-->

<!--                </div>-->
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
          <div class="text-h6">Modificar Sala</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="salaUpdate">
              <div class="row">
                <div class="col-3">
                  <q-input dense outlined label="Numero Sala (Opcional)" v-model="sala2.nro" type="number" />
                </div>
                <div class="col-3">
                  <q-input dense outlined label="Nombre" v-model="sala2.nombre" />
                </div>
                <div class="col-3">
                  <q-input dense outlined label="Filas" type="number" v-model="filas" readonly @change="tablacine" />
                </div>

                <div class="col-3">
                  <q-input dense outlined label="Columnas" type="number" v-model="columnas" readonlysa @change="tablacine"/>
                </div>
                <div class="col-12">
                  <table>
                    <thead>
                    <tr>
                      <th></th>
                      <th v-for="(c,i) in parseInt(columnas) " :key="i">{{columnas-c+1}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(f,i) in parseInt(filas)" :key="i">
                      <th>{{letra[i+1]}}</th>
                      <td v-for="(c,j) in parseInt(columnas)" @click="cambio(f,c)" :key="j" class="text-center tdx">
                        <q-btn round icon="o_chair" :color="valorindice(c,f)=='ACTIVO'?'green-6':'grey-9'" >
                          <q-badge color="primary" floating>{{letra[i+1]+'-'+(columnas-c+1).toString()}}</q-badge>
                        </q-btn>
                      </td>
                    </tr>
                    </tbody>
                  </table>
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
  name: `Salas`,
  data() {
    return {
      contador:0,
      loading: false,
      salaFilter:'',
      filas:5,
      columnas:5,
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
      letra:['','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB'],
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
        res.data.forEach(p=>{
          p.label=p.nombre
        })
        this.store.salas2=res.data
        this.store.salas3=res.data
        this.store.salas=res.data
        this.$q.loading.hide()
      })
    }
    this.tablacine()
  },
  methods: {
    valorindice(c,f){
      return this.asientos[(this.columnas*(f-1))+(c-1)].activo
    },
    cambio(f,c){
      let index=this.columnas*(f-1)+(c-1)
      this.asientos[index].activo=this.asientos[index].activo=='ACTIVO'?'INACTIVO':'ACTIVO'
    },
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
      if (this.filas=='' || this.filas==undefined) {
        this.filas=1
      }
      if (this.columnas=='' || this.columnas==undefined) {
        this.columnas=1
      }
      this.asientos=[]
      if(this.filas>0 && this.columnas>0){
        for (let f=1;f<=this.filas;f++){
          for(let i=this.columnas;i>=1;i--){
            this.asientos.push({'fila':parseInt(f),'columna':parseInt(i),'letra':this.letra[f],'activo':'ACTIVO'})
          }
        }
      }
    },
    salaCreate(){
      if(this.sala.nombre=='' || this.sala.nombre==undefined){
       return false;
      }
      if(this.filas=='' || this.filas==undefined || this.filas==0){
        return false;
      }
      if(this.columnas=='' || this.columnas==undefined || this.columnas==0){
        return false;
      }
      this.sala.filas=parseInt(this.filas)
      this.sala.columnas=parseInt(this.columnas)
      this.sala.capacidad=this.total
      this.sala.seats=this.asientos
      this.loading=true
      this.$api.post('sala',this.sala).then(res=>{
        this.loading=false
        this.store.salas.push(res.data)
        this.salaDialog=false
      }).catch(error=>{
        this.$q.notify({
          color:'negative',
          message:error.response.data.message,
          position:'top',
          icon:'error',
          timeout:5000
        })
        this.loading=false
      })
    },
    salaUpdate(){
      this.sala2.seats=this.asientos
      this.$q.loading.show()
      this.$api.put('sala/'+this.sala2.id,this.sala2).then(res=>{
        this.$q.loading.hide()
        this.sala2={}
        let index = this.store.salas.findIndex(m => m.id == res.data.id);
        this.store.salas[index]=res.data
        this.salaUpdateDialog=false
      })
    },
    salaDelete(id,pageIndex){
      this.$q.dialog({
        title: 'Eliminar Sala',
        message: '¿Esta seguro de eliminar la sala?',
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
            message: 'Sala eliminada',
            color: 'positive',
            icon: 'done'
          })
        })
      })
    }
  },
  computed:{
    total(){
      let sum=0
      this.asientos.forEach(function(r){
        if(r.activo=='ACTIVO') sum++
      })
      return sum
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
