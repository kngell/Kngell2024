import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("UserMenu");

export default class UserMenu {
  constructor() {
    this.menus = document.querySelectorAll(".user-menu");
    this.init();
  }

  init() {
    this.menus.forEach((menu) => {
      const trigger = menu.querySelector(".user-menu__trigger");

      trigger.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        // Toggle class for click-based visibility
        menu.classList.toggle("is-open");
      });

      // Close when clicking outside
      document.addEventListener("click", (e) => {
        if (!menu.contains(e.target)) {
          menu.classList.remove("is-open");
        }
      });
    });
  }
}

// export default class UserMenu {
//   constructor() {
//     this.menus = document.querySelectorAll(".user-menu");
//     this.init();
//   }

//   init() {
//     if (this.menus.length === 0) return;

//     // Remove no-js class if it exists
//     document.documentElement.classList.remove("no-js");

//     this.menus.forEach((menu) => {
//       const trigger = menu.querySelector(".user-menu__trigger");
//       const dropdown = menu.querySelector(".user-menu__dropdown");

//       if (!trigger || !dropdown) return;

//       // Set initial states
//       trigger.setAttribute("aria-expanded", "false");
//       dropdown.setAttribute("aria-hidden", "true");

//       // Click toggle
//       trigger.addEventListener("click", (e) => {
//         e.preventDefault();
//         e.stopPropagation();

//         const isExpanded = trigger.getAttribute("aria-expanded") === "true";
//         this.toggleMenu(menu, !isExpanded);
//       });

//       // Close on outside click
//       document.addEventListener("click", (e) => {
//         if (!menu.contains(e.target)) {
//           this.closeMenu(menu);
//         }
//       });

//       // Close on Escape
//       menu.addEventListener("keydown", (e) => {
//         if (e.key === "Escape") {
//           this.closeMenu(menu);
//           trigger.focus();
//         }
//       });
//     });
//   }

//   toggleMenu(menu, open) {
//     const trigger = menu.querySelector(".user-menu__trigger");
//     const dropdown = menu.querySelector(".user-menu__dropdown");

//     if (open) {
//       menu.classList.add("is-open");
//       trigger.setAttribute("aria-expanded", "true");
//       dropdown.setAttribute("aria-hidden", "false");
//     } else {
//       menu.classList.remove("is-open");
//       trigger.setAttribute("aria-expanded", "false");
//       dropdown.setAttribute("aria-hidden", "true");
//     }
//   }

//   closeMenu(menu) {
//     this.toggleMenu(menu, false);
//   }
// }
