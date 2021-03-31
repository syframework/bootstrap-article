CKEDITOR.addTemplates('websyte', {
	imagesPath: '/assets/ckeditor/',
	templates: [
		{
			title: 'Article example',
			image: 'article.gif',
			description: 'Example of an article standard structure.',
			html: '<h1>Title 1 <small>Optionnal small title</small></h1>'+
			'<p class="lead">'+
				'A lead paragraph introduction for example.'+
			'</p>'+
			'<p>'+
				'<img alt="Photo" class="img-fluid rounded" src="https://picsum.photos/800/400" />'+
			'</p>'+
			'<h2>Title 2</h2>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'+
			'<figure class="figure float-right ml-2">'+
				'<img alt="Photo" class="figure-img img-fluid rounded" src="https://picsum.photos/350/200" /><figcaption class="figure-caption text-right">Example of picture with a legend.</figcaption>'+
			'</figure>'+
			'<h2>Title 2</h2>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'+
			'<h3>Title 3</h3>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'+
			'<h3>Title 3</h3>'+
			'<p>'+
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
			'</p>'
		},
		{
			title: 'Image with caption',
			image: 'image-with-caption.gif',
			description: 'An image with a caption below.',
			html: '<div><figure class="figure">'+
				'<img src="https://picsum.photos/800/400" class="figure-img img-fluid rounded" alt="">'+
				'<figcaption class="figure-caption">A caption for the above image.</figcaption>'+
			'</figure></div><p>&nbsp;</p>'
		},
		{
			title: '2 columns',
			image: 'two-columns.gif',
			description: '2 columns layout on desktop and 1 column on mobile',
			html: '<div class="row mb-3">'+
				'<div class="col-sm">'+
					'<p>'+
						'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
					'</p>'+
				'</div>'+
				'<div class="col-sm">'+
					'<p>'+
						'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'+
					'</p>'+
				'</div>'+
			'</div><p>&nbsp;</p>'
		}
	]
});
