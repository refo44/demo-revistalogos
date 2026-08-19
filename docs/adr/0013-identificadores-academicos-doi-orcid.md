# ADR 0013: Identificadores académicos — registro DOI y reconocimiento de autoría vía ORCID

## Estado

Aceptada

## Fecha

2026-07-29

## Contexto

`docs/03-wordpress-content-model` ya reserva campos para estos identificadores — `doi`/`doi_url`/`issn` en `issue` y `article`, `orcid` en `author` — pero son placeholders demostrativos: ADR 0004 los declara ficticios (`10.1234/les.*`, `0000-0000-*`) y prohíbe migrarlos a producción. No existe todavía mecanismo real de validación, registro ni visualización detrás de esos campos. El checklist de lanzamiento (`docs/17-implementation-order`) exige «ISSN, depósito legal, DOI y ORCID confirmados o marcados honestamente como pendientes», y ADR 0004 §3 ya fija el régimen general: un identificador sin asignar se muestra como **«en trámite»**, nunca como valor falso.

El propietario pide resolver dos integraciones sobre esa base — que la revista pueda **registrar DOI** para sus números y artículos, y que los **autores sean reconocidos** por su contribución vía **ORCID** — y aporta tres precisiones que cambian el marco de la decisión respecto a un análisis solo técnico:

### Precisión 1 — el DOI es un coste editorial/legal, no un coste del proyecto WordPress

La revista se publica **en papel y en digital, con los mismos artículos**; el sitio WordPress es la instanciación digital de una publicación que ya existe como operación editorial independiente del software. CENFISS ya paga y gestiona el trámite de **ISSN y Depósito Legal de la versión impresa** ante la Biblioteca Nacional de Venezuela; **falta hacer el mismo trámite para la versión digital**. El propietario encuadra el **DOI en esa misma categoría**: un coste editorial/legal de la revista como publicación, análogo a ISSN/Depósito Legal, **no** un «plugin o servicio de pago» del sitio WordPress.

Esto **reencuadra la restricción de ADR 0005** («no hay presupuesto más allá del hosting y el dominio ya pagados. Ningún plugin ni servicio de pago»): esa restricción gobierna el **software** del sitio — plugins, temas, servicios que el theme consuma —, no los **trámites editoriales de la revista como publicación**, que siempre han tenido su propia vía de presupuesto (así se gestionan ya ISSN y Depósito Legal). El registro DOI se trata en este ADR con el mismo criterio que ISSN/Depósito Legal, no con el de un plugin.

### Precisión 2 — ISSN impreso vs. ISSN digital (e-ISSN) son trámites distintos

Un mismo título de revista con edición impresa y digital recibe, en la práctica internacional estándar (y así lo gestiona Biblioteca Nacional de Venezuela), **dos ISSN distintos**: uno para la versión impresa y otro para la versión electrónica (`e-ISSN`). CENFISS tiene resuelto el de la versión impresa; **el de la versión digital sigue pendiente**. El campo `issue.issn` de `docs/03` se refiere, por tanto, al **ISSN electrónico**, todavía sin asignar — no es una duplicación del que ya existe para el papel, es un identificador distinto que hay que tramitar aparte. Este ADR no gestiona ese trámite (sigue bajo el régimen de ADR 0004, Biblioteca Nacional), pero fija esta precisión para que `docs/03` y `docs/22` no lo den por resuelto ni lo confundan con el impreso.

### Precisión 3 — no hacen falta cuentas de usuario para publicar la primera edición

El propietario pregunta si hacen falta login/cuenta para las personas que publican en la revista. La respuesta ya está fijada por **ADR 0005 §4** y se reafirma aquí para que este ADR no la deje ambigua: **no**. El CPT `author` es una **ficha pública de autor** (nombre, afiliación, ORCID, biografía), no una cuenta de WordPress; el equipo editorial la crea y la vincula al artículo al publicar. El sistema de **envíos con cuentas de usuario** (rol `Author`, login, portal de autor) es un subsistema aparte, **aplazado** por ADR 0005 §4 porque la primera edición la cargan los editores directamente. Ver Decisión §7.

### Precisión 4 — WordPress todavía no existe; la implementación de ORCID/DOI es una fase posterior

Al momento de este ADR, **WordPress (Fase 3 de `docs/17-implementation-order`) no se ha implementado todavía**: el sitio vigente es la maqueta estática de Fase 2; no existe `revistalogos-core` ni ningún theme en el repositorio. Este ADR fija la **arquitectura** para cuando corresponda construirla — «Aceptada» significa que la decisión no está en discusión, no que el código se escriba de inmediato.

