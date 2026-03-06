# {{PROJECT_NAME}} — Information Architecture and Navigation

**Navigation map and live links**  
**Version 1.0**

This document defines what links leave each screen, where they go, in what order, and which must not exist. It does not describe design or layout. It connects the system with the real visitor experience.

**Reference:** `04-screen-map`, `06-wireframes`, `10-user-journey`, `09-ui-copy-sheet`

---

## 1. Structural principles

| Link type | Function |
|-----------|----------|
| **Primary** | Continue reading or enter main content. One clear focus per context. |
| **Secondary** | Context or ordered exit from current flow. |
| **Forbidden** | Noise that breaks the journey. |

**Central rule:** Each screen must offer a next step or a clear exit. Never a maze.

---

## 2. Global navigation

**Quantity rule:** Menu of 3–6 items. Fewer options = less cognitive friction.

### Header

| Link | Destination |
|------|-------------|
| {{NAV_1}} | {{DESTINATION}} |
| {{NAV_2}} | {{DESTINATION}} |
| … | … |

### Footer

| Link | Destination |
|------|-------------|
| {{FOOTER_1}} | {{DESTINATION}} |
| … | … |

---

## 3. Per-screen links

Define for each main screen:

| Screen | Primary link(s) | Secondary link(s) |
|--------|-----------------|-------------------|
| Home | {{PRIMARY}} | {{SECONDARY}} |
| {{PAGE_1}} | … | … |
| … | … | … |

---

## 4. States

### Empty / no results

| Link | Destination |
|------|-------------|
| {{ACTION}} | {{DESTINATION}} |

### 404

| Link | Destination |
|------|-------------|
| {{ACTION}} | Home or previous |

**Never** leave a screen without an exit.

---

## 5. Final rule

If a link does not push toward:

- The main content
- The main action
- Contact or next step

**it does not exist.**

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
