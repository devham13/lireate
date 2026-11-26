/* LIREATE Light V4 home — ES5, scoped to .lh4-root */
(function () {
  "use strict";
  var body = document.body;
  if (!body || !body.classList.contains("lireate-home-light-v4")) {
    return;
  }
  body.classList.remove("lireate-cms-page-dark", "lireate-home-dark");
  body.style.backgroundColor = "#ffffff";
  body.style.color = "#1f212c";
  var footerCss = document.querySelector('link[href*="lireate-blog-footer.css"]');
  if (footerCss && !document.querySelector('link[href*="lireate-blog-footer-light-v4.css"]')) {
    var ver = "20261126r21";
    var headV4 = document.querySelector('link[href*="lireate-light-v4.css?v="]');
    if (headV4 && headV4.href) {
      var m = headV4.href.match(/[?&]v=([^&]+)/);
      if (m) {
        ver = m[1];
      }
    }
    var lightFooter = document.createElement("link");
    lightFooter.rel = "stylesheet";
    lightFooter.href =
      "/template/page/news/lireate-blog-footer-light-v4.css?v=" + encodeURIComponent(ver);
    footerCss.parentNode.insertBefore(lightFooter, footerCss.nextSibling);
  }
  var legacyHome = document.getElementById("lireate-home");
  if (legacyHome) {
    legacyHome.style.display = "none";
    legacyHome.setAttribute("aria-hidden", "true");
  }
  var root = document.getElementById("lireate-home-light-v4");
  if (!root) {
    return;
  }
  var scope = root.classList.contains("lh4-root") ? root : root.querySelector(".lh4-root");
  if (!scope) {
    return;
  }
  var each = function (list, fn) {
    Array.prototype.forEach.call(list, fn);
  };

  each(scope.querySelectorAll(".expert-photo img,.avatar img"), function (img) {
    var fallback = function () {
      var parent = img.parentElement;
      if (!parent || parent.querySelector(".portrait-fallback")) {
        return;
      }
      var words = (img.alt || "Лирейт").trim().split(/\s+/).slice(0, 2);
      var initials = words
        .map(function (word) {
          return word[0] || "";
        })
        .join("")
        .toUpperCase();
      img.hidden = true;
      img.style.display = "none";
      var holder = document.createElement("div");
      holder.className = "portrait-fallback";
      holder.textContent = initials || "Л";
      holder.setAttribute("aria-hidden", "true");
      parent.appendChild(holder);
    };
    img.addEventListener("error", fallback, false);
    if (img.complete && img.naturalWidth === 0) {
      fallback();
    }
  });

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  var motionOK = !reduceMotion.matches;
  var progress = scope.querySelector(".scroll-progress");
  var sections = scope.querySelectorAll(".reveal");
  var track = scope.querySelector("#lh4-team-track");
  var filters = scope.querySelectorAll(".filter");

  if (motionOK && "IntersectionObserver" in window) {
    body.classList.add("motion-ready");
    var observer = new IntersectionObserver(
      function (entries) {
        each(entries, function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.08, rootMargin: "0px 0px -28px 0px" }
    );
    each(sections, function (el) {
      observer.observe(el);
    });
  } else {
    each(sections, function (el) {
      el.classList.add("visible");
    });
  }

  var syncScroll = function () {
    var max = document.documentElement.scrollHeight - window.innerHeight;
    var value = max > 0 ? Math.min(1, Math.max(0, window.pageYOffset / max)) : 0;
    if (progress) {
      progress.style.transform = "scaleX(" + value + ")";
    }
  };
  window.addEventListener("scroll", syncScroll, { passive: true });
  window.addEventListener("resize", syncScroll, { passive: true });
  syncScroll();

  var countNodes = scope.querySelectorAll(".count");
  var animateCounter = function (node) {
    if (node.dataset.done === "1") {
      return;
    }
    node.dataset.done = "1";
    var from = Number(node.getAttribute("data-from") || 0);
    var to = Number(node.getAttribute("data-to") || 0);
    if (!motionOK) {
      return;
    }
    var duration = 1250;
    var start = performance.now();
    var draw = function (now) {
      var u = Math.min(1, (now - start) / duration);
      var eased = 1 - Math.pow(1 - u, 3);
      node.textContent = String(Math.round(from + (to - from) * eased));
      if (u < 1) {
        requestAnimationFrame(draw);
      } else {
        node.textContent = String(to);
      }
    };
    requestAnimationFrame(draw);
  };
  if ("IntersectionObserver" in window) {
    var ci = new IntersectionObserver(
      function (entries) {
        each(entries, function (e) {
          if (e.isIntersecting) {
            animateCounter(e.target);
            ci.unobserve(e.target);
          }
        });
      },
      { threshold: 0.5 }
    );
    each(countNodes, function (node) {
      ci.observe(node);
    });
  } else {
    each(countNodes, animateCounter);
  }

  var heroRight = scope.querySelector(".hero .hero-right");
  var hero = scope.querySelector(".hero");
  if (hero && heroRight && motionOK && window.matchMedia("(pointer:fine)").matches) {
    hero.addEventListener(
      "pointermove",
      function (ev) {
        var r = hero.getBoundingClientRect();
        var x = (ev.clientX - r.left) / r.width - 0.5;
        var y = (ev.clientY - r.top) / r.height - 0.5;
        heroRight.style.setProperty("--hero-x", (x * 9).toFixed(2) + "px");
        heroRight.style.setProperty("--hero-y", (y * 7).toFixed(2) + "px");
      },
      { passive: true }
    );
    hero.addEventListener("pointerleave", function () {
      heroRight.style.setProperty("--hero-x", "0px");
      heroRight.style.setProperty("--hero-y", "0px");
    });
  }

  if (motionOK && window.matchMedia("(pointer:fine)").matches) {
    each(scope.querySelectorAll(".service-card,.why-card,.case-card,.blog-card,.price-card"), function (card) {
      card.addEventListener(
        "pointermove",
        function (ev) {
          var r = card.getBoundingClientRect();
          card.style.setProperty("--mx", ev.clientX - r.left + "px");
          card.style.setProperty("--my", ev.clientY - r.top + "px");
        },
        { passive: true }
      );
    });
  }

  var visibleCards = function () {
    if (!track) {
      return [];
    }
    return Array.prototype.filter.call(track.querySelectorAll(".team-card"), function (card) {
      return !card.hidden;
    });
  };
  var stepWidth = function () {
    var cards = visibleCards();
    var card = cards[0];
    if (!card) {
      return 224;
    }
    var style = window.getComputedStyle(track);
    var gap = parseFloat(style.columnGap || style.gap || "18");
    if (isNaN(gap)) {
      gap = 18;
    }
    return card.getBoundingClientRect().width + gap;
  };
  var moveTeam = function (direction) {
    if (!track) {
      return;
    }
    var step = stepWidth() * direction;
    var end = track.scrollWidth - track.clientWidth;
    var behavior = motionOK ? "smooth" : "auto";
    if (direction > 0 && track.scrollLeft >= end - 10) {
      track.scrollTo({ left: 0, behavior: behavior });
    } else if (direction < 0 && track.scrollLeft <= 2) {
      track.scrollTo({ left: end, behavior: behavior });
    } else {
      track.scrollBy({ left: step, behavior: behavior });
    }
  };
  var prevTeamBtn = scope.querySelector("#lh4-prev-team");
  var nextTeamBtn = scope.querySelector("#lh4-next-team");
  if (prevTeamBtn) {
    prevTeamBtn.addEventListener("click", function () {
      moveTeam(-1);
    });
  }
  if (nextTeamBtn) {
    nextTeamBtn.addEventListener("click", function () {
      moveTeam(1);
    });
  }
  if (track) {
    track.addEventListener("keydown", function (ev) {
      if (ev.key === "ArrowRight") {
        ev.preventDefault();
        moveTeam(1);
      }
      if (ev.key === "ArrowLeft") {
        ev.preventDefault();
        moveTeam(-1);
      }
    });
  }
  each(filters, function (btn) {
    btn.addEventListener("click", function () {
      each(filters, function (b) {
        var active = b === btn;
        b.classList.toggle("active", active);
        b.setAttribute("aria-pressed", String(active));
      });
      var group = btn.getAttribute("data-filter");
      if (!track) {
        return;
      }
      each(track.querySelectorAll(".team-card"), function (card, i) {
        var show = group === "all" || group === card.getAttribute("data-group");
        card.hidden = !show;
        card.classList.remove("filter-enter");
        if (show && motionOK) {
          void card.offsetWidth;
          card.style.animationDelay = Math.min(i, 6) * 35 + "ms";
          card.classList.add("filter-enter");
        }
      });
      track.scrollTo({ left: 0, behavior: motionOK ? "smooth" : "auto" });
    });
  });

  var carouselOnScreen = false;
  var carouselPaused = false;
  if (track && motionOK) {
    if ("IntersectionObserver" in window) {
      var io = new IntersectionObserver(
        function (es) {
          carouselOnScreen = es.some(function (e) {
            return e.isIntersecting;
          });
        },
        { threshold: 0.35 }
      );
      io.observe(track);
    } else {
      carouselOnScreen = true;
    }
    track.addEventListener("mouseenter", function () {
      carouselPaused = true;
    });
    track.addEventListener("mouseleave", function () {
      carouselPaused = false;
    });
    track.addEventListener("focusin", function () {
      carouselPaused = true;
    });
    track.addEventListener("focusout", function () {
      carouselPaused = false;
    });
    track.addEventListener("pointerdown", function () {
      carouselPaused = true;
    });
    track.addEventListener("pointerup", function () {
      carouselPaused = false;
    });
    each(scope.querySelectorAll("#lh4-prev-team,#lh4-next-team,.filter"), function (x) {
      x.addEventListener("focus", function () {
        carouselPaused = true;
      });
    });
    var auto = setInterval(function () {
      if (
        carouselOnScreen &&
        !carouselPaused &&
        !document.hidden &&
        !reduceMotion.matches &&
        track.scrollWidth > track.clientWidth + 10
      ) {
        moveTeam(1);
      }
    }, 5800);
    window.addEventListener(
      "pagehide",
      function () {
        clearInterval(auto);
      },
      { once: true }
    );
  }

  var stageData =
    window.lireateHomeProcessStages && window.lireateHomeProcessStages.length
      ? window.lireateHomeProcessStages
      : [];
  var stepButtons = scope.querySelectorAll(".process-new .process-card[data-step]");
  var detail = scope.querySelector("#step-panel");
  if (stageData.length && stepButtons.length && detail) {
    var selectedStep = 0;
    var switchVersion = 0;
    var setStep = function (i, focus) {
      var n = (i + stageData.length) % stageData.length;
      var data = stageData[n];
      var version = ++switchVersion;
      selectedStep = n;
      each(stepButtons, function (btn, k) {
        var active = k === n;
        btn.classList.toggle("active", active);
        btn.setAttribute("aria-selected", String(active));
        btn.tabIndex = active ? 0 : -1;
      });
      var prog = scope.querySelector("#processProgress");
      if (prog) {
        prog.style.width = ((n + 1) / stageData.length) * 100 + "%";
      }
      detail.classList.toggle("is-switching", motionOK);
      var redraw = function () {
        if (version !== switchVersion) {
          return;
        }
        var counter = detail.querySelector("#detailCounter");
        var mark = detail.querySelector("#detailMark");
        var title = detail.querySelector("#detailTitle");
        var intro = detail.querySelector("#detailIntro");
        var tasks = detail.querySelector("#detailTasks");
        var result = detail.querySelector("#detailResult");
        var resultText = detail.querySelector("#detailResultText");
        var action = detail.querySelector("#detailAction");
        if (counter) {
          counter.textContent = "0" + (n + 1) + " / 03";
        }
        if (mark) {
          mark.textContent = "0" + (n + 1);
        }
        if (title) {
          title.textContent = data.title;
        }
        if (intro) {
          intro.textContent = data.intro;
        }
        if (tasks) {
          while (tasks.firstChild) {
            tasks.removeChild(tasks.firstChild);
          }
          each(data.tasks || [], function (item) {
            var li = document.createElement("li");
            li.textContent = item;
            tasks.appendChild(li);
          });
        }
        if (result) {
          result.textContent = data.result;
        }
        if (resultText) {
          resultText.textContent = data.resultText;
        }
        if (action) {
          action.setAttribute("href", data.url || "#");
          var label = action.querySelector(".action-label");
          if (label) {
            label.textContent = data.action;
          }
        }
        detail.setAttribute("aria-labelledby", stepButtons[n].id);
        detail.setAttribute("data-current", String(n));
        detail.classList.remove("is-switching");
      };
      if (motionOK) {
        setTimeout(redraw, 180);
      } else {
        redraw();
      }
      if (focus) {
        stepButtons[n].focus({ preventScroll: true });
      }
    };
    each(stepButtons, function (btn, k) {
      btn.addEventListener("click", function () {
        setStep(k);
      });
      btn.addEventListener("keydown", function (e) {
        var next;
        if (e.key === "ArrowRight" || e.key === "ArrowDown") {
          next = k + 1;
        } else if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
          next = k - 1;
        } else if (e.key === "Home") {
          next = 0;
        } else if (e.key === "End") {
          next = stageData.length - 1;
        }
        if (next !== undefined) {
          e.preventDefault();
          setStep(next, true);
        }
      });
    });
    var prevProc = scope.querySelector("#prevProcess");
    var nextProc = scope.querySelector("#nextProcess");
    if (prevProc) {
      prevProc.addEventListener("click", function () {
        setStep(selectedStep - 1);
      });
    }
    if (nextProc) {
      nextProc.addEventListener("click", function () {
        setStep(selectedStep + 1);
      });
    }
    setStep(0);
  }
})();