# ADR 0010: Formulario de contacto

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

La página de contacto (`page-contacto`) ofrece, según `docs/03-wordpress-content-model`, información de contacto y **opcionalmente** un formulario (o un enlace `mailto`). El correo público de la revista es `revista.cenfiss@gmail.com`.

Un formulario de contacto es funcionalidad estándar y bien resuelta; construirlo a mano sería desproporcionado. ADR 0006 (política de plugins) permite un plugin de terceros si es **gratuito, muy usado, activamente mantenido y acotado**. El propietario prefiere usar un plugin robusto y popular ya existente.

Un formulario recoge **datos personales** (nombre, correo, mensaje), lo que implica consideraciones de privacidad (Ley venezolana 1581/2012 y RGPD para visitantes de la UE) y de coherencia con la dirección **sin cookies** del sitio (a fijar en el backlog **D10**). El proyecto hermano registró que el contacto sigue recogiendo datos personales aunque no haya analítica (su hallazgo PRIV-001b).

## Decisión

### 1. Plugin: Contact Form 7

Se adopta **Contact Form 7** para el formulario de contacto. Cumple ADR 0006:

- **Gratuito** y GPL, sin funciones esenciales tras muro de pago.
- **Muy usado** (~5M+ instalaciones activas; de los plugins más instalados).
- **Activamente mantenido**, con largo historial.
- **Acotado**: hace formularios, nada más.
- Por defecto **envía por correo** y **no almacena** en la base de datos, lo que reduce la huella de datos personales.

Se registra en la lista de plugins de terceros a instalar (ADR 0006, ADR 0009 §3).

### 2. Antispam: honeypot; nunca Google reCAPTCHA

- Se usa **honeypot** (campo oculto que rellenan los bots): sin cookies, sin terceros, invisible para el usuario.
- **No** se usa **Google reCAPTCHA**: introduce cookies y rastreo de terceros, incompatible con la dirección sin cookies (D10).
- Alternativa aceptable si el honeypot resulta insuficiente: **Cloudflare Turnstile** (captcha respetuoso con la privacidad, sin cookies de rastreo).

### 3. Datos: solo correo, sin almacenamiento en BD

- Los envíos se **envían por correo** a `revista.cenfiss@gmail.com`; **no** se almacenan en la base de datos (no se instala Flamingo ni similar).
- Motivo: mínima huella de datos personales; menos que proteger y respaldar.
- Si en el futuro se necesita almacenar envíos, se reabre esta decisión con su nota de privacidad.

### 4. Privacidad

- El formulario recoge datos personales; requiere el **aviso de privacidad** que se decida en **D10** (enlace/checkbox de consentimiento según proceda).
- Marco: Ley 1581/2012 (Venezuela) y RGPD para visitantes de la UE. La conclusión jurídica queda fuera del alcance técnico; aquí se fija minimizar la recogida y enlazar la política.

### 5. Alcance

Este ADR cubre el **formulario de contacto público**, distinto del **sistema de envíos de autores** (manuscritos), que es un subsistema propio y aplazado (ADR 0005) — no se resuelve con Contact Form 7.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Solo enlace `mailto` | Más simple y sin datos en servidor, pero peor UX y el correo queda expuesto a scraping; se prefiere formulario con huella de datos mínima (solo correo). |
| WPForms Lite | Popular pero freemium con funciones esenciales limitadas; CF7 es plenamente gratuito. |
| Construir el formulario a mano | Desproporcionado; ADR 0006 permite el plugin para funcionalidad estándar bien resuelta. |
| Google reCAPTCHA | Cookies y rastreo de terceros; incompatible con la política sin cookies (D10). |
| Almacenar envíos en la BD (Flamingo) | Aumenta la huella de datos personales a proteger; innecesario para un formulario de contacto. |

## Consecuencias

**Beneficios:**

- Solución estándar, robusta y mantenida, sin coste ni desarrollo propio.
- Huella de datos mínima: solo correo, sin almacenamiento, sin cookies de terceros.
- Coherente con la dirección sin cookies (pendiente de ratificar en D10).

**Riesgos / costes:**

- Una dependencia de terceros a mantener actualizada (mitigado por el mantenimiento activo de CF7).
- El honeypot es más débil que un captcha; aceptable para el tráfico bajo de la revista; Turnstile como respaldo.
- El tratamiento de datos personales exige el aviso de privacidad de D10.

**Trabajo futuro:**

- Instalar y configurar Contact Form 7 en WordPress (envío a `revista.cenfiss@gmail.com`, honeypot, sin almacenamiento en BD).
- Añadir el aviso/consentimiento de privacidad según D10.
- Incluir CF7 en la lista documentada de plugins de terceros (ADR 0006 / ADR 0009).

## Referencias

- ADR 0006 (política de plugins; CF7 cumple el criterio)
- ADR 0005 (sistema de envíos de autores, separado y aplazado)
- ADR 0009 §3 (lista de plugins de terceros para el despliegue)
- `docs/03-wordpress-content-model` (§ contacto)
- Backlog **D10** (analítica y privacidad; aviso de privacidad, postura sin cookies)
