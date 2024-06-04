function toggleList(button) {
  const wordsList = button.parentElement.nextElementSibling;
  const isExpanded = wordsList.style.display === 'block';
  wordsList.style.display = isExpanded ? 'none' : 'block';
  button.textContent = isExpanded ? '+' : '-';
}

document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.toggle-button');
  const wordsListWrapper = document.querySelector('.language-words');

  wordsListWrapper.style.display = isExpanded ? 'none' : 'block';
  console.log(wordsListWrapper);

  buttons.forEach(button => {
      button.addEventListener('click', () => toggleList(button));
  });
});