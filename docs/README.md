# WordPress Project Documentation Templates

Generic, fillable templates for any WordPress site project. Based on reference projects (community sites, author platforms) and designed to be reusable across different website types.

---

## How to use

1. **Copy** this `docs/` folder into your new project.
2. **Replace** all `{{PLACEHOLDER}}` values with your project-specific content.
3. **Follow** the document order (01 → 20). No document should depend on one with a higher number.
4. **Remove** documents you don't need (e.g. skip 03 if not using WordPress).
5. **Add** documents if your project has unique needs; use the next available number.

---

## Placeholder reference

| Placeholder | Example / meaning |
|-------------|-------------------|
| `{{PROJECT_NAME}}` | "My Community Site" |
| `{{PROJECT_FOLDER}}` | `my-community-site` |
| `{{SITE_TYPE_NEGATIVE}}` | "a landing page" |
| `{{SITE_TYPE_POSITIVE}}` | "a space of welcome" |
| `{{CORE_CONCEPT}}` | "the community" / "the work" |
| `{{BRAND_MANUAL_FILENAME}}` | `brand-manual.pdf` |
| `{{LOGO_FILENAME}}` | `logo.png` |

Search for `{{` in any document to find all placeholders.

---

## Document list

| # | Document | Purpose |
|---|----------|---------|
| 00 | order-documents | Document order and dependency rules |
| 01 | platform-plan | Master plan, layers, principles |
| 02 | corporate-identity | Palette, typography, logo, brand essence |
| 03 | wordpress-content-model | Post types, taxonomies, fields |
| 04 | screen-map | What screens exist |
| 05 | information-architecture-navigation | Links per screen, menu structure |
| 06 | wireframes | Block structure per view |
| 07 | voice-guide-microcopy-ux | How the site speaks |
| 08 | voice-dictionary | Allowed / forbidden terms |
| 09 | ui-copy-sheet | Buttons, menus, messages |
| 10 | user-journey | Visitor paths and validation |
| 11 | url-tree | Canonical routes |
| 12 | theme-file-structure | WordPress theme architecture |
| 13 | static-file-structure | Project geography |
| 14 | css-architecture | Tokens, layers, naming |
| 15 | assets-strategy | Images, fonts, icons |
| 16 | content-source-inventory | Source files and mapping |
| 17 | implementation-order | Phases, maquette → WordPress |
| 18 | ux-ui-trends | Adoption / avoidance filter |
| 19 | accessibility-standards | WCAG, checklist, testing |
| 20 | layout-principles | Reading width, rhythm, responsive |

---

## Guidelines for any website

These principles apply regardless of site type:

- **Strategy first:** Define purpose and audience before design.
- **Identity as constraint:** Brand manual is source of truth.
- **Content drives structure:** Model content before building screens.
- **Voice consistency:** One tone across navigation, copy and errors.
- **Accessibility by default:** WCAG AA from the start.
- **Static before dynamic:** Validate with HTML/CSS maquette before WordPress.
- **Single source of truth:** One document per concern.

---

## Site types

These templates work for:

- **Community sites** — Events, practice, contact, donations
- **Author / portfolio** — Works, books, essays, workshops
- **Blog / editorial** — Posts, categories, archives
- **Small business** — Services, contact, simple e-commerce
- **Non-profit** — Mission, programs, donate, volunteer

Adapt placeholders and sections to your domain. Remove irrelevant documents. Add domain-specific ones as needed.

---

**Version:** 1.0