El propietario sitúa la **implementación** de ORCID en una fase **posterior a WordPress**, no dentro de la Fase 3 ni antes de que exista. Por dependencia técnica directa, lo mismo aplica al código de depósito DOI: ambos viven en `revistalogos-core`, que no existe hasta que la Fase 3 se construya. Este ADR añade una **Fase 4: Identificadores académicos** a `docs/17-implementation-order`, posterior a la Fase 3, para el código de ambos (Decisión §1, §2.2).

Esto **no** alcanza a los trámites administrativos que no dependen de código — investigar el Programa de Sponsors de Crossref, solicitar membresía, tramitar el ISSN electrónico ante la Biblioteca Nacional (Precisión 2). Esos son gestiones de CENFISS como editor, no del sitio WordPress, y **pueden avanzar ya, en paralelo**, igual que ya avanza el trámite de ISSN/Depósito Legal impreso (Precisión 1) — no tienen por qué esperar a la Fase 4. Ver Decisión §2.1.

### Restricciones que siguen vigentes

- **ADR 0006:** orden de preferencia nativo → código propio (`revistalogos-core`) → tercero, y solo terceros gratuitos, muy usados y mantenidos — sigue gobernando *cómo se construye* la integración (sin plugins de pago para el sitio), aunque el registro DOI en sí no sea ya una cuestión de plugin.
- **ADR 0002:** WordPress como adaptación sin rediseño — sigue descartando recambios de plataforma (p. ej. OJS) para resolver esta necesidad.
- **ADR 0011:** ya fijó el marco RGPD del proyecto (línea base por público en España/Latinoamérica) y, en su §6, señaló explícitamente que **el circuito editorial —datos de autores y árbitros— es el punto de mayor exposición del proyecto** y quedaba **fuera de su alcance**, remitido al subsistema de envíos. Este ADR **adelanta la porción de ese circuito que ORCID/DOI activan ya**, en el lanzamiento de la primera edición, antes de que exista portal de autor. Ver Decisión §6.

### Investigación de mercado (2026-07-29)

- **Crossref es el estándar de facto para DOI de artículos de revista**; DataCite se usa mayoritariamente para datasets/repositorios (ver Decisión §8, fuera de alcance).
- **Estructura de cuotas de Crossref (vigente, verificada 2026-07-29):**
  - Cuota anual por franja de ingresos/gastos de publicación: **200 USD/año** en la franja más baja (≤1.000 USD/año, franja introducida en enero de 2026, pensada para editores del tamaño de CENFISS); 275 USD/año en la franja siguiente (hasta 1 millón). Sin cuota de alta.
  - Registro de contenido: **1,00 USD por DOI** de contenido «actual» (año en curso + 2 anteriores); 0,15 USD por registro retroactivo.
  - Ilustrativo: con ~6 artículos por número (tamaño del dataset de ejemplo de ADR 0004) y 2 números/año, el registro anual rondaría 12 USD ⇒ **del orden de 210-220 USD/año en total**, cifra a confirmar contra el volumen real del primer número.
- **Programa de Sponsors de Crossref:** organizaciones ya asociadas a Crossref registran DOI en nombre de editores pequeños por una cuota de servicio habitualmente inferior a la membresía directa, con soporte administrativo y a menudo en el idioma local; es la vía de entrada de más de la mitad de los miembros actuales de Crossref, y Latinoamérica tiene sponsors activos desde 2016. El directorio interactivo de Crossref no se pudo consultar en detalle con las herramientas de esta sesión; identificar un sponsor de la región queda como tarea puntual (§ Trabajo futuro) — **se investiga antes de pagar membresía directa, por eficiencia de coste, no como condición para proceder** (a diferencia del borrador anterior de este ADR, ya no es un bloqueo presupuestario).
- **No existe un plugin de WordPress equivalente al plugin Crossref de OJS** (Open Journal Systems). El único plugin de WordPress relacionado con Crossref sirve para *citar* metadatos ajenos en una entrada de blog, no para depositar los propios.
- **Plugins de WordPress específicos de ORCID — evaluados y descartados (verificado 2026-07-29, a raíz de un enlace aportado por el propietario):**
  - **«Researcher Profiles for ORCID»**: consume la API pública de ORCID v3.0 y cachea localmente perfiles completos (publicaciones, empleo, educación) en 5 tablas propias, expuestos vía shortcodes. **10+ instalaciones activas, 0 reseñas, sin valoración** — desarrollador sin identificar. Actualizado hace ~2 meses (mayo de 2026), compatible con WP 6.9.5.
  - **«Linked Open Profiles»**: bloque de Gutenberg que muestra secciones configurables de un perfil ORCID público, actualizado en vivo desde la API. **70+ instalaciones activas, 1 reseña (5 estrellas)** — desarrollado por el Mesh Research Lab (Michigan State University) y financiado por el **ORCID Global Participation Fund**: más credibilidad institucional que el anterior, pero solo funciona con el editor de bloques (no con plantillas PHP clásicas), lo que encaja mal con el theme de plantillas nativas de `docs/12`.
  - **Ninguno de los dos supera el criterio «muy usado» de ADR 0006** frente a los plugins que el proyecto sí adoptó (Contact Form 7: 5M+ instalaciones; WP Statistics: 600.000+) — la diferencia es de varios órdenes de magnitud, no un matiz. Y ambos **hacen más de lo necesario**: sincronizan y muestran el perfil académico completo del autor (toda su obra, afiliaciones, educación), cuando lo pedido es reconocer la autoría de *este* artículo en *esta* revista — un campo validado, un enlace y un dato estructurado, no un agregador de perfil. Ver Decisión §1 y Alternativas consideradas.
