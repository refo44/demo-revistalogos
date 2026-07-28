# ADR 0011: Analítica y privacidad

## Estado

Aceptada

## Fecha

2026-07-28

## Contexto

El backlog **D10** agrupaba tres decisiones acopladas: si se instala analítica y cuál; si el sitio usa cookies —de lo que depende la necesidad de banner de consentimiento—; y dónde vive el aviso de privacidad.

### Estado verificado de la maqueta (2026-07-28)

- **Sin analítica:** no hay `gtag`, Google Tag Manager, Matomo ni equivalente en ningún HTML.
- **Sin cookies:** el sitio no escribe ninguna.
- **Sin peticiones a terceros:** los únicos hosts externos presentes en el HTML son enlaces `<a>` y URLs de metadatos (`schema.org`, `doi.org`); no se carga ninguna fuente, script, hoja de estilo ni contenido incrustado externo. Coherente con `docs/15-assets-strategy` («sin CDN de fuentes ni peticiones externas»).
- **Formulario de contacto:** usa `action="mailto:"`; abre el cliente de correo del visitante. **No envía nada a un servidor propio.**
- **Búsqueda:** formulario sin `action`; se resuelve en el navegador.
- **Lo que hoy se enlaza como «Privacidad»** en el footer es `page-politicas.html#politica-privacidad`, que trata **confidencialidad editorial** (autoría, árbitros), no el tratamiento de datos del visitante web.
- `docs/05-information-architecture-navigation` §2 ya preveía una «Página de privacidad» que no existía.

ADR 0010 dejó pendiente el aviso de privacidad (§4) y ya rechazó Google reCAPTCHA por sus cookies y rastreo de terceros.

### Ámbito jurídico (dato aportado por el propietario, 2026-07-28)

La revista **es venezolana y está registrada en Venezuela**, pero su **público objetivo es hispanohablante: España y Latinoamérica**.

Esto activa el **art. 3(2)(a) del RGPD** —ofrecer servicios a interesados en la Unión—, vía **independiente del rastreo**; que el acceso sea gratuito no la desactiva (Considerando 23). Que el sitio esté en español no basta por sí solo como indicio (Directrices 3/2018 del CEPD: la lengua propia del país del responsable no es criterio suficiente), pero sí lo son elementos ya publicados: `page-acerca` nombra explícitamente **España (Madrid)** entre los países donde está su comunidad y declara sus páginas abiertas a autores «nacionales e internacionales».

**Corrección de una cita:** ADR 0010 se refiere a una «Ley venezolana 1581/2012». La **Ley 1581 de 2012 es colombiana** (Ley General de Protección de Datos Personales). Este ADR fija el marco correcto y sustituye esa referencia; 0010 no se edita (regla de inmutabilidad).

## Decisión

### 1. Marco de diseño: el estándar más estricto

Con público en España y en múltiples países de Latinoamérica, auditar cada régimen nacional (Colombia 1581/2012, México LFPDPPP, Argentina 25.326, Brasil LGPD, Chile…) **antes** de publicar la v1 es inviable. Se adopta el **RGPD como línea base de diseño por ser el más exigente**; los regímenes latinoamericanos, de raíz común, quedan cubiertos en la práctica mientras tanto.

**Esto es un punto de partida, no un sustituto del cumplimiento por país.** En fases posteriores se realizarán **auditorías incrementales, jurisdicción por jurisdicción**, para confirmar el cumplimiento de la ley de cada país del público objetivo y corregir lo que la línea base no cubra. Se prioriza por volumen real de lectores y autores (una vez haya métricas o registros de envíos que lo indiquen), empezando por Venezuela —sede— y España —única jurisdicción UE con presencia declarada en `page-acerca`—. Cada auditoría que obligue a cambiar la postura técnica se registra en un ADR propio.

El anclaje de sede sigue siendo venezolano: *habeas data* (art. 28 CRBV) y normativa sectorial.

Este ADR fija la **postura técnica**. La validación jurídica del texto corresponde al propietario y a su asesoría legal, como ya asumió ADR 0010 §4.

### 2. Analítica: propia y sin cookies desde la v1. GA4 después, con asesoría legal

Se adoptan **dos pasos, en este orden**:

#### 2.1. Ahora: analítica propia, autoalojada y sin cookies — **WP Statistics**

La revista necesita saber qué se lee (memoria del CENFISS, solicitudes a índices), y esa necesidad no justifica esperar a GA4. Se adopta **WP Statistics**, que cumple ADR 0006:

