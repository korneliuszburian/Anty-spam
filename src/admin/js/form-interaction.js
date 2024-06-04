document.addEventListener('DOMContentLoaded', () => {
  var interacted = false;
  var keyboardInteracted = false;
  var timeout = 30;
  var timer;

  function startTimer() {
    if (!timer) {
      timer = setInterval(() => {
        if (timeout > 0) {
          console.log(`Time remaining: ${timeout} seconds`);
          timeout--;
        } else {
          clearInterval(timer);
          timer = null;
          console.log('Timer expired');
        }
      }, 1000);
    }
  }

  function stopTimer() {
    if (timer) {
      clearInterval(timer);
      timer = null;
      console.log('Timer stopped due to keyboard interaction');
    }
  }

  document.addEventListener('mousemove', () => {
    if (!interacted) {
      interacted = true;
      startTimer();
    }
  });

  document.addEventListener('keypress', () => {
    if (!keyboardInteracted) {
      keyboardInteracted = true;
      stopTimer();
    }
    interacted = true;
  });

  var form = document.querySelector('form');
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    if (!interacted) {
      // alert("This form was submitted without keyboard or mouse interaction, which is rather suspicious!");
      return;
    }

    if (!keyboardInteracted && timeout > 0) {
      // alert(`This form was submitted so rapidly that it made you look like a bot! Please try again after ${30 - timeout} seconds.`);
      return;
    }

    fetch(form.action, {
      method: 'POST',
      body: new URLSearchParams(new FormData(form))
    }).then(response => {
      if (response.ok) {
        return true;
      }
    });
  });
});
