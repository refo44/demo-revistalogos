# Revista de Filosofía LOGO ET SPES — CLAUDE.md

Monorepo: a static HTML prototype (`static/`, Fase 2 — done, base visual
frozen) and WordPress (`wordpress/wp-content/{themes,plugins}/`, Fase 3 —
classic complete as of 0.2.0). WordPress clásico live en producción; carga
de contenido editorial real iniciada y actualmente en proceso desde wp-admin
(`https://logo-et-spes.cenfiss.net`, since 2026-08-19). FSE (ADR 0015) is
still the destination but did **not** block the cutover; implement it later,
Docker first. See
`docs/operations/produccion-wordpress.md`, `docs/17-implementation-order.md`,
and `docs/fase3-execution-state.md` (durable resume state — read it before
resuming Fase 3 work).

## Binding sources, in priority order

Tracked Git files are the durable source of truth. Local editor config is not.

1. **`content-source/`** — canonical content/text. Never modify wording;
   use exactly as written.
2. **`docs/`** — architecture, content model, IA, URL policy, implementation
   order, testing strategy (`docs/23-testing-foundation.md`), etc. Must not
   contradict content-source; if it does, content-source wins and the doc
   gets flagged for correction.
3. **`docs/adr/`** — binding decisions (numbered ADRs + `BACKLOG.md`).
   Accepted pending work is listed in `docs/adr/BACKLOG.md` (not `.cursor/`).
   Don't relitigate a resolved ADR; if new information contradicts one, raise
   it explicitly rather than silently deviating.
4. **This file (`CLAUDE.md`)** — agent-facing operational constraints. Must
   not contradict 1–3. Testing policy is **not** defined here in full: follow
   `docs/23-testing-foundation.md` and ADR 0018.

**`.cursor/` is gitignored by design.** Cursor rules a developer may keep
locally are optional convenience mirrors only: not authoritative, not
required, not shared via Git, not a substitute for the files above.

## Project-specific constraints that aren't obvious from the code

- **No native PHP/WP-CLI/Composer on the developer laptop; WordPress runs
  locally via Docker** (ADR 0014, `docker-compose.yml`: site at
  `localhost:8080`, image `wordpress:7.1.0-php8.3-apache`, WP-CLI via
  `docker compose run --rm wpcli wp <cmd>`). Changing that image tag does
  not update core files inside the `wp_data` volume — follow the comments
  in `docker-compose.yml`. Use the containers to actually execute/verify
  PHP instead of claiming something "works" from reading it. Local
  evidence is recorded as `Pass (local)` in `docs/fase3-validation-matrix.md`.
  The public host is **cPanel `cenfiss2`** (LiteSpeed), not a Hostinger
  control panel (ADR 0016). Docker is not deployed to that server. See
  `docs/fase3-execution-state.md` → Next exact action.
- **`content-source/` is gitignored** — it exists only in the local working
  tree, not in Git. Treat it as a local input, not a tracked dependency.
- **Dummy/demo data must never reach production** (ADR 0004): the Vol. 12
  Nº 2 issue, the six sample articles, fake news posts, `1234-5678`,
  `10.1234/les.*`, `0000-0000-*`, and demo paginations are excluded as
  production editorial truth and must not be imported as `fixtures seed`.
  **Exception (owner Option 2, 2026-08-19):** a Volume 1 editorial
  bootstrap (`wp revistalogos fixtures bootstrap`) may **adapt** selected
  maquette presentation fields (count, titles, abstracts, sections, order,
  placeholder cover/PDFs) into Vol. 1 Nº 1 objects so editors can replace
  them in wp-admin. It reuses Author `rafael-eduardo-figueredo-oropeza`,
  must not duplicate or mark that author, and uses `_les_bootstrap*` (not
  disposable `_les_fixture` teardown). No fake DOI/ORCID/ISSN, no dummy
  authors, no dummy bibliographic pagination. Do **not** run
  `wp revistalogos fixtures seed` on production. Production hosting has no
  usable SSH/WP-CLI path; the temporary Tools → Volume 1 Editorial Bootstrap
  (`Bootstrap_Admin`) was an execution bridge and was **removed in plugin
  0.2.6** after production bootstrap. Fixtures domain and CLI remain.
  Plugin 0.2.6 replaces author checkboxes with a searchable picker (core
  REST; no full catalog preload). 0.2.5 added the publish-requires-published-author
  rule and native Media Library PDF picker; it does **not** unpublish existing
  authorless bootstrap articles on load and does **not** force Classic
  Editor on `article`. Indexing must
  not open while public fixture records remain; prefer `_les_fixture=1`
  count 0.
