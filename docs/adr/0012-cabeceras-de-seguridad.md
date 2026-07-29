# ADR 0012: Cabeceras de seguridad HTTP

## Estado

Aceptada

## Fecha

2026-07-28

## Contexto

El backlog **D12** agrupaba dos asuntos distintos: las cabeceras de seguridad —incluida HSTS— y el momento de automatizar CI/CD. Este ADR resuelve **solo el primero**; el segundo queda deliberadamente abierto (§6).

### Estado verificado del dominio de producción (2026-07-28)

Comprobado con `curl` contra `logo-et-spes.cenfiss.net`:

- **`http://` responde `200 OK` sirviendo contenido en claro.** No hay redirección a HTTPS.
- `https://` funciona correctamente (HTTP/2, servidor **LiteSpeed**).
- **Ninguna** de las dos respuestas incluye cabecera de seguridad alguna: sin HSTS, sin `X-Content-Type-Options`, sin `X-Frame-Options`, sin `Referrer-Policy`, sin CSP.
- El `.htaccess` del repositorio solo contiene reglas de reescritura de URL.

Que el servidor sea LiteSpeed importa: honra `.htaccess`, y el workflow de despliegue ya copia ese archivo (ADR 0009). Las cabeceras se pueden enviar **sin infraestructura nueva**.

### Hallazgo: el `.htaccess` no estaba versionado

Al implementar esta decisión se descubrió que **`.gitignore` excluía `/.htaccess`** (línea heredada de una plantilla pensada para el `.htaccess` que genera WordPress). Consecuencias que estaban activas sin que nadie lo supiera:

- El archivo **no estaba en Git**, contra lo que afirma ADR 0009 §2, que lo declara fuente de verdad de código.
- El paso `cp .htaccess` del workflow era un **no-op silencioso**: `actions/checkout` nunca lo traía al runner, de modo que **ningún despliegue ha publicado nunca ese archivo**.
- El `.htaccess` que sirve hoy en producción se subió a mano en algún momento. Verificado con `curl` el 2026-07-28: las reglas de URL sin extensión funcionan en el dominio vivo, así que su contenido coincide con el del repositorio; no hay divergencia que recuperar.

Sin corregir esto, esta decisión sería inerte. Se retira la exclusión y el archivo pasa a versionarse. En la fase WordPress no hay conflicto: el despliegue apuntará solo al theme y al plugin (ADR 0009 §3), de modo que el `.htaccess` que WordPress gestione en el servidor no se verá afectado.

### Estado de la maqueta relevante para una futura CSP

- **Cero estilos inline y cero manejadores de eventos inline** (`onclick` y similares) en las 21 páginas.
- **Un único `<script>` inline** en todo el sitio: el de copiar la cita, en `single-article.html`.
- Los 9 bloques `<script type="application/ld+json">` no son ejecutables y no los afecta `script-src`.

Es una posición de partida excepcionalmente buena: la CSP estricta que ADR 0011 §3 hace posible tiene un solo obstáculo en la maqueta, y es trivial de retirar.

### Hay dos despliegues públicos, no uno

Dato aportado por el propietario el 2026-07-28 y verificado el mismo día: el sitio está publicado **en dos direcciones** y ambas se mantienen por ahora.

| Dirección | Proveedor | Cómo se publica | Cabeceras configurables |
| --------- | --------- | --------------- | ----------------------- |
| `logo-et-spes.cenfiss.net` | Hostinger (LiteSpeed) | Workflow **manual** (`workflow_dispatch`, ADR 0009) | **Sí**, vía `.htaccess` |
| `refo44.github.io/demo-revistalogos` | GitHub Pages | **Automático en cada push a `main`** | **No** |

La primera es **producción**; la segunda es la **URL de revisión**, donde se harán pruebas y donde usuarios beta revisarán el sitio antes de que se retire en una fase posterior.

Comprobado con `curl`: GitHub Pages devuelve **cero cabeceras de seguridad** y no ofrece forma de configurarlas —no interpreta `.htaccess` ni admite cabeceras propias—. Las URL sin extensión funcionan en ambos, y `robots.txt` sirve `Disallow: /` en los dos, de modo que el contenido duplicado entre ambas direcciones no está causando daño de indexación mientras eso siga así.

