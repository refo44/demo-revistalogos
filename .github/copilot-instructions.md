# Copilot code review — Revista LOGO ET SPES

Pointer only. Do not invent policy. Priority: `content-source/` > `docs/` > ADRs.
Does not replace ADR 0018, 0019, `docs/23`, `docs/24`, or `CLAUDE.md`.

## How to comment

- Spanish. Conventional Comments: `<label> (decoration): subject`.
- Default `(non-blocking)`. `(blocking)` only for a real defect (security, broken test, ADR violation) — author guidance, not a GitHub merge lock.
- Labels: `praise`, `nitpick`, `suggestion`, `issue`, `todo`, `question`, `thought`, `chore`, `note`, `typo`. At most one extra decoration (`security`, `test`, `if-minor`).
- Leave a **Comment** review. The merge lock is CI `PHP lint, Composer audit, and unit (PHP 8.3)`. Approvals = 0 (ADR 0019).
- KISS/YAGNI. Do not suggest `develop`, GitFlow, CODEOWNERS, commitlint, required reviews, extra runners, or changing deploy (manual `workflow_dispatch`, ADR 0009). Pages auto-publish after merge to `main` is deliberate.

## Scope — do not demand

- CPT/taxonomy/role registration in the theme (ADR 0005: plugin owns domain).
- Article PDF generation in the theme; turning publication enforcement ON (ADR 0017; default OFF).
- Dummy/demo fixtures as production truth (ADR 0004).
- Fase 4 DOI/ORCID/ISSN validation or derived URLs (ADR 0013).

## PHP / WordPress (if the diff touches plugin or theme PHP)

Sanitize on input, escape on output; nonce + capability on state-changing actions; no raw SQL if a WP API exists; `function_exists()` / `class_exists()` when the theme calls the plugin.

## Tests (docs/24 craft, docs/23 stack, ADR 0018)

Apply only if the diff adds/changes tests or domain PHP. Do not request tests for docs-only, copy-only, or CI-yaml-only PRs.

**Flag `(test)` when:**

- New domain behavior with no failing test first (TDD).
- Internals mocked: `expects()` / `createMock()` on policy, orchestrator, mappers; SUT mocked; WordPress functions mocked.
- New runner or mock lib (Behat, Brain Monkey, WP_Mock, Mockery, Pest, Playwright).
- WordPress booted in `tests/Unit/`; SUT assigned in `setUp()`; shared mutable `$this->…`.
- Test named after a production method (`test_decide_pdf_action`) or numbered (`test_1`).
- Asserts private methods, call order, coverage %, handbook behavior, or trivial getters.
- `$sut` / `$use_case->execute()` hiding the business action.

**Do not flag:**

- Missing `tools/qa-*.sh` on a unit-only or docs-only PR.
- Handwritten doubles that implement a public interface (`Recording_Article_Pdf_Renderer`) — preferred over `createMock()`.
- Short tests without `// Arrange` labels if AAA is obvious.
- PHPUnit names without the words Given/When/Then (`test_last_token_is_the_surname_used_in_citation_formats` is the convention).
- Gherkin in Spanish without Behat (`.feature` is spec, not a runner).

**Prefer:** sociable tests (real internal collaborators; double only renderer/FS/HTTP/clock); one relevant isolated `qa-*.sh` only when WordPress wiring changed.

## Git (ADR 0019)

Branches: `feat`/`fix`/`hotfix`/`chore` (or `cursor/`/`ai/`). `dependabot/*` is an exception.
Commits: Conventional Commits (`feat`/`fix`/`docs`/`chore`/`refactor`/`ci`/`test`/`perf`/`revert`). Prefer squash.
