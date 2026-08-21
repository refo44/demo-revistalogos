# Features (Gherkin)

Canonical location for `.feature` files: this directory.

Gherkin here is a **versioned business specification**, not an executable
Behat suite. PHPUnit verifies PHP behavior; isolated `tools/qa-*.sh`
harnesses verify integrated WordPress workflows. Do not install Behat
until there are enough scenarios to justify a runner.

Language: **Spanish** (same as `docs/`). Do not mix English and Spanish
inside one feature.

Write observable business behavior, not class or method names. Rules:
`docs/23-testing-foundation.md`.

Do not add ADR 0017 (`article-pdf-generation.feature`) until that feature
is implemented with TDD.
