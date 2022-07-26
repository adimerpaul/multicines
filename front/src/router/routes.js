import IndexPage from "pages/IndexPage";
import Peliculas from "pages/Peliculas";
import Distribuidores from "pages/Distribuidores";

const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: IndexPage },
      { path: 'peliculas', component: Peliculas },
      { path: 'distribuidores', component: Distribuidores },
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
