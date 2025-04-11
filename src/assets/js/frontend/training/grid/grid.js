const resizeBtn = document.getElementById("btn-resize");

resizeBtn.addEventListener("click", (e) => {
  e.preventDefault();
  const gridContainer = document.querySelector(".grid-container");
  gridContainer.classList.toggle("grid-container--expanded");
});
