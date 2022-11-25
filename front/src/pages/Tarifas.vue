<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <q-table dense title="Gestionar Tarifas" :rows-per-page-options="[100,50,20,0]" :rows="store.prices" :columns="priceColumns" :filter="priceFilter">
          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="priceDialog=true"/>
            <q-input outlined dense debounce="300" v-model="priceFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="priceUpdateDialog=true;price2=props.row;this.store.price=props.row.price">
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="priceDelete(props.row.id,props.pageIndex)">
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
    <q-dialog v-model="priceDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Tarifa</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="priceCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Serie" v-model="price.serie" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Precio" v-model="price.precio" type="number" step="0.01"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="price.descripcion" />
              </div>
              <div class="col-3">
                <q-toggle
                false-value="NO"
                :label="'Promo '+ price.promo"
                true-value="SI"
                color="green"
                v-model="price.promo"
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

    <q-dialog v-model="priceUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Tarifa</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="priceUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="Serie" v-model="price2.serie" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="Precio" v-model="price2.precio" type="number" step="0.01"/>
              </div>
              <div class="col-3">
                <q-input dense outlined label="Descripcion" v-model="price2.descripcion" />
              </div>
              <div class="col-3">
                <q-toggle
                  false-value="NO"
                  :label="'Promo '+ price2.promo"
                  true-value="SI"
                  color="green"
                  v-model="price2.promo"
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
  name: `Tarifas`,
  data() {
    return {
      loading: false,
      priceFilter:'',
      price:{'promo':'NO'
      },
      price2:{},
      priceDialog:false,
      priceUpdateDialog:false,
      store: globalStore(),
      priceColumns:[
        {label:'OPCIONES',field:'opciones',name:'opciones'},
        {label:'SERIE',field:'serie',name:'serie',sortable:true},
        {label:'PRECIO',field:'precio',name:'precio',sortable:true},
        {label:'PROMO',field:'promo',name:'promo',sortable:true},
        {label:'DESCRIPCION',field:'descripcion',name:'descripcion'},
      ]
    }
  },
  created() {
    if(!this.store.priceSingleTon) {
      this.$q.loading.show()
      this.store.priceSingleTon=true
      this.$api.get('price').then(res=>{
        res.data.forEach(p=>{
          p.label=p.serie+' '+p.precio+' Bs.'
        })
        this.store.prices2=res.data
        this.store.prices3=res.data
        this.store.prices=res.data
        this.$q.loading.hide()
      })

    }
  },
  methods: {
    priceCreate(){
      this.loading=true
      this.$api.post('price',this.price).then(res=>{
        this.loading=false
        this.store.prices.push(res.data)
        this.priceDialog=false
      })
    },
    priceUpdate(){
      this.$q.loading.show()
      this.$api.put('price/'+this.price2.id,this.price2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.price2={}
        let index = this.store.prices.findIndex(m => m.id == res.data.id);
        this.store.prices[index]=res.data
        this.priceUpdateDialog=false
      })
    },
    priceDelete(id,pageIndex){
      this.$q.dialog({
        title: 'Eliminar Tarifa',
        message: '¿Esta seguro de eliminar la Tarifa?',
        ok: {
          push: true
        },
        cancel: {
          push: true,
          color: 'negative'
        },
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('price/'+id).then(res=>{
          this.store.prices.splice(pageIndex,1)
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Tarifa eliminada',
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
