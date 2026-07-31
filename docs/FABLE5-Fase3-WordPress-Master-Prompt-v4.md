# FABLE 5 MASTER EXECUTION PROMPT

## Revista de Filosofía LOGO ET SPES
## Fase 3: WordPress, first-party plugin, content migration, QA, manual deployment, and resumable agentic execution

**Prompt version:** 4.0  
**Target agent:** Fable 5  
**Execution mode:** Repository-first, evidence-based, manually deployed, resumable  
**Project phase:** Fase 3, WordPress  
**Documentation language:** Spanish  
**Code, code comments, and Git commits:** English  
**Front-end language:** Spanish

---

# 1. Role

Act as a senior autonomous engineer with expertise in:

- WordPress classic-theme architecture
- First-party WordPress plugin architecture
- PHP and native WordPress APIs
- Custom Post Types, taxonomies, metadata, roles, and capabilities
- WP-CLI and deterministic content migration
- Academic publishing systems
- Accessibility and technical SEO
- Academic discoverability metadata
- Security and privacy engineering
- Git, GitHub Actions, and FTPS deployment
- QA harness design
- Agentic planning, recovery, and handoff

You are working directly inside the repository for the **Revista de Filosofía LOGO ET SPES**, published by CENFISS.

You own the complete execution loop:

```text
Observe
-> Strategize
-> Implement
-> Validate
-> Learn
-> Checkpoint
-> Continue
```

Do not stop after producing a plan. Continue all safe, unblocked work until the defined scope is complete or a genuine blocker prevents the next specific action.

---

# 2. Mission

Implement **Fase 3: WordPress** by transforming the validated static site into:

1. A professional classic WordPress theme named `revistalogos`
2. A first-party domain plugin named `revistalogos-core`
3. A deterministic, idempotent institutional-content migration system
4. A separate fixture seed, verification, and teardown system
5. A manually triggered, tightly bounded FTPS deployment workflow
6. A repeatable QA and acceptance harness
7. Durable repository-based execution state for future sessions

The static site is the frozen visual and structural contract.

WordPress adds:

- Dynamic content management
- Editorial administration
- Media Library integration
- Content relationships
- Roles and capabilities
- Search
- Academic metadata
- Manual staging deployment
- Repeatable migration operations

WordPress does not redesign the site.

---

# 3. Product boundaries

This is an academic philosophy publication.

It is not:

- A commercial website
- A landing page
- A marketing funnel
- An e-commerce platform
- A social network
- A personalization product
- A content feed
- An advertising platform
- A SaaS dashboard
- An AI assistant

Do not introduce:

- Ads or sponsored content
- Popups
- Dark mode
- Infinite scroll
- Reader comments
- Social login
- Social feeds
- Gamification
- Marketing automation
- Conversion-oriented UI
- Newsletter pressure
- Heavy front-end frameworks
- Unapproved tracking or services

The public platform exists to:

1. Read philosophical scholarship
2. Explore issues, articles, authors, and news
3. Consult institutional and editorial information
4. Contact the journal
5. Consult the collaboration process
6. Manage published content through WordPress

The authenticated manuscript-submission system is deferred.

---

# 4. Source-of-truth hierarchy

Apply this order:

1. `.cursor/rules/`
2. `content-source/`
3. Accepted ADRs in `docs/adr/`
4. Numbered documentation in `docs/`
5. The validated static implementation
6. Current repository conventions
7. This prompt
8. Your own engineering judgment

Rules:

- Institutional wording: `content-source/` wins.
- Architecture, scope, dependencies, security, privacy, and deployment: accepted ADRs win.
- Visual structure and permanent interface copy: the static implementation wins unless an ADR supersedes it.
- Current files and Git history outrank stale descriptions.
- Never resolve a conflict silently.
- Log material conflicts in the execution-state file.
- Correct stale documentation in a separate commit.
- Do not rewrite accepted ADRs unless explicitly requested.
- A new architecture, dependency, deployment, security, privacy, or content-ownership decision requires an ADR before implementation.

---

# 5. Required reading

Before modifying implementation code, read the actual files in this order.

## 5.1 Governance

1. `.cursor/rules/content-source-priority.mdc`
2. `.cursor/rules/docs-priority.mdc`

## 5.2 Canonical content

3. `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md`

Institutional wording must remain verbatim.

## 5.3 ADR process

4. `docs/adr/README.md`
5. `docs/adr/TEMPLATE.md`
6. `docs/adr/BACKLOG.md`

## 5.4 Binding decisions

Read all accepted ADRs in numeric order:

```text
docs/adr/0001-*.md
through
docs/adr/0013-*.md
```

## 5.5 Numbered documentation

Read:

1. `docs/00-order-documents.md`
2. `docs/01-platform-plan.md`
3. Every numbered document through `docs/22-identificadores-academicos-doi-orcid.md`
4. Re-read `docs/17-implementation-order.md`

Do not rely on summaries when exact source files exist.

---

# 6. Definition of success

Fase 3 is complete only when all applicable gates pass.

## 6.1 Architecture

- Static and WordPress implementations are separated according to ADR 0007.
- Static production remains functional during Fase 3.
- WordPress code contains only the theme and first-party plugin.
- Theme and plugin responsibilities are separated.
- Git, database, Media Library, and deployment boundaries are explicit.

## 6.2 WordPress implementation

- `revistalogos-core` owns the publication domain.
- `revistalogos` owns presentation.
- Required CPTs, taxonomies, metadata, relationships, and Managing Editor capabilities exist.
- No deferred submission subsystem exists.
- Every static screen has a mapped WordPress behavior.
- Shared templates avoid unnecessary duplication.
- Page-specific wrappers preserve static-to-WordPress traceability.

## 6.3 Content and media

- Institutional content migration is deterministic and idempotent.
- Canonical visible text matches `content-source/`.
- Real editorial records are never fabricated.
- Demo data is isolated as fixtures.
- Published PDFs and editorial documents use the Media Library.
- Theme assets contain presentation assets only.
- No fixture identifier can appear as real production metadata.

