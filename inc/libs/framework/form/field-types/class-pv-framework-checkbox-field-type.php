<?php
/**
 * @class          PV_Framework_Checkbox_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Checkbox_Field_Type extends PV_Framework_Form_Field_Type_Base {

	public $defaultFormValue = '0';

	var $checkbox_label = '';

	function checkbox_label( $label ) {
		$this->checkbox_label = $label;

		return $this;
	}


	function display() {
		$output = '';
		$checkbox = PV_Framework_HTML::htmlCheckbox(
			$this->settings[ 'inputName' ],
			$this->get_value(),
			array(
				'class' => 'form-control ' . $this->settings[ 'inputClasses' ]
			)
		);
		if ( ! empty( $this->checkbox_label ) ) {
			$output .= htmlTag( 'label', $checkbox . ' ' . $this->checkbox_label );
		} else {
			$output .= $checkbox;
		}

		return $output;
	}

}