- **Gratuito** y GPL en su versión del repositorio oficial.
- **Muy usado:** 600.000+ instalaciones activas (verificado el 2026-07-28), el mayor de los candidatos evaluados.
- **Activamente mantenido:** actualizado días antes de esta decisión y declarado compatible con la versión vigente de WordPress.
- **Necesario:** medir por artículo no se resuelve con los registros del servidor sin herramientas de análisis adicionales.
- **Autoalojado:** los datos se crean y quedan **en el servidor de la revista**; no se envían a ninguna plataforma externa.

**Requisitos vinculantes de configuración** (condición de la adopción, no preferencias):

1. **Sin cookies y sin almacenamiento en el cliente.** WP Statistics no usa cookies por defecto; se verifica en la instalación y tras cada actualización mayor. Si una versión futura cambiara ese comportamiento, se reconfigura o se sustituye el plugin: el invariante de §3 manda sobre la herramienta.
2. **Sin IP en claro.** Se emplea el hash con sal rotatoria diaria que el propio plugin aplica; no se almacenan direcciones IP legibles.
3. **Sin transferencia a terceros.** Ninguna integración externa (Search Console u otras) sin reabrir este ADR.
4. **Sin complementos de pago.** Se asume la consecuencia: según la página del plugin, el seguimiento de enlaces y descargas es un complemento de pago, de modo que **el recuento de descargas de PDF puede no estar disponible** en la versión gratuita. Si esa métrica resulta imprescindible, se resuelve con código propio en `revistalogos-core` (ADR 0005/0006), no pagando.

Se añade a la lista de plugins de terceros a instalar (ADR 0006, ADR 0009 §3), junto a Contact Form 7.

#### 2.2. Después: GA4, condicionado a asesoría legal previa

**GA4 queda planificado para una fase posterior**, no se descarta y **no se instala ahora**. Precondiciones que deberán resolverse **antes** de activarlo, registradas aquí para que la fase posterior no las redescubra:

1. **Banner de consentimiento previo**, con bloqueo de la etiqueta hasta que se consienta y registro del consentimiento. Implica un plugin adicional (evaluar contra ADR 0006).
2. **Actualización de esta política de privacidad**: cookies, Google como destinatario, transferencias internacionales.
3. **Revisión de la CSP de D12**: `default-src 'self'` sin excepciones deja de ser posible; habrá que abrir dominios de Google y probablemente relajar la política de scripts.
4. **Cuestión del art. 27 RGPD** (representante en la Unión): la excepción del art. 27.2 se apoya en que el tratamiento sea *ocasional* y de bajo riesgo. Formulario de contacto y envíos de periodicidad anual encajan; una **analítica continua sobre visitantes europeos debilita ese argumento**. A valorar con la asesoría legal, porque su coste es estructural.
5. **Nuevo ADR que sustituya a este**, conforme a la regla de inmutabilidad (`docs/adr/README`).

### 3. Cookies: cero para el visitante anónimo; sin banner en la v1

- **Invariante:** el sitio **no escribe cookies a visitantes anónimos** y **no realiza peticiones a terceros** en tiempo de ejecución. **Este invariante prevalece sobre cualquier herramienta**: si una analítica —la actual o una futura— no puede funcionar sin cookies, se sustituye la herramienta, no el invariante.
- En WordPress el invariante se sostiene porque el núcleo solo pone cookies a **usuarios autenticados** (`wordpress_logged_in_*`, `wp-settings-*`), a **comentaristas** (`comment_author_*`) y a **contenido protegido por contraseña** (`wp-postpass_*`). Las de sesión de la redacción son estrictamente necesarias y quedan exentas de consentimiento.
- **Medida de aplicación:** los **comentarios se desactivan globalmente** y los CPTs de `revistalogos-core` (ADR 0005) **no declaran `comments`** en sus `supports`.
- **No se instala banner de cookies en la v1.** Se registra explícitamente: un banner sin cookies que consentir es ruido, sugiere un rastreo que no existe y daña la sobriedad de lectura. No se añade «por si acaso».
- Cualquier incorporación futura de recursos de terceros (embeds de YouTube o Maps, fuentes desde CDN, reCAPTCHA) **rompe el invariante y obliga a reabrir este ADR**.

### 4. Aviso de privacidad: página propia y provisional

Se crea **`page-privacidad`** (`/privacidad/` en WordPress), separada de `page-politicas` §6. Reparto de responsabilidades:

| Documento | Qué cubre |
| --------- | --------- |
| `page-politicas` §6 — Privacidad y Confidencialidad | Confidencialidad **editorial**: autoría, árbitros, manuscritos |
| `page-privacidad` (nuevo) | Tratamiento de datos de los **visitantes del sitio web** |

