document.addEventListener("DOMContentLoaded", function () {
  var isCollapsed = false;

  document.addEventListener("keydown", function (e) {
    if (e.ctrlKey && e.shiftKey && e.keyCode === 88) {
      e.preventDefault();

      if (!isCollapsed) {
        document
          .querySelectorAll(".layout:not(.-collapsed)")
          .forEach(function (layout) {
            var collapseButton = layout.querySelector(".acf-icon.-collapse");
            if (collapseButton) {
              collapseButton.click();
            }
          });
        isCollapsed = true;
      } else {
        document
          .querySelectorAll(".layout.-collapsed")
          .forEach(function (layout) {
            var collapseButton = layout.querySelector(".acf-icon.-collapse");
            if (collapseButton) {
              collapseButton.click();
            }
          });
        isCollapsed = false;
      }
    }
  });
});
