
(function($) {
	// Функции для работы с хэшем
	var hashChanger = {
		get : function() {
			return window.location.hash.replace('#', '');
		},
		set : function( newHash ) {
			window.location.hash = newHash;
		},
		clear : function() {
			this.set('');
		}
	};
	$.fn.hashChanger = function(options) {
		if (typeof options === 'string' && hashChanger[options]) {
			return hashChanger[options].apply(hashChanger, Array.prototype.slice.call(
					arguments, 1));
		}
	};
//	$(window).bind('hashchange', function() {
//		$.album('onHashChange');
//	});
})(jQuery);


(function($) {
	// Централизованный обработчик ответов от сервера
	var ResponseHandler = function( options ) {
		this._init(options);
	}
	ResponseHandler.prototype = {
		responseType : null,
		onSuccess : this._doNothing,
		onFailure : this._doNothing,
		_doNothing : function() {return true},
		response_statuses : {
			ST_SUCCESS : 'Success',
			ST_FAIL : 'Fail'
		},
		_init : function( options ) {
			if (typeof options === 'object')
				for(var key in options)
					this[key] = options[key];
		},
		// Обработчик ответов от сервера
		handler : function( response ) {
			// Если ожидаемый тип данных не указан, определяем его
			if (null === this.responseType)
				try {
					var json = $.parseJSON( response );
					this.responseType = 'ajax';
					response = json;
				} catch (e) {
					this.responseType = 'html';
				}
			switch ( this.responseType ) {
				case 'ajax': 
					switch ( response.status ) {
						case this.response_statuses.ST_SUCCESS : return this.onSuccess( response.body );
						case this.response_statuses.ST_FAIL : return this.onFailure( response.error );
					}
					break;
				case 'html': 
					return this.onSuccess( response );
			}
		}
	}
	
	var nfsko = {
		// ��� �������, ��������� � ������������� �������
		//
		/* Функции, связанные с галереей
		 * 
		 * История изменений:
		 * 1.0 Файл создан
		 * 1.1 Вынесено в отдельные классы: thumbClass, hashClass, thumbUploadClass, fancyBoxButtonsClass, albumClass, mainPageClass
		 * 1.2 Рефакторинг thumbClass, albumClass, fancyBoxButtonsClass, mainPageClass
		 * 1.3 thumbClass и albumClass вынесены в отдельные файлы, переведены на jquery-ui.
		 */
		gallery : function(options) {
											
			// Общие кнопки с интерфейсом fancybox (ajax)
			var fancyBoxButtonsClass = function(obj) {
				/*
				 * Под obj понимается следующий код:
				 * <div class="fb-dialog right">
				 * 	<a href='...'>Добавить</a>
				 * </div>
				 */
				this._init(obj);
			};
			fancyBoxButtonsClass.prototype = {
					// Конструктор
					_init : function(obj) {
						if ((obj === null) || (!obj.is('div.fb-dialog.right') ))
							throw new Error("Error in fancyBoxButtonsClass: входной объект не соответствует шаблону");
						this.obj = obj;
						this.buttons = this.obj.find('a');
						if (this.buttons.length > 0)
							this.buttons.fancybox({
								type : 'ajax',
								minWidth : 640,
								autoSize : true,
								afterLoad : function(coming) {
									// Обработчик ответа от сервера
									var rh = new ResponseHandler({
										responseType:null,
										onSuccess : function(body) {
											if (this.responseType != 'ajax') {
												var content = $(coming.content);
												content.children('.title').remove();
												coming.content = content;
											} else {
												//alert( "Операция успешно выполнена" );
												$.fancybox({
													autoSize : false,
													width : 300,
													height : 100,
													content : "Операция успешно выполнена"
												});
												return false;
											}											
										},
										onFailure : function(error) {
											alert( "DestinationInnerFail: "+error[0] );
											return false;
										}
									});
									return rh.handler(coming.content);
								}
							});					
					}
			};
			
			// Область загрузки изображений
			var dropAreaClass = function(obj) {
				/*
				 * Под obj понимается следующий код:
				 * <ul class='gallery_container' id='droparea'>
				 * 	<li class='gallery_thumb small' id='thumb-template'>
				 * 		<a href="" status="show" imgid=""></a>
				 * 	</li>
				 * 	<div id='dropbox'></div>
				 * 	<input type='file' multiple class='hide' id='input-files' destination='...'></input>
				 * </ul>
				 */
				this._init(obj);
			};
			dropAreaClass.prototype = {
				// Конструктор
				_init : function(obj) {					
					if ((obj === null) || (!obj.is('ul.gallery-container#droparea') ))
						throw new Error("Error in dropAreaClass: входной объект не соответствует шаблону");
					this.obj = obj;
					this.dropbox = $('#dropbox');
					this.input = $('#input-files');
					this.thumbTemplate = $('#droparea_utils li');
					// Установка заготовки
					//thumbClass.prototype.setThumbnailsTemplate(this.thumbTemplate);
					// Перенаправление клика на скрытый input
					var t = this;
					this.obj.bind('click', function() {
						t.input.trigger('click');
					});
					this.obj.filedrop({
						fallback_id : t.input.attr('id'), // Загрузка файлов из input
						paramname : 'image', // Имя поля в $_FILES
						maxfilesize : 20, // в Мб
						queuewait : 100,
						allowedfiletypes : [ 'image/jpeg' ],
						url : t.input.attr('destination'),
						// Переименование файла (если в названии будут русские буквы, то загрузка зафейлится)
						rename : t._fileRename,
						// Вызывается перед загрузкой для каждого файла
						beforeEach : function(file) { return t._addThumbFromFile(file); },
						// Обновление процесса загрузки
						progressUpdated : function(i, file, progress) { return t._updateThumbProgress(i, file, progress); },
						// Завершение процасса загрузки
						uploadFinished : function(i, file, response) { return t._updateThumbFinished(i, file, response); },
						// Обработка ошибки загрузки, отловленная загрузчиком
						error : t._onError
					});
				},
				// Переименование файла перед загрузкой
				_fileRename : function(name) {
					var date = new Date(),
						newName = date.getFullYear()
									+ date.getMonth() + date.getDate()
									+ date.getTime();
					return name.replace(/(.+?)(\[jpg,jpeg])/, newName + "$2");
				},
				// Вставка миниатюры в область загрузки
				_addThumbFromFile : function (file) {	
					var thumb = this.thumbTemplate.clone(),
						thumb = thumb.thumbnail(),
						thumb = thumb.thumbnail('createFromFile', file);
					this.obj.append( thumb );
					$.data( file, thumb );
					return true;
				},
				// Обновление процесса загрузки
				_updateThumbProgress : function(i, file, progress) {
					try {
						var thumb = $.data(file),
							thumb = thumb.thumbnail();
						thumb.thumbnail('updateProgressBar', progress);
					} catch (e) {
						console.error(e);
					}
				},
				// Завершение процасса загрузки
				_updateThumbFinished : function(i, file, response) {
					try {
						var thumb = $.data(file),
							thumb = thumb.thumbnail();
						thumb.thumbnail('uploadDone', 0, response.error);
					} catch (e) {
						console.error(e);
					}
					
					// Обработчик ответа от сервера
					var rh = new ResponseHandler({
						responseType : 'ajax',
						onSuccess : function( body ) {
							try {
								var thumb = $.data(file),
									thumb = thumb.thumbnail();
								thumb.thumbnail('uploadDone', 0, null);
							} catch (e) {
								console.error(e);
							}
							var thumb = $($.trim( body.image.html )),
								thumb = thumb.thumbnail();
							$.album.album( 'addThumb', thumb ); // TODO 
							$.album.album( 'initGroup' );
						},
						onFailure : function( error ) {
							try {
								var thumb = $.data(file),
									thumb = thumb.thumbnail();
								thumb.thumbnail('uploadDone', 1, error);
							} catch (e) {
								console.error(e);
							}
						}
					});
					rh.handler( response );				
				},
				// Обработка ошибки загрузки, отловленная загрузчиком
				_onError : function(errCode, file) {
					var data = $.data(file);
					if (!$.isEmptyObject(data))
						data.progressError(errCode);
					else
						alert(errCode);
				}
			};
			
			// Окно создания нового альбома
			var addAlbumClass = function(obj) {
				this._init(obj);
			};
			addAlbumClass.prototype = {
				_init : function(obj) {
					this.obj = obj;
					this.choices = this.obj.children('select');
					var t = this;
					this.obj.children('button[type="submit"]').bind('click', function() {
						var request = $.ajax({
							  url: t.obj.attr('action'),
							  type: "POST",
							  data: { 'albums' : t.choices.val() },
							  dataType: "json",
							  context: t,
						}).done(function( response ) {
							// Обработчик ответа от сервера
							var rh = new ResponseHandler({
								responseType:'ajax',
								onSuccess : function(body) {
									if (confirm('Альбом '+body.album.name+' успешно создан. Перейти в него?'))
										window.location.replace(body.album.url);
									else location.reload();
								},
								onFailure : function(error) {
									alert( "DestinationInnerFail: "+error[0] );
								}
							});
							rh.handler(response);
						}).fail(function( jqXHR, textStatus ) {
							// Обработка ошибки при вызове сервера
							alert( "RequestNotValidFail: " + textStatus );
						});
						return false;
					});
				}
			};
			
			if (this.is('.gallery-container#catalog')) {
				$.catalog = $('.gallery-container#catalog').catalog();
				var b = new fancyBoxButtonsClass($('.fb-dialog'));
			}
			if (this.is('.gallery-container#category')) {
				var b = new fancyBoxButtonsClass($('.fb-dialog'));
			}
			if (this.is('.gallery-container#album')) {
				$.album = $('.gallery-container#album').album();
				var b = new fancyBoxButtonsClass($('.fb-dialog'));
			}
			if (this.is('.gallery-container#droparea')) {
				$.droparea = new dropAreaClass($('.gallery-container#droparea'));
			}
			if (this.is('#form')) {
				$.addAlbum = new addAlbumClass(this);
			}
			if (this.is('.gallery-container#image')) {
				var b = new fancyBoxButtonsClass($('.fb-dialog'));
			}
//			if (typeof options === 'string' && gallery[options]) {
//				return gallery[options].apply(this, Array.prototype.slice.call(
//						arguments, 1));
//			}
		}
	};
	$.fn.nfsko = function(options) {
		if (typeof options === 'string' && nfsko[options]) {
			return nfsko[options].apply(this, Array.prototype.slice.call(
					arguments, 1));
		}
	};
})(jQuery);

$(document).ready(function() {
	// $('#menu_main').nfsko('mainMenu');
});