- Ambos documentos se **enlazan mutuamente**; el enlace «Privacidad» del footer pasa a apuntar a la página nueva.
- **La versión de la v1 es provisional**, y así lo declara la propia página, con fecha de última actualización visible. Se revisa con asesoría legal.
- Contenido conforme al **art. 13 RGPD**: responsable, fines, base jurídica, destinatarios, plazos de conservación, derechos y derecho a reclamar ante la **autoridad de control del país del interesado** (p. ej. la AEPD para lectores en España).
- En WordPress se usará la **página de privacidad nativa** (Ajustes → Privacidad) y las herramientas nativas de exportación y borrado de datos personales: nativo antes que plugin (ADR 0006).

### 5. Disparadores de revisión obligatoria

Esta política se revisa cuando:

- **se active la analítica propia** al pasar de la maqueta estática a WordPress: la nota de la página que advierte de que aún no está en funcionamiento debe retirarse ese mismo día;
- se active GA4, se cambie de herramienta de analítica o se incorpore cualquier cookie;
- el formulario pase de `mailto:` a **Contact Form 7** (ADR 0010): entonces sí habrá tratamiento en servidor propio;
- arranque el **sistema de envíos de autores** (ADR 0005);
- se incorpore cualquier recurso de terceros.

### 6. Alcance, y el punto de mayor exposición

Este ADR cubre a los **visitantes del sitio web**. Se deja constancia de que el tratamiento de mayor riesgo del proyecto **no es la analítica sino el circuito editorial**: de autores y árbitros se recogen —según declara `page-politicas` §6— nombre, **número de documento de identidad**, teléfono y correo, y entre ellos habrá residentes en la UE. Se resolverá junto con el subsistema de envíos (ADR 0005); queda **fuera del alcance** de este ADR, pero señalado para que no se confunda esta decisión con un cierre del asunto.

### 7. Destinatario del correo (observación registrada)

ADR 0010 fija el destino del formulario en `revista.cenfiss@gmail.com`. Los datos de quien escriba —incluidos autores en España— quedan por tanto **en servidores de Google**, que debe declararse como destinatario en el aviso.

Un buzón en el propio dominio (`revista@cenfiss.net` o similar) eliminaría ese destinatario de la cadena. **Está por verificar si el plan de hosting actual incluye correo en el dominio**; si no lo incluye, la alternativa tiene coste y compite con el presupuesto fijado en ADR 0005 (solo hosting + dominio). Se registra como **trabajo futuro** condicionado a esa verificación; la decisión es del propietario. Mientras tanto, el aviso de privacidad declara a Google como destinatario, que es lo exigible.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| **GA4 desde la v1** | Pone cookies → obliga a banner → añade un plugin de consentimiento y un elemento de UI que rompe la sobriedad académica; transfiere datos a Google, contradiciendo el motivo por el que ADR 0010 rechazó reCAPTCHA; y debilita la excepción del art. 27.2 RGPD. **Se aplaza a fase posterior con asesoría legal; no se descarta.** |
| **No instalar analítica en la v1** (solo registros del servidor) | Era la opción de mínimo riesgo, pero deja a la revista sin cifras de uso por artículo justo cuando más las necesita: memoria institucional y solicitudes a índices. El propietario decide medir desde el principio, y una herramienta sin cookies permite hacerlo sin banner ni terceros. |
| **Koko Analytics** | El más fiel a «acotado» y el más ligero (datos agregados, huella mínima en la base de datos, sin peticiones externas), pero 60.000 instalaciones frente a 600.000 y **cookies opcionales**: el invariante dependería de dejar bien una casilla. Descartado por el criterio «muy usado» de ADR 0006 y por robustez del invariante. Sigue siendo el sustituto natural si WP Statistics decepciona. |
| **Burst Statistics** | 200.000+ instalaciones y bien mantenido, pero **usa cookies por defecto** y el modo sin cookies es opcional. Hacer depender un invariante de años de una casilla que un ajuste o una actualización pueden revertir es frágil. Descartado. |
| **Matomo, Plausible o Umami autoalojados** | Plausible y Umami exigen Node/Docker: inviable en hosting compartido. Sus versiones cloud cuestan dinero (presupuesto = hosting + dominio, ADR 0005). Matomo autoalojado es una segunda aplicación PHP + base de datos que mantener. |
| **Ampliar `page-politicas` §6 en vez de crear página propia** | Mezcla dos tratamientos distintos —confidencialidad editorial y datos del visitante— y contradice `docs/05` §2, que ya preveía página propia. |
| **Instalar un banner de cookies «por si acaso»** | Sin cookies que consentir es ruido; sugiere un rastreo inexistente y empeora la experiencia de lectura sin aportar cumplimiento. |
| **Auditar cada ley nacional del público objetivo antes de la v1** | Inviable como requisito de lanzamiento para un público que abarca España y toda Latinoamérica, y prematuro sin saber de dónde vienen realmente lectores y autores. Se diseña contra el estándar más exigente y las auditorías por jurisdicción se hacen **de forma incremental en fases posteriores** (§1), no se omiten. |

