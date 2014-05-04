/*
 * Под obj понимается следующий код:
 * <ul class='gallery_container' id='album'>
 * 	<li class='gallery_thumb small'>...</li>
 * 	<div id='gallery_exception'>No images in album</div>
 * </ul>
 */
$(function() {	
    $.widget( "nfsko.album", {
    	imageRegExp : /_image\/(\d+?)/,
		noImages : null,
		lazyLoadInProgress : false,
    	// Конструктор
    	_create : function() {
    		ALB = this;
    		if (!this.element.is('ul.gallery-container#album') )
				throw new Error("Error in nfsko.album: входной объект не соответствует шаблону");
    		this.element.addClass('ui-album');
			this.noImages = this.element.find('div#gallery_exception');
			this.initGroup();
			this._lazyLoad();
			var t = this;
			$(document).bind('scroll', function() {
				t._lazyLoad();
			});
			this.onHashChange();
    	},
    	// Дейструктор
    	_destroy : function() {
    		this.element.removeClass('ui-album');
    	},
    	onHashChange : function() {
			// Чтение хэша
			var hash = $(window).hashChanger('get');
			if ( this.imageRegExp.test(hash) ) {
				var imageId = hash.replace(this.imageRegExp, "$1"),
				image = this._getThumbs().find('a[imgid="'+imageId+'"]');
				if ( image.length > 0 )
					image.click();
			}
    	},
    	// Возвращает все миниатюры
		_getThumbs : function() {
			// Получение миниатюр реализовано функцией, так как число миниатюр может меняться
			return this.element.find('li.gallery_thumb');
		},
		// Возвращает миниатюры без img
		_getThumbsWithoutImg : function() {
			// Реализовано в виде функции, так как число миниатюр без img может меняться
			return this.element.find('li.gallery_thumb:not(:has(img[src]))');
		},
		// Ленивая загрузка изображений
		_lazyLoad : function() {
			if (this.lazyLoadInProgress)
				return;
			this.lazyLoadInProgress = true;
			// Поиск миниатюр без изображений
			var thumbs = this._getThumbsWithoutImg(),
				showLine = $(window).height() + $(window).scrollTop();
			// Тут применяется метод двоичного поиска
			// ... не зря я проучился 5 лет в институте...
			var start = 0,
				stop = thumbs.length-1,
				current = 0;
			while (start < stop) {
				current = Math.round(start + (stop-start)/2);							
				var thumb = $(thumbs.get(current));
				// Если миниатюра видима
				if (showLine > thumb.offset().top)
					start = current;
				else 
					stop = current;
				if (start+1 == stop)
					start++;
			}
			// Отображение img у миниатюр, начиная с первой без img и до границы экрана
			for (current = 0; current <= stop; current++)
				$(thumbs.get(current)).thumbnail();
			this.lazyLoadInProgress = false;
		},
		// Инициализация fancybox для миниатюр			
		initGroup : function() {
			this._toggleNoImages();
			this._getThumbs().find('a').attr('rel', 'group').unbind('click').fancybox({
					nextEffect : 'none',
					prevEffect : 'none',
					minWidth : 800,
					type : 'ajax',
					aspectRatio : true,
				    helpers : {
				        title: {
				            type: 'inside'
				        }				
				    },
				    afterLoad : function(coming) {
				    	var href = $(coming.content).find('img').attr('src'),
				    		links = $(coming.content).find('.image-links'),
				    		about = $(coming.content).find('.image-about'),
				    		size = $(coming.content).find('#image-size'),
				    		controls = $(coming.content).find('.fb-dialog.right'),
				    		counter = 'Изображение ' + (this.index + 1) + ' из ' + this.group.length + (this.title ? ' - ' + this.title : '') + '.',
				    		helper = 'Подсказка: доступны быстрые клавиши "&larr;/&rarr;" - назад/вперёд, "Esc" - закрыть, "F" - полный размер.';
				    	coming.content = coming.tpl.image.replace('{href}', href);	
				    	coming.autoWidth = false;
				    	coming.autoHeight = false;
				    	coming.title = counter + ' ' + helper;
				    	this.outer.append( about );
				    	this.outer.append( links );				    	
				    	size.text().replace(/.+\s(\d+)x(\d+)\s\w+/, function(str, w, h) {
				    		coming.width = w;
				    		coming.height = h;
				    	});
				    	links.find('input').bind('click', function() {
				    		this.select();
				    	});
				    	about.find('.left').append(controls).find('div')[0].remove();
				    },
					beforeLoad : function() {
						var imageId = $(this.element).attr('imgid'),
							newHash = '_image/' + imageId;
						$(window).hashChanger( 'set', newHash );
					},
					afterClose : function() {
						$(window).hashChanger( 'clear' );
					}
			});
		},
		// Уничтожает fancybox для изображений, убирает onclick
		removeGroup : function() {
			this._getThumbs().find('a').removeAttr('rel').unbind('click');
		},
		// Добавляет изображение в начало
		addThumb : function( thumb ) {
			this.element.prepend( thumb );
		},
		// Скрытие сообщения альбома
		_toggleNoImages : function() {
			if (this.imagesCount() > 0)
				this.noImages.hide();
			else this.noImages.show();
		},
		// Количество изображений
		imagesCount : function() {
			return this.element.children('li').length;
		}
    });
});