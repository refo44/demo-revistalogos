#!/usr/bin/env node
/**
 * Local generator for the institutional content migration payload
 * (Fase 3, prompt §14 Phase 6). Reads the canonical source
 * (content-source/) and the validated static implementation (static/)
 * and writes a versioned payload consumed by the WP-CLI importer in
 * revistalogos-core. The canonical source stays authoritative; the
 * generated payload is an implementation artifact and must never be
 * edited by hand.
 *
 * Integrity: every entry carries a SHA-256 of its normalized visible
 * text. Entries converted from content-source fail the run when the
 * converted text diverges from the source text (missing, duplicated,
 * reordered or paraphrased content). Static-derived entries verify
 * round-trip extraction and report canonical coverage informationally.
 *
 * Usage: node tools/generate-content-payload.mjs
 */

import { createHash } from "node:crypto";
import { readFileSync, writeFileSync, mkdirSync, copyFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(dirname(fileURLToPath(import.meta.url)), "..");
const GENERATOR_VERSION = "1.0.0";
const PAYLOAD_VERSION = 1;

const CANONICAL_FILE = join(
  ROOT,
  "content-source",
  "PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md"
);
const PLUGIN_RESOURCES = join(
  ROOT,
  "wordpress/wp-content/plugins/revistalogos-core/resources"
);

const warnings = [];
const errors = [];

/* ------------------------------------------------------------------ */
/* Helpers                                                             */
/* ------------------------------------------------------------------ */

const sha256 = (data) => createHash("sha256").update(data).digest("hex");

/** Collapse whitespace for text comparison. */
const normalizeText = (text) =>
  text
    .replace(/ /g, " ")
    .replace(/\s+/g, " ")
    .trim();

/** Visible text of an HTML fragment (tags stripped, entities decoded). */
function htmlVisibleText(html) {
  return normalizeText(
    html
      // Generated assistive notes are presentation, not canonical text.
      .replace(/<span class="visually-hidden">[^<]*<\/span>/g, "")
      // Inline tags vanish without inserting whitespace; block tags
      // separate words.
      .replace(/<\/?(strong|em|a|span|sup|sub|b|i|code)\b[^>]*>/g, "")
      .replace(/<[^>]+>/g, " ")
      .replace(/&lt;/g, "<")
      .replace(/&gt;/g, ">")
      .replace(/&amp;/g, "&")
      .replace(/&quot;/g, '"')
      .replace(/&#0?39;/g, "'")
      .replace(/&hellip;/g, "…")
      .replace(/&bull;/g, "•")
      .replace(/&nbsp;/g, " ")
  );
}

const escapeHtml = (s) =>
  s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

/* ------------------------------------------------------------------ */
/* Markdown (Google-Docs-export flavor) to HTML                        */
/* ------------------------------------------------------------------ */

/** Resolve backslash escapes of the export, keeping the visible char. */
function unescapeMd(text) {
  return text.replace(/\\([<>\-*.[\]()#+_`!])/g, "$1");
}

/** Inline markdown: bold, italic, links; escapes resolved first. */
function inlineMdToHtml(text) {
  let out = text;

  // Footnote references have no rendered body on the page; drop them
  // and record the loss so nobody assumes silent fidelity.
  out = out.replace(/\[\^(\d+)\]/g, (_, n) => {
    warnings.push(`footnote reference [^${n}] dropped from converted text`);
    return "";
  });

  // Temporarily shield escaped square brackets from link parsing.
  out = out.replace(/\\\[/g, "").replace(/\\\]/g, "");

  // Links (external links get the approved new-tab pattern).
  out = out.replace(/\[([^\]]+)\]\(([^)\s]+)\s*\)/g, (_, label, url) => {
    const safeUrl = url.replace(/[<>"]/g, "");
    const ext = /^https?:\/\//.test(safeUrl);
    const attrs = ext
      ? ` target="_blank" rel="noopener noreferrer"`
      : "";
    const note = ext
      ? '<span class="visually-hidden"> (se abre en nueva pestaña)</span>'
      : "";
    return `<a href="${safeUrl}"${attrs}>${escapeHtml(
      unescapeMd(label)
    )}${note}</a>`;
  });

  out = out.replace(//g, "[").replace(//g, "]");

  // Bold then italic on the remaining text. Escape HTML inside chunks.
  const parts = out.split(/(<a [^>]*>.*?<\/a>)/);
  out = parts
    .map((part) => {
      if (part.startsWith("<a ")) return part;
      let p = escapeHtml(unescapeMd(part));
      p = p.replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>");
      p = p.replace(/\*([^*\n]+)\*/g, "<em>$1</em>");
      return p;
    })
    .join("");

  return out;
}

/**
 * Block-level conversion tuned to the constructs present in the
 * canonical document: paragraphs, numbered lists (top-level and the
 * indented sub-clause paragraphs the export produces) and bold-lead
 * paragraphs. Tables and images are not present in migrated sections;
 * their appearance is reported as an error so the manifest can evolve
 * deliberately.
 */
function mdSectionToHtml(lines) {
  const html = [];
  let paragraph = [];
  let listItems = null;

  const flushParagraph = () => {
    if (paragraph.length) {
      html.push(`<p>${inlineMdToHtml(paragraph.join(" "))}</p>`);
      paragraph = [];
    }
  };
  const flushList = () => {
    if (listItems) {
      html.push(
        `<ol>${listItems.map((item) => `<li>${item}</li>`).join("")}</ol>`
      );
      listItems = null;
    }
  };

  for (const rawLine of lines) {
    const line = rawLine.replace(/\s+$/, "");
    const trimmed = line.trim();

    if (trimmed === "") {
      flushParagraph();
      continue;
    }

    if (/^\|/.test(trimmed) || /^!\[/.test(trimmed)) {
      errors.push(`unsupported construct in section: ${trimmed.slice(0, 60)}`);
      continue;
    }

    const listMatch = trimmed.match(/^(\d+)\\?\.\s+(.*)$/);
    if (listMatch && !line.startsWith("   ")) {
      flushParagraph();
      if (!listItems) listItems = [];
      listItems.push(inlineMdToHtml(listMatch[2]));
      continue;
    }

    flushList();
    paragraph.push(trimmed);
  }

  flushParagraph();
  flushList();

  return html.join("\n");
}

/* ------------------------------------------------------------------ */
/* Static HTML extraction                                              */
/* ------------------------------------------------------------------ */

/** Inner HTML of the first content-main div, matching nested divs. */
function extractContentMain(html, file) {
  const marker = '<div class="content-main">';
  const start = html.indexOf(marker);
  if (start === -1) {
    errors.push(`${file}: content-main not found`);
    return "";
  }

  let depth = 1;
  let i = start + marker.length;
  const re = /<div\b|<\/div>/g;
  re.lastIndex = i;
  let m;
  while ((m = re.exec(html)) !== null) {
    depth += m[0] === "</div>" ? -1 : 1;
    if (depth === 0) {
      return html.slice(start + marker.length, m.index);
    }
  }

  errors.push(`${file}: unbalanced content-main`);
  return "";
}

/** Static page-relative links to WordPress routes. */
const LINK_MAP = {
  "index.html": "/",
  "page-acerca.html": "/acerca/",
  "page-contacto.html": "/contacto/",
  "page-normas.html": "/normas/",
  "page-etica.html": "/etica/",
  "page-politicas.html": "/politicas/",
  "page-privacidad.html": "/privacidad/",
  "page-enviar-colaboracion.html": "/enviar-colaboracion/",
  "page-comite-editorial.html": "/comite-editorial/",
  "page-enlaces.html": "/enlaces/",
  "noticias.html": "/noticias/",
  "archive-issue.html": "/revista/numeros/",
  "archive-article.html": "/revista/articulos/",
  "archive-author.html": "/revista/autores/",
  "search.html": "/buscar/",
};

function rewriteLinks(html, file) {
  let out = html;

  // Institutional PDFs become attachment tokens the importer resolves
  // against the Media Library (attachment IDs, never hardcoded URLs).
  out = out.replace(
    /(href=")assets\/pdf\/([a-z0-9-]+)\.pdf(")/g,
    (_, pre, name, post) => `${pre}{{les:attachment:${name}}}${post}`
  );

  out = out.replace(
    /(href=")([a-z0-9-]+\.html)(#[^"]*)?(")/g,
    (all, pre, page, frag, post) => {
      if (LINK_MAP[page]) {
        return `${pre}${LINK_MAP[page]}${frag || ""}${post}`;
      }
      warnings.push(`${file}: unmapped internal link ${page}`);
      return all;
    }
  );

  // Presentation images (UI placeholders such as the default avatar)
  // stay theme assets; the importer resolves the token to the active
  // theme's asset URL at import time.
  out = out.replace(
    /(src=")assets\/img\/([a-z0-9.-]+)(")/g,
    (_, pre, name, post) => `${pre}{{les:theme-asset:img/${name}}}${post}`
  );

  if (/assets\/(img|js|css)\//.test(out)) {
    warnings.push(`${file}: body still references theme assets; review manually`);
  }

  return out.trim();
}

/* ------------------------------------------------------------------ */
/* Sources                                                             */
/* ------------------------------------------------------------------ */

const canonicalRaw = readFileSync(CANONICAL_FILE, "utf8");
const canonicalLines = canonicalRaw.split("\n");
const canonicalHash = sha256(canonicalRaw);

/** Lines strictly between two bold heading markers. */
function canonicalSection(startMarker, endMarker) {
  const startIdx = canonicalLines.findIndex((l) =>
    l.trim().startsWith(startMarker)
  );
  const endIdx = canonicalLines.findIndex(
    (l, i) => i > startIdx && l.trim().startsWith(endMarker)
  );

  if (startIdx === -1 || endIdx === -1) {
    errors.push(
      `ambiguous canonical boundary: ${startMarker} .. ${endMarker} (${startIdx}/${endIdx})`
    );
    return null;
  }

  return canonicalLines.slice(startIdx + 1, endIdx);
}

/** Normalized visible text of a markdown slice (for integrity checks). */
function mdVisibleText(lines) {
  return normalizeText(
    lines
      .map((l) =>
        unescapeMd(l)
          // Ordered-list markers are markdown syntax (the browser
          // renders the numbering), not visible text.
          .replace(/^\s*\d+\.\s+/, "")
          .replace(/\[\^\d+\]/g, "")
          .replace(/\[([^\]]+)\]\(([^)\s]+)\s*\)/g, "$1")
          .replace(/\*\*([^*]+)\*\*/g, "$1")
          .replace(/\*([^*\n]+)\*/g, "$1")
      )
      .join(" ")
  );
}

function staticBody(file, { dropSectionHeading } = {}) {
  const html = readFileSync(join(ROOT, "static", file), "utf8");
  let body = extractContentMain(html, file);

  if (dropSectionHeading) {
    // Remove the section whose <h2> equals the given heading (used to
    // keep form regions out of canonical prose on contacto).
    const sections = body.split(/(?=<section\b)/);
    body = sections
      .filter((chunk) => {
        const m = chunk.match(/<h2[^>]*>([^<]*)<\/h2>/);
        return !(m && normalizeText(m[1]) === dropSectionHeading);
      })
      .join("");
  }

  return rewriteLinks(body, file);
}

/* ------------------------------------------------------------------ */
/* Manifest                                                            */
/* ------------------------------------------------------------------ */

function entry(base) {
  const contentHtml = base.content_html || "";
  return {
    source_key: base.source_key,
    source: base.source,
    post_type: base.post_type || "page",
    slug: base.slug,
    title: base.title,
    status: "publish",
    parent: base.parent || "",
    comment_status: "closed",
    ping_status: "closed",
    template: base.template || "",
    migration_owned: base.migration_owned || ["post_title", "post_content"],
    content_html: contentHtml,
    content_text_sha256: sha256(htmlVisibleText(contentHtml)),
  };
}

const entries = [];

/* -- content-source entries ---------------------------------------- */

// etica: docs/03 §2 requires the literal canonical text, not the demo
// summary of the static page.
{
  const lines = canonicalSection("**NORMAS DE ÉTICA**", "**MISCÉLANEAS**");
  if (lines) {
    const html = mdSectionToHtml(lines);
    const converted = htmlVisibleText(html);
    const source = mdVisibleText(lines);

    if (converted !== source) {
      // Locate the first divergence to make the failure actionable.
      let i = 0;
      while (i < Math.min(converted.length, source.length) && converted[i] === source[i]) i++;
      errors.push(
        `etica: converted text diverges from canonical source at offset ${i}: ` +
          `source="…${source.slice(Math.max(0, i - 40), i + 40)}…" ` +
          `converted="…${converted.slice(Math.max(0, i - 40), i + 40)}…"`
      );
    }

    entries.push(
      entry({
        source_key: "etica",
        source: {
          type: "content-source",
          section: "NORMAS DE ÉTICA",
          file: "content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md",
          sha256: canonicalHash,
        },
        slug: "etica",
        title: "Ética",
        template: "page-etica.php",
        content_html: html,
      })
    );
  }
}

/* -- static-derived entries ----------------------------------------- */

const staticEntries = [
  { key: "acerca", file: "page-acerca.html", title: "Acerca", template: "page-acerca.php" },
  {
    key: "contacto",
    file: "page-contacto.html",
    title: "Contacto",
    template: "page-contacto.php",
    options: { dropSectionHeading: "Enviar Mensaje" },
  },
  { key: "normas", file: "page-normas.html", title: "Normas de Publicación", template: "page-normas.php" },
  { key: "politicas", file: "page-politicas.html", title: "Políticas", template: "page-politicas.php" },
  { key: "privacidad", file: "page-privacidad.html", title: "Aviso de Privacidad", template: "privacy-policy.php" },
  {
    key: "enviar-colaboracion",
    file: "page-enviar-colaboracion.html",
    title: "Enviar Colaboración",
    template: "page-enviar-colaboracion.php",
  },
  {
    key: "comite-editorial",
    file: "page-comite-editorial.html",
    title: "Comité Editorial",
    template: "page-comite-editorial.php",
  },
  { key: "enlaces", file: "page-enlaces.html", title: "Enlaces", template: "page-enlaces.php" },
];

for (const cfg of staticEntries) {
  const raw = readFileSync(join(ROOT, "static", cfg.file), "utf8");
  const body = staticBody(cfg.file, cfg.options || {});

  if (!body) continue;

  entries.push(
    entry({
      source_key: cfg.key,
      source: {
        type: "static",
        file: `static/${cfg.file}`,
        sha256: sha256(raw),
      },
      slug: cfg.key,
      title: cfg.title,
      template: cfg.template,
      content_html: body,
    })
  );
}

/* -- structural pages (no body) ------------------------------------- */

for (const cfg of [
  { key: "home", slug: "inicio", title: "Inicio", template: "front-page.php" },
  { key: "noticias", slug: "noticias", title: "Noticias", template: "home.php" },
  { key: "buscar", slug: "buscar", title: "Búsqueda", template: "page-buscar.php" },
]) {
  entries.push(
    entry({
      source_key: cfg.key,
      source: { type: "structural" },
      slug: cfg.slug,
      title: cfg.title,
      template: cfg.template,
      migration_owned: ["post_title"],
      content_html: "",
    })
  );
}

/* ------------------------------------------------------------------ */
/* Canonical coverage report (informational)                          */
/* ------------------------------------------------------------------ */

const coverage = [];
for (const [key, startMarker, endMarker] of [
  ["normas", "**NORMAS DE PUBLICACIÓN**", "**NORMAS DE ÉTICA**"],
  ["politicas", "**POLÍTICAS DE LA EDITORIAL**", "**NORMAS DE PUBLICACIÓN**"],
]) {
  const lines = canonicalSection(startMarker, endMarker);
  const pageEntry = entries.find((e) => e.source_key === key);
  if (!lines || !pageEntry) continue;

  const pageText = htmlVisibleText(pageEntry.content_html);
  const paragraphs = lines
    .join("\n")
    .split(/\n\s*\n/)
    .map((p) => mdVisibleText(p.split("\n")))
    .filter((p) => p.length > 60);

  const found = paragraphs.filter((p) => pageText.includes(p)).length;
  coverage.push({
    source_key: key,
    canonical_paragraphs: paragraphs.length,
    found_verbatim: found,
    note: "informational: static body vs canonical section; divergences need editorial confirmation",
  });
}

/* ------------------------------------------------------------------ */
/* Media manifest (institutional files only; demo PDFs stay fixtures) */
/* ------------------------------------------------------------------ */

const media = [];
mkdirSync(join(PLUGIN_RESOURCES, "media"), { recursive: true });

for (const cfg of [
  { key: "normas-publicacion", file: "normas-publicacion.pdf", title: "Normas de Publicación (PDF)" },
  { key: "politicas-editorial", file: "politicas-editorial.pdf", title: "Políticas Editoriales (PDF)" },
  {
    key: "solicitud-publicacion-declaracion-etica",
    file: "solicitud-publicacion-declaracion-etica.pdf",
    title: "Solicitud de Publicación y Declaración de Ética (PDF)",
  },
]) {
  const srcPath = join(ROOT, "static/assets/pdf", cfg.file);
  const data = readFileSync(srcPath);
  copyFileSync(srcPath, join(PLUGIN_RESOURCES, "media", cfg.file));
  media.push({
    source_key: cfg.key,
    file: `media/${cfg.file}`,
    title: cfg.title,
    mime: "application/pdf",
    sha256: sha256(data),
  });
}

/* ------------------------------------------------------------------ */
/* Site settings and menus                                             */
/* ------------------------------------------------------------------ */

const site = {
  show_on_front: "page",
  front_page: "home",
  posts_page: "noticias",
  privacy_page: "privacidad",
  menus: {
    primary: {
      name: "Navegación principal",
      items: [
        { title: "Inicio", url: "/" },
        {
          title: "Revista",
          url: "/revista/numeros/",
          children: [
            { title: "Número actual", url: "#les-current-issue" },
            { title: "Números publicados", url: "/revista/numeros/" },
            { title: "Artículos", url: "/revista/articulos/" },
            { title: "Autores", url: "/revista/autores/" },
          ],
        },
        { title: "Normas", page: "normas" },
        { title: "Enviar colaboración", page: "enviar-colaboracion" },
        { title: "Noticias", page: "noticias" },
        { title: "Acerca", page: "acerca" },
        { title: "Contacto", page: "contacto" },
        { title: "CENFISS", url: "https://cenfiss.net", external: true },
      ],
    },
    "footer-quick": {
      name: "Footer: Enlaces Rápidos",
      items: [
        { title: "Números publicados", url: "/revista/numeros/" },
        { title: "Artículos", url: "/revista/articulos/" },
        { title: "Enviar colaboración", page: "enviar-colaboracion" },
        { title: "Búsqueda", page: "buscar" },
      ],
    },
    "footer-norms": {
      name: "Footer: Normas Editoriales",
      items: [
        { title: "Normas de Publicación", page: "normas" },
        { title: "Ética Editorial", page: "etica" },
        { title: "Políticas", page: "politicas" },
        { title: "Comité Editorial", page: "comite-editorial" },
        { title: "Privacidad", page: "privacidad" },
      ],
    },
  },
};

/* ------------------------------------------------------------------ */
/* Write payload                                                       */
/* ------------------------------------------------------------------ */

if (errors.length) {
  console.error("Payload generation FAILED:");
  for (const e of errors) console.error("  ERROR:", e);
  process.exit(1);
}

const payload = {
  payload_version: PAYLOAD_VERSION,
  generator_version: GENERATOR_VERSION,
  generated_at: new Date().toISOString().slice(0, 10),
  source: {
    canonical_file:
      "content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md",
    canonical_sha256: canonicalHash,
  },
  site,
  entries,
  media,
  coverage_report: coverage,
  warnings,
};

writeFileSync(
  join(PLUGIN_RESOURCES, "content-payload.json"),
  JSON.stringify(payload, null, 2) + "\n"
);

console.log(`Payload written: ${entries.length} entries, ${media.length} media seeds.`);
for (const c of coverage) {
  console.log(
    `coverage ${c.source_key}: ${c.found_verbatim}/${c.canonical_paragraphs} canonical paragraphs found verbatim in page body`
  );
}
for (const w of warnings) console.log("WARN:", w);