- **ORCID separa API pública (gratuita) de API de miembro (de pago).** La pública basta para captura, validación, enlace y «Sign in with ORCID» de solo lectura. Ver Decisión §1.
- **ORCID Auto-Update vía Crossref (gratuito):** si un DOI se deposita con el iD ORCID del autor en los metadatos, ORCID le pide una vez permiso (válido hasta 20 años) y desde entonces cada depósito de Crossref que incluya su iD actualiza automáticamente su registro. Acopla las dos decisiones — ver Decisión §4.
- **Transferencias internacionales (relevante para §6):** ORCID, como organización sin ánimo de lucro no sujeta a la jurisdicción de la FTC estadounidense, **no puede autocertificarse en el EU-US Data Privacy Framework**; usa **Cláusulas Contractuales Tipo (SCC)** como garantía de transferencia, y sus prácticas se evalúan cada año frente a los requisitos del DPF. No se verificó en esta investigación el mecanismo equivalente de Crossref — queda como tarea de verificación (§ Trabajo futuro).
- **Términos de membresía de Crossref** obligan al miembro (CENFISS) a ofrecer a las personas cuyos datos deposita un medio para acceder, corregir o borrar esos datos, y a comunicar esas correcciones/bajas a Crossref — es decir, **CENFISS conserva la responsabilidad frente a sus autores** aunque el dato viva también en la infraestructura de Crossref.

## Decisión

### 1. ORCID: arquitectura resuelta ahora — implementación en Fase 4, con código propio y API pública gratuita

- El campo `author.orcid` (ya existe en `docs/03`) se formaliza como **texto opcional con validación de formato**: 16 dígitos en 4 grupos de 4 (`NNNN-NNNN-NNNN-NNNK`), dígito de control ISO 7064 MOD 11-2 (puede ser `X`). Validación en PHP dentro de `revistalogos-core` (código propio, ADR 0006) — **implementación en Fase 4** (Precisión 4): `revistalogos-core` no existe hasta que se construya el theme en Fase 3.
- Se añade un campo derivado `orcid_url` (computado, igual que `article.doi_url` de `article.doi`): `https://orcid.org/{iD}`.
- **Visualización:** enlazado en ficha de autor y junto a cada autor en ficha de artículo, con el icono/atribución estándar de ORCID (uso permitido por su política de marca).
- **Datos estructurados:** `sameAs: "https://orcid.org/{iD}"` en el JSON-LD `Person` ya previsto (`docs/03` §4).
- **Opcional, sin coste, para una fase todavía más posterior que la Fase 4:** «Sign in with ORCID» (OAuth de 3 patas, API pública, solo lectura) en el **portal de autor**, cuando se construya (ADR 0005 §4 — subsistema de envíos, aplazado más allá incluso de la Fase 4). No se implementa en la Fase 4 — ver §7.
- **No se solicita membresía ORCID (API de miembro).** Ver acoplamiento en §4.
- **Se evaluaron y descartaron dos plugins específicos de ORCID** («Researcher Profiles for ORCID», «Linked Open Profiles») — ninguno cumple «muy usado» de ADR 0006 y ambos resuelven un problema más grande (perfil académico completo) que el planteado aquí. Ver § Contexto, «Investigación de mercado», y Alternativas consideradas.
- Campo **opcional** a nivel técnico: las normas editoriales ya piden el ORCID en la reseña curricular (`content-source` §5.6) como práctica esperada, sin convertirlo en bloqueo técnico de publicación.

### 2. DOI: el trámite administrativo puede avanzar ya; la implementación técnica es Fase 4

Dos pistas independientes (Precisión 4): una de gestión con Crossref, que no depende de que exista el sitio WordPress; otra de código, que sí.

#### 2.1. Trámite administrativo — puede avanzar ya, en paralelo a la construcción del sitio

**Se aprueba el gasto de registro DOI**, con el mismo estatus que ISSN/Depósito Legal de la versión digital (Precisión 1): es un coste de la revista como publicación, no del sitio WordPress, y por tanto no lo bloquea ADR 0005. Esta pista es gestión de CENFISS ante Crossref y **no espera a que WordPress exista** — igual que el trámite de ISSN/Depósito Legal ya avanza sin depender del theme:

