window.Bayti = {};

(() => {
  const homes = JSON.parse(document.querySelector("#makanHomes").textContent);
  const storageKey = "makan.favorite-homes";
  const favorites = new Set([1, 4]);
  try {
    const stored = JSON.parse(localStorage.getItem(storageKey));
    if (Array.isArray(stored)) {
      favorites.clear();
      stored.filter(id => homes.some(home => home.id === id)).forEach(id => favorites.add(id));
    }
  } catch {
    // Favorites stay available in memory when browser storage is unavailable.
  }

  Bayti.homes = {
    get: id => homes.find(home => home.id === Number(id)),
    byTitle: title => homes.find(home => home.title === title),
    isFavorite: id => favorites.has(id),
    toggleFavorite(id) {
      if (favorites.has(id)) favorites.delete(id);
      else favorites.add(id);
      try {
        localStorage.setItem(storageKey, JSON.stringify([...favorites]));
      } catch {
        // The current page can still update its favorite buttons.
      }
    },
    matching({ query = "", category = "", maxTotal = Infinity }) {
      const term = query.trim().toLowerCase();
      return homes.filter(home => (!category || home.category === category)
        && home.total <= maxTotal
        && (`${home.title} ${home.area}, Amman`).toLowerCase().includes(term));
    }
  };
})();
