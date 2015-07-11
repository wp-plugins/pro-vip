<?php
/**
 * @class          PV_Framework_Text_Field_Type
 * @version        1.0
 * @package        Phoeix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_WP_Editor_Field_Type extends PV_Framework_Form_Field_Type_Base {

	function display() {
		ob_start();
		wp_editor(
			$this->get_value(),
			$this->settings[ 'id' ],
			array(
				'textarea_name' => $this->settings[ 'inputName' ]
			)
		);

		return ob_get_clean();
	}

}
