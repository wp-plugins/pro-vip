<?php
/**
 * @class          PV_Framework_Text_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Text_Field_Type extends PV_Framework_Form_Field_Type_Base {

	public function placeholder( $placeholder ) {
		$this->settings[ 'placeholder' ] = $placeholder;

		return $this;
	}

	function display() {
		$output = '';
		$output .= PV_Framework_HTML::htmlTextInput(
			$this->settings[ 'inputName' ],
			$this->get_value(),
			array(
				'placeholder' => empty( $this->settings[ 'placeholder' ] ) ? '' : $this->settings[ 'placeholder' ],
				'id'          => $this->settings[ 'inputName' ],
				'class'       => 'form-control ' . $this->settings[ 'inputClasses' ]
			)
		);

		return $output;
	}

}
