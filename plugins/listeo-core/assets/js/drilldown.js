/* ----------------- Start Document ----------------- */
(function ($) {
  "use strict";

  $(document).ready(function () {
    
      // Global default categories if none are provided (optional)
      var defaultCategories = [
        {
          label: "Default",
          children: [{ label: "Item 1" }, { label: "Item 2" }],
        },
      ];

      // Set up each drilldown menu instance
      $(".drilldown-menu").each(function () {
        var $menu = $(this);
        var selectedItems = [];
        var menuStack = []; // Array to keep track of drilldown levels
        var initialized = false; //
        // Add this option - read from data attribute or default to false
        var singleSelect = $menu.data("single-select") === true;

        // Read categories from the data attribute; fallback to defaultCategories if needed.
        var categories = $menu.data("categories");
        if (typeof categories === "string") {
          try {
            categories = JSON.parse(categories);
          } catch (e) {
            categories = defaultCategories;
          }
        } else if (!categories) {
          categories = defaultCategories;
        }

        // Cache commonly used elements within this menu
        var $menuToggle = $menu.find(".menu-toggle");
        var $menuPanel = $menu.find(".menu-panel");
        var $menuLevelsContainer = $menu.find(".menu-levels");
        var $menuLabel = $menu.find(".menu-label");
        var $menuLabelText = $menu.data("label");
        var $resetButton = $menu.find(".reset-button");

        // Recursive function to check if an item (or any descendant) matches the search term
        function itemMatchesSearch(item, searchTerm) {
          if ($.trim(searchTerm) === "") return true;
          var lowerSearch = searchTerm.toLowerCase();
          if (item.label.toLowerCase().indexOf(lowerSearch) !== -1) {
            return true;
          }
          if (item.children && item.children.length > 0) {
            for (var i = 0; i < item.children.length; i++) {
              if (itemMatchesSearch(item.children[i], searchTerm)) {
                return true;
              }
            }
          }
          return false;
        }

        // Initialize the menu at the root level
        function initMenu() {
          menuStack = [];
          menuStack.push({ data: categories, parent: null });
          $menuLevelsContainer.empty();
          var $levelElement = createMenuLevel(categories, 0);
          $menuLevelsContainer.append($levelElement);
          updateMenuLevelPosition();
          updateMenuHeight();
          initializePreselectedValues();
        }

        // Create a new menu level element for the given data
        function createMenuLevel(data, levelIndex) {
          var $levelDiv = $("<div/>")
            .addClass("menu-level")
            .attr("data-level", levelIndex);

          // Add a "Back" button if not at the root level
          if (levelIndex > 0) {
            var $backButton = $("<button/>")
              .addClass("back-button")
              .text(listeo_core.back)
              .on("click", function (e) {
                e.stopPropagation();
                drillUp();
              });
            $levelDiv.append($backButton);
          }

          // Add a search input field
          var $searchInput = $("<input/>", {
            type: "text",
            placeholder: listeo_core.search,
            class: "menu-search",
          }).on("input", function () {
            filterMenuLevel($levelDiv, $searchInput.val());
          });
          $levelDiv.append($searchInput);

          // Create a container for menu items
          var $itemsContainer = $("<div/>").addClass("menu-items");
          $levelDiv.append($itemsContainer);

          // Iterate over the items and create each menu item element
          $.each(data, function (i, item) {
            var $itemDiv = $("<div/>")
              .addClass("menu-item")
              .attr("data-label", item.label);

            // Add value attribute if it exists
            if (item.value) {
              $itemDiv.attr("data-value", item.value);
            }
            if (item.id) {
              $itemDiv.attr("data-id", item.id);
            }
            // Store the entire item object for use in search filtering
            $itemDiv.data("item", item);
            var $labelSpan = $("<span/>")
              .addClass("item-label")
              .text(item.label);
            $itemDiv.append($labelSpan);

            if (item.children && item.children.length > 0) {
              // Item with subcategories: add an arrow and set up drilldown
              var $arrowSpan = $("<span/>").addClass("arrow");
              $itemDiv.append($arrowSpan);
              $itemDiv.on("click", function (e) {
                e.stopPropagation();
                drillDown(item);
              });
            } else {
              // Leaf item: toggle selection on click
              $itemDiv.on("click", function (e) {
                e.stopPropagation();
                // Remove 'active' class from any .category-item
                $(".category-item").removeClass("active");
                toggleSelection(item, $itemDiv);
              });
              if (isSelected(item)) {
                $itemDiv.addClass("selected");
              }
            }
            $itemsContainer.append($itemDiv);
          });

          return $levelDiv;
        }

        // Modified filter function that checks parent items and their descendants.
        // It also highlights matches using <mark>.
        function filterMenuLevel($levelDiv, searchTerm) {
          var $itemsContainer = $levelDiv.find(".menu-items");
          var $items = $itemsContainer.find(".menu-item");
          var anyVisible = false;
          $levelDiv.find(".no-results").remove();

          $items.each(function () {
            var $item = $(this);
            var itemObj = $item.data("item"); // get the complete data object
            var label = itemObj.label;
            var lowerSearch = $.trim(searchTerm).toLowerCase();
            // Determine if there is a direct match in the label
            var directMatch =
              lowerSearch !== "" &&
              label.toLowerCase().indexOf(lowerSearch) > -1;
            // Determine if the item or any descendant matches
            var matches = itemMatchesSearch(itemObj, searchTerm);
            if (matches) {
              $item.css("display", "flex");
              anyVisible = true;
              var $labelSpan = $item.find(".item-label");
              // Reset any previous highlighting and classes
              $item.removeClass("child-match");
              $labelSpan.text(label);
              if ($.trim(searchTerm) !== "") {
                if (directMatch) {
                  // Highlight the matching substring in the label
                  var regex = new RegExp(
                    "(" + escapeRegExp(searchTerm) + ")",
                    "gi"
                  );
                  $labelSpan.html(label.replace(regex, "<mark>$1</mark>"));
                } else {
                  // No direct match—but a descendant matches: add a special class
                  $item.addClass("child-match");
                }
              }
            } else {
              $item.css("display", "none");
            }
          });
          if (!anyVisible) {
            var $noResults = $("<div/>")
              .addClass("no-results")
              .text("No results");
            $itemsContainer.append($noResults);
          }
          updateMenuHeight();
        }

        // Utility function to escape regex special characters
        function escapeRegExp(string) {
          return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        }

        // Drill down into a submenu for the given category.
        // Also, propagate the parent's search term to the child's search field.
        function drillDown(category) {
          if (!category.children || category.children.length === 0) return;
          // Get parent's search term from the current active level.
          var $currentLevel = $menuLevelsContainer.children().last();
          var parentSearchTerm = $currentLevel.find(".menu-search").val();
          menuStack.push({ data: category.children, parent: category });
          var levelIndex = menuStack.length - 1;
          var $newLevel = createMenuLevel(category.children, levelIndex);
          // Propagate parent's search term to child's search input and filter.
          $newLevel.find(".menu-search").val(parentSearchTerm);
          filterMenuLevel($newLevel, parentSearchTerm);
          $menuLevelsContainer.append($newLevel);
          updateMenuLevelPosition();
          setTimeout(updateMenuHeight, 0);
        }

        // Return to the previous menu level
        function drillUp() {
          if (menuStack.length <= 1) return;
          menuStack.pop();
          $menuLevelsContainer.children().last().remove();
          updateMenuLevelPosition();
          updateMenuHeight();
        }

        function findItemByValue(categories, value) {
          for (var i = 0; i < categories.length; i++) {
            var item = categories[i];
            // Check if this item matches
            if ("#submit-listing-form".length) {
              if (item.id == value) {
                return item;
              }
            } else {
              if (
                item.value === value ||
                (!item.value && item.label === value)
              ) {
                return item;
              }
            }

            // Check children if they exist
            if (item.children && item.children.length > 0) {
              var found = findItemByValue(item.children, value);
              if (found) return found;
            }
          }
          return null;
        }

        // Make drilldown control functions available globally
        if (!window.ListeoDrilldown) window.ListeoDrilldown = {};

        window.ListeoDrilldown[$menu.attr("id")] = {
          initMenu: initMenu,
          selectById: function (categoryId) {
            initMenu();

            setTimeout(function () {
              const item = findItemByValue(categories, categoryId);
              if (item) {
                // Fake a jQuery element just to pass to toggleSelection
                const $fake = $("<div>")
                  .addClass("menu-item")
                  .attr("data-id", categoryId);
                toggleSelection(item, $fake);
              } else {
                // reset the menu if no item found
                resetSelections();
              }
            }, 20);
          },
        };
        

        function initializePreselectedValues() {
          selectedItems = []; // Clear existing selections

          // Get the original input class and name
          var $originalInput = $menu.find("input.drilldown-values");
          var inputName = $menu.data("name");

          // Get all existing array inputs
          var $existingInputs = $menu.find('input[name="' + inputName + '[]"]');

          $existingInputs.each(function () {
            var value = $(this).val();
            if (value) {
              var item = findItemByValue(categories, value.trim());
              if (item) {
                selectedItems.push(item);
              }
            }
          });

          if (selectedItems.length > 0) {
            updateMainButton();
            // Ensure hidden inputs are created for preselected items
            updateHiddenInput();
          }
        }
        initializePreselectedValues();

        function updateHiddenInput() {
          // Find the hidden input by class within this menu instance
          var $originalInput = $menu.find("input.drilldown-values");

          var inputName = $menu.data("name");

          // First remove any existing array inputs
          $menu.find('input[name="' + inputName + '[]"]').remove();

          // Create new hidden inputs for each selected value
          selectedItems.forEach(function (item) {
            if ($("#submit-listing-form").length) {
              var value = item.id;
            } else {
              var value = item.value || item.label;
            }

            $("<input>", {
              type: "hidden",
              name: inputName + "[]",
              value: value,
              class: "drilldown-generated", // Add a class to identify generated inputs
            }).appendTo($menu);
          });
          var target = $("div#listeo-listings-container");
          target.triggerHandler("update_results", [1, false]);

          $menu.trigger("drilldown-updated");

          // Verify inputs were created
        }

        // Update the container's transform to slide to the active level
        function updateMenuLevelPosition() {
          var levelIndex = menuStack.length - 1;
          $menuLevelsContainer.css(
            "transform",
            "translateX(-" + levelIndex * 100 + "%)"
          );
        }

        // Update the panel height to match the active level’s natural height
        function updateMenuHeight() {
          var $levels = $menuLevelsContainer.children();
          if ($levels.length === 0) return;
          var $activeLevel = $levels.last();
          $menuPanel.height($activeLevel[0].scrollHeight);
        }

        // Toggle selection of a leaf item.
        // Also update the main button with highlighting of the current search term.
        function toggleSelection(item, $itemDiv) {
          // remove class active from any .category-item
          
          var index = selectedItems.findIndex(function (selected) {
            return item.value && selected.value
              ? selected.value === item.value
              : selected.label === item.label;
          });

          if (index > -1) {
            // Deselect
            selectedItems.splice(index, 1);
            $itemDiv.removeClass("selected");
          } else {
            // Select
            if (singleSelect) {
              // Remove 'selected' class from all items
              $menu.find(".menu-item.selected").removeClass("selected");
              // Clear the array
              selectedItems = [];
            }
            // Check if item already exists before pushing
            if (!isSelected(item)) {
              selectedItems.push(item);
            }
            $itemDiv.addClass("selected");
           
            
          }
          //$(".category-item").removeClass("active");
          updateMainButton();
          updateHiddenInput();
        }

        // Check if an item is already selected
        // function isSelected(item) {
        //   var exists = false;
        //   $.each(selectedItems, function (i, sel) {
        //     if (sel.label === item.label) {
        //       exists = true;
        //       return false;
        //     }
        //   });
        //   return exists;
        // }

        function isSelected(item) {
          return selectedItems.some(function (selected) {
            if ($("#submit-listing-form").length) {
              if (item.id && selected.id) {
                return selected.id === item.id;
              }
            } else {
              if (item.value && selected.value) {
                return selected.value === item.value;
              }
            }

            return selected.label === item.label;
          });
        }

        // Update the main button text.
        // If a search term is active in the current level, highlight it in the label.
        function updateMainButton() {
          var searchTerm =
            $menuLevelsContainer.children().last().find(".menu-search").val() ||
            "";
          if (selectedItems.length === 0) {
            $menuLabel.html($menuLabelText);
            $resetButton.hide();
            $menuToggle.removeClass("dd-chosen"); // Remove class when no selection
          } else if (selectedItems.length === 1) {
            var label = selectedItems[0].label;
            if ($.trim(searchTerm) !== "") {
              var regex = new RegExp(
                "(" + escapeRegExp(searchTerm) + ")",
                "gi"
              );
              label = label.replace(regex, "<mark>$1</mark>");
            }
            $menuLabel.html(label);
            $resetButton.show();
            $menuToggle.addClass("dd-chosen"); // Add class when selection exists
          } else {
            var label = selectedItems[0].label;
            if ($.trim(searchTerm) !== "") {
              var regex = new RegExp(
                "(" + escapeRegExp(searchTerm) + ")",
                "gi"
              );
              label = label.replace(regex, "<mark>$1</mark>");
            }
            $menuLabel.html(label + " +" + (selectedItems.length - 1));
            $resetButton.show();
            $menuToggle.addClass("dd-chosen"); // Add class when selection exists
          }
        }

        // Clear all selections in this menu
        function resetSelections() {
          selectedItems = [];
          $menuPanel.find(".menu-item.selected").removeClass("selected");
          updateMainButton();
          updateHiddenInput();
        }

        // Open the menu and initialize it; also close any other open menus
        function openMenu() {
          // Close all other menus on the page
          
          $(".drilldown-menu")
            .not($menu)
            .each(function () {
              $(this).find(".menu-panel").removeClass("open");
              $(this).find(".menu-toggle").removeClass("dd-active"); // Remove class from other menus
            });

          if ($.fn.selectpicker) {
            // For Bootstrap 4+
            $(".bootstrap-select.show").each(function () {
              $(this).removeClass("show");
              $(this).find(".dropdown-menu").removeClass("show");
            });

            // For older Bootstrap versions
            $(".bootstrap-select.open").each(function () {
              $(this).removeClass("open");
              $(this).find(".dropdown-menu").removeClass("open");
            });
          }
          $menuPanel.addClass("open");
          $menuToggle.addClass("dd-active"); // Add class when menu is opened
          initMenu();
        }

        // Close the menu
        function closeMenu() {
          $menuPanel.removeClass("open");
          $menuToggle.removeClass("dd-active"); // Remove class when menu is closed
        }

        // Toggle the menu when clicking the main button
        $menuToggle.on("click", function (e) {
          e.stopPropagation();
          if ($menuPanel.hasClass("open")) {
            closeMenu();
          } else {
            openMenu();
          }
        });

        // Reset selections when clicking the reset button
        $resetButton.on("click", function (e) {
          e.stopPropagation();
          resetSelections();
          $(".category-item").removeClass("active");
        });

        // Close this menu if clicking outside it
        $(document).on("click", function (e) {
          if (!$menu.is(e.target) && $menu.has(e.target).length === 0) {
            closeMenu();
          }
        });
      });

      window.selectDrilldownCategoryById = function (categoryId) {
        $(".drilldown-menu").each(function () {
          var $menu = $(this);
          var $matchingItem = $menu.find('.menu-item[data-id="${categoryId}"]');
          console.log("Matching item:", $matchingItem);
          if ($matchingItem.length) {
            $matchingItem.trigger("click");
            console.log("Item clicked:", $matchingItem.text());
            // Open the menu if needed and close it after selection
         
          }
        });
      };
    });
  
})(this.jQuery);
