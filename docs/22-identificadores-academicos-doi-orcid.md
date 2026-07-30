# Revista de Filosofía LOGO ET SPES — Identificadores académicos: DOI y ORCID

**Versión 1.0**

Especificación operativa del registro de DOI y la integración con ORCID: validación, formato, flujo de depósito y visualización. Complementa `03-wordpress-content-model` (qué campos existen) con el *cómo* se validan, se muestran y se depositan. La decisión, sus alternativas y sus costes están en `ADR 0013`; este documento no repite ese razonamiento, lo aplica.

**Depende de:** `03-wordpress-content-model`, `12-theme-file-structure`, `17-implementation-order`, ADR 0004, ADR 0005, ADR 0006, ADR 0013
**Referencia:** `07-voice-guide-microcopy-ux`, `08-voice-dictionary`, `09-ui-copy-sheet`

---

## 1. Alcance y principio rector

Este documento cubre exclusivamente:

- Cómo se valida y se muestra el iD ORCID de un autor.
- Cómo se genera el depósito DOI a partir del contenido ya publicado en WordPress.
- Qué aparece en pantalla mientras un identificador no está asignado.

**No cubre** el trámite de ISSN digital ni Depósito Legal (Biblioteca Nacional de Venezuela, ver ADR 0004 y ADR 0013 §3), ni la redacción del aviso de privacidad (contenido, no código — ver ADR 0013 §6), ni el sistema de cuentas de usuario para autores (aplazado, ADR 0005 §4 / ADR 0013 §7). El alta administrativa en Crossref (investigar Sponsors, dar de alta la cuenta) es un trámite editorial aparte, aprobado en ADR 0013 §2.1, y no depende de nada de lo que sigue.

> **Nota de fase (ADR 0013 §Contexto, «Precisión 4»):** WordPress todavía no está implementado — el repositorio sigue en la Fase 2 (maqueta estática) de `docs/17-implementation-order`; no existe `revistalogos-core`. Todo lo que este documento especifica en código (§3, §4, §6, §7.2-7.4, §9) es trabajo de la **Fase 4: Identificadores académicos**, posterior a la Fase 3 (WordPress). Se documenta ahora, completo, para que la Fase 4 no tenga que rediseñarlo — no para implementarlo antes de que exista el theme.

---

## 2. Campos de datos

### 2.1 `author` — ampliación de `docs/03` §3

| Campo | Tipo | Uso | Estado |
| ----- | ---- | --- | ------ |
| `orcid` | text | iD ORCID, formato `NNNN-NNNN-NNNN-NNNK` | Ya definido en `docs/03` |
| `orcid_url` | computed | `https://orcid.org/{orcid}`, calculado al mostrar, no almacenado | Nuevo — añadido por ADR 0013 §1, análogo a `article.doi_url` |

Ambos opcionales a nivel técnico (§4 de ADR 0013).

### 2.2 `issue` / `article` — sin cambios de esquema

Los campos `issue.issn`, `issue.doi`, `article.doi`, `article.doi_url` ya están definidos en `docs/03` §3 y no cambian. Este documento formaliza su **validación y flujo de llenado**, no su esquema.

| CPT | Campo | Formato esperado |
| --- | ----- | ----------------- |
| `issue` | `issn` | `NNNN-NNNN` — **es el ISSN electrónico (e-ISSN)**, distinto y todavía sin asignar; no confundir con el ISSN de la versión impresa, que CENFISS ya tiene (ADR 0013 §2 «Precisión 2»). Hasta que se asigne, se muestra «ISSN: en trámite» (§5) — nunca el ISSN impreso ni un valor inventado. |
| `issue` | `doi` | `10.{prefijo}/{sufijo}` (opcional a nivel de número; ver §7 — lo habitual es depositar a nivel de artículo, no de número) |
| `article` | `doi` | `10.{prefijo}/{sufijo}`, convención de sufijo en §7.3 |
| `article` | `doi_url` | `https://doi.org/{doi}`, calculado al mostrar |

---

## 3. Validación del iD ORCID

Formato: 16 dígitos en 4 grupos de 4, separados por guion; el último carácter es un dígito de control que puede ser `X` (representa el valor 10). Algoritmo ISO 7064 MOD 11-2 (fuente: soporte oficial de ORCID, «Structure of the ORCID Identifier»):

