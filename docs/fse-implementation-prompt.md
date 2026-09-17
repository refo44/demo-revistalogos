# Agent prompt — FSE conversion (next undone session only)

Paste this file as the task. Ignore Fase 3 classic history, WU0–WU12,
execution-state diaries, and already-shipped plugin/theme features.

You are implementing **ADR 0015** on the classic theme that is already live.
Do **not** rebuild WordPress. Do **not** redesign. Convert presentation to a
block theme **incrementally**: Docker (`http://localhost:8080`) first, then a
throwaway noindex subdomain, then production cutover. Parent issue:
[#12](https://github.com/refo44/demo-revistalogos/issues/12).

## Binding sources (read these, nothing else for scope)

1. `docs/adr/0015-block-theme-fse-site-editor.md` — the spec (especially §7).
2. `docs/adr/0005-modelo-de-contenido-cpts-y-taxonomias.md` — domain stays in
   `revistalogos-core`; theme only assembles.
3. `docs/adr/0001-maquette-estatica-como-base-definitiva.md` and
   `docs/adr/0002-wordpress-como-adaptacion-sin-rediseno.md` — no redesign.
4. `static/` — visual oracle (HTML + CSS). Compare against this, not memory.
5. `docs/23-testing-foundation.md`, `docs/24-project-testing-standard.md`,
   ADR 0018 — TDD is mandatory.

Do not revive FABLE 5. Do not install Playwright, Behat, or extra runners.

## Two tracks (production stays classic)

`logo-et-spes.cenfiss.net` already runs the **classic** theme + plugin.
Editorial support continues there. FSE beta is Docker **and** a throwaway
noindex subdomain (ADR 0016 amendment: [#73](https://github.com/refo44/demo-revistalogos/issues/73),
host: [#74](https://github.com/refo44/demo-revistalogos/issues/74)). GitHub Pages
is the static mirror, not a WordPress FSE preview. Do not reuse
`cenfiss.net`, `test.cenfiss.net`, or the production FTP jail.

| Track | Where | Git | Deploy |
| ----- | ----- | --- | ------ |
| **Support** (bugs, editorial UX, plugin domain that production needs) | Production classic | Short `fix/` / `feat/` → PR → `main` → annotated tag → FTPS | Yes, when the owner tags (ADR 0020) |
| **FSE beta** (Sessions 2–9) | Docker `localhost:8080`, then tmp host | Short `feat/fse-session-N-*` → PR into **`feat/fse`**, not into `main` | Tmp host only ([#75](https://github.com/refo44/demo-revistalogos/issues/75)). **Never** production FTPS from `feat/fse`. Never tag from it. |

`feat/fse` is a conversion branch for this work only. Rebase or merge `main`
into it whenever a production hotfix lands, so beta does not drift. This is
an exception to “every PR lands on `main`” (ADR 0019) until cutover. Do not
invent `develop`. Do not invent a second theme directory: convert
`revistalogos` on `feat/fse`; production keeps serving the copy last tagged
from `main`.

**Plugin:** production and FSE share `revistalogos-core`. A plugin change that
must go live (citation bug, PDF, authors) ships on `main` and is merged into
`feat/fse`. Domain **blocks** may live on `feat/fse` until cutover. Do not
enqueue FSE block assets on the classic front. Do not turn on
`global-styles` / stop the dequeue on `main` until cutover — that would
restyle the live site.

**Do not** switch Apariencia → Temas on production. **Do not** include FSE
theme files in a hotfix tag. Cutover is a later owner action: merge
`feat/fse` → `main`, tag, FTPS, then confirm Site Editor on the live host.

## Hard rules

- One **session** per PR. Stop when that session’s gate is green.
- Branch from **`feat/fse`** (create it from current `main` if missing):
  `feat/fse-session-N-short-name`. PR target is `feat/fse`, not `main`.
- Do not implement on `main`. Do not commit, push, merge, or deploy unless
  the owner asks.
- Do not delete a `.php` template the same day its `.html` equivalent is
  incomplete. PHP remains until the HTML is done.
- `main.css` + BEM stay. Blocks get maquette `className`. Do **not** restyle
  the site in `theme.json`. Do **not** replace the header with core
  Navigation until it emits `nav__*` (it does not today — keep the walker).
- Plugin owns domain blocks (`render_callback`). Theme templates only
  compose. No CPT/taxonomy/meta registration in the theme.
- FSE does **not** generate PDFs. Templates consume `pdf_file`.
- Managing Editor does **not** get `edit_theme_options`.
- `settings.color.custom` stays false. Do not unlock typography or spacing
  in Estilos.
- Production FTPS is out of scope until cutover ([#84](https://github.com/refo44/demo-revistalogos/issues/84),
  ADR 0020). This prompt never dispatches production deploy. Tmp-host FTPS
  is a separate workflow, never `deploy-wordpress.yml`.
- Tmp host: `robots.txt` `Disallow: /` (no `Sitemap:`), Lectura `noindex`,
  not linked from production. Delete the subdomain after cutover ([#85](https://github.com/refo44/demo-revistalogos/issues/85)).

## TDD

RED → GREEN → REFACTOR. Write the failing test first.

| Change | Lock |
| ------ | ---- |
| `theme.json` / enqueue / `wp_is_block_theme()` | `tests/WordPress/` via `composer test:wp` |
| Domain block `render_callback` (markup, empty data, BEM classes) | `tests/WordPress/` |
| Site Editor HTTP / admin | one `tools/qa-fse-*.sh` (isolated Docker, `down -v`) |
| Visual | browser: `static/<page>.html` vs matching WP URL; desktop + 320px screenshots; list concrete diffs; patch; re-screenshot. Owner signs the gallery. |

No structure-sensitive mocks of WordPress. No `assertTrue(true)`.

## Visual parity (agent does the work)

Parity is **visual + BEM classes**, not CSS checksums.

For every screen this session touches:

1. Screenshot the static file and the WP URL (desktop and 320px).
2. Report diffs: overlap, missing region, nav classes gone, extra block
   margin, overflow, type scale, skip-link missing.
3. Fix. Re-screenshot.
4. Leave a short gallery for the owner. Do not ask them to hunt pixels.

Acceptable: `wp-block-*` wrappers if BEM layout still holds.
Not acceptable: redesigned nav, lost `nav__*` / `header__*` /
`issue-card` / `single-article__*`, Global Styles restating layout.

## Sessions — do the first one whose gate is not met

Start at the top. Skip a session only if its gate already holds in Docker
**and** in Git. Default: **Session 2** (paper ADR is done; nothing FSE is
implemented: no `templates/`, dequeue of `global-styles` still on).

### Session 2 — Bootstrap (do this first)

**Code**

- Keep `theme.json` palette; add semantic slugs already listed (primary,
  text, link, fondos). Do not invent a second palette.
- `tokens.css`: alias existing `--color-*` to
  `var(--wp--preset--color--<slug>)` so Estilos → Colores moves the front.
- Add `templates/index.html` (required for Site Editor). It may be a thin
  shell; PHP templates stay for all real views.
- Stop dequeue of `global-styles`. Stop dequeue of `wp-block-library` only
  when public markup actually emits blocks (this session: if index is the
  only HTML, dequeue removal of `global-styles` is the minimum).
- Do not convert header, home, or singles yet.

**Gate**

- `wp_is_block_theme()` true in Docker.
- Apariencia → Editor opens (`/wp-admin/site-editor.php`).
- Change **primary** in Estilos; `localhost:8080` shows it on a surface
  that uses `--color-primary` / the alias.
- Classic PHP views still render (home, one issue, one article). No fatal.
- `composer test:wp` for the block-theme / enqueue contract.
- Browser: home vs `static/index.html` at desktop + 320px. Header still
  `nav__*`.

**Stop.** Do not start Session 3 in the same PR.

### Session 3 — Header and footer parts

- `parts/header.html` and `parts/footer.html`.
- Markup must keep `header`, `header__*`, `nav`, `nav__*`, skip-link.
- Reuse `Revistalogos_Nav_Walker` (via a plugin/theme block or
  `render_callback`), **not** `core/navigation`.
- Footer: existing menu locations `footer-quick` / `footer-norms`.
- Visual: header+footer vs static partials, desktop + 320px, 200% zoom on
  the CTA/nav toggle.

### Session 4 — Domain blocks in `revistalogos-core`

Register only what templates will need. One class/file per block family,
wired from `Plugin::load_modules`. Suggested first set (cut if unused this
PR):

- current issue
- issue card / article card / author card
- issue TOC
- article metadata box
- citation section (consume existing `inc/citations.php` behaviour; do not
  reimplement Cómo Citar)

Each block: `block.json` + `render_callback`. Empty data → no broken
labels. BEM `className` from the matching `template-parts/*.php`.
Theme templates call the blocks; they do not query CPTs.

Tests: `composer test:wp` on rendered HTML (classes, empty/full). Gherkin
in `tests/Features/` only if it is a business contract (it usually is not;
presentation).

### Session 5 — `front-page` + institutional pages

- `templates/front-page.html` then `templates/page.html` (shared
  institutional body via `the_content()`).
- Do not copy dummy Vol. 12 content. Live queries / existing Pages.
- Visual vs `static/index.html` and one institutional page (`page-acerca`
  or `page-etica`).
- Delete the PHP equivalent only after the HTML gate is green.

### Session 6 — Archives (`issue`, `article`, `author`, taxonomies, search, 404, news)

Same pattern: HTML template composes cards; PHP goes last.
Visual: one archive vs its `static/archive-*.html`.

### Session 7 — `single-issue`

TOC, stats (omit when 0), PDF CTAs of the issue, editorial. Visual vs
`static/single-issue.html`.

### Session 8 — `single-article` (hardest; last)

Header, metadata, abstracts, keywords, `the_content()`, citations,
article PDF CTA. Keep `citation.js` enqueue. Highwire/JSON-LD/OG stay
(theme `inc/metadata-output.php` or equivalent hook — do not drop).
Visual vs `static/single-article.html` desktop + 320px. Owner gallery
required.

### Session 9 — Visual gate (Docker + tmp host), then stop

Full pair list in `docs/fase3-validation-matrix.md` coverage table, but
**only** the screens now served by HTML templates. Mark those rows with
the method used (screenshots). Do not invent `Pass` without files. Repeat
the same pass on the throwaway subdomain ([#83](https://github.com/refo44/demo-revistalogos/issues/83)).

Production cutover is **not** this prompt. Owner, later: merge `feat/fse`
→ `main`, annotated `vX.Y.Z`, `workflow_dispatch` from that tag (ADR 0020)
after [#38](https://github.com/refo44/demo-revistalogos/issues/38) and [#83](https://github.com/refo44/demo-revistalogos/issues/83)
([#84](https://github.com/refo44/demo-revistalogos/issues/84)). Until then
production stays on the last classic tag.

## Out of scope (never in these sessions)

- Next.js / headless
- PDF generation, WU7 Generate/Regenerate, PDF de número
- Fase 4 DOI/ORCID validation
- CPT `submission` / author portal
- Unlocking custom colors per block
- Rewriting `tokens.css` architecture
- Importing `fixtures seed` to production
- Opening indexing
- Editing `content-source/` wording
- A new master prompt that repeats this file

## Done checklist (every session)

- [ ] Failing test existed before the production change
- [ ] Gate above is green in Docker (and on the tmp host when that session is deployed there)
- [ ] Visual screenshots for touched screens (desktop + 320px)
- [ ] No PHP deleted while HTML is incomplete
- [ ] Plugin version / theme `Version:` bumped **only** if that component’s
      code changed, and both plugin header places stay in sync
- [ ] `CHANGELOG.md` under `## [Sin publicar]` if code shipped
- [ ] Owner has the diff gallery; you did not declare visual `Pass` alone
