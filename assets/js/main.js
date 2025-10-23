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
    const navContainer = document.querySelector(".nav__container");

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

  // Acordeones opcionales para Normas/Ética
  function initAccordions() {
    const accordionToggles = document.querySelectorAll(
      "[data-accordion-toggle]"
    );

    accordionToggles.forEach(function (toggle) {
      toggle.addEventListener("click", function () {
        const targetId = this.getAttribute("data-accordion-toggle");
        const target = document.getElementById(targetId);
        const isExpanded = this.getAttribute("aria-expanded") === "true";

        if (target) {
          if (isExpanded) {
            target.style.display = "none";
            this.setAttribute("aria-expanded", "false");
            this.classList.remove("is-open");
          } else {
            target.style.display = "block";
            this.setAttribute("aria-expanded", "true");
            this.classList.add("is-open");
          }
        }
      });
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

      // Agregar indicador visual para screen readers
      const srText = document.createElement("span");
      srText.className = "visually-hidden";
      srText.textContent = " (abre en nueva ventana)";
      link.appendChild(srText);
    });
  }

  // Smooth scroll para anclas internas
  function initSmoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');

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

  // Lazy loading básico para imágenes
  function initLazyLoading() {
    if ("IntersectionObserver" in window) {
      const imageObserver = new IntersectionObserver(function (
        entries,
        observer
      ) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove("loading");
            observer.unobserve(img);
          }
        });
      });

      const lazyImages = document.querySelectorAll("img[data-src]");
      lazyImages.forEach(function (img) {
        img.classList.add("loading");
        imageObserver.observe(img);
      });
    }
  }

  // Mejorar formularios de búsqueda
  function initSearchForms() {
    const searchForms = document.querySelectorAll(".search-form");

    searchForms.forEach(function (form) {
      const input = form.querySelector('input[type="search"]');
      const button = form.querySelector('button[type="submit"]');

      if (input && button) {
        // Habilitar búsqueda con Enter
        input.addEventListener("keydown", function (event) {
          if (event.key === "Enter") {
            event.preventDefault();
            form.submit();
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

  // Inicialización cuando el DOM esté listo
  function init() {
    initMobileMenu();
    initSkipLink();
    initAccordions();
    initExternalLinks();
    initSmoothScroll();
    initLazyLoading();
    initSearchForms();
  }

  // Ejecutar cuando el DOM esté listo
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
