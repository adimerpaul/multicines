import IndexPage from "pages/IndexPage";
import Peliculas from "pages/Peliculas";
import Distribuidores from "pages/Distribuidores";
import Cuis from "pages/Cuis";
import Sincronizacion from "pages/Sincronizacion";
import Salas from "pages/Salas";
import Tarifas from "pages/Tarifas";
import Cufd from "pages/Cufd";
import Programa from "pages/Programa";
import Sale from "pages/Sale";
import Rubro from "pages/Rubro";
import Productos from "pages/Productos";
import Candy from "pages/Candy";
import ListaVenta from "pages/ListaVenta";
import ListaVentaCandy from "pages/ListaVentaCandy";
import EventoSignificativo from "pages/EventoSignificativo";
import Rental from "pages/Rental";
import Prevalorada from "pages/Prevalorada";
import Cliente from "pages/Cliente";
import CajaBoleteria from "pages/CajaBoleteria";
import CajaCandy from "pages/CajaCandy";
import Login from "pages/Login";
import Cortesia from "pages/Cortesia";
import User from "pages/User";


const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: IndexPage,meta: { requiresAuth: true } },
      { path: 'peliculas', component: Peliculas ,meta: { requiresAuth: true } },
      { path: 'distribuidores', component: Distribuidores ,meta: { requiresAuth: true } },
      { path: 'cuis', component: Cuis ,meta: { requiresAuth: true } },
      { path: 'cufd', component: Cufd ,meta: { requiresAuth: true } },
      { path: 'sincronizacion', component: Sincronizacion ,meta: { requiresAuth: true } },
      { path: 'salas', component: Salas ,meta: { requiresAuth: true } },
      { path: 'tarifas', component: Tarifas ,meta: { requiresAuth: true } },
      { path: 'sale', component: Sale ,meta: { requiresAuth: true } },
      { path: 'programa', component: Programa ,meta: { requiresAuth: true } },
      { path: 'rubro', component: Rubro ,meta: { requiresAuth: true } },
      { path: 'productos', component: Productos ,meta: { requiresAuth: true } },
      { path: 'candy', component: Candy ,meta: { requiresAuth: true } },
      { path: 'listaVenta', component: ListaVenta ,meta: { requiresAuth: true } },
      { path: 'listaVentaCandy', component: ListaVentaCandy ,meta: { requiresAuth: true } },
      { path: 'eventoSignificativo', component: EventoSignificativo ,meta: { requiresAuth: true } },
      { path: 'rental', component: Rental ,meta: { requiresAuth: true } },
      { path: 'prevalorada', component: Prevalorada ,meta: { requiresAuth: true } },
      { path: 'cliente', component: Cliente ,meta: { requiresAuth: true } },
      { path: 'cajaboleteria', component: CajaBoleteria ,meta: { requiresAuth: true } },
      { path: 'cajacandy', component: CajaCandy ,meta: { requiresAuth: true } },
      { path: 'cortesia', component: Cortesia ,meta: { requiresAuth: true } },
      { path: 'usuarios', component: User ,meta: { requiresAuth: true } },
    ]
  },
  {
    path: '/login',
    component: Login,
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
