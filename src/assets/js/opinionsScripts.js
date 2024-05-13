document.addEventListener("DOMContentLoaded", () => {
  const button = document.querySelector(".op__btn");
  let currentIndex = 0;
  let hiddenGroups = document.querySelectorAll(".opi__hidden");
  let groupsMaxIndex = hiddenGroups.length - 1;

  button.addEventListener("click", function () {
    if (currentIndex <= groupsMaxIndex) {
      let element = document.querySelector(".opi__hidden--" + currentIndex);
      if (element) {
        element.classList.add("opi__active");
        currentIndex++;
      }
    }

    if (currentIndex > groupsMaxIndex) {
      button.style.display = "none";
    }
  });
});