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
});/*
 * Под obj понимается следующий код:
 * <ul class='gallery_container' id='catalog'>
 * 	<li class='gallery_thumb big' data-pile='...'>
 * 		<a href='...' status='show'>
 * 			<div class='gallery_thumb_name'>...</div>
 * 		</a>
 * 	</li>
 * </ul>
*/
$(function() {	
    $.widget( "nfsko.catalog", {
    	thumbs : null,
    	// Конструктор
    	_create : function() {
    		if ((!this.element.is('ul.gallery-container#catalog') ))
				throw new Error("Error in nfsko.catalog: входной объект не соответствует шаблону");
    		this.element.addClass('ui-catalog');
    		this.thumbs = this.element.children('li.gallery_thumb');
    		this.backward = $('#content_backward');
    		this.initGroups();    		
    	},
    	// Дейструктор
    	_destroy : function() {
    		this.element.removeClass('ui-catalog');
    	},
    	// Отображает названия миниатюр
		_showNames : function() {
			// Реализовано в виде отдельной функции, потому что... ну хер его знает, не работает из параметра вызов
			this.element.find('div.gallery_thumb_name').show();
		},
		// Скрывает названия миниатюр
		_hideNames : function() {
			// Реализовано в виде отдельной функции, потому что... ну хер его знает, не работает из параметра вызов
			this.element.find('div.gallery_thumb_name').hide();
		},
		// Инициализация групп категорий
		initGroups : function() {
			this.thumbs.css({
				'position' : 'absolute',
				'display' : 'block'
			});
			var t = this;
			stapel = this.element.stapel({
					gutter : 2,
					pileAngles : 0,
					delay : 0,
					// Действие после загрузки всех миниатюр
					onLoad : function() { t._onLoad(); },
					// Действие перед открытием группы
					onBeforeOpen : function( pileName ) { t._onBeforeOpen( pileName ); },
					// Действие перед закрытием группы
					onBeforeClose : function( pileName ) { t._onBeforeClose( pileName ); }
			});
		},
		// Действие после загрузки всех миниатюр
		// Скрываем названия элементов
		_onLoad : function() {
			this._hideNames();
		},
		// Действие перед открытием группы
		// Показываем названия элементов, проставляем навигацию
		_onBeforeOpen : function( pileName ) {
			this.backward.bind('click', function(){ stapel.closePile(); return false; }).show();
			this._showNames();
			this._setHash();
		},
		// Действие перед закрытием группы
		// Скрываем названия элементов, очищаем навигацию
		_onBeforeClose : function( pileName ) {
			this.backward.unbind('click').hide();
			this._hideNames();
			this._delHash();
		},
		// Установка hash-части
		_setHash : function() {
			
		},
		// Удаление hash-части
		_delHash : function() {
			
		}
    });
});/*
 * <li class='gallery_thumb small' id='thumb-template'> <!-- ID ���� ���� � ��������� -->
 * 	<a href='...' status="show" imgid="...">
 * 		<img/> <!-- ����������� � �������� ����, ������������� ��� ��������� � JS -->
 * 	</a>
 *	<div class="ui-progressbar"></div> <!-- ������������ ������ � ��������� -->
 * </li>
 */