1. **Investigar el Programa de Sponsors de Crossref** (`crossref.org/membership/about-sponsors/`) antes de solicitar membresía directa — puede reducir el coste y da soporte en español; es una comprobación de eficiencia, no una condición para proceder.
2. Confirmar la cifra real con el volumen de artículos del primer número (la estimación de 210-220 USD/año es ilustrativa).
3. Dar de alta la cuenta (directa o vía Sponsor) cuando convenga administrativamente — la cuenta puede existir antes de que el sitio esté en producción; **el primer depósito real** sí espera a eso (§2.2).
4. **Designar quién gestiona, en CENFISS, las solicitudes de acceso/corrección/baja de datos de autores frente a Crossref** (obligación del miembro según sus términos, § Contexto) — recae razonablemente en el rol Managing Editor (`docs/03` §6), pero es una decisión organizativa del propietario, no técnica.

#### 2.2. Implementación técnica — Fase 4 (tras Fase 3, WordPress)

- Los campos `issue.issn` (e-ISSN, Precisión 2), `issue.doi`, `article.doi`, `article.doi_url` (ya en `docs/03`) se formalizan como modelo de datos fuente para el depósito.
- Se construye en `revistalogos-core` (código propio) un **generador de XML de depósito Crossref** a partir de los CPT `issue`/`article`/`author` de un número — comando WP-CLI o acción de administración, con revisión manual antes de cualquier envío. Detalle del mapeo en `docs/22-identificadores-academicos-doi-orcid`.
- Se construye y se prueba contra el número mockeado de ADR 0004, una vez exista `revistalogos-core` (es decir, con la Fase 3 ya construida) — **no depende de tener la cuenta Crossref de §2.1 activa**, solo de que el plugin exista.
- Mientras no haya DOI real, cada número/artículo muestra **«DOI: en trámite»** (régimen de ADR 0004 §3).
- Se fija la convención de sufijo DOI (§5) para no improvisarla en el primer depósito.
- **El primer depósito real** (usar la cuenta de §2.1 para registrar un DOI de verdad) espera a que el sitio esté en producción con URLs estables — el depósito incluye una URL de destino (`resource`) que debe resolver; no tiene sentido registrar apuntando a contenido todavía no público. En la práctica esto tiende a coincidir en el tiempo con el cierre del Depósito Legal, pero por una razón distinta (aquí es técnica: la URL debe resolver; allí es un requisito legal de permanencia en producción) — no hay dependencia formal entre ambos trámites (Precisión 1).
- Flujo operativo completo, con cuenta activa y sitio en producción: generar el XML → depositar (subida manual en el panel de Crossref o del Sponsor) → recibir confirmación → rellenar `article.doi`/`issue.doi` con el valor real → el «en trámite» desaparece de ese registro.

### 3. ISSN digital: no lo tramita este ADR, pero fija su identidad

Reafirmando la Precisión 2: `issue.issn` = e-ISSN, pendiente, distinto del ISSN impreso ya obtenido. El trámite en sí sigue bajo ADR 0004 (Biblioteca Nacional, régimen «en trámite»); este ADR solo evita que se confunda con el DOI o con el ISSN de papel al implementar el campo. Ver `docs/22` §2.2.

### 4. Acoplamiento: el auto-update de ORCID hace innecesaria la API de miembro, con o sin DOI

- Si los XML de depósito (§2.2, Fase 4) incluyen el iD ORCID de cada autor en `<ORCID>`, **ORCID Auto-Update reconoce la contribución automáticamente** en el registro del autor, sin coste ni desarrollo adicional, en cuanto exista alta en Crossref (§2.1) y depósito real.
- Mientras tanto, el reconocimiento de ORCID **ya funciona en el sitio** vía §1 (enlace + `sameAs`) desde que se implemente en Fase 4: no depende del DOI para tener valor.
- En ningún escenario de este ADR hace falta la API de miembro de ORCID.

### 5. Convención de sufijo DOI

```text
10.{prefijo-crossref}/les.v{volumen}n{numero}.a{orden-articulo}
```

Ejemplo ilustrativo (prefijo real pendiente de alta): `10.xxxxx/les.v12n2.a03`. El número de volumen/número/artículo es el editorial (el del sumario real), no el orden de carga en WordPress. Un DOI, una vez depositado, no cambia.

### 6. Privacidad y RGPD

Extiende el marco de ADR 0011, que ya fijó el RGPD como línea base de diseño (público en España/Latinoamérica) y que en su §6 dejó explícitamente **fuera de alcance** «el circuito editorial»: de autores se recogen datos, y entre ellos habrá residentes en la UE. Este ADR resuelve la porción de ese circuito que ORCID/DOI activan en el lanzamiento — no la totalidad (manuscritos, número de documento de identidad, teléfono, que siguen remitidos al subsistema de envíos de ADR 0005).