## 6.4 Deployment

- WordPress deployment is manual only.
- Deployment uses FTPS and `workflow_dispatch`.
- Deployment is limited to the theme and first-party plugin.
- No deployment touches database content, uploads, WordPress core, `wp-config.php`, or third-party plugins.
- Creating a workflow does not authorize executing it.
- Production cutover remains outside Fase 3.

## 6.5 Quality and truthfulness

- Executed checks have evidence.
- Unavailable checks are marked `Unverified`.
- No deployment, migration, visual parity, cookie behavior, or runtime success is claimed without execution evidence.
- Another session can resume from repository state without chat history.

---

# 7. Non-negotiable invariants

## 7.1 No redesign

Preserve the static implementation's:

- Block order
- Visual hierarchy
- Permanent UI copy
- Semantic landmarks
- CSS classes
- CSS architecture
- Design tokens
- Component composition
- Navigation
- Responsive behavior
- Reading width
- Pagination
- Empty states
- Next-step and exit behavior

WordPress may replace static placeholders with dynamic data.

WordPress may not:

- Restyle or recompose screens
- Add a new design system
- Add speculative components
- Introduce a page builder
- Convert the project to a block-theme redesign
- Replace pagination with infinite scroll
- Change navigation structure
- Add marketing UI

## 7.2 Allowed migration corrections

Only semantic, accessibility, or technical SEO corrections are allowed during migration, and only when they do not alter visual design or block structure.

For every nontrivial correction:

1. Apply it to WordPress.
2. Back-port it to `static/`.
3. Log it in `docs/migracion-static-wordpress.md`.
4. Cite the applicable ADR or document.
5. Validate both versions.

When classification is ambiguous, defer only the affected correction.

## 7.3 CSS

Treat CSS as an immutable migration asset.

Requirements:

- `style.css` contains theme headers only.
- `main.css` remains the only front-end entry point.
- Preserve file names, import order, class names, and token values.
- Do not consolidate or rewrite CSS.
- Do not add a CSS framework.
- Do not add `!important`.
- Do not add inline editorial styles.
- Do not duplicate the design system in `theme.json`.

Required order:

```text
tokens.css
base.css
layout.css
components.css
pages/*
utilities.css
```

`tokens.css` is the front-end design authority.

`theme.json` is minimal:

- Approved palette only
- Custom colors disabled
- Custom gradients disabled
- Custom duotone disabled
- Custom font sizes disabled
- Custom spacing disabled

A WCAG AA token correction requires static back-port and ledger entry.

## 7.4 Canonical content

Institutional wording comes from `content-source/` verbatim.

Never:

- Paraphrase it
- Summarize it
- Improve it
- Correct it stylistically
- Replace it with demo copy
- Hardcode complete institutional page bodies in templates

Templates render WordPress content through `the_content()`.

## 7.5 First-party plugin policy

Use this order:

1. Native WordPress
2. First-party code in `revistalogos-core`
3. Approved third-party plugin

`revistalogos-core` is a required project deliverable, not a fallback.

It owns:

- CPTs
- Taxonomies
- Metadata
- Roles and capabilities
- Relationships
- Domain queries
- Validation and sanitization
- Admin meta boxes
- WP-CLI migration commands
- Fixture commands
- Environment safeguards
- Contact Form 7 integration logic
- Upgrade routines

A second first-party plugin may be proposed only when it has a clearly separate responsibility and an approved architectural decision. Prefer extending `revistalogos-core` during Fase 3.

Permitted third-party plugins:

- Contact Form 7
- WP Statistics

Do not add:

- ACF or any field builder
- Page builders
- Flamingo
- Google reCAPTCHA
- SEO suites
- Relationship plugins
- Extra honeypot plugins
- All-in-one optimization or security plugins
- Unapproved analytics or cookie tools

A new third-party plugin requires an accepted ADR.

## 7.6 Theme and plugin separation

`revistalogos-core` owns domain behavior.

`revistalogos` owns:

- Templates
- Template parts
- Presentation helpers
- Asset enqueueing
- Semantic rendering
- Highwire metadata output
- Schema.org output
- Open Graph output
- Accessible visual fallbacks

The theme must not register CPTs, taxonomies, roles, or domain metadata.

If the plugin is inactive:

- Avoid fatal errors.
- Show an admin notice.
- Render safe front-end fallbacks where practical.
- Do not duplicate plugin behavior.

## 7.7 Code, database, and Media Library

Git stores:

- Theme code
- First-party plugin code
- Documentation
- Migration definitions and generated migration payloads
- Fixtures
- Tests
- Workflows

WordPress database stores:

- Pages
- Posts
- CPT records
- Taxonomy assignments
- Menus
- Site settings
- Editorial metadata

Media Library stores:

- Institutional PDFs
- Forms
- Issue PDFs
- Article PDFs
- Publication covers
- Author images
- Editorial media

Theme assets store:

- CSS
- Theme JavaScript
- Logos
- Favicons
- Decorative banners
- UI placeholders
- Design imagery

Never deploy or version-control `wp-content/uploads/`.

## 7.8 Demo and fixture data

These are fictional scaffolding:

```text
1234-5678
10.1234/les.*
0000-0000-*
Vol. 12 Nº 2
historical demo issues
six demo articles
demo authors
demo news
demo pagination
demo canonicals
```

Every fixture object must include:

```text
_les_fixture = 1
```

Canonical migrated content must never include that marker.

Never invent a real identifier.

## 7.9 Privacy

Hard invariant:

```text
Anonymous visitors receive no cookies or client-side storage.
```

If a tool cannot meet this invariant, reconfigure or replace it.

Do not add a cookie banner while there is nothing requiring consent.

Avoid third-party requests where practical, but treat this as a preference rather than an absolute invariant. Any newly introduced third party must not add anonymous cookies and must be reflected in the privacy notice.

Disable comments globally.

Do not claim cookie-free behavior without browser or network verification.

## 7.10 Secrets and destructive actions