$(function() {	
    $.widget( "nfsko.thumbnail", {
    	options : {
    		thumbnailsPath : 'thumbs/'
    	},
		imageDOM : null,
		thumbDOM : null,
		progressDOM : null,
    	// �����������
    	_create : function() {
			if ( !this.element.is('li.gallery_thumb.small') )
				throw new Error("Error in nfsko.thumb: ������� ������ �� ������������� �������");
			this.element.addClass('ui-thumb');
			this.imageDOM = this.element.children('a');
			/*this.thumbDOM = $('<img>').appendTo( this.imageDOM );
			// ��������� ���������
			this.thumb( this._getThumbSrc() );*/
			this.thumbDOM = this.imageDOM.children('img');
			this.thumb( this.thumbDOM.attr('lazysrc') );
			
//			var t = this;
//			this.element.find('div.buttons div').each(function(){
//				$(this).bind('click', function() {
//					t._toggleVisibility(this);
//				});
//			});
    	},
    	// �����������
    	_destroy : function() {
    		this.element.removeClass('ui-thumb');
    	},
    	// ����������/������������� ������ �� �����������
    	image : function( newImage ) {
    		if (undefined === newImage)
    			return this.imageDOM.attr('href');
    		this.imageDOM.attr('href', newImage);
    		return newImage;
    	},
    	// ����������/������������� ���������
    	thumb : function( newThumb ) {
    		if (undefined === newThumb)
    			return this.thumbDOM.attr('src');
    		var t = this;
    		this.thumbDOM.bind('load', function() {
    			t.imageDOM.removeClass('ui-loading');
    			t._format();
    		});
    		t.imageDOM.addClass('ui-loading');
    		this.thumbDOM.attr('src', newThumb);    		
    		return newThumb;
    	},
    	// ����������/������������� ������ (���������) �����������
    	status : function( newStatus ) {
    		if (undefined === newStatus)
    			return this.imageDOM.attr('status');
    		this.imageDOM.attr('status', newStatus);
    	},
		// ������������� ������ ��������� (�������/���������)
		_format : function() {
			if (this.thumbDOM.width() / this.thumbDOM.height() < 1)
				this.element.addClass('book');
			else
				this.element.addClass('album');
		},
		// ���������� URL ���������, �� ������ URL ����������� 
		/*_getThumbSrc : function() {
			return this.image().replace(
					/(.+?)([^\/]+?\.[jpg,jpeg])/,
					"$1" + this.options.thumbnailsPath + "$2");
		},*/
		// �������� ������ ��������
		createProgressBar : function() {
			var t = this;
			this.progressDOM = $('<div>').appendTo( this.element );
			this.progressDOM.progressbar({
				max : 101, // � ���������
				value : 0,
				complete : function( event, ui ) {
					setTimeout(function() {
						t.progressDOM.progressbar("destroy");
						// ����������� ���������
						//$.album.album( 'addThumb', t.element.remove() ); // TODO 
						//$.album.album( 'initGroup' ); 
					}, 1000);
				}
			});
		},
		// ���������� ��������� ��������
		updateProgressBar : function( percent ) {						
			this.progressDOM.progressbar("option", "value", percent);
		},
		// �������� �� �����
		createFromFile : function( file ) {
			var reader = new FileReader(),
				t = this;
			reader.onload = function(e) {
				// e.target.result �������� ���� � �����������
				t.thumb( e.target.result );	
			};
			reader.readAsDataURL( file );
			// ������ ������ ��������
			this.createProgressBar();				
		},
		// ���������� �������� ��������
		uploadDone : function( isError, error ) {
			if ( false == isError ) {
				this.element.addClass('new');
				this.updateProgressBar(101);
			} else {
				this.element.addClass('error');
				this.uploadError( error[0] );
			}
		},
		// ������ �������� �����������
		uploadError : function( errCode ) {	
			this.progressDOM.progressbar("option", "value", 'auto').text(errCode);
		},
//		// ������������ ��������� �����������
//		_toggleVisibility : function( button ) {
//			var element = this;
//			$.ajax({
//				url: $(button).attr('href'),
//				type: "POST",
//				dataType: "json",
//				context: element,
//				beforeSend : function( jqXHR, textStatus ) {
//					this.status('changing');
//				}
//			}).done(function( response ) {
//				// ���������� ������ �� �������
//				var rh = new ResponseHandler({
//					responseType: 'ajax',
//					onSuccess : function( body ) {
//						this.status( body.image_visibility );											
//					},
//					onFailure : function( error ) {
//						alert( "DestinationInnerFail: ���-�� �� ��� ��� ��������� ��������� �����������" );
//					}
//				});
//				rh.handler( response );
//			}).fail(function( jqXHR, textStatus ) {
//				// ��������� ������ ��� ������ �������
//				alert( "RequestNotValidFail: " + textStatus );
//			});
//			return false;
//		}	
    });
});