## Consecuencias

**Beneficios:**

- La revista **mide desde el primer día sin renunciar al invariante**: métricas por contenido, en su propio servidor, sin banner, sin plugins de consentimiento y sin ceder datos a nadie.
- La v1 se publica **sin banner y sin terceros**, que es la vía más barata y estable de cumplir.
- Permite a **D12** una CSP estricta (`default-src 'self'`) sin excepciones mientras el invariante se mantenga.
- Cierra el pendiente de ADR 0010 §4 y llena el hueco que `docs/05` §2 ya preveía.
- El coste real de GA4 queda **documentado por adelantado**, de modo que la fase posterior decida con la factura a la vista y no por inercia.

**Riesgos / costes:**

- **Una dependencia de terceros más** que mantener (WP Statistics), sumada a Contact Form 7. Su comportamiento sin cookies debe **reverificarse tras cada actualización mayor**: es el punto por donde el invariante puede romperse en silencio.
- **Crecimiento de la base de datos** en hosting compartido, porque el plugin registra visitas de forma continua. Requiere vigilar el tamaño y fijar una purga de datos antiguos.
- **Las descargas de PDF pueden quedar sin medir** en la versión gratuita (§2.1.4), justo una de las métricas que más interesan a una revista. Se acepta y, si hace falta, se resuelve con código propio.
- La política es **provisional y sin validar jurídicamente**; su texto puede cambiar tras la asesoría legal.
- El invariante «sin terceros» es **frágil ante peticiones editoriales** (un vídeo incrustado, un mapa). Por eso se exige reabrir el ADR.
- Quedan **datos por confirmar** en la página (plazo de conservación de los registros del servidor), marcados de forma visible en el propio documento.

**Trabajo futuro:**

- **Instalar y configurar WP Statistics** en WordPress: verificar que no escribe cookies, que la IP no se almacena en claro, desactivar cualquier integración externa y fijar una **purga periódica** de datos antiguos.
- Añadir a la rutina de mantenimiento la **reverificación del comportamiento sin cookies** tras cada actualización mayor del plugin.
- Confirmar con Hostinger el plazo de conservación de los registros de acceso y completar la página.
- Someter la política provisional a **asesoría legal** antes de abrir la indexación (ADR 0004: `robots.txt` sigue en `Disallow: /` hasta el lanzamiento).
- Al migrar a WordPress: crear la página, fijarla en Ajustes → Privacidad, desactivar comentarios globalmente y enlazar el aviso desde el formulario de Contact Form 7 (ADR 0010 §4).
- **Verificar si el plan de hosting actual incluye buzón en el dominio**; según el resultado, evaluar el traslado desde `gmail.com` (§7).
- **Auditorías de cumplimiento por jurisdicción**, incrementales, en fases posteriores (§1): empezando por Venezuela y España, y ampliando según de dónde vengan realmente lectores y autores. Cada hallazgo que altere la postura técnica se registra en su propio ADR.
- Resolver la privacidad del **circuito editorial** con el subsistema de envíos (ADR 0005).
- Al abordar GA4: recorrer las cinco precondiciones de §2 y redactar el ADR que sustituya a este.

## Referencias

- ADR 0004 (indexación bloqueada hasta el lanzamiento)
- ADR 0005 (CPTs de `revistalogos-core`; subsistema de envíos aplazado)
- ADR 0006 (política de dependencias de plugins: nativo antes que plugin)
- ADR 0010 (formulario de contacto; §4 dejó pendiente este aviso; reCAPTCHA rechazado por cookies)
- Backlog **D10** (esta decisión) y **D12** (cabeceras de seguridad; la CSP depende del invariante §3)
- `docs/05-information-architecture-navigation` §2 (la IA ya preveía página de privacidad)
- `docs/15-assets-strategy` (sin CDN ni peticiones externas)
- RGPD arts. 3(2)(a), 13 y 27; Considerando 23; Directrices 3/2018 del CEPD sobre ámbito territorial
