---
name: code-review
description: >-
  Reviews pull requests for Revista de Filosofía LOGO ET SPES using repository
  policy (Spanish Conventional Comments, ADR 0019). Use when Copilot code
  review runs, when reviewing a pull request or diff, or when the user asks
  for a code review.
---

# Code review (LOGO ET SPES)

Pointer only. Do not invent policy.

1. Read [`.github/copilot-instructions.md`](../../copilot-instructions.md) and follow it.
2. Priority of truth: `content-source/` > `docs/` > ADRs. Do not contradict them.
3. Comment in **Spanish**. Conventional Comments: `<label> (decoration): subject`.
   Default `(non-blocking)`. `(blocking)` only for a real defect (security, broken
   test, ADR violation). Author guidance, not a GitHub merge lock.
4. Leave a **Comment** review. Merge lock is CI `PHP lint, Composer audit, and unit (PHP 8.3)`. Approvals = 0 (ADR 0019).
5. Review the **diff**, not the whole repo. Never review or suggest edits to WordPress core.

## MCP

GitHub MCP is enough when the PR cites an issue or another PR. Do not add MCP
servers. Do not call Playwright unless the diff changes user-visible web
behaviour and a running app is in scope.

## Do not suggest

`develop`, GitFlow, CODEOWNERS, commitlint, required reviews, extra CI runners,
PHPCS/PHPStan/Psalm, Playwright, the official WP test suite, DI containers, or
new Composer/plugin dependencies without an ADR. Do not suggest
`sonar-project.properties`, a Sonar CI scanner while Automatic Analysis is
ON, or treating Quality Gate 0.0% coverage as a missing test suite. Do not
change deploy (`workflow_dispatch` from an annotated `vX.Y.Z` tag, ADR 0009 + ADR 0020)
or hosting PHP. Merge to `main` is not a production deploy. Pages
auto-publish on `main` is deliberate. Do not suggest keeping merged
feature branches; GitHub deletes the PR head branch on merge.

## Paths

- Plugin: `wordpress/wp-content/plugins/revistalogos-core/`
- Theme: `wordpress/wp-content/themes/revistalogos/`

Full PHP, test, and Git checks live in `copilot-instructions.md`. Do not
duplicate them here.
