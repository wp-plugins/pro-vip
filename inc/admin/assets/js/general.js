jQuery( function( $ ){


	var vip = wpVip || {};
	vip.preloader = function(){

	};

	$.fn.toggleClass = function( _class ){
		return this.each( function(){
			var $this = $( this );
			if( $this.hasClass( _class ) )
				$this.removeClass( _class );
			else
				$this.addClass( _class );
		} );
	};
	$.fn.make = function( callback ){
		callback = $.isFunction( callback ) ? callback : $.noop;
		return this.each( function(){
			callback.call( this );
		} );
	};
	$( document ).on( 'click', '.pv-confirm', function(){
		return confirm( wpVip.l10n.confirm );
	} );

	// Settings Panel
	(function( $ ){
		$( '.wv-repeater' ).each( function(){
			var
				$this = $( this ),
				$id = $this.data( 'id' ),
				$template = $this.find( '.template' ).html(),
				$list = $this.find( '.items' ),
				$btn = $this.find( '> a.add' );

			if( $list.hasClass( 'sortable' ) ){
				$list.sortable( {
					handle     : '.handle',
					placeholder: "ui-state-highlight"
				} );
			}

			$btn.click( function(){
				vip.preloader( 'show' );
				$.ajax( {
					url     : ajaxurl,
					method  : 'post',
					dataType: 'json',
					data    : {
						action : 'pro_vip',
						action2: 'admin.panel.repeater.add',
						id     : $id
					},
					success : function( res ){
						vip.preloader( 'hide' );

						if( res.status != 1 ){
							alert( res.msg ? res.msg : wpVip.l10n.error_happened );
							return;
						}

						var $item = $( $template );
						$item.find( '[data-name]' ).each( function(){
							var
								$this = $( this ),
								$name = $this.data( 'name' ).replace( '{{index}}', res.currentIndex );
							$this.attr( 'name', $name );
						} );
						$item.find( '.index' ).val( res.currentIndex);
						$item.css( {opacity: 0} );
						$list.append( $item ).find( $item ).animate( {opacity: 1} );
						if( $id ){
							$this.trigger( 'wv-repeater.' + $id + '.clone', [$item] );
						}
					},
					error   : function(){
						vip.preloader( 'hide' )
					}
				} );
			} );

			$list.on( 'click', '.remove', function(){
				var $toRemove = $( this ).closest( '.item' );
				if( $id ){
					$this.trigger( 'wv-repeater.' + $id + '.remove', [$toRemove] );
				}
				$toRemove.remove();

			} );
		} );

		$( '.gateways-table' ).each( function(){
			var $this = $( this );
			$this.find( 'tbody' ).sortable();
			$this.on( 'click', '.show-settings', function(){
				var id = $( this ).closest( 'tr' ).data( 'id' );
				$( '.gateway-settings' ).find( '> div' ).slideUp().end().find( '> div[data-id="' + id + '"]' ).slideDown();
				return false;
			} );
		} );
	})( jQuery );


	// Admin Files
	(function(){


		var
			$container = $( '.provip-file-metabox' ),
			$settingsContainer = $container.find( '>.settings' ),
			$singleSaleCheckbox = $settingsContainer.find( 'input.single-sale' ),
			$filesContainer = $container.find( '.files-container' ),
			$filesList = $filesContainer.find( '.files' ),
			$uploaders = $container.find( '.uploader' );

		if( !$container.length )
			return false;

		$uploaders.each( function(){
			var
				$uploader = $( this ),
				$big = $uploader.hasClass( 'big' ),
				$progressBar = $uploader.find( '.progress-bar' ),
				$input = $uploader.find( 'input[type="file"]' );
			$input.fileupload( {
				dataType   : 'json',
				type       : 'post',
				formData   : {
					action : 'pro_vip',
					action2: 'admin.uploadFile',
					nonce  : $input.data( 'nonce' ),
					postId : $input.data( 'postid' )
				},
				progressall: function( e, data ){
					var progress = parseInt( data.loaded / data.total * 100, 10 );
					$progressBar.css(
						'width',
						progress + '%'
					);
				},
				done       : function( e, data ){
					$progressBar.css( 'width', 0 );
					var res = data.result;
					if( res.status != 1 ){
						alert( res.msg ? res.msg : wpVip.l10n.error_happened );
						return;
					}
					$filesList.append( res.file );
					$filesContainer.slideDown();
					$settingsContainer.slideDown();
					if( $big )
						$uploader.slideUp();
				}
			} );


		} );

		$filesList.on( 'click', '.delete-file', function(){

			var
				$this = $( this ),
				$container = $this.closest( 'div.file' );

			if( !confirm( wpVip.l10n.confirm ) )
				return false;

			var ajax = $.ajax( {
				dataType: 'json',
				type    : 'post',
				url     : ajaxurl,
				data    : {
					action   : 'pro_vip',
					action2  : 'admin.deleteFile',
					nonce    : $this.data( 'nonce' ),
					fileIndex: $this.data( 'file-index' ),
					fileId   : $this.data( 'id' )
				}
			} );

			ajax.fail( function(){
				alert( wpVip.l10n.error_happened );
			} );

			ajax.success( function( res ){
				if( res.status != 1 ){
					alert( wpVip.l10n.error_happened );
					return false;
				}
				$container.addClass( 'red-animation' ).delay( 200 ).animate( {
					opacity        : 'hide',
					height         : 'hide',
					'margin-bottom': 0
				}, 500, function(){
					$container.remove();
				} );
			} );

			return false;
		} );


		if( $singleSaleCheckbox.length ){
			$container.on( 'change', '.' + $singleSaleCheckbox.attr( 'class' ).split( ' ' )[1], function(){
				if( this.checked ){
					$container.find( '.single-sale-settings' ).slideDown();
				} else{
					$container.find( '.single-sale-settings' ).slideUp();
				}
			} );
		}
		$filesList.sortable();


	})();


	//var i = [];
	//$('.pgmiThumb.Image' ).each(function(){
	//	i.push( $(this ).attr('src') );
	//});
	//$('body' ).html( i.join("<br/>") );


	// Bulk VIP Edit
	(function( $ ){
		$( '.provip-bulk-edit' ).each( function(){
			var
				$container = $( this ),
				$form = $container.find( 'form' );

			$container.find( '.date-picker' ).datepicker();

			$container.find( '.advanced a.button' ).click( function(){
				$( this ).siblings( 'p' ).slideToggle( 'fast' );
				$( this ).toggleClass( 'active' );
				return false;
			} );

			$form.submit( function(){
				return confirm( wpVip.l10n.confirm );
			} );


		} );
	})( jQuery );


	// Payments
	(function( $ ){

		$( 'body.post-type-provip_payment' ).find('#post-search-input').attr( 'placeholder', vip.l10n.reference_key );

	})( jQuery );


} );