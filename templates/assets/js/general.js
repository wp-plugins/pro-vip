jQuery( function( $ ){

	$.modal.defaults.modalClass = 'wv-modal';
	$.modal.defaults.zIndex = 9999;
	$.modal.defaults.animationDuration = 200;

	//var proVip = proVip || {};


	// File Download Single
	(function(){

		var
			$downloadContainer = $( '.wv-single-download' );

		if( !$downloadContainer.length ){
			return false;
		}

		var
			$filesList = $downloadContainer.find( '.files-list' ),
			$purchaseForm = $downloadContainer.find( '#pv-purchase-file' );


		$filesList.on( 'click', '.purchase', function(){
			var $data = $( this ).siblings( '.file-data' );
			$purchaseForm.find( '.file-index' ).val( $data.find( '.file-index' ).text() );
			$purchaseForm.find( '.file-name' ).html( $data.find( '.file-name' ).html() );
			$purchaseForm.find( '.file-price' ).html( $data.find( '.file-price' ).html() );
		} );


		// Single Purchase Form
		$purchaseForm.validate( {
			rules   : {
				'pv-first-name'   : "required",
				'pv-email-address': {
					required: true,
					email   : true
				}
			},
			messages: {
				'pv-first-name'   : {
					required: proVip.l10n.required_field
				},
				'pv-email-address': {
					required: proVip.l10n.required_field,
					email   : proVip.l10n.valid_email
				}
			}
		} );
		$purchaseForm.on( 'click', 'label.error', function(){
			$( this ).remove();
		} );

	})();


	// Plans Form
	(function(){
		$( '.pv-plans-form' ).each( function(){
			var
				$form = $( this ),
				$loader = $form.find( '.preloader' );

			var updatePrice = function(){
				$loader.fadeIn();
				$.ajax( {
					url     : proVip.ajaxurl,
					method  : 'post',
					dataType: 'json',
					data    : {
						action : 'pro_vip',
						action2: 'frontend.calculatePlanPrice',
						level  : $form.find( '#pv-plan-level' ).val(),
						plan   : $form.find( '#pv-plan' ).val()
					},
					success : function( res ){
						$loader.fadeOut();
						if( res.status != 1 ){
							alert( 'An error happened.' );
							return false;
						}
						$form.find( '.cost' ).html( res.priceHtml );
					},
					error   : function(){
						$loader.fadeOut();
						alert( 'An error happened.' );
					}
				} );
			};
			updatePrice();
			$form.find( '#pv-plan, #pv-plan-level' ).change( updatePrice );

		} );
	})();

} );