- **Plugin owns the domain, theme owns presentation only** (ADR 0005) — no
  CPT/taxonomy/role registration in the theme, ever.
- **Two live deployments, deliberately asymmetric**:
  `logo-et-spes.cenfiss.net` (cPanel `cenfiss2`, **WordPress classic** since
  2026-08-19, PHP **8.3** via CloudLinux PHP Selector + Site Isolation,
  per-domain; `cenfiss.net` / `test.cenfiss.net` untouched; manual
  `workflow_dispatch` FTPS via `deploy_revista@…`,
  Environment `wordpress-production`) and GitHub Pages
  (`refo44.github.io/demo-revistalogos`, beta review mirror, auto-publishes
  on every push to `main` — this is intentional, not a bug). Never make the
  static-site deploy workflow auto-trigger on push; never suggest removing
  the Pages mirror without being asked. The legacy cPanel static workflow
  (`deploy.yml`, «Deploy to Hostinger») was **retired** after the WordPress
  cutover; do not recreate it. Same hosting also runs
  `cenfiss.net` (institutional WP + Moodle) and `test.cenfiss.net` (dead
  Laravel) — do not deploy the journal there. Do not import fixtures to
  production. A WordPress administrator is already assigned to editorial
  entry; do not document that account’s identity or credentials. Treat
  search-engine indexing as **closed until verified**; do not assume it is
  open. Opening it is an explicit owner launch decision, never an effect of
  deploy. Completing 100% of editorial content is **not** required. Before
  opening, run the launch gate in `docs/operations/produccion-wordpress.md`.
  Do not open indexing in documentation or deploy work. Editorial content
  entry is in progress, not complete.
- **FSE is accepted** (ADR 0015): block theme + Site Editor + brand colors
  in Global Styles. The live theme is still classic; convert incrementally
  in Docker after production QA. Do not convert to Next.js. ADR 0003 §1/§3
  are superseded for `theme.json` / block CSS dequeue; keep BEM and
  `main.css`.
- **Identifiers (DOI/ORCID/ISSN) are Fase 4, deliberately deferred** past
  the WordPress launch (ADR 0013). Fase 3 stores them as inert fields only —
  no validation, no derived URLs.
- **Automatic Article PDF on publish is accepted architecture (ADR 0017).
  Implementation complete locally (work units 1–6B).** Domain policy,
  read-only adapter, orchestrator, Dompdf renderer, Media Library
  persistence, source builder, explicit generator, and
  admin-configurable publication enforcement exist in the plugin.
  **Default enforcement is OFF** (missing option = OFF). Deploy or
  upgrade does **not** turn it ON. When OFF, `pdf_file` remains
  optional and publish with a valid Author and no PDF still succeeds.
  When ON, a valid stored or same-request manual PDF is preserved;
  a missing PDF is generated from the publication candidate and
  persisted; generation/persistence failure blocks publish.
  Classic and REST share `revistalogos_article_pdf_publication_enforcement`.
  Do not generate PDFs in the theme or during FSE conversion.
  Production activation remains a separate owner decision.
  Production PHP remains **8.3**. `setup-php` in CI/deploy configures
  only the GitHub Actions runner. Before a future deploy that ships
  plugin `vendor/`, verify `ext-dom` and `ext-mbstring` on that
  existing 8.3 runtime; do not change CloudLinux PHP Selector,
  MultiPHP, or the hosting PHP version.
- **Testing:** follow `docs/23-testing-foundation.md` and ADR 0018. New
  domain behavior uses TDD. For PHP changes, before completion: (1) syntax
  gate `./tools/php-lint.sh` or `composer lint:php`; (2) Composer lockfile
  audit `composer audit:deps` (`composer audit --locked`); (3) relevant
  PHPUnit (`composer test:unit` or `./tools/run-phpunit.sh`); (4) relevant
  `tools/qa-*.sh` if WordPress integration behavior changed. `composer test`
  runs lint, audit, then units — not acceptance harnesses. Root
  `composer.json` is test-only. Composer audit does not scan WordPress,
  npm, or hosting. Dependabot may open weekly Composer and GitHub Actions
  PRs; they are not auto-merged. Review them after CI. Do not treat a
  PHPUnit major as an automatic policy change.

## Working style

- This project runs on ADR-driven decisions with an explicit resume
  protocol (`docs/fase3-execution-state.md` → Resume procedure). When
  picking up Fase 3 work, follow that protocol rather than starting fresh
  from the chat.
- Keep `docs/fase3-execution-state.md` current when a work unit lands —
  it's the source of truth for "what's actually done," not git log alone.
- Small, reviewable commits per work unit (see the WU table in
  `docs/fase3-execution-state.md`), not one large commit spanning several.
