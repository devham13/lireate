(function () {
  "use strict";
  var body = document.body;

  if (body.classList.contains("lireate-reviews-body")) {
    var box = document.querySelector("#face .contentBox");
    if (box && box.getAttribute("data-lx-static-hero") !== "1") {
      box.setAttribute("data-lx-static-hero", "1");
      var descs = box.querySelectorAll(".titleDesc");
      if (descs.length >= 1) {
        descs[0].textContent = "На рынке интеллектуальной собственности с 2015 года";
      }
      if (descs.length >= 2) {
        descs[1].textContent = "15 специалистов в команде";
      }
      for (var i = 2; i < descs.length; i++) {
        descs[i].style.display = "none";
      }
    }
  }

  if (body.classList.contains("lireate-spasibo-body")) {
    var txt = document.querySelector(".lireate-spasibo-body .txt-content");
    if (txt && !txt.querySelector(".lx-thanks-contacts")) {
      var contacts = document.createElement("p");
      contacts.className = "lx-thanks-contacts";
      contacts.style.textAlign = "center";
      contacts.style.marginTop = "16px";
      contacts.innerHTML =
        '<a href="tel:+74999384595">+7 (499) 938-45-95</a><a href="tel:+79600484957">+7 960 048-49-57</a>';
      txt.appendChild(contacts);
    }
    var home = txt && txt.querySelector('a[href*="lireate"], a[href="/"]');
    if (home) {
      home.setAttribute("href", "/");
    }
  }
})();
