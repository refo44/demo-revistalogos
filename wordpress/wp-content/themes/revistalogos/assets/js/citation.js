/**
 * Citation tools for single-article (copy, export, RIS download).
 * Data-driven replacement of the static prototype's inline script
 * (moved out per ADR 0012 §5 pre-audit checklist); all citation data
 * comes from the DOM rendered by PHP.
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var currentDateEl = document.getElementById("current-date");
    if (currentDateEl) {
      currentDateEl.textContent = new Date().toLocaleDateString("es-ES");
    }

    var copyStatus = document.getElementById("citation-copy-status");

    document.querySelectorAll(".citation-copy").forEach(function (button) {
      button.addEventListener("click", function () {
        var format = this.getAttribute("data-format");
        var citationText = this.previousElementSibling;
        var textToCopy;

        if (format === "bibtex") {
          textToCopy = citationText.querySelector("pre").textContent;
        } else {
          textToCopy = citationText.textContent.trim();
        }

        var self = this;
        navigator.clipboard
          .writeText(textToCopy)
          .then(function () {
            var originalHTML = self.innerHTML;
            self.textContent = "✅ Copiado";
            self.classList.add("is-copied");
            if (copyStatus) {
              copyStatus.textContent = "✅ Copiado";
            }

            setTimeout(function () {
              self.innerHTML = originalHTML;
              self.classList.remove("is-copied");
            }, 2000);
          })
          .catch(function (err) {
            console.error("Error copying text: ", err);
            alert("Error al copiar la cita");
          });
      });
    });

    function downloadText(content, filename) {
      var blob = new Blob([content], { type: "text/plain" });
      var url = window.URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }

    var exportAll = document.getElementById("export-all-citations");
    if (exportAll) {
      exportAll.addEventListener("click", function () {
        var citations = [];

        document.querySelectorAll(".citation-format").forEach(function (format) {
          var title = format.querySelector("h3").textContent;
          var text = format.querySelector(".citation-text");
          var citationText;

          if (title === "BibTeX") {
            citationText = text.querySelector("pre").textContent;
          } else {
            citationText = text.textContent.trim();
          }

          citations.push(title + ":\n" + citationText + "\n");
        });

        downloadText(
          citations.join("\n---\n\n"),
          this.getAttribute("data-filename") || "citas-articulo.txt"
        );
      });
    }

    var downloadRis = document.getElementById("download-ris");
    if (downloadRis) {
      downloadRis.addEventListener("click", function () {
        var risData = document.getElementById("ris-data");
        if (!risData) {
          return;
        }

        downloadText(
          risData.textContent,
          this.getAttribute("data-filename") || "articulo.ris"
        );
      });
    }
  });
})();
