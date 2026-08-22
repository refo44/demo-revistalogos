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

ADR 0017 work unit 1 lives in `article-pdf-generation.feature`
(business specification only; no Behat). Later work units add
WordPress wiring and a renderer; do not expand this file for those
until that TDD slice starts.