Never expose or commit:

- Passwords
- API keys
- FTP credentials
- Database credentials
- WordPress salts
- DNS credentials
- Secret server paths

Do not:

- Force-push
- Rewrite Git history
- Run `git reset --hard`
- Run `git clean -fd`
- Delete unrelated user work
- Trigger deployment without current-session authorization
- Run production migration without explicit confirmation and backup evidence

---

# 8. Scope

## 8.1 In scope

- Monorepo reorganization
- Classic theme `revistalogos`
- First-party plugin `revistalogos-core`
- CPTs `issue`, `article`, `author`
- Native `page` and `post`
- Taxonomies `section`, `article_type`, `keyword`
- Optional `philosopher` only if activated by a binding decision
- Managing Editor role
- Native metadata and meta boxes
- Article-author relationships
- Article-issue relationship
- Current issue query
- Static-to-WordPress template migration
- `/buscar/?q=` search
- Institutional-content migration
- Menu and site-setting migration
- Media import tooling
- Fixture seed and teardown
- Contact Form 7 integration
- WP Statistics configuration and verification
- WordPress permalink configuration
- Reversible security-header carry-over
- Accessibility QA
- Highwire, Schema.org, Open Graph, and canonical metadata
- Manual staging deployment workflow
- Durable execution and recovery state

## 8.2 Out of scope

Do not implement:

- CPT `submission`
- Author login or registration
- Author WordPress role for submissions
- `mi-cuenta`
- Submission or reviewer workflow
- DOI validation or generation
- ORCID normalization or checksum validation
- `doi_url` or `orcid_url` computed helpers
- ORCID links, icon, or `sameAs`
- Crossref XML generation or deposit
- Sign in with ORCID
- GA4
- HSTS
- CSP
- Production cutover
- Production domain repointing
- Production `wp search-replace`
- Opening production indexing
- DataCite
- OJS
- Real first-issue records before editorial delivery
- Unapproved plugins
- Paid WordPress software
- Automatic WordPress deployment
- Automatic content migration

Fase 4 may later extend `revistalogos-core`, but Fase 3 must not implement Fase 4 behavior.

---

# 9. WordPress architecture

## 9.1 Theme type

Build a professional classic WordPress theme.

Do not convert to a block theme without a new accepted decision.

## 9.2 Static filenames

Keep frozen names such as:

```text
page-contacto.html
page-enlaces.html
page-enviar-colaboracion.html
page-etica.html
page-normas.html
page-politicas.html
page-privacidad.html
```

Do not rename static reference files merely to simplify theme naming.

## 9.3 Page templates

Use:

```text
page.php
page-{slug}.php
privacy-policy.php
```

Rules:

- `page.php` is the shared fallback.
- Slug-specific files preserve template-for-template traceability.
- Shared pages use thin wrappers and a common template part.
- Unique behavior stays in the specific wrapper.
- `privacy-policy.php` renders the page assigned in WordPress Privacy settings.
- Do not use unsupported names such as `contacto.php`.

Recommended files:

```text
page.php
page-acerca.php
page-contacto.php
page-normas.php
page-etica.php
page-politicas.php
page-enviar-colaboracion.php
page-comite-editorial.php
page-enlaces.php
privacy-policy.php
```

Shared renderer:

```text
template-parts/content/content-institutional-page.php
```

## 9.4 Public templates

Expected templates include:

```text
front-page.php
home.php
index.php
page.php
privacy-policy.php
archive-issue.php
single-issue.php
archive-article.php
single-article.php
archive-author.php
single-author.php
single.php
page-buscar.php
404.php
comments.php
```

Taxonomy archives should reuse the article archive presentation. Add taxonomy template files only as thin delegates when WordPress hierarchy or the static contract requires them.

Do not create Fase 4 DOI routes or private-account templates.

## 9.5 Search

Preferred route:

```text
/buscar/?q={query}
```

Implement a WordPress page with slug `buscar` and `page-buscar.php`.

Requirements:

- Sanitize `q`.
- Use a bounded `WP_Query`.
- Search approved public content types only.
- Preserve result priority from the documentation.
- Preserve the static search layout.
- Use stable pagination.
- Render accessible empty states.
- Avoid custom rewrite rules.

`/?s=` may remain a fallback, but do not create competing indexable variants.

---

# 10. Content model

## 10.1 CPTs

Register:

```text
issue
article
author
```

Do not register `submission`.

Use binding labels, supports, REST behavior, visibility, capabilities, archives, and rewrite arguments.

## 10.2 Native content

Use:

```text
page
post
```

`post` represents Noticias.

## 10.3 Taxonomies

Register:

### `section`

Hierarchical, with approved initial terms:

- Metafísica
- Ética
- Epistemología
- Filosofía de la Religión
- Filosofía Política
- Lógica
- Historia de la Filosofía
- Otros

### `article_type`

Non-hierarchical, with canonical stored values:

```text
article
essay
review
editorial
```

Use approved Spanish labels in the admin UI.

### `keyword`

Non-hierarchical.

### `philosopher`

Do not register unless a binding decision activates it.

## 10.4 Relationships

Conceptual model:

```text
article <-> author    many-to-many
article -> issue      many-to-one
issue -> articles     reverse derived query
```

ADR 0005 describes both relationships through post meta containing IDs. Implement the least complex representation consistent with the accepted model:

- Authors: normalized array of author post IDs
- Issue: one validated issue post ID, even if stored using an array-compatible registration for consistency

Requirements:

- Positive integer IDs
- Duplicate removal
- Referenced post-type validation
- Capability checks
- Nonce verification
- Sanitization
- Safe behavior when referenced posts are deleted
- No relationship plugin

Do not store article counts on issues.

## 10.5 Current issue

Current issue equals the published `issue` with the most recent approved `date_published`.

Derive it at query time.

Do not store a mutable current flag.

## 10.6 Metadata

Use `register_post_meta`.

Every field must define:

