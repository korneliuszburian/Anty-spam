document.addEventListener("DOMContentLoaded", function () {
  const selectIds = ["#form-project-type", "#form-budget", "#form-deadline"];

  function handleSelectChange(selectId) {
    const selectElement = document.querySelector(selectId);

    if (selectElement) {
      selectElement.addEventListener("change", function () {
        const selectedOption =
          selectElement.options[selectElement.selectedIndex];

        for (let i = 0; i < selectElement.options.length; i++) {
          selectElement.options[i].removeAttribute("selected");
        }

        selectedOption.setAttribute("selected", "selected");
      });
    }
  }

  selectIds.forEach(handleSelectChange);

  const fieldsToTrack = [
    "input-name",
    "input-company",
    "input-phone",
    "input-email",
    "menu-project-type",
    "menu-budget",
    "menu-deadline",
    "textarea-comments",
  ];

  function isSelectValid(selectElement) {
    const selectedOption = selectElement.querySelector(
      "option[selected][disabled]"
    );
    return !selectedOption;
  }

  function isFieldValid(field) {
    return (
      !field.classList.contains("wpcf7-not-valid") && field.value.trim() !== ""
    );
  }

  function addFilledAndValidClass(field) {
    const className = "q__in--fill";

    if (field.tagName === "SELECT") {
      if (isSelectValid(field)) {
        field.classList.add(className);
      } else {
        field.classList.remove(className);
      }
    } else if (isFieldValid(field)) {
      field.classList.add(className);
    } else {
      field.classList.remove(className);
    }
  }

  fieldsToTrack.forEach(function (fieldName) {
    const field = document.querySelector('[name="' + fieldName + '"]');

    if (field) {
      field.addEventListener("blur", () => addFilledAndValidClass(field));
      field.addEventListener("change", () => addFilledAndValidClass(field));
    }
  });
});