Tres consecuencias que conviene no perder de vista:

- **La postura de seguridad será asimétrica** mientras existan las dos direcciones, y no por un descuido: en GitHub Pages es inarreglable. Se asume porque es **temporal y tiene una función**: el propietario confirma (2026-07-28) que **GitHub Pages es el prototipo donde se harán pruebas y donde usuarios beta revisarán el sitio**, y que se retirará en una fase posterior, cuando esa revisión concluya. La dirección principal es la de Hostinger, y a ella apuntan ya las URL canónicas del HTML.
- **Los dos entornos difieren, y es sabido.** GitHub Pages no aplica el `.htaccess`: allí no hay cabeceras de seguridad y las URL no redirigen como en producción (`page-acerca.html` responde 200 en vez de redirigir con 301 a `/page-acerca`). Los usuarios beta ya lo saben y revisan sobre esa base (propietario, 2026-07-28); se registra como contexto, no requiere acción.
- **Ya existe un despliegue automático, y es deliberado.** Que GitHub Pages publique en cada push **es el comportamiento buscado**: los usuarios beta esperan ver el último estado sin esperar a nadie (propietario, 2026-07-28). La restricción de que «nada se despliega en automático» (2026-07-23) aplica a **producción**, no a la URL de revisión. El modelo real, ya en pie, es de dos niveles: **automático a revisión, manual a producción**. Es un dato material para **D12b**, que por tanto no decide «si automatizar» —eso ya está resuelto— sino si se añaden **comprobaciones** automáticas al flujo.

### Restricción del propietario (2026-07-28)

Terminada la migración y con WordPress sirviendo en la URL principal, se encargará una **auditoría profesional de SEO y seguridad**. **HSTS no se activa hasta después de esa auditoría.**

## Decisión

El criterio ordenador es la **reversibilidad**: lo que puede retirarse desde el servidor con efecto inmediato se aplica ya; lo que no, espera a la auditoría.

### 1. Ahora — forzar HTTPS

Se añade al `.htaccess` una redirección **301 de HTTP a HTTPS**, colocada **antes** del resto de reglas de reescritura para no encadenar dos redirecciones.

- Cierra el agujero de servir la revista en texto plano, que es real hoy.
- Elimina de paso el contenido duplicado entre `http://` y `https://`.
- **Es un prerrequisito de HSTS, no una alternativa:** HSTS no puede proteger la primera petición en claro; sin esta redirección, activarla más adelante sería poner el tejado antes que las paredes.
- Totalmente reversible: retirar la regla surte efecto en la siguiente petición.

### 2. Ahora — cabeceras reversibles

Se añaden al `.htaccess`, dentro de `<IfModule mod_headers.c>` para que la ausencia del módulo no derribe el sitio:

