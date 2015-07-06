<?php
/**
 * @class          PV_Framework_Multicheckbox_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Multicheckbox_Field_Type extends PV_Framework_Form_Field_Type_Base {

	var $options = array();

	function __construct( $params ) {
		parent::__construct( $params );
		$this->options = $params[ 'options' ];
	}

	function display() {

		$output = '';
		$output .= PV_Framework_HTML::htmlCheckboxList( $this->settings[ 'inputName' ], $this->get_value(), $this->options, array( 'class' => 'form-control ' . $this->settings[ 'inputClasses' ] ) );

		return $output;
	}

}
