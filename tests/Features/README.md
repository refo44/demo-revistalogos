# Features (Gherkin)

Canonical location for `.feature` files: this directory.

Gherkin here is a **versioned business specification**, not an executable
Behat suite. PHPUnit level 1 verifies pure PHP; `composer test:wp`
(`tests/WordPress/`) verifies in-process WordPress contracts; isolated
`tools/qa-*.sh` harnesses verify HTTP / wp-admin / CLI. Do not install
Behat until there are enough scenarios to justify a runner.

Language: **Spanish** (same as `docs/`). Do not mix English and Spanish
inside one feature.

Write observable business behavior, not class or method names. Rules:
`docs/23-testing-foundation.md` (stack, CI, Gherkin location) and
`docs/24-project-testing-standard.md` (how to write a test).

PHPUnit level 1, the WordPress suite, and the harnesses may each
execute part of a feature. There is no required 1:1 trace.

ADR 0017 lives in `article-pdf-generation.feature` (business
specification only; no Behat). Scenarios cover enforcement OFF
(publish without PDF remains allowed) and ON (keep, generate, or
block). Do not mention classes, hooks, or renderer internals.

Issue #30 (Gutenberg publish + published author) lives in
`article-author-publication.feature`. PHPUnit WordPress
`ArticleAuthorPublicationRestTest` (`composer test:wp`) and
`tools/qa-article-editorial-ux.sh` execute that contract.