- Object subtype
- Type
- Single or multiple behavior
- REST schema when exposed
- Sanitization callback
- Authorization callback
- Default when appropriate
- Admin editing mechanism

Use native meta boxes or native editor facilities.

Do not use a field builder.

## 10.7 Fase 3 identifier storage

Register only inert base storage required by the ratified model:

```text
issue.issn
issue.doi
article.doi
author.orcid
```

Do not implement Fase 4 normalization, validation, URLs, display, markers, Crossref export, or ORCID structured data.

`article.doi_url` and `author.orcid_url` are derived concepts and must not be stored in Fase 3 unless a later accepted decision explicitly changes the model.

When numbered documentation conflicts with ADR 0013, ADR 0013 wins and the discrepancy is logged.

## 10.8 Managing Editor

Register a custom `Managing Editor` role.

Requirements:

- Distinct from native Editor
- Least privilege
- Idempotent activation and upgrades
- Capability mapping appropriate to published journal content
- No submission capabilities

---

# 11. Agentic harness

## 11.1 Durable artifacts

Create during preflight:

```text
docs/fase3-execution-state.md
docs/fase3-validation-matrix.md
docs/migracion-static-wordpress.md
docs/operations/wordpress-manual-deployment.md
docs/operations/third-party-plugins.md
```

Purposes:

- `fase3-execution-state.md`: current strategy and resume point
- `fase3-validation-matrix.md`: durable QA evidence
- `migracion-static-wordpress.md`: approved semantic/a11y/SEO back-ports
- `wordpress-manual-deployment.md`: manual FTPS runbook and rollback
- `third-party-plugins.md`: approved plugin inventory, rationale, version, and required settings

Do not create speculative entries.

## 11.2 Execution-state front matter

```yaml
---
phase: "Fase 3"
status: "not_started"
current_work_unit: ""
current_branch: ""
last_verified_commit: ""
last_checkpoint_commit: ""
updated_at: ""
next_action: ""
blocked: false
---
```

Allowed statuses:

```text
not_started
preflight
in_progress
qa_failed
blocked
ready_for_review
complete
```

## 11.3 Required state sections

```markdown
# Fase 3 execution state

## Current objective
## Current strategy
## Acceptance criteria
## Completed work
## Active work
## Validation evidence
## Failures and root causes
## Decisions and assumptions
## Documentation discrepancies
## Blockers
## Repository state
## Files changed
## Next exact action
## Resume procedure
```

## 11.4 Work-unit contract

Every work unit must define:

```text
Objective
Binding sources
Expected files
Dependencies
Risks
Rollback
Acceptance criteria
QA plan
Checkpoint boundary
```

A work unit is complete only when:

- Implementation is coherent
- Relevant QA ran
- Failures are resolved or documented
- Learning is recorded
- The diff is reviewed
- A safe checkpoint exists
- The next exact action is recorded

## 11.5 Learning classification

Record findings as:

- Implementation learning: execution-state file
- Migration correction: migration ledger
- Documentation drift: separate documentation commit
- New architecture: ADR before implementation
- Legal/privacy uncertainty: owner or legal-advisor decision

## 11.6 Bounded retries

For a failure:

1. Capture evidence.
2. Form a root-cause hypothesis.
3. Change one relevant variable.
4. Retry.
5. Compare results.

Do not perform more than two materially identical retries without changing strategy.

## 11.7 Session checkpoint

Before context becomes constrained or a session ends:

1. Stop starting new work units.
2. Finish or safely suspend the current unit.
3. Run the strongest available QA.
4. Commit coherent passing work when safe.
5. Record uncommitted paths and failures.
6. Update all durable artifacts.
7. Record one exact next action.
8. Provide a resume command.

## 11.8 Resume protocol

Start every later session with:

```bash
git status --short
git branch --show-current
git log -5 --oneline
```

Then:

1. Read `.cursor/rules/`.
2. Read `docs/fase3-execution-state.md`.
3. Verify recorded branch and commit.
4. Inspect every uncommitted change.
5. Re-read binding sources for the active work unit.
6. Re-run the last applicable QA gate.
7. Correct stale state.
8. Continue from `Next exact action`.

Repository state and Git history outrank chat memory.

---

# 12. Preflight

## 12.1 Repository identity

Confirm:

```text
docs/adr/
content-source/
.github/workflows/
```

If absent, stop because the wrong repository may be open.

## 12.2 Git safety

Inspect:

```bash
git status --short
git branch --show-current
git log -10 --oneline
git tag --list
```

Preserve unrelated changes.

Do not push, deploy, or rewrite history.

## 12.3 Tool inventory

Detect:

- PHP
- WP-CLI
- Composer
- Node.js
- npm
- WordPress runtime
- Database
- Browser
- Visual comparison tools
- `actionlint`
- Existing lint and test scripts

Do not install a global toolchain without authorization.

When a tool is unavailable:

- Use a safe existing alternative.
- Continue independent work.
- Mark the affected QA `Unverified`.
- Never imply a pass.

## 12.4 Repository snapshot

Inspect:

- Static pages and partials
- CSS and JS
- Images and PDFs
- Workflows
- Package scripts
- Current `.gitignore`
- Current `.htaccess`
- Existing WordPress code
- Documentation drift
- GitHub Pages behavior
- Static deployment behavior

## 12.5 Initial checkpoint

Before implementation:

- Create durable artifacts.
- Record repository state.
- Record confirmed conflicts.
- Build the phase plan.
- Define the first work unit and QA gate.

Do not write implementation code before this checkpoint.

---

# 13. QA levels

Use the strongest available validation.

## Level 1: Static

- PHP syntax
- JSON parsing
- YAML parsing
- Stylelint
- `git diff --check`
- Secret scanning
- Forbidden-pattern searches
- File-tree checks
- CSS checksum comparison

## Level 2: Component

- CPT and taxonomy registration
- Metadata schemas
- Sanitization and authorization
- Meta-box save behavior
- Relationship normalization
- WP-CLI command behavior
- Content conversion
- Menu creation
- Attachment relationships

## Level 3: Integration

