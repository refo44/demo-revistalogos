# Agent prompt — FSE conversion (next undone session only)

Paste this file as the task. Ignore Fase 3 classic history, WU0–WU12,
execution-state diaries, and already-shipped plugin/theme features.

You are implementing **ADR 0015** on the classic theme that is already live.
Do **not** rebuild WordPress. Do **not** redesign. Convert presentation to a
block theme **incrementally**: Docker (`http://localhost:8080`) first, then
its own noindex subdomain, `logos-et-spes.cenfiss.net` (ADR 0024). Production
`logo-et-spes.cenfiss.net` is never switched to FSE. Parent issue:
[#12](https://github.com/refo44/demo-revistalogos/issues/12).

## Binding sources (read these, nothing else for scope)

1. `docs/adr/0015-block-theme-fse-site-editor.md` — the spec (especially §7).
2. `docs/adr/0005-modelo-de-contenido-cpts-y-taxonomias.md` — domain stays in
   `revistalogos-core`; theme only assembles.
3. `docs/adr/0001-maqueta-estatica-como-base-definitiva.md` and
   `docs/adr/0002-wordpress-como-adaptacion-sin-rediseno.md` — no redesign.
4. `static/` — visual oracle (HTML + CSS) for Sessions 2–8. Compare against
   this, not memory. Session 9 and #103 compare the FSE subdomain against
   live production instead (ADR 0015, note 2026-11-23).
5. `docs/23-testing-foundation.md`, `docs/24-project-testing-standard.md`,
   ADR 0018 — TDD is mandatory.

Do not revive FABLE 5. Do not install Playwright, Behat, or extra runners.

## Two tracks (production stays classic)

`logo-et-spes.cenfiss.net` already runs the **classic** theme + plugin.
Editorial support continues there. FSE is Docker **and** its own noindex
subdomain, `logos-et-spes.cenfiss.net` (ADR 0024; ADR 0016 note 2026-11-18;
setup: [#74](https://github.com/refo44/demo-revistalogos/issues/74)). It is a
separate WordPress, it is permanent, and it has existed since 2026-11-20
(still empty and noindex). GitHub Pages
is the static mirror, not a WordPress FSE preview. Do not reuse
`cenfiss.net`, `test.cenfiss.net`, or the production FTP jail.

| Track | Where | Git | Deploy |
| ----- | ----- | --- | ------ |
| **Support** (bugs, editorial UX, plugin domain that production needs) | Production classic | Short `fix/` / `feat/` → PR → `main` → annotated tag → FTPS | Yes, when the owner tags (ADR 0020) |
| **FSE** (Sessions 2–9) | Docker `localhost:8080`, then `logos-et-spes.cenfiss.net` | Short `feat/fse-session-N-*` → PR into **`feat/fse`**, not into `main` | FSE subdomain only ([#75](https://github.com/refo44/demo-revistalogos/issues/75)). **Never** production FTPS from `feat/fse`. Never tag from it. |

`feat/fse` is a conversion branch for this work only. Rebase or merge `main`
into it whenever a production hotfix lands, so beta does not drift. This is
an exception to “every PR lands on `main`” (ADR 0019) until the final merge.
Do not invent `develop`. Do not create a second theme directory, in these
sessions or later: convert `revistalogos` on `feat/fse`. Production keeps the
live minor of that same theme and of `revistalogos-core`, and can still
ship later minors and patches of that line. The FSE line is a new major
of both, with breaking presentation changes. Production never runs that
major: it goes to the FSE subdomain only (ADR 0015, note 2026-11-02;
ADR 0024). There is no `revistalogos-classic`.

**Plugin:** production and FSE share `revistalogos-core`. A plugin change that
must go live (citation bug, PDF, authors) ships on `main` and is merged into
`feat/fse`. Domain **blocks** may live on `feat/fse` until the final merge.
Do not enqueue FSE block assets on the classic front. Do not turn on
`global-styles` / stop the dequeue on `main` — that would restyle the live
site.

**Do not** deploy the FSE major over the live theme or the live plugin.
**Do not** include FSE theme files in a minor or patch of the live line.
There is no in-place switch (ADR 0024). Production keeps the versions
already enabled. Theme and plugin each keep their directory and their name.

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
- Production FTPS is out of scope: the FSE major never goes to
  `logo-et-spes.cenfiss.net` (ADR 0020, ADR 0024). This prompt never
  dispatches a production deploy. The FSE subdomain has its own workflow
  and Environment, never `deploy-wordpress.yml`, `wordpress-production` or
  `PRODUCTION_*`. Its first deploy is [#84](https://github.com/refo44/demo-revistalogos/issues/84).
- FSE subdomain: `robots.txt` `Disallow: /` (no `Sitemap:`), Lectura
  `noindex`, not linked from production, GitHub Pages or `/llms.txt`.
  Its content is a copy of the production DB and uploads. Never
  `fixtures seed`. It is permanent, so [#85](https://github.com/refo44/demo-revistalogos/issues/85)
  no longer applies. Indexing stays closed until the owner decides.

## Conversion contract

ADR 0015, note «Contrato de conversión» (2026-10-19). These rules bind every
session:

- A template does not create a Page, an issue, an article, or an author.
  Read what WordPress already has. Do not re-run the institutional importer
  or `fixtures seed`.
- Prove a converted view with an incoming HTTP request to the canonical URL
  (trailing slash, ADR 0008). `get_permalink()` is not the proof.
- A green workflow or FTPS transfer proves the file copy. Visual parity is
  screenshots of `static/` against the WordPress URL, desktop and 320px
  (Sessions 2–8; Session 9 and #103 use live production, see below).
- One file per block family in `revistalogos-core`: `block.json` plus
  `render_callback`, loaded from `Plugin::load_modules`. The theme places
  the block and does not query CPTs. No data, or a missing domain helper:
  return an empty string. No broken labels. No fatal.
- `parts/header.html` and `parts/footer.html` may be a thin shell that
  renders that block. `Revistalogos_Nav_Walker` still emits `nav__*`.
- `theme.json` keeps the palette already declared. `tokens.css` aliases
  existing `--color-*` to `var(--wp--preset--color--*)`. Layout stays in
  `main.css`.
- Delete a `.php` view only after its `.html` gate is green.
- This conversion closes presentation and the behavior that template
  replaces. It does not reopen institutional content or the URL tree.
  Production Additional CSS ([#38](https://github.com/refo44/demo-revistalogos/issues/38))
  blocks [#84](https://github.com/refo44/demo-revistalogos/issues/84)
  only. Docker session 2 can start without it.

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

- `parts/header.html` and `parts/footer.html` may be a thin shell. The
  block's `render_callback` calls `Revistalogos_Nav_Walker` and keeps
  `header`, `header__*`, `nav`, `nav__*`, and the skip-link. Core
  Navigation does not replace that header.
- Footer: existing menu locations `footer-quick` / `footer-norms`. A
  missing menu uses the fallback already shipped. No fatal.
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

Each block: one file, `block.json` + `render_callback`. Empty data, or a
missing domain helper, returns an empty string: no broken labels, no
fatal. BEM `className` from the matching `template-parts/*.php`.
Theme templates call the blocks; they do not query CPTs.

Tests: `composer test:wp` on rendered HTML (classes, empty/full). Gherkin
in `tests/Features/` only if it is a business contract (it usually is not;
presentation).

### Session 5 — `front-page` + institutional pages

- `templates/front-page.html` then `templates/page.html` (shared
  institutional body via `the_content()`).
- Do not copy dummy Vol. 12 content. Live queries / existing Pages.
  The new HTML does not create those Pages.
- Prove `/` and one institutional URL with an incoming request (trailing
  slash). Visual vs `static/index.html` and one institutional page
  (`page-acerca` or `page-etica`).
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

### Session 9 — Visual gate (FSE subdomain vs production), then stop

The reference is live production, `logo-et-spes.cenfiss.net`, not
`static/` and not Docker (ADR 0015, note 2026-11-23). Screenshot the same
route on `logos-et-spes.cenfiss.net` and on production, desktop and
320px, for every screen now served by an HTML template (route list in the
`docs/fase3-validation-matrix.md` coverage table). List each difference.
The host in links and canonicals, and the subdomain's `noindex`, are not
differences; content added to production after the subdomain's copy is a
data difference, not a theme one. Mark rows with the method used. Do not
invent `Pass` without files ([#83](https://github.com/refo44/demo-revistalogos/issues/83)).

There is no production cutover (ADR 0024). Production
`logo-et-spes.cenfiss.net` keeps the live minor of `revistalogos` and
`revistalogos-core` enabled, and further minors and patches of that line
keep shipping from `main`. The FSE major goes to
`logos-et-spes.cenfiss.net` only; its first deploy is
[#84](https://github.com/refo44/demo-revistalogos/issues/84), which does
not wait on #103. There is no second theme directory (ADR 0015, note
2026-11-02).

[#103](https://github.com/refo44/demo-revistalogos/issues/103) still
blocks the final merge of `feat/fse` into `main` (ADR 0015, note
2026-11-07; ADR 0024 §7). Before it, the FSE subdomain gets a fresh copy
of the production database and the `uploads/` that content references
(ADR 0024 §6), plus a deploy of the `feat/fse` commit that will be merged.
Nothing is loaded into Docker (ADR 0015, note 2026-11-23). The same
routes are compared: live production against the subdomain. Any unresolved discrepancy blocks the
merge. The order is Session 9, then #103 on the `feat/fse` commit that
will be merged, then the merge. What `main` and the live line do after
that merge is an open owner decision (ADR 0024, point c). Sessions 2–9,
and minors of the live line, do not wait on this.

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
- [ ] Gate above is green in Docker (and on `logos-et-spes.cenfiss.net` when that session is deployed there)
- [ ] Visual screenshots for touched screens (desktop + 320px)
- [ ] No PHP deleted while HTML is incomplete
- [ ] Any screen whose template changed answers its canonical URL over HTTP
- [ ] Plugin version / theme `Version:` bumped **only** if that component’s
      code changed, and both plugin header places stay in sync
- [ ] `CHANGELOG.md` under `## [Sin publicar]` if code shipped
- [ ] Owner has the diff gallery; you did not declare visual `Pass` alone
