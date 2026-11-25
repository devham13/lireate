/**
 * Lu-модалки: вынести на body (stacking), выше backdrop и шапки.
 */
(function () {
  "use strict";
  if (
    !document.body ||
    (!document.body.classList.contains("lireate-home-dark") &&
      !document.body.classList.contains("lireate-cms-page-dark"))
  ) {
    return;
  }

  var portalSel = "#main-modal, .modal.modal-mail, .modal.mbg.apmodal, .modal.popup";

  function portalModals() {
    document.querySelectorAll(portalSel).forEach(function (el) {
      if (el.parentElement !== document.body) {
        document.body.appendChild(el);
      }
    });
  }

  function liftZ() {
    document.querySelectorAll(".modal-backdrop").forEach(function (bd) {
      bd.style.zIndex = "10040";
    });
    document.querySelectorAll(".modal.in, .modal.show").forEach(function (m) {
      m.style.zIndex = "10050";
    });
  }

  portalModals();
  if (window.jQuery) {
    jQuery(document).on("show.bs.modal shown.bs.modal", function () {
      portalModals();
      liftZ();
    });
    jQuery(document).on("hidden.bs.modal", function () {
      document.querySelectorAll(".modal-backdrop").forEach(function (b) {
        b.remove();
      });
    });
  }
  document.addEventListener("DOMContentLoaded", portalModals);
})();
