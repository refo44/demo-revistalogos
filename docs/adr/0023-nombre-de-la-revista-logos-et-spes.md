# ADR 0023: Nombre de la revista: LOGOS ET SPES

## Estado

Aceptada

## Fecha

2026-11-08

## Contexto

El propietario corrigió el nombre de la revista: es **LOGOS ET SPES**, con S. El sitio mostraba los dos a la vez. El título del sitio en la base de producción ya decía `LOGOS ET SPES`, mientras el código, las citas, los metadatos y el contenido de las páginas decían `LOGO ET SPES`.

`content-source/`, la fuente de mayor prioridad, sigue con el nombre anterior. Esto es una contradicción explícita con esa fuente, no una desviación silenciosa, y el propietario decide cómo se resuelve.

## Decisión

1. **Formas del nombre.** `LOGOS ET SPES` en presentación, `Revista de Filosofía LOGOS ET SPES` como forma larga y `Logos et Spes` en texto corrido. `rLES` no cambia.
2. **Superficies que cambian ahora.** Theme, plugin, citas, metadatos académicos, `llms.txt`, wp-admin, pruebas, `static/` y documentación vigente. Prevalece sobre el nombre del theme citado en ADR 0015.
3. **Identificadores técnicos que no cambian ahora.** El dominio `logo-et-spes.cenfiss.net` (ADR 0016), las URLs, la cuenta FTPS y el nombre de archivo de la fuente canónica. `revistalogos*` ya lleva la S.
4. **Registros históricos.** Los ADR anteriores, las secciones publicadas de `CHANGELOG.md` y la evidencia de `docs/fase3-validation-matrix.md` quedan como se escribieron.
5. **`content-source/` y el payload.** No se tocan sin autorización expresa del propietario. `content-payload.json` se genera desde esa fuente y no se edita a mano, así que sigue con el nombre anterior hasta entonces.
6. **Migración completa, fuera de este alcance.** El cambio de dominio y URLs, las redirecciones y la reindexación necesitan un plan propio en una fase posterior (`docs/adr/BACKLOG.md`).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Cambiar también el dominio ahora | El sitio está indexado; exige redirecciones 301 y cambios de hosting. El propietario lo deja para una fase posterior. |
| Cambiar solo lo que ve el lector | Las citas y los metadatos seguirían registrando el nombre anterior en los indexadores. |
| Editar el payload a mano | El generador lo prohíbe, y regenerarlo devolvería el nombre anterior. |

## Consecuencias

El código nombra la revista de una sola forma, y `tests/Unit/JournalNameTest.php` impide que vuelva el nombre anterior.

El renombrado no termina con el despliegue. Quedan fuera de Git:

- El sello (`logo-revista.png`), la tarjeta social (`og-twitter-card.jpg`) y el favicon llevan el nombre en la imagen o derivan de ella, y necesitan arte nuevo.
- Unas 30 menciones en seis páginas y en la editorial del Vol. 1, en la base de producción.
- Los PDF de la Biblioteca de medios y las plantillas de correo de CF7.
- Los `citation_override_*` que un editor haya guardado con el nombre anterior.
- El trámite de ISSN.

El texto «Origen y Significado del Nombre» (Prof. Luis Felipe Ramírez) explica «Logo, del griego λόγος». Actualizarlo es una decisión de redacción de su autor, no un reemplazo de texto.

Un payload regenerado o una instalación limpia mostrarán el nombre anterior en las páginas institucionales hasta que se actualice `content-source/`.

## Referencias

- [ADR 0015](0015-block-theme-fse-site-editor.md)
- [ADR 0016](0016-topologia-hosting-cpanel.md)
- `docs/adr/BACKLOG.md`