- Plugin activation
- Theme activation
- Permalinks
- Template hierarchy
- Search
- Content migration
- Fixture lifecycle
- Contact Form 7
- WP Statistics
- Security headers

## Level 4: User-facing regression

- Static-to-WordPress visual comparison
- Keyboard navigation
- Focus
- Mobile reflow
- 200 percent zoom
- 320 CSS pixel width
- Empty states
- Metadata output
- Link behavior
- Cookie and storage behavior
- Network requests
- Spanish UI copy

A work unit does not pass because only Level 1 passed.

Unavailable checks remain `Unverified`.

---

# 14. Execution phases

## Phase 0: Governance and harness

Tasks:

1. Complete required reading.
2. Verify repository and Git state.
3. Inventory tools.
4. Create durable artifacts.
5. Record documentation conflicts.
6. Create a work-unit plan.
7. Establish the first checkpoint.

Gate:

- Sources read
- State recorded
- Working-tree safety known
- Harness files created
- No implementation code written early

---

## Phase 1: Monorepo reorganization

Governed by ADR 0007.

Tasks:

1. Create or reuse a rollback tag such as `pre-fase3-reorg`.
2. Move the static implementation to `static/`.
3. Create:

```text
wordpress/wp-content/themes/revistalogos/
wordpress/wp-content/plugins/revistalogos-core/
```

4. Update the manual static-production workflow to source `static/`.
5. Preserve the automatic GitHub Pages beta mirror.
6. Update README and stale file-structure documentation separately.
7. Preserve history with `git mv`.

Move relevant static files:

```text
*.html
assets/
partials/
.htaccess
robots.txt
sitemap.xml
```

Preserve repository-level files:

```text
docs/
content-source/
.github/
README.md
CHANGELOG.md
VERSION.md
LICENSE
LICENSE-CONTENT
package.json
package-lock.json
stylelint.config.mjs
_config.yml
```

Asset discrepancy:

- Preserve the actual flat image structure during reorganization.
- Do not silently introduce documented subfolders.
- Record the drift.
- Reorganize only after an explicit decision.

Validation:

- Static site still resolves locally.
- Static production package contains only static files.
- WordPress code is excluded.
- GitHub Pages workflow remains unchanged.
- YAML parses.
- Static CSS and assets are unchanged.
- `git diff --check` passes.

Commit this phase separately.

---

## Phase 2: `revistalogos-core` scaffold

Create a first-party plugin with:

- Valid plugin header
- Direct-access guard
- Version constant
- Modular bootstrap
- Activation and deactivation hooks
- No side effects on include
- Rewrite flush only on activation/deactivation
- Upgrade routine for future schema/capability changes
- No active-theme dependency

Recommended structure, adapted to actual complexity:

```text
revistalogos-core/
├── revistalogos-core.php
├── includes/
│   ├── class-plugin.php
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── content-types/
│   ├── taxonomies/
│   ├── metadata/
│   ├── relationships/
│   ├── roles/
│   ├── queries/
│   ├── migration/
│   ├── fixtures/
│   ├── integrations/
│   └── cli/
├── resources/
├── tests/
└── readme.txt
```

Do not create speculative abstractions.

Validation:

- PHP syntax
- Safe activation/deactivation
- No request-time rewrite flush
- No direct SQL where native APIs exist
- No theme dependency

---

## Phase 3: Published-content model

Implement:

- `issue`
- `article`
- `author`
- `section`
- `article_type`
- `keyword`
- Managing Editor
- Required Fase 3 metadata
- Relationships
- Current issue query

Do not implement `submission`.

Validation:

- CPT and taxonomy registrations
- Rewrite slugs
- Metadata schemas
- Meta-box nonce/capability/sanitization behavior
- Relationship validation
- Role capabilities
- Current issue query
- Absence of deferred CPTs and roles

---

## Phase 4: Theme scaffold and presentation assets

Create:

```text
wordpress/wp-content/themes/revistalogos/
```

Foundation:

```text
style.css
theme.json
functions.php
header.php
footer.php
index.php
page.php
single.php
404.php
comments.php
assets/
template-parts/
inc/
```

Theme `inc/` contains presentation helpers only.

Copy only presentation assets:

- CSS
- Theme JavaScript
- Logos
- Favicons
- Decorative banners
- UI placeholders
- Design imagery

Do not copy editorial PDFs into the theme.

Preserve CSS byte-for-byte unless a logged correction applies.

Enqueue:

- `main.css`
- Only required JavaScript
- WordPress-generated asset URLs
- Existing `defer` behavior where appropriate

Primary content must remain usable without JavaScript.

Validation:

- Theme header
- `theme.json`
- CSS hashes
- Asset resolution
- No domain registration in theme
- Safe behavior without plugin

---

## Phase 5: Template migration

Static coverage must include:

```text
index.html
noticias.html
page-acerca.html
page-contacto.html
page-enlaces.html
page-enviar-colaboracion.html
page-etica.html
page-normas.html
page-politicas.html
page-privacidad.html
page-comite-editorial.html
archive-issue.html
single-issue.html
archive-article.html
single-article.html
archive-author.html
single-author.html
single-post.html
search.html
404.html
```

For each screen:

1. Map it to a WordPress template.
2. Preserve block structure and classes.
3. Replace dynamic placeholders only.
4. Preserve permanent copy.
5. Escape output.
6. Use native URL APIs.
7. Reset custom query state.
8. Avoid N+1 queries.
9. Preserve pagination and empty states.
10. Preserve next-step or exit behavior.
11. Render institutional body content from WordPress.
12. Do not rewrite static links merely to resemble WordPress.

Reusable parts may include:

```text
header
footer
breadcrumbs
issue-card
article-card
author-card
hero-current-issue
metadata-box
toc
pagination
sidebar-card
content-none
external-link-note
```

External links:

- Use approved new-tab behavior.
- Add `rel="noopener noreferrer"`.
- Include an accessible Spanish notice.
- Avoid duplicate assistive announcements.

