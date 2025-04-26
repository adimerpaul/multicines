// src/router.js
import { createRouter, createWebHistory } from 'vue-router'
import Home from './views/Home.vue'

const routes = [
    { path: '/', name: 'Home', component: Home },
    { path: '/pelicula/:id', name: 'Pelicula', component: () => import('./views/Pelicula.vue') },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
