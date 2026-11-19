(() => {
  const { homes, view } = Bayti;
  document.addEventListener("submit", event => {
    const form = event.target.closest("[data-demo-form]");
    if (!form) return;
    event.preventDefault();
    if (form.dataset.demoNext) {
      location.assign(form.dataset.demoNext);
      return;
    }
    if (form.querySelector("[data-demo-notice]")) return;
    const notice = document.createElement("p");
    notice.dataset.demoNotice = "";
    notice.className = "small text-muted mb-0";
    notice.setAttribute("role", "status");
    notice.textContent = "Demo preview. Changes are not saved.";
    form.prepend(notice);
  });
  const params = new URLSearchParams(location.search);
  const header = document.querySelector(".site-header");
  if (header) {
    const updateHeader = () => header.classList.toggle("is-scrolled", scrollY > 4);
    addEventListener("scroll", updateHeader, { passive: true });
    updateHeader();
  }

  document.addEventListener("click", event => {
    const arrow = event.target.closest("[data-photo-step]");
    if (arrow) view.showPhoto(arrow.closest(".card-gallery"), Number(arrow.dataset.photoStep));
  });

  const search = document.querySelector("#allHomesGrid");
  if (!search) {
    const landing = document.querySelector("#homesGrid");
    if (landing) {
      const cards = view.cards(landing);
      const filters = document.querySelector(".home-filters");
      const status = document.querySelector("#homesFilterStatus");
      filters.addEventListener("click", event => {
        const button = event.target.closest("[data-home-category]");
        if (!button) return;
        const matches = new Set(homes.matching({ category: button.dataset.homeCategory }));
        view.filterCards(cards, matches);
        filters.querySelectorAll("[data-home-category]").forEach(option => {
          option.setAttribute("aria-pressed", option === button);
        });
        status.textContent = matches.size ? "" : "No homes in this category yet.";
      });
    }
    const home = homes.get(params.get("home"));
    const gallery = document.querySelector("#propertyGallery");
    if (gallery && home && home.id !== 1) view.detail(gallery.closest("main"), home);
    return;
  }

  const rawBudget = params.get("max") || "";
  const budget = Number(rawBudget);
  const hasBudget = rawBudget.trim() !== "" && Number.isFinite(budget) && budget >= 0;
  const filters = {
    query: params.get("q") || "",
    category: params.get("category") || "",
    maxTotal: hasBudget ? budget : Infinity
  };
  const relevant = new Set(homes.matching(filters));
  const sidebar = document.querySelector("#seekerFilters");
  sidebar.elements.q.value = filters.query;
  sidebar.elements.max.value = hasBudget ? budget : "";
  for (const option of sidebar.elements.category) {
    option.checked = option.value === filters.category;
  }
  if (!sidebar.elements.category.value) sidebar.elements.category[0].checked = true;
  const navSearch = document.querySelector(".nav-search input");
  navSearch.value = filters.query;
  const favoritesGrid = document.querySelector("#favoritesGrid");
  const grids = [search, document.querySelector("#matchesGrid"), favoritesGrid].map(grid => ({
    grid,
    cards: grid === favoritesGrid ? [] : view.cards(grid),
    caption: grid.closest(".tab-pane").querySelector("p")
  }));

  function render() {
    for (const entry of grids) {
      const favoritesOnly = entry.grid === favoritesGrid;
      if (favoritesOnly) {
        entry.cards = grids[0].cards.filter(({ home }) => homes.isFavorite(home.id)).map(item => {
          const column = item.column.cloneNode(true);
          return { home: item.home, card: column.querySelector(".prop-card"), column };
        });
        entry.grid.replaceChildren(...entry.cards.map(item => item.column));
      }
      const count = view.filterCards(entry.cards, relevant);
      for (const { home, card } of entry.cards) {
        view.favorite(card, homes.isFavorite(home.id));
      }
      const label = favoritesOnly ? "saved " : "";
      entry.caption.textContent = count ? `${count} ${label}${count === 1 ? "home" : "homes"}` : `No ${label}homes match this view.`;
    }
  }

  document.querySelector(".tab-content").addEventListener("click", event => {
    const button = event.target.closest(".fav-btn");
    if (!button) return;
    const favoriteTab = button.closest("#favoritesGrid");
    const home = homes.byTitle(button.closest(".prop-card").querySelector(".prop-title").textContent.trim());
    if (!home) return;
    homes.toggleFavorite(home.id);
    render();
    if (favoriteTab) document.querySelector("#favorites-tab").focus();
  });

  render();
  if (filters.query || filters.category || hasBudget) {
    bootstrap.Tab.getOrCreateInstance(document.querySelector("#all-tab")).show();
  }
})();
