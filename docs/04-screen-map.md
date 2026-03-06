# {{PROJECT_NAME}} — Screen Map

List of what screens exist. Does not describe design; only what views to build. Single source for "what views to build". Content of each is in 03; theme structure in 12.

**Depends on / reference:** `01-platform-plan`, `03-wordpress-content-model`, `05-information-architecture-navigation`  
**Block structure per screen:** `06-wireframes`

---

## Fixed pages

| Page | Function |
|------|----------|
| Home | {{HOME_FUNCTION}} |
| {{PAGE_1}} | {{PAGE_1_FUNCTION}} |
| {{PAGE_2}} | {{PAGE_2_FUNCTION}} |
| … | … |

---

## Conditional pages

| Page | Condition |
|------|-----------|
| {{CONDITIONAL_PAGE}} | {{CONDITION}} |

---

## Content views (single)

| View | Use |
|------|-----|
| {{SINGLE_1}} | {{USE}} |
| … | … |

---

## States

| State | Description |
|-------|-------------|
| {{STATE_1}} | {{DESCRIPTION}} |
| 404 | Page does not exist |

---

## Global elements (components, not screens)

**Header:** {{HEADER_CONTENT}}  
**Footer:** {{FOOTER_CONTENT}}

Present on all pages.

---

## Total screens to build

**Main pages (static):** {{LIST}}

**Conditional:** {{CONDITIONAL_LIST}}

**Content views:** {{SINGLE_LIST}}

**States:** {{STATE_LIST}}

**Global components:** Header, Footer

---

## WordPress template mapping

| View | Template |
|------|----------|
| Home | `front-page.php` |
| {{PAGE}} | `page-{{slug}}.php` |
| … | … |
| 404 | `404.php` |
| Header | `parts/header.php` |
| Footer | `parts/footer.php` |

---

## What the site does NOT have

- {{EXCLUDED_1}}
- {{EXCLUDED_2}}
- …

Helps scope and avoid creep.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
