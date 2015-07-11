(function( $ ){
	if( typeof $ === 'undefined' )
		return false;

	(function(){
		"use strict";
		var PhoenixModal = function( content, args ){
			var self = this;

			var element = $( content );
			this.content( $( element.prop( 'outerHTML' ) )[0] );
			this.modal = element;

			var userSettings = {};
			if( typeof args[0] == 'object' )
				userSettings = args[0];
			if( typeof args[1] == 'object' )
				userSettings = args[1];

			this.settings = $.extend(
				{},
				$.PhoenixModal.defaultSettings,
				userSettings
			);


			if( args.length > 0 ){
				switch( typeof args[0] ){
					default :
						break;
					case 'string':
						switch( args[0] ){
							default :
								break;
							case 'show':
								this.show();
								break;
							case 'hide':
								this.hide();
								break;
							case 'toggle':
								if( this.isShown )
									this.hide();
								else
									this.show();
								break;
						}
						break;
				}
			}
		}
		PhoenixModal.prototype = {

			settings: {},

			overlay: $(),

			modalBox: null,

			modal: $(),

			isInitialized: false,

			_content: '',

			isShown: false,

			show: function(){

				if( this.isShown )
					return this;

				var event;

				event = $.Event( 'pm.modalShow' );
				this.modal.trigger( event );
				this.isShown = false;
				if( event.isDefaultPrevented() )
					return this;

				this.isShown = true;
				this.updateZindex();


				if( !this.isInitialized ){
					this._buildDOM();
					this._attachEventHandlers();
					this.isInitialized = true;
					this.modalBox.find( classesIds.modalContent ).html( this.content() );
				}

				this.updateZindex();


				if( this.settings.width )
					this.modalBox.width( this.settings.width );


				this.modalBox.css(
					'margin-top',
					-this.modalBox.outerHeight() / 2
				);


				var self = this;
				setTimeout( function(){
					self.modalBox.addClass( 'in' );
					self.overlay.addClass( 'in' );
				}, 10 );

				this.modal.trigger( $.Event( 'pm.modalShown' ) );

				return this;
			},


			hide: function(){
				var event = $.Event( 'pm.modalHide' );
				this.modal.trigger( event );
				this.isShown = false;
				if( event.isDefaultPrevented() )
					return this;
				var self = this;
				setTimeout( function(){
					self.modalBox.removeClass( 'in' );
					self.overlay.removeClass( 'in' );

				}, 10 );
				this.modal.trigger( 'pm.modalHidden' );
				return this;
			},

			updateZindex: function(){
				$.PhoenixModal.modalsIndex++;
				this.overlay.css( 'z-index', $.PhoenixModal.modalsIndex + zIndexStart );
			},

			content: function( content ){
				if( typeof content !== 'undefined' ){
					this._content = content;
					return this;
				}
				return this._content;
			},

			_buildDOM: function(){
				var overlay = $( '<div class="' + classesIds.overlay.substr( 1 ) + '"></div>' );
				this.overlay = overlay;
				if( this.settings.rtl )
					this.overlay.addClass( 'rtl' );

				this.modalBox = $( '<div></div>' );

				var closeModal = $( '<span class="' + classesIds.closeModal.substr( 1 ) + '"></span>' );
				this.modalBox.append( closeModal );

				var modalContent = $( '<div class="' + classesIds.modalContent.substr( 1 ) + '"></div>' );
				this.modalBox.append( modalContent );


				this.modalBox.addClass( 'pm-modal' );
				body.append( this.overlay );
				overlay.append( this.modalBox );
			},

			_attachEventHandlers: function(){
				var self = this;
				this.overlay.on( 'click', function( e ){
					var target = $( e.target );
					if(
						target.is( self.modalBox )
						|| target.closest( self.modalBox ).length > 0
					)
						return true;
					self.hide();
				} );
				this.overlay.on( 'click', classesIds.closeModal, function(){
					self.hide();
				} );
			}
		};

		$.PhoenixModal = function( modal ){
			return new PhoenixModal( modal, arguments );
		};

		$.PhoenixModal.modalsIndex = 0;

		$.PhoenixModal.defaultSettings = {
			width     : 0,
			rtl       : false,
			classesIds: {
				modal       : '.pm-modal',
				overlay     : '.pm-overlay',
				closeModal  : '.close-modal',
				modalContent: '.pm-modal-content'
			}
		};
		var classesIds = $.PhoenixModal.defaultSettings.classesIds;

		$.PhoenixModal.hasActiveModal = function(){
			return body.find( classesIds.overlay ).length !== 0;
		};

		$.fn.PhoenixModal = function(){
			return new PhoenixModal( this.get( 0 ), arguments );
		};
	})();

	$.fn.formBuilder = function( _s ){

		var
			generateHtmlFields = function( $container ){
				if( $container.find( '>.fields-container' ).length ){
					return false;
				}
				$container.append( '<div class="fields-container"></div>' );
			},
			settings = $.extend(
				{},
				{
					sortable: true
				},
				_s
			);

		return this.each( function(){

			var
				$this = $( this ),
				$fieldsContainer;

			generateHtmlFields( $this );

			if( settings.sortable && $.fn.sortable ){

			}

		} );

	};

})( jQuery );