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
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
        const fecha = moment(value)
        const dia = fecha.date()
        const mes = meses[fecha.month()]
        const anio = fecha.year()
        return `${dia} ${mes}, ${anio}`
    },
    formatFecha (value) {
        if (!value) return ''
        // 06/12/2020
        const fecha = moment(value)
        const dia = fecha.date()
        const mes = fecha.month() + 1
        const anio = fecha.year()
        return `${dia}/${mes}/${anio}`
    },
    formatHora (value) {
        if (!value) return ''
        // 12:00 am
        const fecha = moment(value)
        const hora = fecha.format('hh:mm')
        const ampm = fecha.format('a')
        return `${hora} ${ampm}`
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
    },
    timeFormatLarge(datetime) {
        if (!datetime) return ''
        // Lunes, 24 de noviembre 2025 a las 12:00 am
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        const fecha = moment(datetime)
        const dia = dias[fecha.day()]
        const diaDelMes = fecha.date()
        const mes = meses[fecha.month()]
        const anio = fecha.year()
        const hora = fecha.format('HH:mm')
        const ampm = fecha.format('a')
        return `${dia}, ${diaDelMes} de ${mes} ${anio} a las ${hora} ${ampm}`

    }
}

app.use(router)
app.mount('#app')