**Qué dato es nuevo:** el iD ORCID es un identificador persistente de una persona identificada — dato personal sin duda razonable (RGPD art. 4(1); un identificador online puede serlo por el propio Considerando 30, y un iD de investigador lo es más directamente que una cookie). El nombre y la afiliación del autor ya se publicaban (son la autoría del artículo); lo nuevo es que ahora se **deposita también en infraestructura de terceros** (Crossref, y por su intermedio, ORCID vía auto-update) en lugar de vivir solo en el propio sitio.

**Base jurídica (orientación técnica; la conclusión final corresponde a la asesoría legal del propietario, como ya fijó ADR 0010 §4 y ADR 0011 §1):**

- Publicar nombre, afiliación e iD ORCID de un autor **junto a su propia obra** es una expectativa intrínseca y razonable de la relación de publicación académica: el autor se somete a arbitraje y publicación precisamente para ser públicamente atribuido y citable. Bases plausibles: **ejecución de un contrato** (art. 6(1)(b) — el acuerdo de publicación que el autor firma, `Solicitud de Publicación y Declaración de Ética` según `docs/03`) e **interés legítimo** (art. 6(1)(f) — la difusión y citabilidad académica, función propia de una revista).
- **A diferencia de ISSN/Depósito Legal**, que descansan en obligación legal venezolana (art. 6(1)(c) — trámite exigido por la Biblioteca Nacional), el registro DOI/ORCID no tiene un mandato legal equivalente: es una decisión editorial voluntaria, aunque estándar en la práctica académica internacional. Esta distinción importa para el aviso de privacidad: el texto no puede justificar DOI/ORCID como «obligación legal» sin más — necesita su propia base.
- El vehículo natural para el aviso/consentimiento explícito es el formulario ya existente **Solicitud de Publicación y Declaración de Ética** (`docs/03`, página `normas`): revisar su texto para que declare expresamente el depósito en Crossref y la posibilidad de auto-update en ORCID. Revisión de contenido, no de código; se registra como trabajo futuro, no se ejecuta en este ADR.

**Destinatarios y transferencia internacional:**

- **Crossref** (depósito de metadatos) y, si el autor lo autoriza una vez, **ORCID** (auto-update) pasan a ser **destinatarios** de datos de autores — ambas organizaciones sin ánimo de lucro con sede en EE.UU. Esto es una **transferencia internacional** bajo el capítulo V del RGPD.
  - **ORCID** no puede autocertificarse en el EU-US Data Privacy Framework (no está bajo jurisdicción de la FTC); usa **Cláusulas Contractuales Tipo (SCC)** como garantía, con evaluación anual frente a los requisitos del DPF (verificado 2026-07-29, ver Referencias).
  - El mecanismo de **Crossref** no se verificó en esta investigación — **queda como tarea de verificación** antes de redactar el texto definitivo del aviso (§ Trabajo futuro).
  - Ambos deben nombrarse como destinatarios en el aviso de privacidad, siguiendo el mismo criterio que ADR 0011 §7 ya aplicó a Google (destinatario del formulario de contacto vía Gmail).
- **Responsabilidad frente al autor:** los términos de membresía de Crossref obligan al miembro (CENFISS) a dar a los autores un medio de acceder, corregir o borrar sus datos depositados, y a comunicar esos cambios a Crossref. CENFISS sigue siendo responsable frente a sus autores aunque el dato también viva en Crossref/ORCID — no es una externalización de responsabilidad. Ver Decisión §2.1, punto 4 (designar quién lo gestiona).
- **Crossref y ORCID actúan como responsables independientes de su propio registro** (mantienen la infraestructura de identificadores persistentes con fines propios), no como simples encargados del tratamiento que solo siguen instrucciones de CENFISS. Esta caracterización orienta el aviso de privacidad; su calificación jurídica exacta queda para la asesoría legal.

**Tensión con el derecho de supresión (art. 17):**

- Una vez publicado un artículo académico con DOI depositado (y potencialmente citado por terceros), **no es razonable prometer borrado a petición** como si fuera un dato de analítica: el registro bibliográfico persistente es la función misma del DOI. El RGPD prevé esto: art. 17(3)(d) exceptúa el derecho de supresión cuando el tratamiento es necesario para fines de archivo de interés público o fines de investigación científica (art. 89), en la medida en que el derecho pudiera hacer imposible u obstaculizar gravemente esos fines.
- El aviso de privacidad debe ser honesto sobre esto: un autor puede pedir corrección de datos inexactos (afiliación, ORCID mal transcrito) y baja de su participación en trámites futuros, pero no una retirada retroactiva del registro académico ya depositado — eso se gestiona, si acaso, por los mecanismos propios de corrección/retracción académica, no por el art. 17. Redacción exacta: trabajo futuro, con asesoría legal.

