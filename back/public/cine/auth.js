/* MultiCines Plaza - login con Google via Firebase.
   Firebase solo valida la identidad: se obtiene el ID token, el backend lo
   verifica y crea su propia sesion (datos en web_clientes). Luego se cierra la
   sesion de Firebase en el navegador; el resto de la web ya no lo usa. */
import { initializeApp } from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js';
import {
  getAuth, GoogleAuthProvider, signInWithPopup, signOut, browserSessionPersistence, setPersistence
} from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js';

const Cine = window.Cine;
const cfg = Cine && Cine.auth && Cine.auth.firebase;

if (Cine && cfg && cfg.apiKey) {
  const app = initializeApp(cfg);
  const auth = getAuth(app);
  const provider = new GoogleAuthProvider();
  provider.setCustomParameters({ prompt: 'select_account' });
  let ocupado = false;

  async function ingresar(boton) {
    if (ocupado) return;
    ocupado = true;
    boton.disabled = true;
    try {
      await setPersistence(auth, browserSessionPersistence);
      const res = await signInWithPopup(auth, provider);
      const idToken = await res.user.getIdToken();
      const data = await Cine.api(Cine.auth.loginUrl, { id_token: idToken });
      Cine.loginOk(data.cliente);
    } catch (e) {
      const code = e && e.code ? String(e.code) : '';
      if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') return;
      let msg = e && e.message ? e.message : 'No se pudo iniciar sesión';
      if (code === 'auth/popup-blocked') msg = 'Tu navegador bloqueó la ventana de Google. Permite las ventanas emergentes e inténtalo otra vez.';
      else if (code === 'auth/unauthorized-domain') msg = 'Este dominio no está autorizado en Firebase.';
      else if (code === 'auth/network-request-failed') msg = 'Sin conexión. Revisa tu internet.';
      Cine.loginError(msg);
    } finally {
      // La sesion buena es la de Laravel; Firebase ya no se necesita
      signOut(auth).catch(() => {});
      ocupado = false;
      boton.disabled = false;
    }
  }

  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-login-google]');
    if (b) ingresar(b);
  });
}
