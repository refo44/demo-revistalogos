# Document Order (numeric prefix)

Documents in `docs/` use a two-digit prefix (`01-`, `02-`, …) to maintain a fixed order. The order follows the **dependency hierarchy**: from "why" to "how", and from strategy to implementation.

---

## Order rationale

| Range | Criterion | Rationale |
|-------|-----------|------------|
| **01** | Master plan | The platform plan defines the territory; it precedes and guides everything else. |
| **02** | Identity | Palette, typography and brand manual define the visual criteria. |
| **03–05** | What exists | WordPress content model, screen map and information architecture. Define *what* content and *what* screens exist. |
| **06** | Wireframes | Screen structure: hierarchy, blocks and reading flow per view. Not visual design; supports screen map and navigation. |
| **07–09** | Voice and copy | Voice guide, term dictionary, UI copy sheet. Copy depends on identity; navigation uses them later. |
| **10** | Visitor experience | User journey. How the site is traversed and how the person reaches the main action. |
| **11–12** | Navigation and theme | URL tree and theme file structure. Depend on screens, copy and journeys. |
| **13** | Static file structure | Project geography: docs, content-source, theme, assets. Where static files live. |
| **14** | CSS architecture | Layers (theme.json, main.css), tokens, naming, specificity, accessibility in styles. |
| **15–17** | Technical implementation | Assets, content source inventory, implementation order. Convert architecture into code. |
| **18** | Contemporary criteria | UX/UI trends applied to the system. Strategic filter for design and implementation validation. |
| **19** | Accessibility | Unique accessibility standards: strategy, design, semantic HTML, ARIA, editorial content, checklist and testing. WCAG 2.1/2.2 AA. |
| **20** | Layout principles | Reading width, vertical rhythm, use of whitespace, typography/image relationship. Closes the visual system before writing HTML. |

---

## Dependency rule

No document may depend on one with a higher number. Cross-references always point to documents with a lower or equal prefix. If you add a new document, assign the next available number and review references.

---

## Ordered list (document names)

1. `01-platform-plan`
2. `02-corporate-identity`
3. `03-wordpress-content-model`
4. `04-screen-map`
5. `05-information-architecture-navigation`
6. `06-wireframes`
7. `07-voice-guide-microcopy-ux`
8. `08-voice-dictionary`
9. `09-ui-copy-sheet`
10. `10-user-journey`
11. `11-url-tree`
12. `12-theme-file-structure`
13. `13-static-file-structure`
14. `14-css-architecture`
15. `15-assets-strategy`
16. `16-content-source-inventory`
17. `17-implementation-order`
18. `18-ux-ui-trends`
19. `19-accessibility-standards`
20. `20-layout-principles`

---

## Content sources

All content comes from:

- `content-source/{{PROJECT_FOLDER}}/` — Replace with your project folder name
- Add specific source files (docx, pdf, images) as needed

---

## Traceability

Map each requirement or brainstorm item to at least one document. Use a table like:

| Requirement / brainstorm | Doc(s) |
|-------------------------|--------|
| {{REQUIREMENT_1}} | 01, 02 |
| {{REQUIREMENT_2}} | 03, 04 |
| … | … |

---

## Guidelines for any website

These principles apply regardless of site type (community, author, blog, portfolio, e-commerce):

- **Strategy first:** Define purpose and audience before design.
- **Identity as constraint:** Brand manual is the source of truth; no visual decisions outside it.
- **Content drives structure:** Model content before building screens.
- **Voice consistency:** One tone across navigation, copy and error states.
- **Accessibility by default:** WCAG AA from the start, not as an afterthought.
- **Static before dynamic:** Validate with HTML/CSS maquette before WordPress.
- **Single source of truth:** One document per concern; avoid duplication.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
