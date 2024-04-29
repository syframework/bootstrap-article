import Masonry from 'https://cdn.jsdelivr.net/npm/masonry-layout/+esm';

window.addEventListener('load', () => {
	const msnry = new Masonry('#feed-articles');
	msnry.layout();

	let observer = new MutationObserver(() => {
		msnry.layout();
	});

	observer.observe(document.getElementById('feed-articles'), { attributes: true, childList: true, subtree: true });

	// Menu
	document.querySelectorAll('div[data-menu-articles]').forEach((div) => {
		div.appendChild(document.getElementById('menu-articles').content.cloneNode(true));
	})
});