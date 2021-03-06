<?php
global $_wp_admin_css_colors;
$left   = is_rtl() ? 'right' : 'left';
$colors = $_wp_admin_css_colors[ get_user_option( 'admin_color' ) ]->colors;
?>
<style type="text/css">
	.pv-panel .form-tab {
		position: relative;
	}

	.pv-panel .form-tab .section {
		padding-top: 50px;
		display: none;;

	}

	.pv-panel .form-tab .tab-sections {
		position: absolute;
		top: 13px;
		border-bottom: 2px solid #D5D5D5;
		width: 100%;;
	}

	.pv-panel .form-tab .tab-sections ul {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.pv-panel .form-tab .tab-sections ul li {
		display: inline-block;
		float: <?= $left ?>
	}

	.pv-panel .form-tab .tab-sections ul li a {
		display: block;
		text-decoration: none;
		background: <?= $colors[2] ?>;
		color: #FFF;
		padding: 5px 20px;
		border-radius: 2px;
		margin- <?= $left ?>: 7px;
	}
</style>

<div class="wrap pv-panel <?= $id ?>">


	<?php
	if ( ! empty( $title ) ) {
		echo '<h2>' . $title . '</h2>';
	}
	?>


	<?php
	if ( ! empty( $notice ) ) {
		echo '<div class="updated"><p>' . $notice . '</p></div>';
	}
	?>

	<?php
	if ( ! empty( $tabs_list ) ) {
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			$i = 0;
			foreach ( $tabs_list as $tab_id => $tab ) {
				$i ++;
				echo '<a href="#" class="nav-tab ' . ( $i == 1 ? 'nav-tab-active' : '' ) . '" data-id="' . $tab_id . '">' . $tab . '</a>';
			}
			?>
		</h2>
	<?php
	}
	?>


	<div class="tabs">

	</div>

	<div class="fields">
		<form method="post">
			<?= $fields; ?>
			<?= $hidden_fields; ?>
			<input type="submit" value="<?= __( 'Save Changes', 'provip' ); ?>" class="button button-primary" name="panel-save">
			<input type="submit" value="<?= __( 'Reset Settings', 'provip' ); ?>" class="button" name="panel-reset" onclick="return confirm( 'Are you sure?' );">
		</form>

	</div>


	<script type="text/javascript">
		(function( $, e, b ){
			var c = "hashchange", h = document, f, g = $.event.special, i = h.documentMode, d = "on" + c in e && (i === b || i > 7);

			function a( j ){
				j = j || location.href;
				return "#" + j.replace( /^[^#]*#?(.*)$/, "$1" )
			}

			$.fn[c] = function( j ){
				return j ? this.bind( c, j ) : this.trigger( c )
			};
			$.fn[c].delay = 50;
			g[c] = $.extend( g[c], {
				setup      : function(){
					if( d ){
						return false
					}
					$( f.start )
				}, teardown: function(){
					if( d ){
						return false
					}
					$( f.stop )
				}
			} );
			f = (function(){
				var j = {}, p, m = a(), k = function( q ){
					return q
				}, l = k, o = k;
				j.start = function(){
					p || n()
				};
				j.stop = function(){
					p && clearTimeout( p );
					p = b
				};
				function n(){
					var r = a(), q = o( m );
					if( r !== m ){
						l( m = r, q );
						$( e ).trigger( c )
					} else{
						if( q !== m ){
							location.href = location.href.replace( /#.*/, "" ) + q
						}
					}
					p = setTimeout( n, $.fn[c].delay )
				}

				$.browser.msie && !d && (function(){
					var q, r;
					j.start = function(){
						if( !q ){
							r = $.fn[c].src;
							r = r && r + a();
							q = $( '<iframe tabindex="-1" title="empty"/>' ).hide().one( "load", function(){
								r || l( a() );
								n()
							} ).attr( "src", r || "javascript:0" ).insertAfter( "body" )[0].contentWindow;
							h.onpropertychange = function(){
								try {
									if( event.propertyName === "title" ){
										q.document.title = h.title
									}
								} catch( s ) {
								}
							}
						}
					};
					j.stop = k;
					o = function(){
						return a( q.location.href )
					};
					l = function( v, s ){
						var u = q.document, t = $.fn[c].domain;
						if( v !== s ){
							u.title = h.title;
							u.open();
							t && u.write( '<script>document.domain="' + t + '"<\/script>' );
							u.close();
							q.location.hash = v
						}
					}
				})();
				return j
			})()
		})( jQuery, this );
		jQuery( document ).ready( function( $ ){
			$( '.fields .form-table' ).find( 'label' ).click( function(){
				$( this ).parent().next().find( ':input' ).trigger( 'focus' );
			} );
			$( '.form-tab' ).find( '> .section:first' ).show().end().hide().first().show();
			$( '.nav-tab-wrapper' ).find( 'a' ).click( function( e ){
				e.preventDefault();
				var id = $( this ).data( 'id' );
				$( '.nav-tab-wrapper' ).find( 'a' ).removeClass( 'nav-tab-active' );
				$( this ).addClass( 'nav-tab-active' );
				$( '.form-tab' ).hide();
				$( '.form-tab[data-id="' + id + '"]' ).show();
			} );

			$( '.tab-sections' ).on( 'click', 'a', function(){
				var
					$this = $(this ),
					$formTab = $this.closest('.form-tab');
				$formTab.find('> .section' ).hide();
				$formTab.find('> .section[data-id="'+$this.data('id')+'"]' ).show();

				return false;
			} );

		} );
	</script>

</div>