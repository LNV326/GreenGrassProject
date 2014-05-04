/*
 * РџРѕРґ obj РїРѕРЅРёРјР°РµС‚СЃСЏ СЃР»РµРґСѓСЋС‰РёР№ РєРѕРґ:
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
    	// РљРѕРЅСЃС‚СЂСѓРєС‚РѕСЂ
    	_create : function() {
    		ALB = this;
    		if (!this.element.is('ul.gallery-container#album') )
				throw new Error("Error in nfsko.album: РІС…РѕРґРЅРѕР№ РѕР±СЉРµРєС‚ РЅРµ СЃРѕРѕС‚РІРµС‚СЃС‚РІСѓРµС‚ С€Р°Р±Р»РѕРЅСѓ");
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
    	// Р”РµР№СЃС‚СЂСѓРєС‚РѕСЂ
    	_destroy : function() {
    		this.element.removeClass('ui-album');
    	},
    	onHashChange : function() {
			// Р§С‚РµРЅРёРµ С…СЌС€Р°
			var hash = $(window).hashChanger('get');
			if ( this.imageRegExp.test(hash) ) {
				var imageId = hash.replace(this.imageRegExp, "$1"),
				image = this._getThumbs().find('a[imgid="'+imageId+'"]');
				if ( image.length > 0 )
					image.click();
			}
    	},
    	// Р’РѕР·РІСЂР°С‰Р°РµС‚ РІСЃРµ РјРёРЅРёР°С‚СЋСЂС‹
		_getThumbs : function() {
			// РџРѕР»СѓС‡РµРЅРёРµ РјРёРЅРёР°С‚СЋСЂ СЂРµР°Р»РёР·РѕРІР°РЅРѕ С„СѓРЅРєС†РёРµР№, С‚Р°Рє РєР°Рє С‡РёСЃР»Рѕ РјРёРЅРёР°С‚СЋСЂ РјРѕР¶РµС‚ РјРµРЅСЏС‚СЊСЃСЏ
			return this.element.find('li.gallery_thumb');
		},
		// Р’РѕР·РІСЂР°С‰Р°РµС‚ РјРёРЅРёР°С‚СЋСЂС‹ Р±РµР· img
		_getThumbsWithoutImg : function() {
			// Р РµР°Р»РёР·РѕРІР°РЅРѕ РІ РІРёРґРµ С„СѓРЅРєС†РёРё, С‚Р°Рє РєР°Рє С‡РёСЃР»Рѕ РјРёРЅРёР°С‚СЋСЂ Р±РµР· img РјРѕР¶РµС‚ РјРµРЅСЏС‚СЊСЃСЏ
			return this.element.find('li.gallery_thumb:not(:has(img[src]))');
		},
		// Р›РµРЅРёРІР°СЏ Р·Р°РіСЂСѓР·РєР° РёР·РѕР±СЂР°Р¶РµРЅРёР№
		_lazyLoad : function() {
			if (this.lazyLoadInProgress)
				return;
			this.lazyLoadInProgress = true;
			// РџРѕРёСЃРє РјРёРЅРёР°С‚СЋСЂ Р±РµР· РёР·РѕР±СЂР°Р¶РµРЅРёР№
			var thumbs = this._getThumbsWithoutImg(),
				showLine = $(window).height() + $(window).scrollTop();
			// РўСѓС‚ РїСЂРёРјРµРЅСЏРµС‚СЃСЏ РјРµС‚РѕРґ РґРІРѕРёС‡РЅРѕРіРѕ РїРѕРёСЃРєР°
			// ... РЅРµ Р·СЂСЏ СЏ РїСЂРѕСѓС‡РёР»СЃСЏ 5 Р»РµС‚ РІ РёРЅСЃС‚РёС‚СѓС‚Рµ...
			var start = 0,
				stop = thumbs.length-1,
				current = 0;
			while (start < stop) {
				current = Math.round(start + (stop-start)/2);							
				var thumb = $(thumbs.get(current));
				// Р•СЃР»Рё РјРёРЅРёР°С‚СЋСЂР° РІРёРґРёРјР°
				if (showLine > thumb.offset().top)
					start = current;
				else 
					stop = current;
				if (start+1 == stop)
					start++;
			}
			// РћС‚РѕР±СЂР°Р¶РµРЅРёРµ img Сѓ РјРёРЅРёР°С‚СЋСЂ, РЅР°С‡РёРЅР°СЏ СЃ РїРµСЂРІРѕР№ Р±РµР· img Рё РґРѕ РіСЂР°РЅРёС†С‹ СЌРєСЂР°РЅР°
			for (current = 0; current <= stop; current++)
				$(thumbs.get(current)).thumbnail();
			this.lazyLoadInProgress = false;
		},
		// Р?РЅРёС†РёР°Р»РёР·Р°С†РёСЏ fancybox РґР»СЏ РјРёРЅРёР°С‚СЋСЂ			
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
				    		counter = 'Р?Р·РѕР±СЂР°Р¶РµРЅРёРµ ' + (this.index + 1) + ' РёР· ' + this.group.length + (this.title ? ' - ' + this.title : '') + '.',
				    		helper = 'РџРѕРґСЃРєР°Р·РєР°: РґРѕСЃС‚СѓРїРЅС‹ Р±С‹СЃС‚СЂС‹Рµ РєР»Р°РІРёС€Рё "&larr;/&rarr;" - РЅР°Р·Р°Рґ/РІРїРµСЂС‘Рґ, "Esc" - Р·Р°РєСЂС‹С‚СЊ, "F" - РїРѕР»РЅС‹Р№ СЂР°Р·РјРµСЂ.';
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
		// РЈРЅРёС‡С‚РѕР¶Р°РµС‚ fancybox РґР»СЏ РёР·РѕР±СЂР°Р¶РµРЅРёР№, СѓР±РёСЂР°РµС‚ onclick
		removeGroup : function() {
			this._getThumbs().find('a').removeAttr('rel').unbind('click');
		},
		// Р”РѕР±Р°РІР»СЏРµС‚ РёР·РѕР±СЂР°Р¶РµРЅРёРµ РІ РЅР°С‡Р°Р»Рѕ
		addThumb : function( thumb ) {
			this.element.prepend( thumb );
		},
		// РЎРєСЂС‹С‚РёРµ СЃРѕРѕР±С‰РµРЅРёСЏ Р°Р»СЊР±РѕРјР°
		_toggleNoImages : function() {
			if (this.imagesCount() > 0)
				this.noImages.hide();
			else this.noImages.show();
		},
		// РљРѕР»РёС‡РµСЃС‚РІРѕ РёР·РѕР±СЂР°Р¶РµРЅРёР№
		imagesCount : function() {
			return this.element.children('li').length;
		}
    });
});/*
 * РџРѕРґ obj РїРѕРЅРёРјР°РµС‚СЃСЏ СЃР»РµРґСѓСЋС‰РёР№ РєРѕРґ:
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
    	// РљРѕРЅСЃС‚СЂСѓРєС‚РѕСЂ
    	_create : function() {
    		if ((!this.element.is('ul.gallery-container#catalog') ))
				throw new Error("Error in nfsko.catalog: РІС…РѕРґРЅРѕР№ РѕР±СЉРµРєС‚ РЅРµ СЃРѕРѕС‚РІРµС‚СЃС‚РІСѓРµС‚ С€Р°Р±Р»РѕРЅСѓ");
    		this.element.addClass('ui-catalog');
    		this.thumbs = this.element.children('li.gallery_thumb');
    		this.left_arrow = $('.gallery-navigate.left');
    		this.right_arrow = $('.gallery-navigate.right');
    		this.initGroups();
    	},
    	// Р”РµР№СЃС‚СЂСѓРєС‚РѕСЂ
    	_destroy : function() {
    		this.element.removeClass('ui-catalog');
    	},
    	// РћС‚РѕР±СЂР°Р¶Р°РµС‚ РЅР°Р·РІР°РЅРёСЏ РјРёРЅРёР°С‚СЋСЂ
		_showNames : function() {
			// Р РµР°Р»РёР·РѕРІР°РЅРѕ РІ РІРёРґРµ РѕС‚РґРµР»СЊРЅРѕР№ С„СѓРЅРєС†РёРё, РїРѕС‚РѕРјСѓ С‡С‚Рѕ... РЅСѓ С…РµСЂ РµРіРѕ Р·РЅР°РµС‚, РЅРµ СЂР°Р±РѕС‚Р°РµС‚ РёР· РїР°СЂР°РјРµС‚СЂР° РІС‹Р·РѕРІ
			this.element.find('div.gallery_thumb_name').show();
		},
		// РЎРєСЂС‹РІР°РµС‚ РЅР°Р·РІР°РЅРёСЏ РјРёРЅРёР°С‚СЋСЂ
		_hideNames : function() {
			// Р РµР°Р»РёР·РѕРІР°РЅРѕ РІ РІРёРґРµ РѕС‚РґРµР»СЊРЅРѕР№ С„СѓРЅРєС†РёРё, РїРѕС‚РѕРјСѓ С‡С‚Рѕ... РЅСѓ С…РµСЂ РµРіРѕ Р·РЅР°РµС‚, РЅРµ СЂР°Р±РѕС‚Р°РµС‚ РёР· РїР°СЂР°РјРµС‚СЂР° РІС‹Р·РѕРІ
			this.element.find('div.gallery_thumb_name').hide();
		},
		// Р?РЅРёС†РёР°Р»РёР·Р°С†РёСЏ РіСЂСѓРїРї РєР°С‚РµРіРѕСЂРёР№
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
					// Р”РµР№СЃС‚РІРёРµ РїРѕСЃР»Рµ Р·Р°РіСЂСѓР·РєРё РІСЃРµС… РјРёРЅРёР°С‚СЋСЂ
					onLoad : function() { t._onLoad(); },
					// Р”РµР№СЃС‚РІРёРµ РїРµСЂРµРґ РѕС‚РєСЂС‹С‚РёРµРј РіСЂСѓРїРїС‹
					onBeforeOpen : function( pileName ) { t._onBeforeOpen( pileName ); },
					// Р”РµР№СЃС‚РІРёРµ РїРµСЂРµРґ Р·Р°РєСЂС‹С‚РёРµРј РіСЂСѓРїРїС‹
					onBeforeClose : function( pileName ) { t._onBeforeClose( pileName ); }
			});
		},
		// Р”РµР№СЃС‚РІРёРµ РїРѕСЃР»Рµ Р·Р°РіСЂСѓР·РєРё РІСЃРµС… РјРёРЅРёР°С‚СЋСЂ
		// РЎРєСЂС‹РІР°РµРј РЅР°Р·РІР°РЅРёСЏ СЌР»РµРјРµРЅС‚РѕРІ
		_onLoad : function() {
			this._hideNames();
		},
		// Р”РµР№СЃС‚РІРёРµ РїРµСЂРµРґ РѕС‚РєСЂС‹С‚РёРµРј РіСЂСѓРїРїС‹
		// РџРѕРєР°Р·С‹РІР°РµРј РЅР°Р·РІР°РЅРёСЏ СЌР»РµРјРµРЅС‚РѕРІ, РїСЂРѕСЃС‚Р°РІР»СЏРµРј РЅР°РІРёРіР°С†РёСЋ
		_onBeforeOpen : function( pileName ) {
//			navigate.append('<a>' + pileName + '</a>');
//			navigate.find('a:first-child').on('click', function() {
//					stapel.closePile();
//					return false;
//			});
			this.left_arrow.bind('click', function(){ stapel.closePile(); return false; }).show();
			this._showNames();
		},
		// Р”РµР№СЃС‚РІРёРµ РїРµСЂРµРґ Р·Р°РєСЂС‹С‚РёРµРј РіСЂСѓРїРїС‹
		// РЎРєСЂС‹РІР°РµРј РЅР°Р·РІР°РЅРёСЏ СЌР»РµРјРµРЅС‚РѕРІ, РѕС‡РёС‰Р°РµРј РЅР°РІРёРіР°С†РёСЋ
		_onBeforeClose : function( pileName ) {
//			navigate.find('a:last-child').remove();
//			navigate.find('a:first-child').unbind('click');
			this.left_arrow.unbind('click').hide();
			this._hideNames();
		}
    });
});/*
 * <li class='gallery_thumb small' id='thumb-template'> <!-- ID есть лишь у заготовки -->
 * 	<a href='...' status="show" imgid="...">
 * 		<img/> <!-- Отсутствует в исходном коде, подставляется при обработке в JS -->
 * 	</a>
 *	<div class="ui-progressbar"></div> <!-- Присутствует только в заготовке -->
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
    	// Конструктор
    	_create : function() {
			if ( !this.element.is('li.gallery_thumb.small') )
				throw new Error("Error in nfsko.thumb: входной объект не соответствует шаблону");
			this.element.addClass('ui-thumb');
			this.imageDOM = this.element.children('a');
			/*this.thumbDOM = $('<img>').appendTo( this.imageDOM );
			// Установка миниатюры
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
    	// Дейструктор
    	_destroy : function() {
    		this.element.removeClass('ui-thumb');
    	},
    	// Возвращает/устанавливает ссылку на изображение
    	image : function( newImage ) {
    		if (undefined === newImage)
    			return this.imageDOM.attr('href');
    		this.imageDOM.attr('href', newImage);
    		return newImage;
    	},
    	// Возвращает/устанавливает миниатюру
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
    	// Возвращает/устанавливает статус (видимость) изображения
    	status : function( newStatus ) {
    		if (undefined === newStatus)
    			return this.imageDOM.attr('status');
    		this.imageDOM.attr('status', newStatus);
    	},
		// Устанавливает формат миниатюры (книжный/альбомный)
		_format : function() {
			if (this.thumbDOM.width() / this.thumbDOM.height() < 1)
				this.element.addClass('book');
			else
				this.element.addClass('album');
		},
		// Возвращает URL миниатюры, на основе URL изображения 
		/*_getThumbSrc : function() {
			return this.image().replace(
					/(.+?)([^\/]+?\.[jpg,jpeg])/,
					"$1" + this.options.thumbnailsPath + "$2");
		},*/
		// Создание полосы загрузки
		createProgressBar : function() {
			var t = this;
			this.progressDOM = $('<div>').appendTo( this.element );
			this.progressDOM.progressbar({
				max : 101, // В процентах
				value : 0,
				complete : function( event, ui ) {
					setTimeout(function() {
						t.progressDOM.progressbar("destroy");
						// Перемещение миниатюры
						//$.album.album( 'addThumb', t.element.remove() ); // TODO 
						//$.album.album( 'initGroup' ); 
					}, 1000);
				}
			});
		},
		// Обновление прогресса загрузки
		updateProgressBar : function( percent ) {						
			this.progressDOM.progressbar("option", "value", percent);
		},
		// Создание из файла
		createFromFile : function( file ) {
			var reader = new FileReader(),
				t = this;
			reader.onload = function(e) {
				// e.target.result содержит путь к изображению
				t.thumb( e.target.result );	
			};
			reader.readAsDataURL( file );
			// Создаём полосу загрузки
			this.createProgressBar();				
		},
		// Завершение процасса загрузки
		uploadDone : function( isError, error ) {
			if ( false == isError ) {
				this.element.addClass('new');
				this.updateProgressBar(101);
			} else {
				this.element.addClass('error');
				this.uploadError( error[0] );
			}
		},
		// Ошибка загрузки изображения
		uploadError : function( errCode ) {	
			this.progressDOM.progressbar("option", "value", 'auto').text(errCode);
		},
//		// Переключение видимости изображения
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
//				// Обработчик ответа от сервера
//				var rh = new ResponseHandler({
//					responseType: 'ajax',
//					onSuccess : function( body ) {
//						this.status( body.image_visibility );											
//					},
//					onFailure : function( error ) {
//						alert( "DestinationInnerFail: Что-то не так при изменении видимости изображения" );
//					}
//				});
//				rh.handler( response );
//			}).fail(function( jqXHR, textStatus ) {
//				// Обработка ошибки при вызове сервера
//				alert( "RequestNotValidFail: " + textStatus );
//			});
//			return false;
//		}	
    });
});