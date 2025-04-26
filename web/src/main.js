import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import api from "./plugins/axios.js";

const app = createApp(App)

app.config.globalProperties.$axios = api
app.config.globalProperties.$url = import.meta.env.VITE_API_URL
app.config.globalProperties.$filters = {
    textCapitalize (value) {
        if (!value) return ''
        value = value.toLowerCase()
        return value.charAt(0).toUpperCase() + value.slice(1)
    }
}

app.use(router)
app.mount('#app')