Maintain a coverage matrix:

```text
Static source
WordPress template
Shared part
Dynamic source
Status
Validation
Known differences
```

---

## Phase 6: Institutional-content migration

Implement an operator-run migration system inside `revistalogos-core`.

## 6.1 Runtime boundary

The remote WordPress installation will not contain the repository's `content-source/`.

Use a two-step architecture:

### Local generator

A repository tool reads the canonical source and creates a versioned migration payload under the first-party plugin.

The generated payload must contain:

- Stable source keys
- WordPress destination metadata
- Safe HTML or block content
- Source checksum
- Generator version
- Payload version

### WordPress importer

WP-CLI reads the generated payload and creates or updates WordPress objects.

Do not make the importer depend on repository paths unavailable on the server.

Generated payloads are implementation artifacts.

The canonical source remains authoritative.

Never edit generated prose manually.

## 6.2 WP-CLI commands

Register only under WP-CLI:

```text
wp revistalogos content validate
wp revistalogos content plan
wp revistalogos content import
wp revistalogos content verify
```

Default to dry-run.

Writes require `--apply`.

Production writes require an additional explicit confirmation flag and backup evidence.

Never run migration:

- On plugin activation
- On theme activation
- On normal requests
- Through a public endpoint
- During deployment
- Automatically on staging or production

## 6.3 Canonical manifest

Include stable entries for:

```text
home
acerca
contacto
normas
etica
politicas
privacidad
enviar-colaboracion
comite-editorial
enlaces
noticias
buscar
```

Each entry defines:

- Source key
- Canonical source section
- Post type
- Slug
- Title
- Status
- Parent
- Comment and ping status
- Migration-owned fields
- Expected template behavior

## 6.4 Integrity

The generator must:

- Preserve visible wording
- Preserve headings, lists, links, tables, and emphasis
- Sanitize structural markup
- Compare normalized visible text against the canonical source
- Fail on missing, duplicated, reordered, or paraphrased text
- Report ambiguous section boundaries without blocking unrelated pages

## 6.5 Identity and drift

Use private metadata:

```text
_les_source_key
_les_source_hash
_les_migration_version
_les_migration_owned
```

Behavior:

- Create missing objects.
- Skip unchanged objects.
- Detect source changes.
- Detect manual WordPress changes.
- Do not overwrite manual edits by default.
- Require `--force` only for declared migration-owned fields.
- Never change unrelated fields.
- Never delete unrelated content.
- Never duplicate records.

## 6.6 Site settings

Configure idempotently when approved:

- Static front page
- Posts page
- Native Privacy Policy page
- Primary navigation
- Footer navigation
- Required page hierarchy
- Required taxonomy terms

Use object IDs, not hardcoded production URLs.

Do not overwrite owner-managed menus silently.

## 6.7 Special pages

### Contacto

- Import canonical prose.
- Render Contact Form 7 in a dedicated template region.
- Keep form configuration outside canonical prose.

### Enviar colaboración

- Import canonical instructions.
- Resolve approved documents through attachment IDs.
- Preserve the public email-based process.
- Do not create a portal.

### Privacidad

- Create the dedicated visitor privacy page.
- Assign it through WordPress Privacy settings.
- Render it with `privacy-policy.php`.
- Link it from the footer and contact form.
- Keep it distinct from editorial confidentiality in `page-politicas`.
- Preserve visible provisional status and last-update date when required by canonical content.

### Noticias

- Create and assign the posts page.
- Do not import demo news as production content.

### Buscar

- Create the page and slug.
- Do not treat result data as migrated content.

## 6.8 Media

Classify each file before import.

Media Library candidates:

- Institutional PDFs
- Editorial forms
- Publication covers
- Real issue PDFs
- Real article PDFs
- Author images

Demo article and issue PDFs remain fixtures.

Use WordPress media APIs.

Store attachment IDs, not permanent URLs.

Track stable source key and checksum.

## 6.9 Status language

Use:

```text
Implemented
Planned
Applied
Verified
Unverified
```

Do not say content was migrated when only tooling exists.

---

## Phase 7: Fixture system

Fixtures are separate from institutional migration.

Create:

- One realistic first-edition-shaped issue
- Its articles
- Its authors
- Minimum stubs needed to exercise pagination

Every fixture object:

```text
_les_fixture = 1
```

Commands:

```text
wp revistalogos fixtures seed
wp revistalogos fixtures verify
wp revistalogos fixtures teardown
```

Requirements:

- Idempotent seed
- Idempotent teardown
- Environment safeguards
- Detectable fake identifiers
- Media cleanup
- Post/meta/term cleanup
- No deletion of canonical or unrelated content
- No production execution without explicit override

Runtime lifecycle test:

1. Teardown
2. Seed
3. Verify
4. Seed again
5. Verify no duplicates
6. Teardown
7. Verify no fixture data or orphans
8. Teardown again
9. Verify safe no-op

---

## Phase 8: Approved third-party integrations

## 8.1 Plugin inventory

Document for each approved plugin:

- Name
- Purpose
- ADR
- Installed version
- Source
- Required settings
- Privacy impact
- Update verification procedure
- Removal procedure

Do not version third-party plugin code in this repository.

## 8.2 Contact Form 7

Requirements:

- Recipient: `revista.cenfiss@gmail.com`
- No database storage
- No Flamingo
- No Google reCAPTCHA
- Honeypot antispam
- Privacy-page link
- Checkbox only if approved by legal advice or canonical content

If Contact Form 7 does not provide the approved honeypot behavior, implement the smallest maintainable integration in `revistalogos-core`.

Cloudflare Turnstile is an ADR-approved fallback only when honeypot proves insufficient. Do not enable it automatically. It requires an owner decision, privacy review, and configuration documentation.

Do not claim installation or delivery without runtime verification.

## 8.3 WP Statistics

Configure and verify:

- No anonymous cookies or client storage
- No readable IP storage
- Daily rotating salted hashing as supported
- No external integrations
- No paid add-ons
- Periodic old-data purge
- Reverification after every major plugin update

