jQuery(document).ready(function ($) {
  // prevent multiple

  // Target the form on the preview page
  var $previewForm = $("form#listing_preview");

  if ($previewForm.length) {
    $previewForm.on("submit", function () {
      
      // Find the continue button (adjust the selector if needed)
      var $continueButton = $(this).find('input[name="continue"]');
      var $editButton = $(this).find('input[name="edit_listing"]');

      // If the continue button was clicked
      if ($continueButton.is(":focus")) {
        // Disable it to prevent multiple submissions
        $continueButton
          .prop("disabled", true)
          .css("opacity", "0.5")
          .val("Processing...");
      } else if ($editButton.is(":focus")) {
        // If edit button was clicked, disable that instead
        $editButton
          .prop("disabled", true)
          .css("opacity", "0.5")
          .val("Processing...");
      }

      // The form continues submission normally
      return true;
    });
  }

  // Also add similar protection to the main submit listing form
  var $submitForm = $("form#submit-listing-form");

  if ($submitForm.length) {
    $submitForm.on("submit", function () {
      var $submitButton = $(this).find('input[type="submit"]');

      // Disable the button
      $submitButton
        .prop("disabled", true)
        .css("opacity", "0.5")
        .val("Processing...");

      // The form continues submission normally
      return true;
    });
  }


  // Create buttons for each time input but hide them initially
  $(".listeo-flatpickr").each(function () {
    var copyButton = $("<button>", {
      text: listeo_core.copytoalldays,
      class: "copy-time-button",
      css: {
        marginTop: "5px",
        display: "none", // Hide buttons by default
      },
    });

    $(this).after(copyButton);
  });

  // Handle hover events on day rows
  $(".opening-day").each(function () {
    $(this).hover(
      function () {
        // On hover in - show only this day's buttons
        $(this).find(".copy-time-button").show();
      },
      function () {
        // On hover out - hide this day's buttons
        $(this).find(".copy-time-button").hide();
      }
    );
  });

  // Handle button clicks
  $(".copy-time-button").on("click", function (e) {
    e.preventDefault();

    var input = $(this).prev(".listeo-flatpickr");
    var timeValue = input.val();

    if (!timeValue) {
      alert(listeo_core.selectimefirst);
      return;
    }

    // Determine if this is an opening or closing time input
    var isOpeningTime = input.attr("name").includes("opening");
    var currentDay = input.attr("name").split("_")[1]; // Get the current day name

    // Get all time inputs of the same type (opening or closing)
    var selector =
      '.listeo-flatpickr[name*="' +
      (isOpeningTime ? "opening" : "closing") +
      '_hour"]';
    var allInputs = $(selector);

    // Copy the time to all other days
    allInputs.each(function () {
      var targetInput = $(this);
      var targetDay = targetInput.attr("name").split("_")[1];

      if (targetDay !== currentDay) {
        targetInput.val(timeValue);

        // Trigger change event to ensure any linked functionality updates
        targetInput.trigger("change");

        // If using flatpickr, update its instance
        if (targetInput[0]._flatpickr) {
          targetInput[0]._flatpickr.setDate(timeValue, true);
        }
      }
    });
  });

  // FullCalendar Initialization for Availability Calendar
  if ($("#fullcalendar").length) {
    var calendarEl = document.getElementById("fullcalendar");

    // Parse existing blocked dates (format: DD-MM-YYYY|DD-MM-YYYY|...)
    var blockedDatesInput = $("#fullcalendar-blocked-dates").val();
    var blockedDates = [];

    if (blockedDatesInput) {
      blockedDatesInput.split("|").forEach(function (dateStr) {
        if (dateStr.trim() !== "") {
          // Convert from DD-MM-YYYY to YYYY-MM-DD for FullCalendar
          var parts = dateStr.split("-");
          if (parts.length === 3) {
            blockedDates.push(parts[2] + "-" + parts[1] + "-" + parts[0]);
          }
        }
      });
    }

    // Parse existing price data (format: {"DD-MM-YYYY":"price",...})
    var priceDataInput = $("#fullcalendar-price-data").val();
    var priceData = {};

    if (priceDataInput) {
      try {
        var parsedPrices = JSON.parse(priceDataInput);
        // Convert keys from DD-MM-YYYY to YYYY-MM-DD for FullCalendar
        Object.keys(parsedPrices).forEach(function (dateStr) {
          if (dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            var parts = dateStr.split("-");
            priceData[parts[2] + "-" + parts[1] + "-" + parts[0]] =
              parsedPrices[dateStr];
          }
        });
      } catch (e) {
        console.error("Error parsing price data:", e);
      }
    }

    // Track currently selected dates
    var selectedDates = [];

    // Track clicks for double-click detection
    var lastClickTime = 0;
    var lastClickDate = null;

    // Track the tooltip position and state
    var tooltipVisible = false;
    var lastSelectionEnd = null;

    // Tooltip positioned near the clicked date with smart positioning
    function showTooltipForDate(dateStr) {
      // Remove any existing tooltips first
      $("#selection-tooltip, #single-date-tooltip").remove();
      $("body").removeClass("has-date-tooltip");

      if (!dateStr) {
        tooltipVisible = false;
        return;
      }

      // Find the cell for the clicked date
      var dayCell = $(
        ".fc-day[data-date='" +
          dateStr +
          "'], .fc-daygrid-day[data-date='" +
          dateStr +
          "']"
      );

      if (!dayCell.length) {
        console.error("Could not find calendar cell for date:", dateStr);
        return;
      }

      // Get position of the calendar and day cell
      var calendar = $("#fullcalendar");
      var calendarOffset = calendar.offset();
      var cellOffset = dayCell.offset();

      // Add class to body
      $("body").addClass("has-date-tooltip");

      // Create tooltip - CSS is now in the theme's style.css file
      var tooltip = $("<div>", {
        id: "single-date-tooltip",
        class: "selection-tooltip",
      });

      // Add count of selected dates (just 1 in this case)
      var selectionText = $("<div>", {
        text: listeo_core.one_date_selected,
        css: {
          marginRight: "10px",
          alignSelf: "center",
          fontWeight: "bold",
        },
      });

      // Add buttons - styles are now in the theme's style.css file
      var blockBtn = $("<button>", {
        text: listeo_core.block,
        type: "button",
        class: "tooltip-btn block-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Add to selection then trigger block
        selectedDates = [dateStr];
        $("#block-dates-btn").trigger("click");

        return false;
      });

      var priceBtn = $("<button>", {
        text: listeo_core.setprice,
        type: "button",
        class: "tooltip-btn price-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Add to selection then trigger set price
        selectedDates = [dateStr];
        $("#set-price-btn").trigger("click");

        return false;
      });

      var clearBtn = $("<button>", {
        text: listeo_core.unblock,
        type: "button",
        class: "tooltip-btn clear-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Add to selection then trigger unblock
        selectedDates = [dateStr];
        $("#clear-selection-btn").trigger("click");

        return false;
      });

      // Add buttons directly to tooltip for horizontal layout
      tooltip.append(selectionText, blockBtn, priceBtn, clearBtn);

      // Add tooltip to the calendar
      $("#fullcalendar").append(tooltip);
      tooltipVisible = true;

      // Position the tooltip - smart positioning to avoid window edge
      positionTooltip(tooltip, dayCell);

      // Prevent clicks on the tooltip from bubbling to document
      tooltip.on("click", function (e) {
        e.stopPropagation();
      });
    }

    // Original tooltip function (still used for multi-select)
    function showSelectionTooltip() {
      // Skip if there are no selected dates
      if (selectedDates.length === 0) {
        tooltipVisible = false;
        return;
      }

      // If it's a single date selection, use the standalone tooltip
      if (selectedDates.length === 1) {
        showTooltipForDate(selectedDates[0]);
        return;
      }

      // For multiple dates, use the original tooltip
      // Remove any existing tooltips
      $("#selection-tooltip, #single-date-tooltip").remove();
      $("body").removeClass("has-date-tooltip");

      // Mark document body with tooltip-active class
      $("body").addClass("has-date-tooltip");

      // Create tooltip element - using CSS from style.css
      var tooltip = $("<div>", {
        id: "selection-tooltip",
        class: "selection-tooltip",
      });

      // Add buttons - using CSS from style.css
      var blockBtn = $("<button>", {
        text: listeo_core.block,
        type: "button",
        class: "tooltip-btn block-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $("#block-dates-btn").trigger("click");
        return false;
      });

      var priceBtn = $("<button>", {
        text: listeo_core.setprice,
        type: "button",
        class: "tooltip-btn price-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $("#set-price-btn").trigger("click");
        return false;
      });

      var clearBtn = $("<button>", {
        text: listeo_core.unblock,
        type: "button",
        class: "tooltip-btn clear-btn",
      }).on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $("#clear-selection-btn").trigger("click");
        return false;
      });

      // Add count of selected dates
      var selectionText = $("<div>", {
        text: selectedDates.length + listeo_core.dates_selected,
        css: {
          marginRight: "10px",
          alignSelf: "center",
          fontWeight: "bold",
        },
      });

      // Add buttons to tooltip
      tooltip.append(selectionText, blockBtn, priceBtn, clearBtn);

      // Add to calendar container first (needed for width calculation)
      $("#fullcalendar").after(tooltip);
      tooltipVisible = true;

      // Find the last selected date's cell for smart positioning
      if (lastSelectionEnd) {
        var dateCell = $(
          `.fc-day[data-date="${lastSelectionEnd}"], .fc-daygrid-day[data-date="${lastSelectionEnd}"]`
        );
        if (dateCell.length) {
          // We have a valid cell, use smart positioning
          positionTooltip(tooltip, dateCell);
        } else {
          // Fallback to old positioning
          var position = getTooltipPosition();
          tooltip.css({
            top: position.top + "px",
            left: position.left + "px",
          });
        }
      } else {
        // Fallback to old positioning
        var position = getTooltipPosition();
        tooltip.css({
          top: position.top + "px",
          left: position.left + "px",
        });
      }
    }

    // Position tooltip with smart boundary detection
    function positionTooltip(tooltip, targetCell) {
      // Get the necessary dimensions and positions
      var calendar = $("#fullcalendar");
      var calendarOffset = calendar.offset();
      var cellOffset = targetCell.offset();
      var windowWidth = $(window).width();

      // First position the tooltip for measurement
      tooltip.css({
        top:
          cellOffset.top -
          calendarOffset.top +
          targetCell.outerHeight() +
          5 +
          "px",
        left: cellOffset.left - calendarOffset.left + "px",
      });

      // Now check if it would overflow the right edge of the window
      var tooltipWidth = tooltip.outerWidth();
      var tooltipRight = cellOffset.left + tooltipWidth;
      var isOverflowing = tooltipRight > windowWidth - 20; // 20px margin

      if (isOverflowing) {
        // Position to the left side of the cell instead
        var newLeft = Math.max(0, cellOffset.left - tooltipWidth);
        tooltip.css({
          left: newLeft - calendarOffset.left + "px",
        });
      }
    }

    // Get position for the tooltip (used by multi-select tooltip)
    function getTooltipPosition() {
      var position = { top: 0, left: 0 };

      // If we have a selection end date, try to position near it
      if (lastSelectionEnd) {
        var dateCell = $(`.fc-day[data-date="${lastSelectionEnd}"]`);
        if (dateCell.length) {
          var rect = dateCell[0].getBoundingClientRect();
          var calendarRect = $("#fullcalendar")[0].getBoundingClientRect();

          position.top = rect.bottom - calendarRect.top + 10; // 10px below the cell
          position.left = rect.left - calendarRect.left + rect.width / 2; // Center horizontally

          // Check for right edge overflow
          var tooltipWidth = 300; // Approximate width of tooltip
          var rightEdge = position.left + tooltipWidth;
          var calendarWidth = calendar.width();

          if (rightEdge > calendarWidth) {
            // Move tooltip to left of cell instead
            position.left = rect.left - calendarRect.left - tooltipWidth;
            if (position.left < 0) {
              // If that would be off-screen left, position at left edge
              position.left = 10;
            }
          }
        } else {
          // Fallback to calendar position
          position.top = $("#fullcalendar").height() / 2;
          position.left = $("#fullcalendar").width() / 2;
        }
      } else {
        // Fallback to calendar position
        position.top = $("#fullcalendar").height() / 2;
        position.left = $("#fullcalendar").width() / 2;
      }

      return position;
    }

    // Create events for blocked dates and price data
    function generateEvents() {
      var events = [];

      // Add blocked dates
      blockedDates.forEach(function (dateStr) {
        events.push({
          start: dateStr,
          display: "background",
          backgroundColor: "rgba(255, 0, 0, 0.2)",
          className: "blocked-date",
          allDay: true,
        });
      });

      // Add price data
      Object.keys(priceData).forEach(function (dateStr) {
        events.push({
          start: dateStr,
          title: (listeo_core.currency_symbol || "$") + priceData[dateStr],
          className: "has-price",
          allDay: true,
        });
      });

      // Add selected dates
      selectedDates.forEach(function (dateStr) {
        events.push({
          start: dateStr,
          display: "background",
          backgroundColor: "rgba(0, 120, 215, 0.2)",
          className: "selected-date",
          allDay: true,
        });
      });

      return events;
    }

    // Initialize calendar
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      locale: listeoCal.language,
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth",
      },
      selectable: true,
      selectMirror: true,
      unselectAuto: false, // Prevent automatic unselection
      unselect: function (info) {
        // Prevent unselect behavior if tooltip is visible
        if (tooltipVisible) {
          return false;
        }
      },
      unselectCancel: ".selection-tooltip", // Prevent unselection when clicking tooltip
      events: generateEvents(),

      // Handle range selection (drag)
      select: function (info) {
        // Clear previous selection
        selectedDates = [];

        // Log selection info for debugging
        console.log("Selection info:", {
          start: info.start,
          end: info.end,
          startStr: info.startStr,
          endStr: info.endStr,
        });

        // Note: FullCalendar's end date is exclusive (the day after the last selected day)
        // So we need to adjust it to match what's visually highlighted

        // For a single day selection
        if (
          info.startStr === info.endStr ||
          new Date(info.endStr) - new Date(info.startStr) === 86400000
        ) {
          // 1 day in ms
          // This is a single day selection
          // Add regardless of whether it's blocked or not - we want to select everything
          selectedDates.push(info.startStr);
        } else {
          // This is a multi-day selection
          var start = new Date(info.startStr);
          var end = new Date(info.endStr);

          // Loop through each day from start to end-1 (since end is exclusive)
          var current = new Date(start);
          while (current < end) {
            var dateStr = current.toISOString().split("T")[0];
            console.log("Processing date:", dateStr);

            // Add all dates to selection, even if blocked
            selectedDates.push(dateStr);

            // Move to next day
            current.setDate(current.getDate() + 1);
          }
        }

        console.log("Final selected dates:", selectedDates);

        // Always store the last selected date for tooltip positioning
        lastSelectionEnd =
          selectedDates.length > 0
            ? selectedDates[selectedDates.length - 1]
            : info.startStr;

        // Highlight the selected dates
        calendar.removeAllEvents();
        calendar.addEventSource(generateEvents());

        // Show the tooltip with action buttons
        showSelectionTooltip();
      },

      // Handle clicking on dates
      dateClick: function (info) {
        var dateStr = info.dateStr;
        var now = new Date().getTime();

        // Check for double-click (within 300ms on the same date)
        if (lastClickDate === dateStr && now - lastClickTime < 300) {
          // This is a double-click - toggle blocked status

          // If already blocked, unblock it
          var blockedIndex = blockedDates.indexOf(dateStr);
          if (blockedIndex !== -1) {
            blockedDates.splice(blockedIndex, 1);
          } else {
            // Not blocked, so block it
            blockedDates.push(dateStr);
          }

          // Update hidden input
          updateBlockedDatesInput();

          // Refresh display
          calendar.removeAllEvents();
          calendar.addEventSource(generateEvents());

          // Reset tracking variables to prevent triple-click issues
          lastClickTime = 0;
          lastClickDate = null;

          // Hide any tooltip
          $("#selection-tooltip, #single-date-tooltip").remove();
          $("body").removeClass("has-date-tooltip");
          tooltipVisible = false;

          return;
        }

        // Not a double-click, update tracking variables
        lastClickTime = now;
        lastClickDate = dateStr;

        // Store in selection array
        selectedDates = [dateStr];

        // Prevent default calendar click behavior
        if (info.jsEvent) {
          info.jsEvent.preventDefault();
          info.jsEvent.stopPropagation();
        }

        // Show the standalone tooltip directly - completely bypass FullCalendar selection
        showTooltipForDate(dateStr);

        // Refresh calendar display
        calendar.removeAllEvents();
        calendar.addEventSource(generateEvents());
      },

      // Handle clicking on events
      eventClick: function (info) {
        var dateStr = info.event.startStr;
        console.log("Event clicked on date:", dateStr, "Event:", info.event);

        // Handle blocked dates
        if (info.event.classNames.includes("blocked-date")) {
          console.log("Clicked on blocked date:", dateStr);

          // Confirm unblocking
          if (confirm("Unblock this date?")) {
            var blockedIndex = blockedDates.indexOf(dateStr);
            if (blockedIndex !== -1) {
              blockedDates.splice(blockedIndex, 1);

              // Update hidden input
              updateBlockedDatesInput();

              // Refresh display
              calendar.removeAllEvents();
              calendar.addEventSource(generateEvents());
            }
          }
          return;
        }

        // Handle price events
        if (info.event.classNames.includes("has-price")) {
          var currentPrice = priceData[dateStr] || "";

          var price = prompt(
            listeo_core.enterPrice +
              " " +
              dateStr +
              "\n" +
              listeo_core.leaveBlank,
            currentPrice
          );

          if (price !== null) {
            if (price === "" || isNaN(parseFloat(price))) {
              delete priceData[dateStr];
            } else {
              priceData[dateStr] = parseFloat(price).toFixed(2);
            }

            // Update hidden input
            updatePriceDataInput();

            // Refresh display
            calendar.removeAllEvents();
            calendar.addEventSource(generateEvents());
          }
        }
      },

      locale: listeoCal.language,
      firstDay: parseInt(listeo_core.firstDay || "1"),
    });

    // Convert YYYY-MM-DD to DD-MM-YYYY
    function formatDateForStorage(dateStr) {
      var parts = dateStr.split("-");
      return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    // Update the hidden inputs
    function updateBlockedDatesInput() {
      var formattedDates = blockedDates.map(formatDateForStorage);
      $("#fullcalendar-blocked-dates").val(formattedDates.join("|") + "|");
    }

    function updatePriceDataInput() {
      var formattedPrices = {};
      Object.keys(priceData).forEach(function (dateStr) {
        formattedPrices[formatDateForStorage(dateStr)] = priceData[dateStr];
      });
      $("#fullcalendar-price-data").val(JSON.stringify(formattedPrices));
    }

    // Reset properties of currently selected dates
    function clearSelection() {
      if (selectedDates.length === 0) {
        alert("Please select dates to modify first");
        return;
      }

      // Confirm action
      if (
        !confirm(
          "This will unblock selected dates and remove any custom prices. Continue?"
        )
      ) {
        return;
      }

      console.log("Clearing properties for these dates:", selectedDates);

      // Create a new array with dates to keep blocked (those not in selectedDates)
      var newBlockedDates = [];
      blockedDates.forEach(function (dateStr) {
        if (!selectedDates.includes(dateStr)) {
          newBlockedDates.push(dateStr);
        } else {
          console.log("Unblocking date:", dateStr);
        }
      });

      // Replace blocked dates with filtered list
      blockedDates = newBlockedDates;

      // Remove prices for selected dates
      selectedDates.forEach(function (dateStr) {
        if (dateStr in priceData) {
          console.log("Removing price for date:", dateStr);
          delete priceData[dateStr];
        }
      });

      // Update hidden inputs
      updateBlockedDatesInput();
      updatePriceDataInput();

      // Clear selection
      selectedDates = [];

      // Visually unselect any selected dates in the calendar
      calendar.unselect();

      // Refresh the calendar display
      calendar.removeAllEvents();
      calendar.addEventSource(generateEvents());

      // Hide the tooltip
      $("#selection-tooltip").remove();
      tooltipVisible = false;
    }

    // Handle Block Dates button
    $("#block-dates-btn").on("click", function (e) {
      e.preventDefault();

      if (selectedDates.length === 0) {
        alert("Please select dates to block first");
        return;
      }

      // Add selected dates to blocked dates
      selectedDates.forEach(function (dateStr) {
        if (!blockedDates.includes(dateStr)) {
          blockedDates.push(dateStr);
        }
      });

      // Update hidden input
      updateBlockedDatesInput();

      // Just clear the selection array and refresh calendar without running clearSelection()
      var tempSelection = selectedDates;
      selectedDates = [];

      // Visually unselect any selected dates in the calendar
      calendar.unselect();

      // Refresh display
      calendar.removeAllEvents();
      calendar.addEventSource(generateEvents());

      // Hide the tooltip
      $("#selection-tooltip").remove();
      tooltipVisible = false;

      //alert(tempSelection.length + " date(s) have been blocked");
    });

    // Set up price dialog handlers once, outside the click event
    // Handle price confirmation
    $("#price-confirm")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var price = $("#price-input").val();

        if (price === "" || isNaN(parseFloat(price))) {
          alert("Please enter a valid price");
          return false;
        }

        // Format price
        price = parseFloat(price).toFixed(2);

        // Set price for all selected dates
        selectedDates.forEach(function (dateStr) {
          priceData[dateStr] = price;
        });

        // Update hidden input
        updatePriceDataInput();

        // Hide dialog
        $("#price-dialog").hide();
        $("#price-input").val("");

        // Just clear the selection array and refresh calendar without running clearSelection()
        var tempSelection = selectedDates;
        selectedDates = [];

        // Visually unselect any selected dates in the calendar
        calendar.unselect();

        // Refresh display
        calendar.removeAllEvents();
        calendar.addEventSource(generateEvents());

        // Hide the tooltip
        $("#selection-tooltip").remove();
        tooltipVisible = false;

        return false;
      });

    // Handle price cancellation
    $("#price-cancel")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $("#price-dialog").hide();
        $("#price-input").val("");
        return false;
      });

    // Handle Set Price button
    $("#set-price-btn").on("click", function (e) {
      e.preventDefault();
      e.stopPropagation(); // Stop event bubbling

      if (selectedDates.length === 0) {
        alert("Please select dates to set price for first");
        return false;
      }

      // Show price dialog
      $("#price-dialog").show();
      return false; // Ensure no form submission
    });

    // Handle Clear/Unblock Selection button
    $("#clear-selection-btn").on("click", function (e) {
      e.preventDefault();
      clearSelection();
    });

    // Update button label to be clearer
    $("#clear-selection-btn").text("Unblock/Clear Selected Dates");

    // Handle the booking status checkbox state change to refresh calendar when it becomes visible
    $('input[name="_booking_status"]').on("change", function () {
      
      // Check if the checkbox is checked (calendar section is visible)
      if ($(this).is(":checked")) {
        // Small delay to ensure the container is fully visible before refreshing
        setTimeout(function () {
          if (window.listeoCalendar) {
            // Trigger window resize to make FullCalendar recalculate dimensions
            window.dispatchEvent(new Event("resize"));

            // For more stubborn cases, explicitly call the calendar's render method again
            window.listeoCalendar.render();

            console.log("Calendar refreshed after becoming visible");
          }
        }, 100); // 100ms delay
      }
    });

    // Add document click handler to help manage tooltip
    $(document).on("click", function (e) {
      // Only handle clicks outside the tooltips and calendar
      if (
        tooltipVisible &&
        !$(e.target).closest(".selection-tooltip, #single-date-tooltip")
          .length &&
        !$(e.target).closest(".fc-day, .fc-daygrid-day").length
      ) {
        // Hide tooltips when clicking elsewhere
        $("#selection-tooltip, #single-date-tooltip").remove();
        $("body").removeClass("has-date-tooltip");
        tooltipVisible = false;
      }
    });

    // Render calendar
    calendar.render();

    // Expose calendar for debugging
    window.listeoCalendar = calendar;
  }
});
