# {{PROJECT_NAME}} — Content Source Inventory

**Canonical list of content sources**

Single source for what content exists, where it lives and how it maps to the site.

**Depends on:** `03-wordpress-content-model`, `04-screen-map`  
**Reference:** `09-ui-copy-sheet`, `15-assets-strategy`

---

## 1. Document sources

| File | Content | Maps to |
|------|---------|---------|
| `{{FILENAME_1}}` | {{DESCRIPTION}} | {{PAGES_SECTIONS}} |
| `{{FILENAME_2}}` | {{DESCRIPTION}} | {{PAGES_SECTIONS}} |
| … | … | … |

---

## 2. Image sources

| File / folder | Content | Maps to |
|---------------|---------|---------|
| `{{FOLDER}}/{{FILE}}` | {{DESCRIPTION}} | {{PAGE_SECTION}} |
| … | … | … |

---

## 3. Video / audio

| Source | Content | Implementation |
|--------|---------|----------------|
| {{URL_OR_FILE}} | {{DESCRIPTION}} | Embed / link |
| … | … | … |

---

## 4. Brand assets

| File | Use |
|------|-----|
| `{{LOGO}}` | Header, favicon |
| `{{BRAND_MANUAL}}` | Identity (02) |
| … | … |

---

## 5. Traceability

Each page/section in `04-screen-map` should trace to at least one content source. If content is created in WordPress without a source, document the decision.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
