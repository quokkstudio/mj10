(() => {
  "use strict";

  const $ = (sel, root=document) => root.querySelector(sel);

  const init = () => {
    const header = $("#cnHeader");
    if (!header) return;

    const navbar = $("#cnNavbar");
    const topbar = $(".cn-topbar");
    const midbar = $(".cn-midbar");

    const searchWrap = $("#cnSearchWrap");
    const navSearchSlot = $("#cnNavSearchSlot");
    const searchHome = searchWrap ? searchWrap.parentElement : null;
    const searchAnchor = searchWrap ? searchWrap.nextElementSibling : null; // 원래 자리 기억

    const allBtn = $("#cnAllCatBtn");
    const mega = $("#cnMega");
    const megaGrid = $("#cnMegaGrid");
    const megaClose = $("#cnMegaClose");
    const backdrop = $("#cnBackdrop");
    const catSource = $("#cnCatSource");
    const catAllSource = $("#cnCatAllSource");
    const mobileMenuBtn = $("#cnMobileMenuBtn");

    const ensureSearchLayer = () => {
      if (!searchWrap) return;
      const layer = header.querySelector(".search_cont");
      if (layer && !searchWrap.contains(layer)) {
        searchWrap.appendChild(layer);
      }
    };

    const getStickThreshold = () => {
      const t = (topbar ? topbar.offsetHeight : 0);
      const m = (midbar ? midbar.offsetHeight : 0);
      return Math.max(40, t + m - 8);
    };

    const moveSearch = (toNav) => {
      if (!searchWrap || !navSearchSlot || !searchHome) return;
      const isInNav = searchWrap.parentElement === navSearchSlot;
      if (toNav && !isInNav) {
        navSearchSlot.appendChild(searchWrap);
        searchWrap.classList.add("cn-search--compact");
        ensureSearchLayer();
      }
      if (!toNav && isInNav) {
        if (searchAnchor && searchAnchor.parentElement === searchHome) {
          searchHome.insertBefore(searchWrap, searchAnchor);
        } else {
          searchHome.appendChild(searchWrap);
        }
        searchWrap.classList.remove("cn-search--compact");
        ensureSearchLayer();
      }
    };

    const setupSmoothScroll = () => {
      if (document.documentElement.dataset.smoothScroll === "on") return;
      const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      const isTouch = "ontouchstart" in window || navigator.maxTouchPoints > 0;
      const isFinePointer = window.matchMedia("(pointer: fine)").matches;
      if (prefersReduced || isTouch || !isFinePointer) return;

      let current = window.scrollY || document.documentElement.scrollTop || 0;
      let velocity = 0;
      let running = false;
      const accel = 0.12;
      const friction = 0.97;
      const maxVelocity = 20;

      // Avoid double-smoothing with CSS
      document.documentElement.style.scrollBehavior = "auto";

      const clampPosition = () => {
        const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
        if (current < 0) {
          current = 0;
          velocity = 0;
        } else if (current > maxScroll) {
          current = maxScroll;
          velocity = 0;
        }
      };

      const isScrollable = (el) => {
        let node = el;
        while (node && node !== document.body) {
          const style = window.getComputedStyle(node);
          const canScroll = /(auto|scroll)/.test(style.overflowY || "");
          if (canScroll && node.scrollHeight > node.clientHeight) return true;
          node = node.parentElement;
        }
        return false;
      };

      const update = () => {
        if (!running) return;
        current += velocity;
        clampPosition();
        window.scrollTo(0, current);
        velocity *= friction;
        if (Math.abs(velocity) < 0.2) {
          velocity = 0;
          running = false;
          return;
        }
        requestAnimationFrame(update);
      };

      const onWheel = (e) => {
        if (e.defaultPrevented || e.ctrlKey) return;
        if (isScrollable(e.target)) return;
        let delta = e.deltaY;
        if (e.deltaMode === 1) delta *= 16;
        if (e.deltaMode === 2) delta *= window.innerHeight;
        current = window.scrollY || document.documentElement.scrollTop || 0;
        velocity += delta * accel * 0.4;
        velocity = Math.max(-maxVelocity, Math.min(maxVelocity, velocity));
        if (!running) {
          running = true;
          requestAnimationFrame(update);
        }
        e.preventDefault();
      };

      window.addEventListener("wheel", onWheel, { passive: false });
      window.addEventListener("resize", () => {
        current = window.scrollY || document.documentElement.scrollTop || 0;
        clampPosition();
      });
      document.documentElement.dataset.smoothScroll = "on";
    };

    const onScroll = () => {
      const y = window.scrollY || document.documentElement.scrollTop;
      const stuck = y >= getStickThreshold();
      document.body.classList.toggle("cn-stuck", stuck);
      if (navbar) {
        navbar.classList.toggle("cn-fixed", stuck);
        // 높이 값 보정이 필요할 때를 대비해 CSS 변수에 저장
        const h = navbar.offsetHeight || 0;
        document.documentElement.style.setProperty("--cn-nav-h", `${h || 56}px`);
      }

      const isMobile = window.matchMedia("(max-width: 768px)").matches;
      if (!isMobile) moveSearch(stuck);

      if (mega && mega.dataset.open === "true") {
        syncMegaPos();
      }
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);

    const syncMegaPos = () => {
      if (!navbar || !mega || !allBtn) return;
      const btnRect = allBtn.getBoundingClientRect();
      const navRect = navbar.getBoundingClientRect();
      const scrollTop = window.scrollY || document.documentElement.scrollTop;
      const scrollLeft = window.scrollX || document.documentElement.scrollLeft;
      mega.style.top = Math.round(navRect.bottom + scrollTop - 1) + "px";
      mega.style.left = Math.round(btnRect.left + scrollLeft) + "px";
    };

    const hydrateMega = () => {
      if (!megaGrid) return;
      if (megaGrid.dataset.hydrated === "true") return;

      let markup = "";
      if (catSource) {
        const box = catSource.querySelector(".gnb_menu_box");
        markup = box ? box.innerHTML : catSource.innerHTML;
      }
      if (!markup && catAllSource) {
        markup = catAllSource.innerHTML;
      }
      if (markup) {
        megaGrid.innerHTML = markup;
        megaGrid.dataset.hydrated = "true";
      }
    };

    const setupAccordion = () => {
      if (!megaGrid) return;
      const items = megaGrid.querySelectorAll(".gnb_menu0 > li, .depth0 > li");
      items.forEach((li) => {
        const sub = li.querySelector(":scope > .depth1");
        if (!sub) return;
        if (li.querySelector(".cn-acc-btn")) return;
        li.classList.add("has-children");
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "cn-acc-btn";
        btn.setAttribute("aria-expanded", "false");
        btn.innerHTML = "<span class=\"cn-acc-icon\" aria-hidden=\"true\"></span>";
        li.appendChild(btn);
      });
    };

    const setMega = (open) => {
      if (!mega || !allBtn) return;
      hydrateMega();
      setupAccordion();
      mega.dataset.open = open ? "true" : "false";
      allBtn.setAttribute("aria-expanded", open ? "true" : "false");
      if (backdrop) backdrop.dataset.open = open ? "true" : "false";
      document.body.classList.toggle("cn-menu-open", open);
      if (open) {
        syncMegaPos();
      } else {
        mega.style.top = "0px";
        mega.style.left = "0px";
      }
    };

    const openMega = () => setMega(true);
    const closeMega = () => setMega(false);
    const toggleMega = () => {
      if (!mega) return;
      const open = mega.dataset.open === "true";
      setMega(!open);
    };

    if (allBtn) {
      allBtn.addEventListener("mouseenter", openMega);
      allBtn.addEventListener("mouseleave", closeMega);
      allBtn.addEventListener("focus", openMega);
      allBtn.addEventListener("click", (e) => { e.preventDefault(); openMega(); });
    }
    if (mega) {
      mega.addEventListener("mouseenter", openMega);
      mega.addEventListener("mouseleave", closeMega);
      mega.addEventListener("click", (e) => {
        const btn = e.target.closest(".cn-acc-btn");
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const li = btn.closest("li");
        if (!li) return;
        const isOpen = li.classList.toggle("is-open");
        btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
      mega.addEventListener("click", (e) => {
        const isMobile = window.matchMedia("(max-width: 768px)").matches;
        if (!isMobile || mega.dataset.open !== "true") return;
        const link = e.target.closest("a");
        if (!link) return;
        const li = link.closest("li.has-children");
        if (!li || !mega.contains(li)) return;
        e.preventDefault();
        e.stopPropagation();
        const isOpen = li.classList.toggle("is-open");
        const accBtn = li.querySelector(".cn-acc-btn");
        if (accBtn) accBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    }
    if (mobileMenuBtn) {
      mobileMenuBtn.addEventListener("click", (e) => {
        e.preventDefault();
        toggleMega();
      });
    }
    if (megaClose) megaClose.addEventListener("click", closeMega);
    if (backdrop) backdrop.addEventListener("click", closeMega);
    document.addEventListener("keyup", (e) => { if (e.key === "Escape") closeMega(); });

    const dedupeLists = () => {
      const selectors = [
        ".cn-gnb .depth0",
        ".cn-gnb .gnb_menu0",
        "#cnMegaGrid .depth0",
        "#cnMegaGrid .gnb_menu0",
      ];
      selectors.forEach((sel) => {
        const list = header.querySelector(sel);
        if (!list) return;
        const seen = new Set();
        Array.from(list.children).forEach((li) => {
          const link = li.querySelector("a");
          const key = link ? (link.getAttribute("href") || link.textContent || "").trim() : "";
          if (seen.has(key)) {
            li.remove();
          } else {
            seen.add(key);
          }
        });
      });
    };

    ensureSearchLayer();
    setupSmoothScroll();
    window.addEventListener("load", () => {
      ensureSearchLayer();
      dedupeLists();
    });
    // 메가가 열릴 때도 한 번 정리
    if (mega) mega.addEventListener("mouseenter", dedupeLists);
    if (allBtn) allBtn.addEventListener("mouseenter", dedupeLists);
    onScroll();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
