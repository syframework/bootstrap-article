import Masonry from 'https://cdn.jsdelivr.net/npm/masonry-layout/+esm';
import imagesLoaded from 'https://cdn.jsdelivr.net/npm/imagesloaded/+esm';

window.addEventListener('load', () => {
	// Menu
	document.querySelectorAll('div[data-menu-articles]').forEach((div) => {
		div.appendChild(document.getElementById('menu-articles').content.cloneNode(true));
	})
});

document.body.addEventListener('feed-loaded', function () {
	var msnry = new Masonry('#feed-articles');
	msnry.layout();

	imagesLoaded('#feed-articles', () => {
		msnry.layout();
	});
});