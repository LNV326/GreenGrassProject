/*
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