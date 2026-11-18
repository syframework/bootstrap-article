import Masonry from 'https://cdn.jsdelivr.net/npm/masonry-layout/+esm';
import imagesLoaded from 'https://cdn.jsdelivr.net/npm/imagesloaded/+esm';

const FEED_ID = 'feed-articles';
// Real feed items are nested inside the .syfeed wrapper rendered by the Feed component
const ITEM_SELECTOR = 'a.item, .feed-next-page-button';
const GAP = 24;
const TWO_COLUMNS_BREAKPOINT = 992;

function getColumnCount() {
	return window.matchMedia(`(min-width: ${TWO_COLUMNS_BREAKPOINT}px)`).matches ? 2 : 1;
}

function layoutFeed() {
	const container = document.getElementById(FEED_ID);
	if (!container) return;

	const items = Array.from(container.querySelectorAll(ITEM_SELECTOR));
	if (items.length === 0 || container.clientWidth === 0) return;

	const columns = getColumnCount();
	const columnWidth = Math.floor((container.clientWidth - GAP * (columns - 1)) / columns);

	items.forEach((item) => {
		// Pagination is a full width brick placed under the grid
		item.style.width = item.classList.contains('feed-next-page-button') ? '100%' : `${columnWidth}px`;
	});

	let msnry = Masonry.data(container);
	if (msnry) {
		msnry.options.columnWidth = columnWidth;
		msnry.options.gutter = GAP;
		msnry.options.transitionDuration = 0;
		msnry.reloadItems();
		msnry.layout();
	} else {
		msnry = new Masonry(container, {
			itemSelector: ITEM_SELECTOR,
			columnWidth: columnWidth,
			gutter: GAP,
			// No transition: Feed.js checks the "load more" button position right after
			// layout(), an animated position would make it auto-load every page at once
			transitionDuration: 0,
		});
	}

	imagesLoaded(container, () => msnry.layout());
}

window.addEventListener('load', () => {
	// Menu
	document.querySelectorAll('div[data-menu-articles]').forEach((div) => {
		div.appendChild(document.getElementById('menu-articles').content.cloneNode(true));
	});

	layoutFeed();
});

document.body.addEventListener('feed-loaded', layoutFeed);

let resizeTimer;
window.addEventListener('resize', () => {
	clearTimeout(resizeTimer);
	resizeTimer = setTimeout(layoutFeed, 200);
});

// Avoid a single column flash before the load event
layoutFeed();