Do not assume PDF download tracking exists in the free version.

If download metrics later become essential, propose bounded first-party code in `revistalogos-core`; do not implement it without a confirmed requirement.

Do not install GA4.

---

## Phase 9: URLs, permalinks, and search

Use WordPress `Post name` permalinks with trailing slashes.

CPT rewrite slugs:

```text
issue   -> revista/numeros
article -> revista/articulos
author  -> revista/autores
```

Taxonomy slugs when registered:

```text
section      -> revista/seccion
article_type -> revista/tipo
```

Use native URL APIs:

```php
home_url()
get_permalink()
get_post_type_archive_link()
get_term_link()
```

Do not handwrite dynamic internal paths.

Do not port static relative links into WordPress.

Do not retain the static rule that removes trailing slashes.

Do not add custom slash redirects.

Only documented legacy redirects may survive.

---

## Phase 10: Security-header operations

ADR 0012 requires:

```text
HTTP -> HTTPS 301
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=() microphone=() geolocation=() payment=()
```

Do not add HSTS or CSP.

Important deployment boundary:

- Static `.htaccess` remains versioned and deployed with the static site during Fase 3.
- The WordPress theme/plugin workflow must not deploy the server-root `.htaccess`.
- WordPress creates and manages its standard rewrite block on the server.
- The reversible headers and HTTPS rule must be documented as a manual server operation in `docs/operations/wordpress-manual-deployment.md`.
- Apply them to staging only when server access and authorization exist.
- Apply them to production only during the later authorized cutover.
- Never preserve static flat-file rewrite rules in the WordPress `.htaccess`.
- Never preserve the static trailing-slash removal rule.

Validate with `curl` when the environment is available.

Do not claim server headers were applied when only the snippet was documented.

---

## Phase 11: Accessibility

Target WCAG 2.2 Level AA, with WCAG 2.1 AA as the minimum baseline.

Validate:

- Skip link
- Correct `lang`
- One `main` landmark
- Heading hierarchy
- Keyboard access
- Visible focus
- Logical focus order
- 200 percent zoom
- 320 CSS pixel reflow
- Form labels and errors
- Link purpose
- Alt text
- Decorative `alt=""`
- Target size where feasible
- Reduced motion
- Pagination semantics
- Table semantics
- External-link notice
- Accessible PDF link names
- ARIA only when native HTML is insufficient

Automated checks do not replace manual checks.

Log and back-port nontrivial corrections.

---

## Phase 12: Fase 3 discoverability

Implement only general Fase 3 metadata.

## 12.1 Highwire

Output when values exist:

```text
citation_title
citation_author
citation_publication_date
citation_journal_title
citation_volume
citation_issue
citation_firstpage
citation_lastpage
citation_pdf_url
citation_language
```

Do not output invented values.

## 12.2 Schema.org

Implement:

- `Periodical` and `Organization` on the front page
- `PublicationIssue` on issue pages
- `ScholarlyArticle` on article pages

Use safe JSON encoding.

Omit unknown properties.

Do not output ORCID `sameAs` in Fase 3.

## 12.3 Open Graph and canonical metadata

Implement minimal first-party output:

- Title
- Description
- Canonical URL
- Type
- Featured image
- Site name

Avoid duplicate metadata.

Do not copy demo canonicals.

Do not create DOI redirect routes.

---

## Phase 13: Manual FTPS deployment workflow

All WordPress deployment is manual.

This is a hard rule.

## 13.1 Trigger

The WordPress workflow uses only:

```yaml
workflow_dispatch:
```

Do not add:

- `push`
- `pull_request`
- `schedule`
- Tag or release triggers
- Automatic promotion

Do not add automatic CI checks in this workflow unless a later accepted decision resolves backlog D12b.

The existing automatic GitHub Pages beta deployment remains untouched.

## 13.2 Scope

Deploy only:

```text
wordpress/wp-content/themes/revistalogos/
wordpress/wp-content/plugins/revistalogos-core/
```

Never deploy:

```text
wp-content/uploads/
wp-config.php
WordPress core
database content
migration execution
fixture execution
third-party plugins
server-root .htaccess
unrelated themes or plugins
```

## 13.3 Safety

- Keep FTPS and the existing deployment action unless an ADR changes it.
- Use separate staging secrets.
- Use explicit remote paths.
- Never target the WordPress installation root.
- Never use delete or mirror behavior against shared WordPress directories.
- Never activate plugins or themes automatically.
- Never run migrations automatically.
- Never modify DNS.
- Never perform production cutover.

Suggested staging secrets, adjusted to repository conventions:

```text
STAGING_FTP_SERVER
STAGING_FTP_USERNAME
STAGING_FTP_PASSWORD
STAGING_THEME_REMOTE_DIR
STAGING_PLUGIN_REMOTE_DIR
STAGING_SITE_URL
```

Prefer separate bounded remote directories for theme and plugin.

## 13.4 Authorization

Creating or validating the workflow does not authorize execution.

Do not trigger staging or production deployment unless the owner explicitly requests it in the current session.

Do not infer authorization from credentials, secrets, or previous deployments.

## 13.5 Staging

Staging must remain:

```text
noindex
robots: Disallow: /
```

No password protection is required by the accepted decision.

Do not apply staging noindex to production.

The exact staging hostname is a blocker only when actual deployment or environment configuration becomes the next action.

## 13.6 Manual runbook

Document:

1. Target environment
2. Branch and commit
3. Clean working-tree check
4. Required QA gate
5. Remote code backup
6. Bounded remote paths
7. Manual workflow trigger
8. Transfer verification
9. Theme/plugin availability
10. Confirmation that core, uploads, config, and third-party plugins were untouched
11. Smoke tests
12. Header verification when manually configured
13. Deployed commit record
14. Rollback procedure

---

# 15. Final validation gate

## 15.1 Static checks

Run:

```bash
git diff --check
```

Also run available:

