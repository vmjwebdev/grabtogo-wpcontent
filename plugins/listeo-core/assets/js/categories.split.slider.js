document.addEventListener("DOMContentLoaded", function () {
  // Category data with icons (SVG paths)
  const categories = sliderData.categories || [];
  
  const categorySlider = document.getElementById("categorySlider");
  const prevButton = document.querySelector(".nav-button.prev");
  const nextButton = document.querySelector(".nav-button.next");

  // Create category items
  categories.forEach((category, index) => {
    const categoryItem = document.createElement("div");
    categoryItem.className = "category-item" + (index === 0 ? " active" : "");
    categoryItem.setAttribute("data-id", category.id);
    categoryItem.setAttribute("data-slug", category.slug);
    categoryItem.innerHTML = `
        <div class="icon-container">${category.icon}</div>
        <div class="category-name">${category.name}</div>
    `;

    categoryItem.addEventListener("click", function () {
      // Remove active class from all items
      document.querySelectorAll(".category-item").forEach((item) => {
        item.classList.remove("active");
      });

      // Add active class to clicked item
      this.classList.add("active");

      const categoryId = this.getAttribute("data-id");
      const categorySlug = this.getAttribute("data-slug");

      // Update Bootstrap Select dropdown
      // Set the value of the select
      const select = document.getElementById("tax-listing_category");
      if (select) {
        select.value = categorySlug;
 
        // Refresh Bootstrap Select (this part still requires jQuery normally)
        if (
          typeof bootstrap !== "undefined" &&
          typeof bootstrap.Select !== "undefined"
        ) {
          bootstrap.Select.refresh();
        } else if (
          typeof jQuery !== "undefined" &&
          typeof jQuery.fn.selectpicker === "function"
        ) {
          jQuery(select).selectpicker("refresh");
        }

        // Trigger change event
        const event = new Event("change", { bubbles: true });
        select.dispatchEvent(event);

        select.addEventListener("change", function () {
          // Remove `.active` class from all slider items
          const sliderItems = document.querySelectorAll(
            ".category-item.active"
          );
          sliderItems.forEach((item) => item.classList.remove("active"));
        });
      }
      // check if page has div with ID 'listeo-drilldown-tax-listing_category'
      if (document.getElementById("listeo-drilldown-tax-listing_category")) {
        const drilldown =
          window.ListeoDrilldown["listeo-drilldown-tax-listing_category"]; // Use correct ID here

        if (drilldown && categoryId) {
          drilldown.selectById(categoryId);
        }
      }
    });

    categorySlider.appendChild(categoryItem);
  });

  // Navigation functionality
  let currentPosition = 0;
  const itemWidth = 90; // Item width + margin

  // Calculate visible items based on container width
  function calculateVisibleItems() {
    const containerWidth = categorySlider.parentElement.clientWidth - 80; // Subtract padding
    return Math.floor(containerWidth / itemWidth);
  }

  let visibleItems = calculateVisibleItems();
  let maxPosition = Math.max(
    0,
    Math.ceil(categories.length / visibleItems) * visibleItems - visibleItems
  );

  function updateSliderPosition() {
    categorySlider.style.transform = `translateX(-${
      currentPosition * itemWidth
    }px)`;
    updateNavigationButtons();
  }

  function updateNavigationButtons() {
    // Hide prev button if at the beginning
    if (currentPosition <= 0) {
      prevButton.classList.add("hidden");
    } else {
      prevButton.classList.remove("hidden");
    }

    // Hide next button if at the end
    if (currentPosition + visibleItems >= categories.length) {
      nextButton.classList.add("hidden");
    } else {
      nextButton.classList.remove("hidden");
    }
  }

  prevButton.addEventListener("click", function () {
    if (currentPosition > 0) {
      // Move by the number of visible items, but not less than 0
      currentPosition = Math.max(0, currentPosition - visibleItems);
      updateSliderPosition();
    }
  });

  nextButton.addEventListener("click", function () {
    if (currentPosition + visibleItems < categories.length) {
      // Move by the number of visible items, but not beyond max
      currentPosition = Math.min(
        categories.length - visibleItems,
        currentPosition + visibleItems
      );
      updateSliderPosition();
    }
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    visibleItems = calculateVisibleItems();
    // Recalculate maximum position based on new visible items count
    maxPosition = Math.max(
      0,
      Math.ceil(categories.length / visibleItems) * visibleItems - visibleItems
    );

    // Make sure current position is valid after resize
    currentPosition = Math.min(
      currentPosition,
      categories.length - visibleItems
    );
    updateSliderPosition();
  });

  // Initialize slider position and navigation buttons
  updateSliderPosition();
});