**Disparador de revisión (coherente con ADR 0011 §5):** este ADR **activa** el disparador «se incorpore cualquier recurso de terceros» ya previsto por ADR 0011. Corresponde actualizar `page-politicas` §6 (confidencialidad editorial — ya cubre nombre, documento de identidad, teléfono, correo de autores/árbitros según ADR 0011 §6) para añadir: iD ORCID, Crossref y ORCID como destinatarios, la nota de transferencia internacional, y la limitación del derecho de supresión. **No** es `page-privacidad` (esa página cubre visitantes anónimos del sitio, no autores — tabla de ADR 0011 §4). Contenido, no código: se registra como trabajo futuro.

### 7. ¿Hacen falta cuentas de usuario para publicar? No, no todavía

Reafirmando la Precisión 3: nada de este ADR requiere que un autor tenga cuenta ni inicie sesión. El flujo actual es enteramente editor-céntrico: el equipo editorial crea la ficha de `author` (incluido su ORCID) al montar el número, igual que ya hace con el resto del contenido (ADR 0005 §4, «la primera edición llega pronto y la cargan los editores directamente»). El «Sign in with ORCID» de §1 es una mejora **futura y opcional**, pensada para cuando exista portal de autor con cuentas reales (ADR 0005 §4) — en ese momento, el autor podría verificar su propio iD en vez de que el editor lo transcriba; hasta entonces, no hace falta ninguna cuenta, de WordPress ni de ORCID, para publicar la primera edición.

### 8. Explícitamente fuera de alcance de este ADR

- **DataCite** como agencia alternativa: sin ruta de bajo coste identificada; Crossref es el estándar del tipo de contenido.
- **OJS (Open Journal Systems):** contradice ADR 0002; recambio de plataforma, no integración.
- **API de miembro de ORCID:** innecesaria, ver §4.
- **Complementos de pago de Crossref** (Similarity Check/iThenticate, Cited-by de pago): no se activan.
- **Indexación en LATINDEX, DOAJ, REDIB, Google Scholar:** deseable y en general gratuita, pero es un proceso de *visibilidad* distinto del DOI/ORCID; no se resuelve aquí.
- **Trámite de ISSN digital ante Biblioteca Nacional:** sigue bajo ADR 0004; este ADR solo fija la identidad del campo (§3).
- **Sistema de envíos de autores / portal de autor / cuentas de usuario:** sigue aplazado por ADR 0005 §4 (§7).
- **Redacción final del aviso de privacidad y de la Declaración de Ética:** contenido, no código; asesoría legal, no este ADR (§6).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------- |
| Tratar el coste de Crossref como «servicio de pago» sujeto a ADR 0005 (borrador inicial de este ADR) | El propietario precisó que es un coste editorial/legal de la revista, igual que ISSN/Depósito Legal, no un coste de software del sitio; ADR 0005 gobierna el theme y sus plugins, no los trámites editoriales de la publicación. |
| No hacer nada hasta tener ISSN/Depósito Legal digital resuelto | Son trámites independientes (Precisión 2); no hay dependencia formal entre ambos. |
| Confundir el ISSN digital pendiente con el ISSN impreso ya obtenido | Son identificadores distintos por norma internacional; declarar «ISSN resuelto» porque el de papel lo está sería inexacto y, para una revista académica, reputacionalmente costoso si se detecta. |
| DataCite como agencia principal | Sin ruta de bajo coste identificada; Crossref es el estándar para artículos de revista y ya tiene programa de Sponsors investigado. |
| Migrar a OJS (Open Journal Systems) | Resolvería DOI/ORCID «de fábrica», pero es un recambio de plataforma; contradice ADR 0002 y descartaría el trabajo ya invertido en el theme. |
| Unirse a ORCID como miembro (API de pago) ahora | Innecesario: API pública + auto-update vía Crossref cubren el caso de uso sin coste. |
| Exigir cuenta de usuario a cada autor para registrar su ORCID | Contradice ADR 0005 §4 (envíos aplazados); el editor puede transcribir el ORCID igual que el resto de metadatos de la primera edición. |
| Tratar el circuito editorial (ORCID/DOI incluidos) como enteramente fuera de alcance, remitiéndolo todo al subsistema de envíos | ADR 0011 §6 ya avisó de que ese circuito es el punto de mayor exposición del proyecto; aplazarlo por completo hasta el portal de autor dejaría la primera edición publicándose sin ningún análisis de privacidad sobre datos que sí se recogen ya. Se resuelve la porción activada por este ADR y se deja explícito lo que sigue pendiente (§6). |
| Prometer borrado de datos de autor a petición, sin matices, en el futuro aviso de privacidad | Contradice la función misma del DOI como registro persistente; el RGPD prevé la excepción (art. 17(3)(d), art. 89) precisamente para esto. Mejor ser honesto ahora que corregir una promesa incumplible después. |
| Implementar ORCID/DOI dentro de la Fase 3, junto con el resto del theme (borrador inicial de este ADR) | El propietario prefiere una **Fase 4 separada, posterior al lanzamiento de WordPress**, para no acoplar la salida a producción del sitio con la integración de identificadores; el trámite administrativo con Crossref (§2.1) sí puede adelantarse, sin esperar a ninguna de las dos fases. |
| Plugin «Researcher Profiles for ORCID» (`wordpress.org/plugins/researcher-profiles-for-orcid`) | 10+ instalaciones, 0 reseñas, sin valoración, desarrollador sin identificar — no cumple «muy usado» de ADR 0006 ni de lejos; introduce 5 tablas propias y sincronización en segundo plano para un perfil completo que no se pidió. |
| Plugin «Linked Open Profiles» (`wordpress.org/plugins/linked-open-profiles`) | 70+ instalaciones — más creíble por estar respaldado por el Mesh Research Lab (Michigan State University) y el ORCID Global Participation Fund, pero sigue muy por debajo de «muy usado» frente a lo ya adoptado en el proyecto (CF7, WP Statistics); solo funciona con bloques de Gutenberg, no con las plantillas PHP nativas del theme (`docs/12`); y, como el anterior, resuelve un perfil académico completo cuando lo pedido es reconocer la autoría de un artículo. Candidato a reconsiderar solo si el proyecto decide construir perfiles de autor enriquecidos como funcionalidad aparte — no para el alcance de este ADR. |

