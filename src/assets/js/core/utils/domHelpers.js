let lastId = 0;

export const generateUniqueId = (prefix = "id") => {
  lastId++;
  return `${prefix}_${Date.now()}_${lastId}_${Math.random().toString(36).substr(2, 6)}`;
};

export const clearInputValues = (element) => {
  const inputs = element.querySelectorAll("input, select, textarea");
  inputs.forEach((input) => {
    if (input.type === "hidden") {
      input.value = "";
    } else if (input.type === "select-one") {
      input.selectedIndex = 0;
      input.value = "";
    } else {
      input.value = "";
    }
    input.classList.remove("is-invalid", "error");
  });
};
