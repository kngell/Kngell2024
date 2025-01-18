const dropdown = () => {
  const _variables = {
    main: "k-dropdown",
    menu: "k-dropdown__menu",
    target: "data-dropdown-target",
    active: "k-active",
  };
  const dropDown = [...document.querySelectorAll(`.${_variables.main}`)];
  document.addEventListener("click", (e) => {
    const target = e.target.closest(`.${_variables.main}`);
    const targetedMenu = e.target.closest(`.${_variables.menu}`);
    if (!target || targetedMenu) return;
    const targetId = target
      .querySelector(`[${_variables.target}]`)
      .getAttribute(_variables.target);
    const activeMenu = document.querySelector(`#${targetId}`);
    const nonTargeted = dropDown.map((drop) => {
      const nonActiveId = drop
        .querySelector(`[${_variables.target}]`)
        .getAttribute(_variables.target);
      const nonActive = document.querySelector(`#${nonActiveId}`);
      return nonActive;
    });
    const filterExceptActive = nonTargeted.filter(
      (target) => target !== activeMenu
    );
    filterExceptActive.forEach((drop) =>
      drop.classList.remove(_variables.active)
    );
    if (activeMenu) activeMenu.classList.toggle(_variables.active);
  });
};
dropdown();
