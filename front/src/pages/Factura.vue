<template>
<q-page>
  <div class="q-pa-md">
    <div class="row">
      <div class="col-2">
        <q-select
          outlined
          dense
          v-model="mes"
          :options="meses"
          label="Mes"
          class="col-12 col-sm-6 col-md-4"
          map-options
          emit-value
          hint="Seleccione el mes de la factura"
        />
      </div>
      <div class="col-2">
        <q-select
          outlined
          dense
          v-model="anio"
          :options="anios"
          label="Año"
          class="col-12 col-sm-6 col-md-4"
          map-options
          emit-value
          hint="Seleccione el año de la factura"
        />
      </div>
      <div class="col-2 flex flex-center">
        <q-btn
          :loading="loading"
          color="primary"
          label="Buscar"
          icon="search"
          no-caps
          class="col-12 col-sm-6 col-md-4"
        />
      </div>
      <div class="col-2">
        <q-file outlined dense v-model="archivo" label="Archivo" class="col-12 col-sm-6 col-md-4" hint="Seleccione el archivo de la factura">
          <template v-slot:prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </div>
      <div class="col-2 flex flex-center">
        <q-btn
          :loading="loading"
          color="primary"
          label="Subir"
          icon="cloud_upload"
          no-caps
          @click="subirFactura"
          class="col-12 col-sm-6 col-md-4"
        />
        {{tiempo}}
      </div>
      <div class="col-12">
        <q-table title="Facturas" :loading="loading" :rows="facturas"
                 :rows-per-page-options="[0]">
          <template v-slot:top-right>
            <q-input
              v-model="filter"
              filled
              dense
              debounce="300"
              hint="Buscar"
              class="q-mb-md"
            />
          </template>
        </q-table>
      </div>
    </div>
  </div>
</q-page>
</template>

<script>
export default {
  name: "FacturaPage",
  data() {
    return {
      filter:'',
      anios:[],
      facturas: [],
      factura: {},
      tiempo: '00:00',
      archivo: null,
      anio: new Date().getFullYear(),
      mes: new Date().getMonth(),
      loading: false,
      meses: [
        { label: "Enero", value: 1 },
        { label: "Febrero", value: 2 },
        { label: "Marzo", value: 3 },
        { label: "Abril", value: 4 },
        { label: "Mayo", value: 5 },
        { label: "Junio", value: 6 },
        { label: "Julio", value: 7 },
        { label: "Agosto", value: 8 },
        { label: "Septiembre", value: 9 },
        { label: "Octubre", value: 10 },
        { label: "Noviembre", value: 11 },
        { label: "Diciembre", value: 12 },
      ],
    };
  },
  mounted() {
    this.getYearMonthFacturas();
    for (let i = 2019; i <= this.anio; i++) {
      this.anios.push({ label: i, value: i });
    }
  },
  methods: {
    getYearMonthFacturas() {
      this.loading = true;
      this.$api.post(`getYearMonthFacturas`,{
        anio: this.anio,
        mes: this.mes,
      }).then((res) => {
        this.facturas = res.data;
      }).catch(err => {
        console.log(err);
        this.$q.notify({
          color: "negative",
          message: err.response.data.message,
          icon: "report_problem",
          position: "top",
        });
      }).finally(() => {
        this.loading = false;
      });
    },
    subirFactura() {
      if (!this.archivo) {
        this.$q.notify({
          color: "negative",
          message: "Debe seleccionar un archivo",
          icon: "report_problem",
          position: "top",
        });
        return;
      }
      this.tiempo = '00:00'
      let time=setInterval(() => {
        let min=parseInt(this.tiempo.split(':')[0])
        let seg=parseInt(this.tiempo.split(':')[1])
        seg++
        if(seg==60){
          seg=0
          min++
        }
        this.tiempo=`${min<10?'0'+min:min}:${seg<10?'0'+seg:seg}`
      }, 1000);
      const formData = new FormData();
      formData.append("archivo", this.archivo);
      this.loading = true;
      this.$api.post("import", formData).then((res) => {
        console.log(res.data);
        clearInterval(time);
      }).catch(err => {
        console.log(err);
        this.$q.notify({
          color: "negative",
          message: err.response.data.message,
          icon: "report_problem",
          position: "top",
        });
      }).finally(() => {
        clearInterval(time);
        this.loading = false;
      });
    },
  },
}
</script>

<style scoped>

</style>
