import moment from 'moment'

// Funciones comunes para mostrar vencimientos (CUIS, certificado digital, token SIAT)

export function diasRestantes(fin) {
  return moment(fin).diff(moment(), 'days')
}

export function textoRestante(fin) {
  if (!fin) return 'Sin fecha'
  const f = moment(fin)
  const ahora = moment()
  if (f.isBefore(ahora)) {
    const d = ahora.diff(f, 'days')
    return d === 0 ? 'Venció hoy' : `Vencido hace ${d} día${d === 1 ? '' : 's'}`
  }
  const d = f.diff(ahora, 'days')
  if (d === 0) {
    const h = f.diff(ahora, 'hours')
    return `Vence en ${h} hora${h === 1 ? '' : 's'}`
  }
  return `${d} día${d === 1 ? '' : 's'}`
}

export function porcentajeRestante(inicio, fin) {
  const f = moment(fin)
  const total = f.diff(moment(inicio))
  if (!total || total <= 0) return 0
  return Math.max(0, Math.min(100, Math.round((f.diff(moment()) / total) * 100)))
}

// aviso: días para "Por vencer" (naranja); urgente: días para "Vence pronto" (rojo)
export function estado(fin, aviso = 30, urgente = 7) {
  if (!fin) return { label: 'Sin fecha', color: 'grey-7', icon: 'help' }
  const d = moment(fin).diff(moment(), 'days', true)
  if (d < 0) return { label: 'Vencido', color: 'grey-7', icon: 'block' }
  if (d <= urgente) return { label: 'Vence pronto', color: 'negative', icon: 'warning' }
  if (d <= aviso) return { label: 'Por vencer', color: 'orange', icon: 'schedule' }
  return { label: 'Vigente', color: 'positive', icon: 'check_circle' }
}

export function formatFecha(f, conHora = false) {
  return f ? moment(f).format(conHora ? 'DD/MM/YYYY HH:mm' : 'DD/MM/YYYY') : '-'
}

// Umbrales de aviso de cada elemento
export const UMBRALES = {
  cui: { aviso: 30, urgente: 7 },
  certificado: { aviso: 60, urgente: 15 },
  token: { aviso: 60, urgente: 15 },
}
