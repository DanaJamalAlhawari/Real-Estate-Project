Bayti.assetImages = new URL("../../images/", document.currentScript.src).href;

Bayti.view = {
  cards(root) {
    return Array.from(root.querySelectorAll(".prop-card")).flatMap(card => {
      const title = card.querySelector(".prop-title");
      const home = card.dataset.homeId
        ? Bayti.homes.get(card.dataset.homeId)
        : title && Bayti.homes.byTitle(title.textContent.trim());
      if (!home) return [];
      card.querySelectorAll('[data-property-link]').forEach(link => {
        link.href += "?home=" + home.id;
      });
      const media = card.querySelector(".prop-media");
      if (media && home.photos.length > 1) this.addGallery(media, home);
      return [{ home, card, column: card.parentElement }];
    });
  },
  filterCards(cards, matches) {
    let count = 0;
    for (const { home, column } of cards) {
      const visible = matches.has(home);
      column.hidden = !visible;
      if (visible) count += 1;
    }
    return count;
  },
  addGallery(media, home) {
    const gallery = document.createElement("div");
    gallery.className = "card-gallery";
    gallery.dataset.home = home.id;
    gallery.dataset.photo = "0";
    media.before(gallery);
    gallery.append(media);
    gallery.insertAdjacentHTML("beforeend", `
      <button type="button" class="card-gallery-arrow card-gallery-arrow--previous"
        data-photo-step="-1" aria-label="Previous property photo"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
      <button type="button" class="card-gallery-arrow card-gallery-arrow--next"
        data-photo-step="1" aria-label="Next property photo"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
      <span class="visually-hidden" role="status"></span>
    `);
  },
  showPhoto(gallery, step) {
    const { photos } = Bayti.homes.get(gallery.dataset.home);
    const index = (Number(gallery.dataset.photo) + step + photos.length) % photos.length;
    const media = gallery.querySelector(".prop-media");
    const photo = document.createElement("img");
    const existing = media.querySelector(".card-gallery-photo");
    photo.className = "card-gallery-photo";
    photo.src = Bayti.assetImages + photos[index];
    photo.alt = "";
    if (existing) existing.replaceWith(photo);
    else media.prepend(photo);
    gallery.dataset.photo = index;
    gallery.querySelector('[role="status"]').textContent = `Photo ${index + 1} of ${photos.length}`;
  },
  favorite(card, saved) {
    const button = card.querySelector(".fav-btn");
    if (!button) return;
    button.classList.toggle("is-on", saved);
    button.setAttribute("aria-pressed", saved);
    button.setAttribute("aria-label", saved ? "Remove from favorites" : "Add to favorites");
    button.querySelector("i").className = saved ? "bi bi-heart-fill" : "bi bi-heart";
  },
  detail(main, home) {
    const back = main.querySelector(".back-link");
    const summary = document.createElement("section");
    summary.className = "panel mt-3";
    summary.innerHTML = `
      <img class="home-detail-image mb-3" alt="" />
      <h1></h1>
      <p class="text-muted"></p>
      <p class="h5 mb-0"></p>
      <small class="text-muted">Monthly total, bills included · Illustrative photo</small>
    `;
    summary.querySelector("img").src = Bayti.assetImages + home.photos[0];
    summary.querySelector("h1").textContent = home.title;
    summary.querySelector("p").textContent = `${home.area}, Amman · ${home.size} m²`;
    summary.querySelector(".h5").textContent = `${home.total} JOD`;
    main.replaceChildren(back, summary);
    document.title = home.title + " | Makan";
  }
};