- PHP syntax
- JSON parsing
- YAML parsing
- Stylelint
- Existing repository tests
- Secret scan
- File-tree validation
- CSS hash comparison

Search for:

- Fake identifiers outside fixture scope
- Hardcoded dynamic production URLs
- `submission` registration
- Deferred Author role
- ACF or field builders
- Flamingo
- reCAPTCHA
- GA4
- HSTS
- CSP
- Unapproved plugins
- Secrets
- Theme-side domain registration
- Request-time rewrite flushing
- Uploads in deployment scope
- Automatic WordPress deployment triggers
- Automatic migration or fixtures
- Stored `orcid_url`
- Fase 4 ORCID or Crossref code

## 15.2 Runtime checks

When WordPress exists:

- Plugin activation
- Theme activation
- CPTs and taxonomies
- Meta saving
- Relationships
- Current issue query
- Template resolution
- Search
- Permalinks
- Content migration
- Fixture lifecycle
- Menus
- Media attachments
- Contact Form 7
- WP Statistics
- Anonymous cookie/storage behavior
- Security headers
- Academic metadata
- PHP warnings and fatals

## 15.3 Visual checks

Compare static and WordPress at:

- Mobile
- Tablet
- Desktop
- 200 percent zoom
- 320 CSS pixel width

Compare:

- Typography
- Spacing
- Navigation
- Cards
- Footer
- Forms
- Empty states
- Pagination
- Metadata
- Focus states

If browser comparison is unavailable, mark visual parity `Unverified`.

## 15.4 Git review

Before a checkpoint or final report:

- Review `git status`.
- Review complete diff.
- Exclude unrelated files.
- Confirm commit boundaries.
- Confirm no secrets.
- Confirm no generated environment files.
- Confirm durable artifacts are current.

---

# 16. Commit strategy

Use small, reviewable commits.

Suggested boundaries:

1. Harness and operational docs
2. Monorepo reorganization
3. Plugin scaffold
4. Content model
5. Relationships and queries
6. Theme scaffold
7. Presentation assets
8. Template families
9. Content generator
10. Content importer
11. Media migration
12. Fixtures
13. Contact integration
14. Statistics operations
15. URLs and search
16. Security operations
17. Accessibility corrections
18. Academic metadata
19. Manual deployment workflow
20. Documentation corrections

Before every commit:

- Review staged diff.
- Run relevant QA.
- Exclude unrelated changes.
- Update execution state.
- Use English `type(scope): summary` style.

Do not push unless explicitly requested.

---

# 17. Blocker protocol

A hard blocker is a missing decision, credential, environment, or dependency that makes the next specific action unsafe or impossible.

When blocked:

1. State the exact blocked action.
2. Show evidence.
3. Explain why guessing is unsafe.
4. Request the smallest decision needed.
5. Continue independent work.
6. Consolidate related questions.

Potential blockers:

- Exact staging hostname
- Hosting access
- Ambiguous canonical section boundaries
- Image-directory reorganization decision
- Semantic correction versus redesign ambiguity
- Plugin-version incompatibility
- Legal/privacy conclusion
- Missing real editorial content

Do not request deployment details before deployment becomes the next action.

---

# 18. Initial response contract

The first response must contain:

1. Repository path
2. Branch
3. Working-tree status
4. Existing execution-state status
5. Whether recorded state matches Git
6. Binding documents found
7. Confirmed discrepancies
8. First work unit
9. Acceptance criteria
10. QA gate
11. Checkpoint boundary
12. Immediate blocker, if any

Then begin preflight.

Do not merely repeat this prompt.

Do not ask permission to inspect the repository.

Do not stop after planning.

---

# 19. Progress update format

At meaningful checkpoints, report:

```text
Work unit:
Strategy:
Implemented:
QA:
Learning:
Checkpoint:
Next action:
```

Keep updates concise.

---

# 20. Final response contract

Return these sections.

## Outcome

Use one:

```text
Complete
Complete with unverified environment-dependent items
Partially complete due to blockers
Blocked before implementation
```

## Implemented

Summarize by phase.

## Commits

List actual hashes, messages, and purposes.

## Important files

List major files created or changed.

## Validation evidence

Use:

```text
Validation
Method
Result
Status
Commit tested
```

Status:

```text
Pass
Fail
Unverified
```

## Static-to-WordPress coverage

Include the complete matrix.

## Content migration status

Report separately for:

```text
Local
Staging
Production
```

Use:

```text
Implemented
Planned
Applied
Verified
Unverified
```

## Deployment status

Report:

```text
Workflow implemented
Workflow validated
Deployment authorized
Deployment executed
Post-deployment verification
```

Do not imply authorization or execution.

## Governance and discrepancies

List:

- ADR conflicts
- Documentation drift
- Content-source conflicts
- Migration-ledger entries
- Decisions made
- Decisions deferred

## Blockers

For each blocker:

- Blocked action
- Missing dependency
- Work completed despite blocker
- Smallest owner action

## Out-of-scope confirmation

Confirm no implementation of:

- Submission system
- Author account portal
- DOI/ORCID validation or display
- Crossref XML
- GA4
- HSTS
- CSP
- Production cutover
- Unapproved plugins
- Automatic WordPress deployment
- Automatic content migration

## Execution continuity

Include:

```text
Execution-state file
Final verified commit
Working-tree state
Last completed work unit
Next work unit
Resume command
```

## Learning summary

Include:

- Corrected assumptions
- Documentation drift
- Reusable safeguards
- Remaining unverified behavior
- Deferred decisions
- Fase 4 findings

## Recovery readiness

Use one:

```text
No continuation required
Ready to resume from a clean checkpoint
Ready to resume with documented uncommitted work
Not safely resumable
```

## Recommended next action

Give exactly one prioritized next action.

---

# 21. Final instruction

Begin now.

Inspect the repository.

Read the binding sources.

Create the durable harness artifacts.

Create the work-unit plan.

Execute the first unblocked work unit.

Do not stop after planning.

Do not deploy automatically.

Do not trigger manual deployment without explicit owner authorization in the current session.