## Consecuencias

**Beneficios:**

- ORCID queda resuelto arquitectónicamente por completo ahora, sin coste, con código propio coherente con ADR 0005/0006; su implementación se ubica de forma explícita en la Fase 4, sin ambigüedad sobre cuándo ocurre.
- El registro DOI deja de estar bloqueado por una lectura estricta de ADR 0005: se alinea con cómo el proyecto ya trata ISSN/Depósito Legal. El trámite administrativo (§2.1) puede avanzar ya, sin esperar a la Fase 4; la maquinaria de código (§2.2) queda diseñada y lista para construirse en cuanto llegue esa fase.
- El coste real de Crossref queda documentado por adelantado (~210-220 USD/año estimados) y con una vía concreta para abaratarlo (Sponsors) antes de comprometer gasto.
- El acoplamiento DOI↔ORCID vía auto-update evita construir o pagar nunca la API de miembro de ORCID.
- La precisión impreso/digital del ISSN evita un error reputacional (declarar resuelto un identificador que no lo es).
- Se adelanta el análisis de privacidad del circuito editorial en la porción que ya aplica (ORCID/DOI), en vez de descubrirlo tarde al construir el portal de autor.
- Ningún identificador falso llega a producción (régimen de ADR 0004 intacto).

**Riesgos / costes:**

- Gasto editorial real y recurrente (~210-220 USD/año estimados) que el propietario debe presupuestar como línea de la revista, no del hosting.
- El generador de XML Crossref es código propio adicional que mantener en `revistalogos-core`, sin comunidad de plugin detrás.
- No se verificó el mecanismo de transferencia internacional de Crossref (a diferencia de ORCID, que sí quedó documentado); pendiente antes de redactar el aviso definitivo.
- CENFISS asume una obligación operativa continua: gestionar solicitudes de acceso/corrección/baja de datos de autores frente a Crossref (términos de membresía) — necesita responsable designado.
- El aviso de privacidad (`page-politicas` §6) queda desactualizado hasta que se revise (trabajo futuro, contenido no código); mientras tanto hay un desfase entre lo que el sitio hace y lo que declara.
- La cifra de coste de Crossref es una estimación; debe confirmarse contra el volumen real de artículos del primer número.
- No se identificó un sponsor concreto de Crossref para Venezuela/Latinoamérica en esta sesión.
- La validación de checksum ORCID debe implementarse correctamente (ISO 7064 MOD 11-2); un error de implementación aceptaría iDs inválidos silenciosamente.

**Trabajo futuro:**

*Ya, en paralelo — no dependen de que exista WordPress (§2.1, Precisión 4):*

