<?php
/**
 * @class          PV_Framework_Panel
 * @version        0.1
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access


class PV_Framework_Panel {

	private
		$_callback,
		$_id,
		$_fields;


	public
		$title = '',
		$hidden_inputs,
		$menu,
		$option_name,
		$form_builder;

	function __construct( $id, $callback ) {
		$this->_id          = $id;
		$this->form_builder = new PV_Framework_Form_Builder(
			array(
				'field_name_generator' => array( $this, 'field_name_generator' )
			)
		);

		$this->_callback = $callback;

		$this->menu = PV_Framework_Admin_Menu::make();

		$this->menu->callback( array( $this, 'display' ) );
		$this->menu->onLoad( array( $this, 'loadAssets' ) );

	}

	function field_name_generator( $field_id ) {
		return sprintf( '_pv_panel_%s[%s]', $this->_id, $field_id );
	}

	public static function loadAssets() {
	}

	/**
	 *
	 */
	function display() {


		$this->hidden_inputs = PV_Framework_Action::hidden_inputs( '_pv_panel_actions' );
		$this->hidden_inputs .= sprintf( '<input type="hidden" name="%s" value="%s"/>', '_panel_id', $this->_id );

		$this->form_builder->setValues( get_option( $this->get_option_name(), array() ) );


		call_user_func( $this->_callback, $this );
		$this->_fields = $this->form_builder->getFieldsHTML();


		Pro_VIP::loadView(
			'admin/panel/panel-template',
			array(
				'id'            => $this->_id,
				'fields'        => $this->_fields,
				'title'         => $this->title,
				'notice'        => PV_Framework_Request::get_post( 'notice' ),
				'hidden_fields' => $this->hidden_inputs,
				'tabs_list'     => $this->form_builder->get_tabs_array(),
				'reset_url'     => add_query_arg( array( '_panel_id' => $this->_id ), PV_Framework_Action::action_url( '_pv_panel_reset', true ) )
			)
		);

	}

	function get_option_name() {
		return $this->_id;
	}


}
