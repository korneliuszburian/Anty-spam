const readSystemColorPreferences = () =>
window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

let selectedScheme;
const updateScheme = (overriddenScheme) => {
selectedScheme = overriddenScheme || 'light';
localStorage.setItem('selectedScheme', selectedScheme);

if (selectedScheme === 'dark') {
    document.querySelector('body').classList.add('dark');
    updateButtonAttribute('dark');
} else {
    document.querySelector('body').classList.remove('dark');
    updateButtonAttribute('light');
}
};

const updateButtonAttribute = (theme) => {
const themeToggleButton = document.getElementById('themeToggle');
themeToggleButton.setAttribute('data-theme', theme);
};

const savedTheme = localStorage.getItem('selectedScheme') || 'light';
updateScheme(savedTheme);

document.getElementById('themeToggle').addEventListener('click', () => {
var newScheme = selectedScheme === 'dark' ? 'light' : 'dark';
updateScheme(newScheme);
});