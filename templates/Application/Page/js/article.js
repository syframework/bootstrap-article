window.addEventListener('load', () => {
	// Menu
	document.querySelectorAll('div[data-menu-article]').forEach((div) => {
		div.appendChild(document.getElementById('menu-article').content.cloneNode(true));
	})
});