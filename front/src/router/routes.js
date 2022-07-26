import IndexPage from "pages/IndexPage";
import Peliculas from "pages/Peliculas";

const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: IndexPage },
      { path: 'peliculas', component: Peliculas },
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
