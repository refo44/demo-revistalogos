# Revista de Filosofía LOGO ET SPES — CLAUDE.md

Monorepo: a static HTML prototype (`static/`, Fase 2 — done, base visual
frozen) migrating to WordPress (`wordpress/wp-content/{themes,plugins}/`,
Fase 3 — in progress). See `docs/17-implementation-order.md` for the phase
plan and `docs/fase3-execution-state.md` for the current work unit — read
that file before resuming any Fase 3 work; it's the durable resume state.

## Binding sources, in priority order

1. **`content-source/`** — canonical content/text. Never modify wording;
   use exactly as written.
2. **`docs/`** — architecture, content model, IA, URL policy, implementation
   order, etc. Must not contradict content-source; if it does,
   content-source wins and the doc gets flagged for correction.
3. **`docs/adr/`** — binding decisions (numbered ADRs + `BACKLOG.md`). Don't
   relitigate a resolved ADR; if new information contradicts one, raise it
   explicitly rather than silently deviating.
4. **`.cursor/rules/*.mdc`** — read these; they carry the same weight as this
   file. In particular `php-wordpress-engineering-standards.mdc` governs all
   PHP in the plugin and theme (SOLID/KISS/YAGNI, WordPress style, escaping/
   sanitization, no-Composer, idempotency) — apply it to every PHP change,
   not just Fase 3 work.

## Project-specific constraints that aren't obvious from the code

- **No native PHP/WP-CLI/Composer on the developer laptop; WordPress runs
  locally via Docker** (ADR 0014, `docker-compose.yml`: site at
  `localhost:8080`, WP-CLI via `docker compose run --rm wpcli wp <cmd>`).
  Use the containers to actually execute/verify PHP instead of claiming
  something "works" from reading it. Local evidence is recorded as
  `Pass (local)` in `docs/fase3-validation-matrix.md`. The public host is
  **cPanel `cenfiss2`** (LiteSpeed), not a Hostinger control panel
  (ADR 0016). Docker is not deployed to that server. See
  `docs/fase3-execution-state.md` → Next exact action.
- **`content-source/` is gitignored** — it exists only in the local working
  tree, not in Git. Treat it as a local input, not a tracked dependency.
- **Dummy/demo data must never reach production** (ADR 0004): the Vol. 12
  Nº 2 issue, the six sample articles, fake news posts, `1234-5678`,
  `10.1234/les.*`, `0000-0000-*`, and demo paginations are explicitly
  forbidden in any WordPress content migration.
- **Plugin owns the domain, theme owns presentation only** (ADR 0005) — no
  CPT/taxonomy/role registration in the theme, ever.
- **Two live deployments, deliberately asymmetric**:
  `logo-et-spes.cenfiss.net` (cPanel `cenfiss2`, static dummy today, manual
  `workflow_dispatch` FTPS via `deploy_revista@…`) and GitHub Pages
  (`refo44.github.io/demo-revistalogos`, beta review mirror, auto-publishes
  on every push to `main` — this is intentional, not a bug). Never make the
  static-site deploy workflow auto-trigger on push; never suggest removing
  the Pages mirror without being asked. After WordPress is installed in
  that subdomain, never run `deploy.yml` (static HTML) against that folder
  (ADR 0016). Same hosting also runs `cenfiss.net` (institutional WP +
  Moodle) and `test.cenfiss.net` (dead Laravel) — do not deploy the journal
  there.
- **FSE is accepted** (ADR 0015): block theme + Site Editor + brand colors
  in Global Styles. Implement incrementally in Docker first. Do not convert
  to Next.js. ADR 0003 §1/§3 are superseded for `theme.json` / block CSS
  dequeue; keep BEM and `main.css`.
- **Identifiers (DOI/ORCID/ISSN) are Fase 4, deliberately deferred** past
  the WordPress launch (ADR 0013). Fase 3 stores them as inert fields only —
  no validation, no derived URLs.

## Working style

- This project runs on ADR-driven decisions with an explicit resume
  protocol (`docs/fase3-execution-state.md` → Resume procedure). When
  picking up Fase 3 work, follow that protocol rather than starting fresh
  from the chat.
- Keep `docs/fase3-execution-state.md` current when a work unit lands —
  it's the source of truth for "what's actually done," not git log alone.
- Small, reviewable commits per work unit (see the WU table in
  `docs/fase3-execution-state.md`), not one large commit spanning several.