```text
total = 0
para cada uno de los primeros 15 dígitos, en orden:
    total = (total + dígito) * 2
resto = total % 11
resultado = (12 - resto) % 11
dígito_control = "X" si resultado == 10, si no, str(resultado)
```

**Regla de implementación:** validar en el servidor al guardar el post-meta (`revistalogos-core`), no solo en el cliente. Rechazar el guardado si el formato o el checksum no son válidos; no rechazar el campo vacío (es opcional). No normalizar variantes de entrada más allá de: aceptar con o sin el prefijo `https://orcid.org/` y con o sin guiones, y almacenar siempre en la forma canónica `NNNN-NNNN-NNNN-NNNK`.

---

## 4. Visualización y datos estructurados

- **Enlace visible:** cada iD ORCID se muestra como enlace a `orcid_url`, en la ficha de autor (`single-author`) y junto al nombre de cada autor en la ficha de artículo (`single-article`), con el icono verde estándar de ORCID (uso permitido por su política de marca; no requiere licencia).
- **JSON-LD:** en el bloque `Person` ya previsto por `docs/03` §4 (Schema.org, dentro de `ScholarlyArticle`/autor), añadir:

  ```json
  {
    "@type": "Person",
    "name": "Nombre Apellido",
    "sameAs": "https://orcid.org/0000-0000-0000-0000"
  }
  ```

- **DOI del artículo:** se muestra como enlace a `doi_url` (`https://doi.org/…`) en la ficha de artículo y en la cita sugerida (`citation_format`, ya previsto en `docs/03` §3).
- **`citation_*` (Highwire/Google Scholar) vs. XML de Crossref:** son dos formatos distintos, para dos consumidores distintos. Los meta tags `citation_*` de `docs/03` §4 los lee Google Scholar directamente del HTML publicado, sin registro previo ni coste. El XML de Crossref (§7) es un depósito aparte, con su propio esquema, que solo tiene efecto tras alta en la agencia de registro. No sustituyen uno al otro: mantener ambos.

---

## 5. Estados «en trámite»

Mientras un identificador no esté asignado, se muestra un marcador honesto (régimen de ADR 0004 §3), nunca un valor inventado ni vacío sin explicación:

| Campo sin asignar | Texto de marcador (borrador) |
| ------------------ | ----------------------------- |
| `article.doi` | «DOI: en trámite» |
| `issue.issn` | «ISSN: en trámite» |
| `author.orcid` | (no aplica — es opcional; simplemente no se muestra el bloque ORCID) |

El copy exacto debe validarse contra `07-voice-guide-microcopy-ux` y `08-voice-dictionary` al implementarse; el texto de arriba es un borrador funcional, no copy final aprobado.

---

## 6. Flujo operativo — ORCID

1. El autor incluye su iD ORCID al entregar su reseña curricular (ya pedido por las normas editoriales, `content-source` §5.6).
2. El editor lo transcribe al campo `author.orcid` al crear/editar el registro de autor.
3. `revistalogos-core` valida formato y checksum al guardar (§3); si falla, rechaza con mensaje claro.
4. La ficha de autor y de artículo muestran el enlace y el `sameAs` automáticamente; no hay paso manual adicional.
5. (Fase posterior, portal de autor — ADR 0005 §4 / ADR 0013 §1): el propio autor podrá verificar su iD mediante «Sign in with ORCID» (OAuth de 3 patas, API pública, sin coste) en lugar de que el editor lo transcriba a mano.

---

## 7. Flujo operativo — DOI (Crossref)

### 7.1 Requisitos previos

- **`revistalogos-core` debe existir** (Fase 3, WordPress construido) — todo lo de esta sección es trabajo de Fase 4 (ADR 0013 §Contexto, «Precisión 4»); no depende de tener cuenta Crossref, sí de que el plugin exista.
- El número y sus artículos deben existir como contenido **real y publicado** en WordPress (no fixtures de ADR 0004) — el depósito incluye una URL de destino que debe resolver. Esto solo es relevante para el **primer depósito real**, no para construir ni probar el generador contra el dataset mockeado.
- Alta en Crossref (membresía directa o vía Sponsor, ver §8) — **no es requisito para construir ni probar el generador** (§7.4), solo para el depósito real. El gasto ya está aprobado (ADR 0013 §2.1) y esa gestión puede adelantarse sin esperar a la Fase 4; lo que falta es el trámite administrativo, no una decisión.