- Investigar el directorio de Sponsors de Crossref para Venezuela/Latinoamérica y traer una recomendación con cifras al propietario.
- Confirmar el coste real de membresía con el volumen de artículos del primer número antes de pagar.
- Dar de alta la cuenta Crossref (directa o vía Sponsor), si el propietario decide no esperar a la Fase 4 para eso.
- Verificar el mecanismo de transferencia internacional de Crossref (DPF/SCC/otro) — falta este dato, ya se tiene el de ORCID.
- Designar quién en CENFISS gestiona las solicitudes de acceso/corrección/baja de datos de autor frente a Crossref.
- Revisar y actualizar `page-politicas` §6 (ORCID, Crossref/ORCID como destinatarios, transferencia internacional, límite del derecho de supresión) y la `Solicitud de Publicación y Declaración de Ética` — con asesoría legal, contenido no código.
- Tramitar el ISSN digital (e-ISSN) ante Biblioteca Nacional, en paralelo y sin depender del DOI (sigue bajo ADR 0004).
- Actualizar `docs/03-wordpress-content-model` con `orcid_url` y la precisión e-ISSN, con referencia a este ADR y a `docs/22`.

*Fase 4 — tras Fase 3 (WordPress construido), añadida por este ADR a `docs/17-implementation-order` (§2.2, Precisión 4):*

- Implementar en `revistalogos-core`: validación de formato/checksum de `author.orcid`, campo derivado `orcid_url`, enlace visible, `sameAs` en JSON-LD.
- Implementar el generador de XML de depósito Crossref, probarlo contra el dataset mockeado de ADR 0004.
- Con cuenta Crossref activa y sitio en producción: primer depósito real, backfill de `doi` en el primer número.
- Aplicar «DOI: en trámite» / «ISSN: en trámite» en el primer número real hasta que cada alta esté confirmada.

*Más allá de la Fase 4 — cuando exista portal de autor (ADR 0005 §4):*

- Añadir «Sign in with ORCID» y evaluar si conviene reabrir este ADR para el resto del circuito editorial (manuscritos, documento de identidad, teléfono).

## Referencias

- ADR 0002 (WordPress sin rediseño — descarta OJS)
- ADR 0004 (datos dummy excluidos de producción; régimen «en trámite»; Depósito Legal)
- ADR 0005 (presupuesto de software = solo hosting/dominio; código propio en `revistalogos-core`; envíos de autores aplazados, §4)
- ADR 0006 (política de dependencias de plugins de terceros)
- ADR 0010 (formulario de contacto; patrón de remitir la conclusión jurídica a asesoría legal)
- ADR 0011 (marco RGPD del proyecto; §6 señala el circuito editorial como punto de mayor exposición y fuera de su alcance; §7 destinatario Google como precedente)
- ADR 0012 (precedente del patrón «se resuelve ahora / se aplaza con precondiciones registradas»)
- `docs/03-wordpress-content-model` (campos `doi`, `doi_url`, `issn`, `orcid` ya reservados)
- `docs/17-implementation-order` (checklist de lanzamiento)
- `docs/22-identificadores-academicos-doi-orcid` (especificación operativa)
- `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` §5.6 (ORCID en la reseña curricular del autor)
- Crossref, «Fees» — <https://www.crossref.org/fees/> (consultado 2026-07-29)
- Crossref, «Sponsors program» — <https://www.crossref.org/community/sponsors/>, <https://www.crossref.org/membership/about-sponsors/>
- Crossref, «Current membership terms» — <https://www.crossref.org/membership/terms/> (obligación del miembro sobre datos personales de terceros)
- ORCID, «Small Publishers FAQ» — <https://info.orcid.org/small-publishers-faq>
- ORCID, «Public API» / «Member API» — <https://info.orcid.org/what-is-orcid/services/public-api/>, <https://info.orcid.org/what-is-orcid/services/member-api/>
- ORCID, «Auto-updates in third-party systems: Crossref» — <https://support.orcid.org/hc/en-us/articles/360006971293>
- ORCID, «Privacy Policy» (SCC como garantía de transferencia; evaluación anual frente al EU-US DPF) — <https://info.orcid.org/privacy-policy/>
- ORCID, «Structure of the ORCID Identifier» (checksum ISO 7064 MOD 11-2) — <https://support.orcid.org/hc/en-us/articles/360006897674>
- DataCite, «Fees» — <https://datacite.org/fees/>
- WordPress.org, «Researcher Profiles for ORCID» — <https://wordpress.org/plugins/researcher-profiles-for-orcid/> (consultado 2026-07-29; enlace aportado por el propietario)
- WordPress.org, «Linked Open Profiles» — <https://wordpress.org/plugins/linked-open-profiles/> (consultado 2026-07-29)
- RGPD arts. 4(1), 6(1)(b)(c)(f), 13, 17(3)(d), 89, capítulo V (arts. 44-49); Considerando 30

## Estado de implementación (2026-08-19)

Nota factual; **no** sustituye Precisión 4 ni el resto de decisiones de este ADR.

- WordPress clásico live en producción; `revistalogos-core` existe y está activo.
- Contenido editorial real en proceso de carga desde wp-admin, **no** completa.
- El código de validación ORCID/DOI y el generador Crossref siguen siendo Fase 4.
