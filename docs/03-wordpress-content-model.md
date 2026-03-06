# {{PROJECT_NAME}} — WordPress Content Model

**Version 1.0**

Official content model for the WordPress implementation. Based on platform plan and content sources.

**Depends on:** `01-platform-plan`, `02-corporate-identity`, `04-screen-map`  
**Reference:** `09-ui-copy-sheet`, `11-url-tree`, `12-theme-file-structure`

---

## 1. General schema

### Post types

| Key | Label | Type | Slug | Main use |
|-----|-------|------|------|----------|
| page | Pages | Native | per page | {{PAGE_USE}} |
| {{CPT_1_KEY}} | {{CPT_1_LABEL}} | Custom | {{CPT_1_SLUG}} | {{CPT_1_USE}} |
| … | … | … | … | … |

### Fixed vs. dynamic content

| Type | Management |
|------|------------|
| Static pages | page |
| {{DYNAMIC_TYPE_1}} | {{CPT_OR_BLOCK}} |
| … | … |

---

## 2. Fields per page

### Home (front-page)

- Hero: title, subtitle, primary button
- {{BLOCK_1}}
- {{BLOCK_2}}
- …

### {{PAGE_1_SLUG}} (page)

- {{FIELD_1}}
- {{FIELD_2}}
- …

### {{PAGE_2_SLUG}} (page)

- {{FIELD_1}}
- …

---

## 3. Custom Post Types

### {{CPT_KEY}}

| Field | Type | Use |
|-------|------|-----|
| {{FIELD_1}} | text | {{USE}} |
| {{FIELD_2}} | date | {{USE}} |
| … | … | … |

**Priority to native fields:** Use WordPress native fields (Title, Content, Featured image) when possible. Custom fields complement what core does not offer.

---

## 4. Taxonomies

| Key | Label | Type | Applies to |
|-----|-------|------|------------|
| {{TAX_1_KEY}} | {{TAX_1_LABEL}} | Hierarchical | {{CPT}} |
| … | … | … | … |

---

## 5. Minimum templates

- `front-page.php`: Home
- `page-{{slug}}.php`: Per page
- `archive-{{cpt}}.php`: Listings
- `single-{{cpt}}.php`: Single items
- `page.php`: Fallback
- `404.php`: Not found

---

## 6. External integrations

| Resource | Implementation |
|----------|-----------------|
| {{INTEGRATION_1}} | {{HOW}} |
| {{INTEGRATION_2}} | {{HOW}} |
| … | … |

---

## 7. Content accessibility requirements

Without replacing `19-accessibility-standards`, this model requires:

- **Images:** Always fill alt text. Decorative: `alt=""`.
- **Informative video:** Subtitles or transcript when content is informative.
- **Editorial structure:** Each page has a unique H1 and clear H2/H3 hierarchy.

---

## 8. Guiding principle

Everything in this model exists for: **{{MAIN_PURPOSE}}**. No marketing layers or funnels. Only clarity and purpose.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
