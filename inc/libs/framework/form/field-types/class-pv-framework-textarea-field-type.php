<?php
/**
 * @class          PV_Framework_Textarea_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Textarea_Field_Type extends PV_Framework_Form_Field_Type_Base {


	function display() {
		$output = '';
		$output .= PV_Framework_HTML::htmlTextarea( $this->settings[ 'inputName' ], $this->get_value(), array( 'class' => 'form-control ' . $this->settings[ 'inputClasses' ] ) );

		return $output;
	}

}
