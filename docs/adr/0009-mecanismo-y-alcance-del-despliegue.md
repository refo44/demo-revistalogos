# ADR 0009: Mecanismo y alcance del despliegue

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

Hoy el sitio estático se despliega con **GitHub Actions vía FTPS** a Hostinger, con **disparo manual** (`workflow_dispatch`); nada se despliega en automático. Con WordPress ratificado como CMS (ADR 0005) y el monorepo `static/` + `wordpress/` (ADR 0007), hay que definir cómo se despliega sin que un despliegue de **código** destruya el **contenido** editorial.

El riesgo central: aplicar una sincronización con borrado sobre todo `public_html` **después** de instalar WordPress borraría `wp-content/uploads/`, el core, `wp-config.php` y los plugins de terceros. Inaceptable.

Restricciones y preferencias del propietario ya fijadas:

- Presupuesto: solo hosting + dominio (ADR 0005); FTPS ya está pagado y funcionando.
- El dominio principal `https://logo-et-spes.cenfiss.net/` **sigue sirviendo el sitio estático hasta el cambio final**.
- WordPress se probará en un **subdominio** del mismo hosting (previsualización real antes del corte).
- KISS/YAGNI: no añadir dependencias ni entornos que no se necesiten.

Análogo: *Camino del Dharma* ADR 0013 (fuentes de verdad duales y alcance del despliegue).

## Decisión

### 1. Mecanismo: FTPS (se conserva)

Se mantiene el **FTPS** actual (`FTP-Deploy-Action`). No se migra a rsync: funcionaría mejor el borrado acotado, pero exige SSH (acceso y claves) que hoy no se usa; FTPS ya funciona y permite acotar origen/destino. KISS.

### 2. Dos fuentes de verdad

| Dominio | Fuente de verdad | Incluye |
| ------- | ---------------- | ------- |
| **Código** | Git | Theme `revistalogos`, plugin `revistalogos-core`, plantillas, CSS/JS, `.htaccess` de código, docs, workflow |
| **Contenido** | WordPress | Base de datos y `wp-content/uploads/` (páginas, CPTs, medios, ediciones cargadas) |

**Regla:** un despliegue de código **nunca** toca el contenido. Cambios de código → Git → workflow. Cambios editoriales → panel de WordPress.

### 3. Despliegue acotado (regla de seguridad)

- **Fase estática (ahora):** destino = `public_html` del dominio principal (el sitio estático es todo el sitio). Tras la reorg (ADR 0007), el origen del workflow cambia de la raíz a `static/`.
- **Fase WordPress:** el despliegue apunta **solo** a:
  - `wp-content/themes/revistalogos/`
  - `wp-content/plugins/revistalogos-core/`
- **Nunca** se apunta el despliegue a `public_html` con WordPress instalado.
- **Nunca** se sincroniza con borrado sobre `wp-content/uploads/`, el core de WordPress, `wp-config.php` ni plugins de terceros.
- Lo que **no** va en Git: core de WordPress, `wp-config.php`, `uploads/`, plugins de terceros (se mantiene una **lista de instalación** documentada, ADR 0006).

### 4. Entornos y ciclo de vida

**Durante la transición — dos rutas de despliegue que no interfieren:**

```
logo-et-spes.cenfiss.net        →  sitio ESTÁTICO (producción)      [workflow actual, intacto]
<subdominio>.cenfiss.net        →  WordPress (theme + plugin)       [nuevo despliegue acotado]
```

- El **dominio principal sirve el sitio estático** hasta el cambio final; su workflow queda disponible para desplegar correcciones del estático en cualquier momento.
- **WordPress vive en un subdominio** de staging para construir, corregir y previsualizar.
- El subdominio de staging está en **`noindex`** (`robots.txt` `Disallow: /` + `noindex`), **sin** protección por contraseña (decisión del propietario). Consecuencia asumida: es alcanzable por URL para quien la conozca, pero no indexable; aceptable por ser contenido demostrativo y temporal (coherente con ADR 0004).

**En el corte (todo validado «en verde»):**

1. Apuntar el *document root* del dominio principal a la instalación de WordPress (o mover WordPress del subdominio al docroot principal).
2. `wp search-replace` del subdominio → dominio principal en la base de datos (enlaces y canónicos pasan a `logo-et-spes.cenfiss.net`).
3. **Retirar** el despliegue estático (`static/` archivado, ADR 0007).
4. **Reapuntar** el despliegue de theme/plugin al `wp-content` del dominio principal → queda **un solo workflow** de producción.
5. **Abrir la indexación** (`robots.txt`, ADR 0004).

### 5. Disparo manual

Se mantiene el despliegue **solo por disparo manual** (`workflow_dispatch`); la automatización CI/CD queda aplazada (backlog **D12**).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| rsync en lugar de FTPS | Exige SSH y claves no usados hoy; FTPS ya funciona y se puede acotar. KISS. |
| Sincronizar todo `public_html` con WordPress instalado | Borraría `uploads/`, core y `wp-config.php`. Es el riesgo que este ADR evita. |
| Probar WordPress en el dominio principal | Rompería la producción estática antes de estar listo; el subdominio aísla la prueba. |
| Staging con contraseña | El propietario prefiere sin contraseña; `noindex` basta para contenido demostrativo. |
| Mantener dos workflows tras el corte | Innecesario: tras el corte el estático se retira y queda un único destino. |

## Consecuencias

**Beneficios:**

- El contenido editorial queda a salvo de los despliegues de código (acotamiento).
- El sitio estático sigue en producción y desplegable hasta el corte.
- Previsualización realista de WordPress en el subdominio antes de decidir.
- Estado final simple: un solo workflow de producción.

**Riesgos / costes:**

- El staging `noindex` sin contraseña es **alcanzable por URL** (mitigado: contenido demostrativo, `noindex`, temporal).
- Durante la transición conviven **dos configuraciones** de despliegue; hay que no confundirlas.
- El corte exige tareas manuales: repuntar docroot, `wp search-replace`, retirar estático, reapuntar despliegue, abrir robots.

**Trabajo futuro:**

- En la reorg (ADR 0007): actualizar el origen del workflow estático a `static/`.
- Crear el despliegue acotado de theme/plugin hacia el subdominio de staging.
- `robots.txt` `Disallow` + `noindex` en el subdominio de staging.
- Documentar la lista de plugins de terceros a instalar (ADR 0006).
- Añadir la secuencia de corte al checklist de lanzamiento (`docs/17-implementation-order`).

## Referencias

- Análogo: *Camino del Dharma* ADR 0013 (fuentes de verdad duales; alcance del despliegue)
- ADR 0004 (noindex hasta el lanzamiento), ADR 0005 (plugin propio; presupuesto), ADR 0006 (lista de plugins), ADR 0007 (monorepo; reorg; `static/` archivado)
- `.github/workflows/deploy.yml`
- Backlog **D12** (HSTS / automatización CI/CD)