| Cabecera | Valor | Por qué |
| -------- | ----- | ------- |
| `X-Content-Type-Options` | `nosniff` | Impide que el navegador adivine el tipo de contenido y ejecute como script algo que no lo es. Sin contraindicaciones. |
| `X-Frame-Options` | `SAMEORIGIN` | Evita que la revista se incruste en un marco ajeno (*clickjacking*, suplantación). Se mantendrá junto a `frame-ancestors` de la futura CSP, por navegadores antiguos. |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | No filtra la ruta completa a sitios externos. Coherente con la postura de privacidad de ADR 0011. No afecta a la medición propia, que registra el referente **entrante**. |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(), payment=()` | Una revista de texto no necesita ninguna de esas capacidades; desactivarlas limita el daño de cualquier código que llegara a colarse. |

Las cuatro son **agnósticas de la plataforma**: pasan verbatim a WordPress. No es trabajo que la migración vaya a tirar.

### 3. Tras la auditoría — HSTS

- **No se activa** hasta que WordPress sirva en la URL principal y la auditoría profesional haya concluido.
- Cuando se active, **escalera de `max-age`**: `300` → verificar → `86400` → verificar → `31536000`. El motivo es que **HSTS es la única de estas cabeceras que no se puede retirar desde el servidor**: una vez recibida, el navegador la respeta hasta que expire, aunque el certificado falle. Un año publicado por error es un año de visitantes bloqueados.
- **Sin `preload`.** Se pre-registra la postura porque es previsible que la auditoría lo recomiende por ser un ítem de lista de verificación estándar: salir de la lista de precarga tarda meses y depende de terceros, mientras que el beneficio frente a un `max-age` normal es marginal para una revista académica. Si la auditoría insiste, que sea con un argumento específico para este sitio, no por defecto.
- `includeSubDomains`: el dominio de producción es a su vez subdominio de `cenfiss.net` y no tiene hijos propios, de modo que la directiva sería inocua pero también inútil. Se decide al activar. **Cuidado**: no debe aplicarse nunca desde `cenfiss.net`, que es el sitio institucional y queda fuera del alcance de este proyecto.

### 4. Tras la auditoría — CSP

- Se escribe **contra el sitio WordPress final**, no contra la maqueta: hacerlo antes obligaría a reescribirla entera.
- Punto de partida: `default-src 'self'`, más los hosts concretos que haga falta listar si para entonces el sitio usa algún recurso de terceros (ADR 0011 §3 lo admite cuando conviene). Añadir un host a una CSP es una línea; no es motivo para renunciar a tenerla.
- Se desplegará primero como `Content-Security-Policy-Report-Only`, revisando la consola del navegador en cada plantilla antes de hacerla vinculante.
- **`/wp-admin/` queda exento**: el editor de bloques usa scripts y estilos inline, y una CSP estricta ahí rompería la administración. La CSP estricta es para la cara pública.

### 5. Checklist previo a la auditoría

Se resuelve **antes** de encargarla, para que su coste se gaste en lo que no podemos ver nosotros y no en señalar lo obvio:

1. Mover el `<script>` inline de `single-article.html` a `assets/js/`. Es el único obstáculo de la maqueta para una CSP sin `'unsafe-inline'`, y el código se traslada al tema de todos modos.
2. ~~Corregir los `<link rel="canonical">` y `og:url`.~~ **Hecho el 2026-07-28**: las 38 referencias absolutas del HTML apuntan ya al dominio principal, sin extensión y sin barra final.

**Sobre los emojis de WordPress y Gravatar:** ambos hacen peticiones a terceros (`s.w.org` y `secure.gravatar.com`). **No se desactivan por defecto.** Son funciones del **núcleo** de WordPress, no plugins: desactivarlas significaría *añadir* código propio al tema, es decir, más mantenimiento —justo lo que ADR 0006 quiere evitar—, a cambio de una ganancia de privacidad menor. Se dejan como vienen salvo que la auditoría dé una razón concreta para quitarlos.

Lo que sí obliga esa elección, según ADR 0011 §3, son dos cosas baratas: comprobar que **no introducen cookies** —si lo hicieran, decide el invariante duro— y **declararlos en el aviso de privacidad**, cuyo apartado 5 tendrá que actualizarse en el corte a WordPress. En la CSP, cada uno es un host más en `img-src` o `script-src`.

### 6. Alcance: la automatización CI/CD queda abierta

La segunda mitad de D12 —**cuándo automatizar CI/CD**— **no se resuelve en este ADR**, por decisión del propietario: se decidirá con la información que aporte la auditoría profesional.

Lo que sí queda fijado es el punto de partida, que no es el que sugería el enunciado del backlog. **El despliegue ya está resuelto y en dos niveles deliberados**: automático a la URL de revisión (GitHub Pages), manual a producción (Hostinger, ADR 0009). Nada de eso está en cuestión.

Lo que queda abierto es una tercera cosa: **comprobar no es desplegar**. Sería posible ejecutar comprobaciones automáticas (`stylelint`, que ya existe como script; validación de HTML; detección de enlaces rotos) sin que ninguna toque servidor alguno. Esa es la decisión de **D12b**, y no se toma ahora.

Permanece en el backlog como **D12b**.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| **Activar HSTS ya, junto al resto** | Es la única cabecera irreversible desde el servidor y la que más se beneficia de una revisión profesional sobre el sitio final. El propietario la condiciona a la auditoría, y el criterio es correcto. |
| **Esperar también a la auditoría para la redirección y las cabeceras reversibles** | Dejaría la revista sirviéndose en texto plano y sin ninguna cabecera durante toda la migración, cuando la corrección es reversible, no cuesta nada y sobrevive verbatim a WordPress. Esperar no compra información: que falte `nosniff` no es algo que una auditoría deba descubrir. |
| **Escribir la CSP ahora contra la maqueta** | Habría que reescribirla al llegar WordPress, que introduce sus propios scripts y estilos inline. Trabajo duplicado. |
| **Configurar las cabeceras en el panel de Hostinger** | Quedarían fuera de Git, rompiendo la fuente de verdad única del código (ADR 0009 §2): nadie podría revisarlas ni saber por qué están, y se perderían en una migración de hosting. |
| **HSTS con `preload`** | Meses para revertir y dependencia de un tercero para una ganancia marginal. Ver §3. |
| **`Referrer-Policy: no-referrer`** | Más privado, pero rompe la atribución de tráfico entrante para los sitios a los que la revista enlaza y no aporta protección real al visitante frente a `strict-origin-when-cross-origin`. |

## Consecuencias

**Beneficios:**

- El agujero real de hoy —contenido servido en claro— se cierra ya, no al final de la migración.
- Las cuatro cabeceras aplicadas no requieren infraestructura nueva y **sobreviven intactas** al corte a WordPress.
- La auditoría llega a un sitio ya saneado en lo básico, de modo que su tiempo se gasta en lo que aporta y no en señalar una cabecera ausente.
- La postura sobre `preload` y sobre la escalera de `max-age` queda argumentada **por adelantado**, no improvisada frente a una recomendación de checklist.

**Riesgos / costes:**

- Un `.htaccess` incorrecto puede derribar el sitio o crear un bucle de redirección. Mitigado con patrones estándar y con `<IfModule>`, pero **exige verificar tras el primer despliegue** (§Trabajo futuro).
- Si el panel de Hostinger tuviera activada su propia opción de forzar HTTPS, coexistirían dos mecanismos. Conviene comprobarlo y dejar solo el del repositorio.
- D12 queda **parcialmente pendiente**: el backlog no se cierra con este ADR.
- Los dos puntos del checklist previo son trabajo real que hay que hacer antes de la auditoría, no después.
- Si el sitio incorpora terceros —emojis, Gravatar o lo que convenga—, la CSP crece con ellos y el aviso de privacidad hay que actualizarlo. Es coste asumido, no un fallo del diseño.
- **La copia en GitHub Pages queda sin cabeceras de seguridad y no hay forma de dárselas.** Se acepta a sabiendas: cumple una función —pruebas y revisión por usuarios beta— y está previsto retirarla. Si para cuando se encargue la auditoría profesional esa revisión ya ha terminado, conviene **retirarla antes**, para no pagar por que señalen un despliegue destinado a desaparecer. Si la revisión sigue en curso, se mantiene y se le indica a la auditoría que está fuera de alcance.

**Trabajo futuro:**

- Desplegar y **verificar**: que `http://` devuelve 301, que `https://` sigue respondiendo 200, que las URL sin extensión siguen funcionando y que las cuatro cabeceras aparecen. Es el primer despliegue de la historia del proyecto que publica realmente el `.htaccess` (ver hallazgo en Contexto), así que la verificación no es una formalidad.
- Comprobar si el panel de Hostinger fuerza HTTPS por su cuenta.
- Ejecutar el checklist de §5 antes de encargar la auditoría.
- Tras la auditoría: activar HSTS con la escalera de §3 e introducir la CSP según §4.
- Resolver **D12b** (automatización CI/CD) con la información de la auditoría.

## Referencias

- ADR 0009 (despliegue manual por FTPS; el workflow ya copia `.htaccess`; fuente de verdad única del código)
- ADR 0011 §3 (invariante duro de cookies; los terceros son preferencia blanda, admisibles cuando convienen)
- ADR 0006 (el criterio que manda: no acumular dependencias que haya que mantener)
- Backlog **D12a** (esta decisión) y **D12b** (automatización CI/CD, pendiente)
- `docs/13-static-file-structure`, `docs/12-theme-file-structure` (dónde vive `.htaccess` antes y después del corte)
