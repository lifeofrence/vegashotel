const rooms = [
  { id: "astoria-deluxe", name: "Astoria Deluxe", description: "Luxury", rate: 85000, tags: ["Breakfast", "Wi‑Fi", "Gym"] },
  { id: "astoria-executive", name: "Astoria Executive", description: "Super Luxury", rate: 95000, tags: ["Breakfast", "Wi‑Fi", "Gym"] },
  { id: "royal-executive", name: "Royal Executive", description: "Executive Room", rate: 100000, tags: ["Breakfast", "Wi‑Fi"] },
  { id: "queens-luxury", name: "Queens Luxury", description: "Luxury room with jacuzzi", rate: 120000, tags: ["Jacuzzi", "Breakfast"] },
  { id: "vegas-suits", name: "Vegas Suits", description: "1 bedroom with living room", rate: 150000, tags: ["Living room", "Breakfast"] },
  { id: "lake-view", name: "Lake View", description: "1 bedroom with living room overseeing Niger River", rate: 200000, tags: ["River view", "Living room"] },
  { id: "ambassadoral-suits", name: "Ambassadoral Suits", description: "2 bedroom with dining and kitchen", rate: 250000, tags: ["Kitchen", "Dining"] },
  { id: "aficionado", name: "Aficionado (Banquet Hall)", description: "Capacity (1 – 500)", rate: 1500000, tags: ["Events", "Banquet"] },
  { id: "angels", name: "Angels (Board Room)", description: "Capacity (1 – 30)", rate: 300000, tags: ["Board room"] },
];

const fmt = (n) => n.toLocaleString("en-NG");

function renderRooms() {
  const list = document.getElementById("room-list");
  if (!list) return; // allow pages without a room grid
  list.innerHTML = "";
  rooms.forEach((r) => {
    const card = document.createElement("article");
    card.className = "room-card";
    card.innerHTML = `
      <div class="body">
        <h4 class="room-name">${r.name}</h4>
        <p>${r.description}</p>
        <div>${r.tags.map(t => `<span class="pill">${t}</span>`).join("")}</div>
        <p class="rate">₦${fmt(r.rate)} / night</p>
        <div class="actions">
          <button class="btn" data-room="${r.id}" aria-label="View ${r.name}">View</button>
          <button class="btn primary" data-book="${r.id}" aria-label="Book ${r.name}">Book</button>
        </div>
      </div>`;
    list.appendChild(card);
  });

  list.addEventListener("click", (e) => {
    const target = e.target;
    if (target.matches("[data-book]")) {
      const id = target.getAttribute("data-book");
      const select = document.getElementById("roomType");
      select.value = id;
      document.getElementById("book").scrollIntoView({ behavior: "smooth" });
    }
  });
}

function populateRoomSelect() {
  const select = document.getElementById("roomType");
  if (!select) return; // allow pages without booking form
  if (select.options && select.options.length > 0) return; // already populated in HTML
  rooms.forEach((r) => {
    const opt = document.createElement("option");
    opt.value = r.id;
    opt.textContent = `${r.name} — ₦${fmt(r.rate)}/night`;
    select.appendChild(opt);
  });
}

function showToast(msg, kind = "info") {
  // Render message at form head instead of toast
  const banner = document.getElementById("formMessage");
  if (!banner) return;
  banner.textContent = msg;
  banner.hidden = false;
  banner.classList.remove("success", "error");
  if (kind === "error") {
    banner.classList.add("error");
  } else {
    banner.classList.add("success");
  }
}

function dateDiffDays(a, b) {
  const MS = 24 * 60 * 60 * 1000;
  return Math.round((b - a) / MS);
}

function calcPrice() {
  const roomId = document.getElementById("roomType").value;
  const room = rooms.find((r) => r.id === roomId);
  const checkIn = new Date(document.getElementById("checkIn").value);
  const checkOut = new Date(document.getElementById("checkOut").value);

  if (!(room && checkIn instanceof Date && !isNaN(checkIn) && checkOut instanceof Date && !isNaN(checkOut))) {
    showToast("Please select room and valid dates.", "error");
    return null;
  }
  const nights = dateDiffDays(checkIn, checkOut);
  if (nights <= 0) {
    showToast("Check‑out must be after check‑in.", "error");
    return null;
  }
  const total = nights * room.rate;
  document.getElementById("summary-nights").textContent = String(nights);
  document.getElementById("summary-rate").textContent = fmt(room.rate);
  document.getElementById("summary-total").textContent = fmt(total);
  document.getElementById("price-summary").hidden = false;
  return { nights, total, rate: room.rate, room };
}

