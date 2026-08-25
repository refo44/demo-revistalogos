# Copilot code review — Revista LOGO ET SPES

Pointer only. Do not invent policy. Priority: `content-source/` > `docs/` > ADRs.
Does not replace ADR 0005–0006, 0018, 0019, `docs/23`, `docs/24`, or `CLAUDE.md`.
Copilot code review loads [`.github/skills/code-review/SKILL.md`](skills/code-review/SKILL.md); that skill points here.

Paths: plugin `wordpress/wp-content/plugins/revistalogos-core/`, theme `wordpress/wp-content/themes/revistalogos/`. Never review or suggest edits to WordPress core.

## How to comment

- Spanish. Conventional Comments: `<label> (decoration): subject`.
- Default `(non-blocking)`. `(blocking)` only for a real defect (security, broken test, ADR violation) — author guidance, not a GitHub merge lock.
- Labels: `praise`, `nitpick`, `suggestion`, `issue`, `todo`, `question`, `thought`, `chore`, `note`, `typo`. At most one extra decoration (`security`, `test`, `if-minor`).
- Leave a **Comment** review. Merge lock is CI `PHP lint, Composer audit, and unit (PHP 8.3)`. Approvals = 0 (ADR 0019).
- KISS/YAGNI. Do not suggest `develop`, GitFlow, CODEOWNERS, commitlint, required reviews, extra CI runners, PHPCS/PHPStan/Psalm, Playwright, the official WP test suite, DI containers, or new Composer/plugin dependencies without an ADR.
- Do not suggest changing deploy (manual `workflow_dispatch`, ADR 0009) or hosting PHP. Pages auto-publish after merge to `main` is deliberate.

## Scope — do not demand

- CPT/taxonomy/role registration in the theme (ADR 0005: plugin owns domain).
- Article PDF generation in the theme; turning publication enforcement ON (ADR 0017; default OFF).
- Dummy/demo fixtures as production truth (ADR 0004).
- Fase 4 DOI/ORCID/ISSN validation or derived URLs (ADR 0013).
- `load_plugin_textdomain` / `.pot` / languages scaffolding.
- Transients, object cache, `WP_List_Table`, `@wordpress/*` blocks, or FSE conversion on a classic-theme PR.
- PHP 8-only syntax that breaks declared `Requires PHP: 7.4`.

## PHP / WordPress (plugin or theme PHP in the diff)

Match existing style: tabs, `snake_case` functions, `Class_Name`, `Revistalogos_Core\` / `revistalogos_` prefixes, `require_once` from `class-plugin.php` (no PSR-4 in plugin/theme). Extend an existing `includes/{domain}/` class before adding a file.

**Flag `(security)` / `(blocking)` when:**

- State-changing action (meta box, admin, AJAX, REST) without nonce **and** `current_user_can`.
- Unescaped user- or meta-derived output (need `esc_html` / `esc_attr` / `esc_url` / `wp_kses`).
- REST route without `permission_callback`, or request args without sanitize/validate.
- Raw SQL / string-concatenated `$wpdb`. Prefer `WP_Query` / existing query APIs; `$wpdb->prepare()` only if no API exists.
- Theme includes plugin files directly, or plugin-dependent calls without `function_exists()` / `class_exists()`.
- Business logic (queries, relationships, validation) inlined in templates.
- New PHP file without `ABSPATH` guard; new global without the project prefix/namespace.

**Flag `(suggestion)` when:**

- New wp-admin / CPT UI string not wrapped in `__( …, 'revistalogos-core' )` or `__( …, 'revistalogos' )`. Do **not** flag public copy that must match `content-source/` literally.
- New front-end CSS/JS not enqueued (`wp_enqueue_style` / `wp_enqueue_script`); new `<script>`/`<style>` tags in PHP templates; new CSS entry bypassing theme `main.css`; new CSS framework.
- Expensive work on every `init` / `wp_loaded` with no need; clearly unbounded queries.

**Do not flag:** existing Classic theme dequeue of block-library on the public side (ADR 0003); Dompdf `vendor/` inside the plugin (ADR 0017); missing PHPCS.

## Tests (docs/24 craft, docs/23 stack, ADR 0018)

Apply only if the diff adds/changes tests or domain PHP. Skip for docs-only, copy-only, or CI-yaml-only PRs.

**Flag `(test)` when:**

- New domain behavior with no failing test first (TDD).
- Internals mocked: `expects()` / `createMock()` on policy, orchestrator, mappers; SUT mocked; WordPress functions mocked.
- New runner or mock lib (Behat, Brain Monkey, WP_Mock, Mockery, Pest, Playwright, `WP_UnitTestCase` / `WP_TESTS_DIR`).
- WordPress booted in `tests/Unit/`; SUT assigned in `setUp()`; shared mutable `$this->…`.
- Test named after a production method (`test_decide_pdf_action`) or numbered (`test_1`).
- Asserts private methods, call order, coverage %, handbook behavior, or trivial getters.
- `$sut` / `$use_case->execute()` hiding the business action.

**Do not flag:**

- Missing `tools/qa-*.sh` on a unit-only or docs-only PR.
- Handwritten doubles that implement a public interface (`Recording_Article_Pdf_Renderer`) — preferred over `createMock()`.
- Short tests without `// Arrange` labels if AAA is obvious.
- PHPUnit names without Given/When/Then (`test_last_token_is_the_surname_used_in_citation_formats`).
- Gherkin in Spanish without Behat (`.feature` is spec, not a runner).

**Prefer:** sociable tests (real internal collaborators; double only renderer/FS/HTTP/clock); one relevant isolated `qa-*.sh` only when WordPress wiring changed. Lint remains `php -l`, not PHPCS.

## Git (ADR 0019)

Branches: `feat`/`fix`/`hotfix`/`chore` (or `cursor/`/`ai/`). `dependabot/*` is an exception.
Commits: Conventional Commits (`feat`/`fix`/`docs`/`chore`/`refactor`/`ci`/`test`/`perf`/`revert`). Prefer squash.
