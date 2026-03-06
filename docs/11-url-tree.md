# {{PROJECT_NAME}} — URL Tree

**Official URL structure**

Defines the canonical routes of the site. Single source for "what URL leads where".

**Depends on:** `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`  
**Reference:** `03-wordpress-content-model`, `12-theme-file-structure`

---

## 1. Structure

### Main pages

| Route | Content |
|-------|---------|
| `/` | Home |
| `{{ROUTE_1}}` | {{CONTENT}} |
| `{{ROUTE_2}}` | {{CONTENT}} |
| … | … |

### Content (single)

| Route | Content |
|-------|---------|
| `{{CPT_SLUG}}/` | Archive / list |
| `{{CPT_SLUG}}/{{slug}}/` | Single item |
| … | … |

### States

| Route | When |
|-------|------|
| `404` | Page not found |

---

## 2. Rules

- **Clean URLs:** No query strings for main content.
- **Consistent slugs:** Match WordPress page slugs exactly.
- **No trailing slash inconsistency:** Choose one convention (with or without) and stick to it.
- **Hierarchy:** Avoid deep nesting (max 2–3 levels recommended).

---

## 3. WordPress mapping

| Route | Template |
|-------|----------|
| `/` | `front-page.php` |
| `{{slug}}/` | `page-{{slug}}.php` |
| `{{cpt}}/` | `archive-{{cpt}}.php` |
| `{{cpt}}/{{slug}}/` | `single-{{cpt}}.php` |

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