### 7.2 Mapeo de campos CPT → XML de Crossref

Tabla de referencia, no el esquema completo (consultar la documentación/XSD vigente de Crossref al implementar — el esquema tiene más elementos obligatorios en `<head>` de los que se listan aquí):

| Dato en WordPress | Elemento Crossref (aprox.) |
| ------------------ | ---------------------------- |
| Título de la revista (opción global) | `journal_metadata / full_title` |
| `issue.issn` | `journal_metadata / issn` |
| `issue.volume_number`, `issue.issue_number`, `issue.year` | `journal_issue / journal_volume`, `issue`, `publication_date` |
| `article.title_en` o título ES según idioma de registro | `journal_article / titles / title` |
| `article.authors` → `author.title` (nombre), apellido derivado | `journal_article / contributors / person_name` (`given_name`, `surname`) |
| `author.orcid` | `person_name / ORCID` (URI completa `https://orcid.org/…`) |
| `article.publication_date` | `journal_article / publication_date` |
| `article.pages` | `journal_article / pages` (`first_page`, `last_page`) |
| `article.doi` (generado según §7.3, no leído — es el valor que se está creando) | `journal_article / doi_data / doi` |
| Permalink de `single-article` (`get_permalink()`) | `journal_article / doi_data / resource` |

### 7.3 Convención de sufijo DOI

Fijada en ADR 0013 §4:

```text
10.{prefijo-crossref}/les.v{volumen}n{numero}.a{orden-articulo}
```

Ejemplo ilustrativo: `10.xxxxx/les.v12n2.a03`. El prefijo lo asigna Crossref al dar de alta la cuenta; hasta entonces no existe y no se inventa. El número de volumen/número/artículo es el editorial (el del sumario real), no el orden de creación del post en WordPress.

**Invariante:** un DOI, una vez depositado, no cambia. Si se detecta un error en el sufijo antes de depositar, se corrige; después de depositado, se gestiona como corrección ante Crossref, no se reasigna.

### 7.4 Generador de XML — `revistalogos-core`

- Implementación: comando WP-CLI (p. ej. `wp les crossref-export --issue=<id>`) o acción de administración accesible al rol Managing Editor (`docs/03` §6), que recorre los `article` publicados vinculados a un `issue` y produce un único archivo XML.
- Código propio (ADR 0006): no existe plugin de WordPress equivalente al plugin Crossref de OJS (verificado en la investigación de ADR 0013).
- El XML generado se **revisa manualmente** antes de cualquier envío — ningún depósito automático sin revisión editorial, al menos en esta primera fase.
- Envío: subida manual en el panel de depósito de Crossref (o del Sponsor elegido). Automatizar el envío por API queda como mejora posible, no como requisito inicial.

### 7.5 Tras el depósito

1. Crossref confirma el registro (habitualmente en minutos).
2. El editor copia el DOI real a `article.doi` (y `issue.doi` si se decide registrar también a nivel de número — no es lo habitual; lo estándar es registrar el artículo, no el número contenedor).
3. El marcador «DOI: en trámite» (§5) desaparece de ese registro; `doi_url` se calcula automáticamente.
4. Si los metadatos incluían el iD ORCID del autor, este recibe la solicitud de autorización de ORCID Auto-Update (una sola vez, válida hasta 20 años) — fuera del control del sitio, la gestiona ORCID directamente con el autor.

---

## 8. Costes y vía de alta (resumen operativo)

**Este es un gasto editorial/legal de la revista, igual que ISSN y Depósito Legal — no un gasto de software del sitio WordPress** (ADR 0013 §Contexto, «Precisión 1»); la política de «ningún plugin ni servicio de pago» de ADR 0005 no lo alcanza. El trámite (investigar Sponsors, dar de alta la cuenta) puede avanzar ya, sin esperar a la Fase 4 (ADR 0013 §2.1). Detalle completo en ADR 0013 §Contexto y §2.1. Resumen:

