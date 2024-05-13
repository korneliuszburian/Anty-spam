document.addEventListener('DOMContentLoaded', function() {
  const nav = document.getElementsByClassName('n')[0];

  if (nav) {
    let navTop = nav.offsetTop;

    if (nav.clientHeight) {
      document.documentElement.style.setProperty('--navHeight', `${nav.clientHeight}px`);
    }
  }
});
