<?php
/**
 * @class          PV_Framework_Panel_Factory
 * @version        1
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */


defined( 'ABSPATH' ) or die; // Prevents direct access


class PV_Framework_Panel_Factory {

	static protected $_panels = array();


	static function init() {

//		self::handlePanelSave();

		PV_Framework_Action::make(
			'_pv_panel_actions',
			array( __CLASS__, 'handle_panel_actions' ),
			array(
				'admin_only' => true
			)
		);
		add_action( 'admin_enqueue_scripts', function () {
			if ( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
		} );
	}


	/**
	 * @param $id
	 *
	 * @return PV_Framework_Panel|false
	 */
	public static function getPanel( $id ) {
		if ( ! empty( self::$_panels[ $id ] ) ) {
			return self::$_panels[ $id ];
		}

		return false;
	}

	static function handle_panel_actions() {
		if (
			empty( $_POST[ '_panel_id' ] )
			|| ! is_string( $_POST[ '_panel_id' ] )
			|| ! array_key_exists( $_POST[ '_panel_id' ], self::$_panels )
		) {
			return;
		}

		$id = $_POST[ '_panel_id' ];

		/**
		 * @var $panel PV_Framework_Panel
		 */
		$panel = self::getPanel( $id );

		if ( ! $panel ) {
			wp_die( __( 'Cheatin&#8217; uh?', 'provip' ) );
		}

		if ( ! empty( $_POST[ 'panel-save' ] ) ) {


			$option_name = $panel->get_option_name();

			do_action( 'pv_panel_save', $id, $_POST[ '_pv_panel_' . $id ] );
			do_action( sprintf( 'pv_panel_%s_save', $id ), $id, $_POST[ '_pv_panel_' . $id ] );

			if ( ! empty( $_POST[ '_pv_panel_' . $id ] ) && is_array( $_POST[ '_pv_panel_' . $id ] ) ) {
				$data = stripslashes_deep( $_POST[ '_pv_panel_' . $id ] );

				$data = apply_filters( sprintf( 'pv_save_panel_%s_data_array', $id ), $data );

				delete_option( $option_name );
				update_option( $option_name, $data );
			}

			Pro_VIP_Admin::addNotice( __( 'Settings saved.', 'provip' ), 'success' );


		} else if ( ! empty( $_POST[ 'panel-reset' ] ) ) {


			delete_option( $id );

			do_action( 'pv_panel_reset', $id );
			do_action( sprintf( 'pv_panel_%s_reset', $id ) );


			Pro_VIP_Admin::addNotice( __( 'Panel reset.', 'provip' ), 'success' );


		}


	}

	/**
	 * @param $id
	 * @param $callback callback
	 *
	 * @return PV_Framework_Panel
	 * @throws Exception
	 */
	static function make( $id, $callback ) {

		if ( ! is_callable( $callback ) ) {
			throw new Exception( sprintf( 'Uncallable callback for %s', __METHOD__ ) );
		}

		self::$_panels[ $id ] = new PV_Framework_Panel( $id, $callback );

		return self::$_panels[ $id ];
	}


}
