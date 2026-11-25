(() => {
  const header = document.querySelector(".site-header");
  if (header) {
    const updateHeader = () => header.classList.toggle("is-scrolled", scrollY > 4);
    addEventListener("scroll", updateHeader, { passive: true });
    updateHeader();
  }

  document.addEventListener("submit", event => {
    const form = event.target.closest("[data-demo-form]");
    if (!form) return;

    event.preventDefault();
    if (form.querySelector("[data-demo-notice]")) return;

    const notice = document.createElement("p");
    notice.dataset.demoNotice = "";
    notice.className = "small text-muted mb-0";
    notice.setAttribute("role", "status");
    notice.textContent = window.makanTranslate("This feature is not available yet.");
    form.prepend(notice);
  });
})();