| Concepto | Coste estimado |
| -------- | --------------- |
| Membresía Crossref (franja ≤1.000 USD/año de ingresos/gastos) | 200 USD/año |
| Registro por DOI (contenido del año en curso + 2 anteriores) | 1,00 USD/DOI |
| Registro retroactivo (años anteriores) | 0,15 USD/DOI |
| ORCID (API pública) | 0 USD — sin membresía necesaria |

Antes de pagar membresía directa, **investigar el Programa de Sponsors de Crossref** (`crossref.org/membership/about-sponsors/`): puede reducir el coste y da soporte en español. Es una comprobación de eficiencia — proceder está ya decidido (ADR 0013 §2.1); lo pendiente es confirmar la cifra real contra el volumen de artículos del primer número y elegir la vía (directa o Sponsor) más conveniente. Nada de esto espera a la Fase 4.

---

## 9. Checklist de implementación

### Ya, en paralelo — no dependen de que exista WordPress

1. Investigar el Programa de Sponsors de Crossref y confirmar la cifra real de coste (§8); dar de alta la cuenta (directa o vía Sponsor) si el propietario decide no esperar a la Fase 4 para eso.
2. Designar quién en CENFISS gestiona las solicitudes de acceso/corrección/baja de datos de autor frente a Crossref (obligación del miembro, ADR 0013 §Contexto/§6).
3. Revisar `page-politicas` §6 y la Solicitud de Publicación/Declaración de Ética con asesoría legal (ADR 0013 §6) — contenido, no código; conviene tenerlo listo antes de que el primer número real recoja datos de autor.
4. Tramitar el ISSN electrónico ante la Biblioteca Nacional, en paralelo y sin depender del DOI (ADR 0004).

### Fase 4 (`revistalogos-core`) — tras la Fase 3, WordPress construido

5. `register_post_meta` + validación de formato/checksum para `author.orcid` (§3).
6. Función helper `orcid_url( $orcid )` (computed field, §2.1).
7. Enlace ORCID + icono en plantillas `single-author.php` / `single-article.php`.
8. `sameAs` en el JSON-LD `Person` ya previsto (§4).
9. Marcadores «en trámite» para `doi`/`issn` sin asignar, con copy validado contra docs 07/08 (§5).
10. Comando WP-CLI o acción de administración: generador de XML de depósito Crossref (§7.4), probado contra el número mockeado de ADR 0004.
11. Documentar en el repositorio (README de `revistalogos-core` o equivalente) el procedimiento manual de subida a Crossref y el checklist de §7.5, para que no dependa de memoria de una sola persona.
12. Con cuenta Crossref activa (paso 1) y sitio en producción: primer depósito real, backfill de `doi`/`issn` en el primer número.

---

## 10. Fuera de alcance

Ver ADR 0013 §8 para el razonamiento completo. Enumerado aquí solo como referencia rápida: DataCite, migración a OJS, API de miembro de ORCID, complementos de pago de Crossref (Similarity Check, Cited-by), indexación en LATINDEX/DOAJ/REDIB, trámite de ISSN digital ante Biblioteca Nacional, sistema de envíos de autores, redacción final del aviso de privacidad.

---

## 11. Privacidad y cuentas de usuario — remisión

Dos preguntas frecuentes al implementar este documento, ya resueltas en ADR 0013, para que no se redescubran aquí:

- **¿Hace falta cuenta de usuario para que un autor tenga su ORCID publicado?** No. El editor transcribe el ORCID a la ficha pública de autor (`author`, CPT) al montar el número; no es una cuenta de WordPress ni requiere login. Ver ADR 0013 §7.
- **¿Qué implica esto para la privacidad de los autores y el RGPD?** El iD ORCID es dato personal; publicar nombre/afiliación/ORCID y depositarlos en Crossref (con posible auto-update en ORCID) implica destinatarios internacionales (Crossref, ORCID) y activa el disparador de revisión de ADR 0011 §5. Ver el análisis completo — base jurídica, destinatarios, transferencia internacional, límite del derecho de supresión — en ADR 0013 §6. La actualización de `page-politicas` §6 que ese análisis exige es contenido, no código, y queda pendiente de asesoría legal.

---

**Versión:** 1.0
**Proyecto:** Revista de Filosofía LOGO ET SPES
