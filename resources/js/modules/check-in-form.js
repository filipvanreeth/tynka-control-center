function onActionKeydown(event, callback) {
  if (event.key === "Enter" || event.key === " ") {
    event.preventDefault();
    callback();
  }
}

function getBrusselsDateTimeValue() {
  const now = new Date();

  const formatter = new Intl.DateTimeFormat("sv-SE", {
    timeZone: "Europe/Brussels",
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });

  const parts = Object.fromEntries(
    formatter.formatToParts(now).map(({ type, value }) => [type, value]),
  );

  return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
}

export function initCheckInForm() {
  const addButton = document.getElementById("add-checkin-btn");
  const formContainer = document.getElementById("checkin-form-container");
  const cancelButton = document.getElementById("cancel-checkin-btn");
  const dateInput = document.getElementById("checkInAt");

  if (!addButton || !formContainer) {
    return;
  }

  const shouldOpenOnLoad = formContainer.dataset.openOnLoad === "true";

  const focusFirstField = () => {
    const firstField = formContainer.querySelector(
      "input, select, textarea, button",
    );

    if (firstField) {
      firstField.focus();
    }
  };

  const setDefaultDateTime = () => {
    if (!dateInput || dateInput.value) {
      return;
    }

    dateInput.value = getBrusselsDateTimeValue();
  };

  const openForm = () => {
    setDefaultDateTime();
    formContainer.classList.remove("hidden");
    addButton.classList.add("hidden");
    addButton.setAttribute("aria-expanded", "true");
    focusFirstField();
  };

  const closeForm = () => {
    formContainer.classList.add("hidden");
    addButton.classList.remove("hidden");
    addButton.setAttribute("aria-expanded", "false");
    addButton.focus();
  };

  addButton.addEventListener("click", openForm);
  addButton.addEventListener("keydown", (event) =>
    onActionKeydown(event, openForm),
  );

  if (cancelButton) {
    cancelButton.addEventListener("click", closeForm);
    cancelButton.addEventListener("keydown", (event) =>
      onActionKeydown(event, closeForm),
    );
  }

  if (shouldOpenOnLoad) {
    openForm();
  }
}
