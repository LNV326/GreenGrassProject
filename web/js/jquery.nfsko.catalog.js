/*
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
    		this.left_arrow = $('.gallery-navigate.left');
    		this.right_arrow = $('.gallery-navigate.right');
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
//			navigate.append('<a>' + pileName + '</a>');
//			navigate.find('a:first-child').on('click', function() {
//					stapel.closePile();
//					return false;
//			});
			this.left_arrow.bind('click', function(){ stapel.closePile(); return false; }).show();
			this._showNames();
		},
		// Действие перед закрытием группы
		// Скрываем названия элементов, очищаем навигацию
		_onBeforeClose : function( pileName ) {
//			navigate.find('a:last-child').remove();
//			navigate.find('a:first-child').unbind('click');
			this.left_arrow.unbind('click').hide();
			this._hideNames();
		}
    });
});