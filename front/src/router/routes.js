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


const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: IndexPage,meta: { requiresAuth: true } },
      { path: 'peliculas', component: Peliculas },
      { path: 'distribuidores', component: Distribuidores },
      { path: 'cuis', component: Cuis },
      { path: 'cufd', component: Cufd },
      { path: 'sincronizacion', component: Sincronizacion },
      { path: 'salas', component: Salas },
      { path: 'tarifas', component: Tarifas },
      { path: 'sale', component: Sale },
      { path: 'programa', component: Programa },
      { path: 'rubro', component: Rubro },
      { path: 'productos', component: Productos },
      { path: 'candy', component: Candy },
      { path: 'listaVenta', component: ListaVenta },
      { path: 'listaVentaCandy', component: ListaVentaCandy },
      { path: 'eventoSignificativo', component: EventoSignificativo },
      { path: 'rental', component: Rental },
      { path: 'prevalorada', component: Prevalorada },
      { path: 'cliente', component: Cliente },
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
