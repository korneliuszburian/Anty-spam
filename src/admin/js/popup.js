document.addEventListener("DOMContentLoaded", function() {
  var popup = document.getElementById("message-popup");
  var popupMessage = document.querySelector(".popup-message");
  var closePopup = document.querySelector(".close-popup");
  
  document.querySelectorAll(".view-message").forEach(function(button) {
      button.addEventListener("click", function(event) {
          event.preventDefault();
          var message = this.getAttribute("data-message");
          popupMessage.textContent = message;
          popup.style.display = "block";
      });
  });

  closePopup.addEventListener("click", function() {
      popup.style.display = "none";
  });

  window.addEventListener("click", function(event) {
      if (event.target == popup) {
          popup.style.display = "none";
      }
  });
});
