/**
 * Main JavaScript - Revista Logos et Spes
 * Compatible con WordPress (sin build tools)
 */

(function () {
  "use strict";

  // Toggle del menú móvil
  function initMobileMenu() {
    const navToggle = document.querySelector(".nav__toggle");
    const nav = document.querySelector(".nav");

    if (!navToggle || !nav) return;

    navToggle.addEventListener("click", function () {
      const isOpen = nav.classList.contains("is-open");

      if (isOpen) {
        nav.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      } else {
        nav.classList.add("is-open");
        navToggle.setAttribute("aria-expanded", "true");
      }
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener("click", function (event) {
      if (!nav.contains(event.target) && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });

    // Cerrar menú con Escape
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
        navToggle.focus();
      }
    });

    // Cerrar menú cuando el foco sale del todo por teclado (WCAG 2.4.11)
    nav.addEventListener("focusout", function (event) {
      if (
        nav.classList.contains("is-open") &&
        !nav.contains(event.relatedTarget)
      ) {
        nav.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  // Reflejar en aria-expanded el submenú que ya se muestra por :hover/:focus-within (CSS)
  function initSubmenuAria() {
    const submenuItems = document.querySelectorAll(".nav__item--has-submenu");

    submenuItems.forEach(function (item) {
      const trigger = item.querySelector(":scope > .nav__link[aria-haspopup]");
      if (!trigger) return;

      function open() {
        trigger.setAttribute("aria-expanded", "true");
      }

      function close(event) {
        if (!item.contains(event.relatedTarget)) {
          trigger.setAttribute("aria-expanded", "false");
        }
      }

      item.addEventListener("mouseenter", open);
      item.addEventListener("mouseleave", function () {
        if (!item.matches(":focus-within")) {
          trigger.setAttribute("aria-expanded", "false");
        }
      });
      item.addEventListener("focusin", open);
      item.addEventListener("focusout", close);
    });
  }

  // Fix de skip-link para navegadores antiguos
  function initSkipLink() {
    const skipLink = document.querySelector(".skip-link");

    if (!skipLink) return;

    skipLink.addEventListener("click", function (event) {
      event.preventDefault();

      const targetId = this.getAttribute("href").substring(1);
      const target = document.getElementById(targetId);

      if (target) {
        target.focus();
        target.scrollIntoView();
      }
    });
  }

  // Mejorar accesibilidad de enlaces externos
  function initExternalLinks() {
    const externalLinks = document.querySelectorAll(
      'a[href^="http"]:not([href*="' + window.location.hostname + '"])'
    );

    externalLinks.forEach(function (link) {
      link.setAttribute("target", "_blank");
      link.setAttribute("rel", "noopener noreferrer");

      // Evitar doble anuncio si el HTML ya trae el aviso escrito a mano
      const alreadyAnnounced = link.querySelector(".visually-hidden");
      if (alreadyAnnounced && /nueva pestaña/i.test(alreadyAnnounced.textContent)) {
        return;
      }

      const srText = document.createElement("span");
      srText.className = "visually-hidden";
      srText.textContent = " (se abre en nueva pestaña)";
      link.appendChild(srText);
    });
  }

  // Smooth scroll para anclas internas (excluye el skip-link, que initSkipLink ya maneja)
  function initSmoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]:not(.skip-link)');

    anchorLinks.forEach(function (link) {
      link.addEventListener("click", function (event) {
        const targetId = this.getAttribute("href").substring(1);
        const target = document.getElementById(targetId);

        if (target) {
          event.preventDefault();
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });

          // Actualizar URL sin recargar página
          history.pushState(null, null, "#" + targetId);
        }
      });
    });
  }

  // Mejorar formularios de búsqueda
  function initSearchForms() {
    const searchForms = document.querySelectorAll(".search-form");

    searchForms.forEach(function (form) {
      const input = form.querySelector('input[type="search"]');
      const button = form.querySelector('button[type="submit"]');

      if (input && button) {
        function submitIfNotEmpty(event) {
          if (!input.value.trim()) {
            event.preventDefault();
            input.focus();
            return;
          }
          form.submit();
        }

        // Habilitar búsqueda con Enter, con la misma validación que el botón
        input.addEventListener("keydown", function (event) {
          if (event.key === "Enter") {
            event.preventDefault();
            submitIfNotEmpty(event);
          }
        });

        // Deshabilitar envío si el campo está vacío
        button.addEventListener("click", function (event) {
          if (!input.value.trim()) {
            event.preventDefault();
            input.focus();
          }
        });
      }
    });
  }

  // Conservar en los campos los valores de búsqueda/filtro tras recargar (búsqueda y archivo de artículos)
  function initFormStatePersistence() {
    const params = new URLSearchParams(window.location.search);
    if (![...params.keys()].length) return;

    const forms = document.querySelectorAll(".search-form, .archive-filters__form");
    forms.forEach(function (form) {
      const fields = form.querySelectorAll("[name]");
      fields.forEach(function (field) {
        const value = params.get(field.name);
        if (value !== null) {
          field.value = value;
        }
      });
    });
  }

  // Inicialización cuando el DOM esté listo
  function init() {
    initMobileMenu();
    initSubmenuAria();
    initSkipLink();
    initExternalLinks();
    initSmoothScroll();
    initSearchForms();
    initFormStatePersistence();
  }

  // Ejecutar cuando el DOM esté listo
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
