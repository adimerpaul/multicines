import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import api from "./plugins/axios.js";
import moment from "moment";

const app = createApp(App)

app.config.globalProperties.$axios = api
app.config.globalProperties.$url = import.meta.env.VITE_API_URL
app.config.globalProperties.$filters = {
    textCapitalize (value) {
        if (!value) return ''
        value = value.toLowerCase()
        return value.charAt(0).toUpperCase() + value.slice(1)
    },
    dateFormat (value) {
        if (!value) return ''
        // 06 Dec, 2020
        const meses = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        const fecha = moment(value)
        const dia = fecha.date()
        const mes = meses[fecha.month()]
        const anio = fecha.year()
        return `${dia} ${mes}, ${anio}`
    },
    timeFormat(duracionInt) {
        if (!duracionInt) return ''
        // 2 hrs 50 mins
        const horas = Math.floor(duracionInt / 60)
        const minutos = duracionInt % 60
        let resultado = ''
        if (horas > 0) {
            resultado += `${horas} hrs `
        }
        if (minutos > 0) {
            resultado += `${minutos} mins`
        }
        return resultado.trim()
    }
}

app.use(router)
app.mount('#app')