function saveBooking(booking) {
  const key = "va_bookings";
  const existing = JSON.parse(localStorage.getItem(key) || "[]");
  existing.push(booking);
  localStorage.setItem(key, JSON.stringify(existing));
}

function randomRef() {
  return "VA" + Math.random().toString(36).slice(2, 8).toUpperCase();
}

function setupForm() {
  const form = document.getElementById("booking-form");
  if (!form) return; // gracefully skip if form not present
  let lastPrice = null;
  const calcBtn = document.getElementById("calcPrice");
  if (calcBtn) calcBtn.addEventListener("click", () => { lastPrice = calcPrice(); });
  form.addEventListener("submit", (e) => {
    // If we're confirming from the modal, allow normal submit
    if (form.dataset.confirm === "true") {
      // clear the flag for future submissions
      form.dataset.confirm = "";
      return;
    }

    const price = lastPrice || calcPrice();
    if (!price) {
      e.preventDefault();
      return;
    }

    const fullName = document.getElementById("fullName").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    if (!fullName || !email || !phone) {
      e.preventDefault();
      showToast("Please fill your contact details.", "error");
      return;
    }

    // Set hidden nightly price for backend
    const priceField = document.getElementById("pricePerNight");
    if (priceField) priceField.value = String(price.rate);

    // Convert date inputs to m/d/Y into hidden fields for PHP backend
    const toMDY = (val) => {
      // expects YYYY-MM-DD
      const parts = String(val).split("-");
      if (parts.length !== 3) return "";
      const [y, m, d] = parts;
      return `${m}/${d}/${y}`;
    };
    const ci = document.getElementById("checkIn").value;
    const co = document.getElementById("checkOut").value;
    const ciHidden = document.getElementById("checkinHidden");
    const coHidden = document.getElementById("checkoutHidden");
    if (ciHidden) ciHidden.value = toMDY(ci);
    if (coHidden) coHidden.value = toMDY(co);

    // Show confirmation modal instead of submitting immediately
    e.preventDefault();
    const modal = document.getElementById("confirmModal");
    if (!modal) return; // safety
    const getText = (id) => document.getElementById(id);
    const getVal = (id, def = "") => {
      const el = document.getElementById(id);
      return el ? el.value : def;
    };
    const roomId = getVal("roomType", "");
    const room = rooms.find(r => r.id === roomId);
    const fullNameVal = getVal("fullName", "").trim();
    const emailVal = getVal("email", "").trim();
    const phoneVal = getVal("phone", "").trim();
    const guestsVal = getVal("guests", "1");
    const nofroomVal = getVal("nofroom", "1");
    const ciStr = getVal("checkIn", "");
    const coStr = getVal("checkOut", "");
    const dateFmt = (val) => {
      const d = new Date(val);
      return isNaN(d) ? val : d.toLocaleDateString("en-NG", { year: "numeric", month: "short", day: "numeric" });
    };
    if (getText("m-name")) getText("m-name").textContent = fullNameVal;
    if (getText("m-email")) getText("m-email").textContent = emailVal;
    if (getText("m-phone")) getText("m-phone").textContent = phoneVal;
    if (getText("m-room")) getText("m-room").textContent = room ? room.name : roomId;
    if (getText("m-nofroom")) getText("m-nofroom").textContent = nofroomVal;
    if (getText("m-guests")) getText("m-guests").textContent = guestsVal;
    if (getText("m-checkin")) getText("m-checkin").textContent = dateFmt(ciStr);
    if (getText("m-checkout")) getText("m-checkout").textContent = dateFmt(coStr);
    if (getText("m-nights")) getText("m-nights").textContent = String(price.nights);
    if (getText("m-rate")) getText("m-rate").textContent = fmt(price.rate);
    if (getText("m-total")) getText("m-total").textContent = fmt(price.total);
    modal.hidden = false;
  });

  // Modal actions
  const modal = document.getElementById("confirmModal");
  const editBtn = document.getElementById("modalEdit");
  const confirmBtn = document.getElementById("modalConfirm");
  if (editBtn) editBtn.addEventListener("click", () => { if (modal) modal.hidden = true; });
  if (confirmBtn) confirmBtn.addEventListener("click", () => {
    if (modal) modal.hidden = true;
    // set flag so submit handler lets it through
    form.dataset.confirm = "true";
    form.submit();
  });
}

function init() {
  renderRooms();
  populateRoomSelect();
  setupForm();
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();
  // Show success or error message from redirect
  const params = new URLSearchParams(window.location.search);
  const msg = params.get("message");
  const err = params.get("error");
  if (msg) showToast(msg, "info");
  if (err) showToast(err, "error");
}

// Ensure init runs whether the script loads before or after DOMContentLoaded
(function(){
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();