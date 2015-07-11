<?php
/**
 * @class          PV_Framework_Tab_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Tab_Field_Type extends PV_Framework_Form_Field_Type_Base {


	function __construct( $params ) {
		parent::__construct( $params );
		$this->settings[ 'callback' ] = array( __CLASS__, 'fieldWrapper' );
	}


	public static function fieldWrapper( PV_Framework_Form_Field_Type_Base $field ) {
		return $field->display();
	}

	function display() {

		switch ( $this->settings[ 'extra' ][ '_tab_type' ] ) {
			case 'open':
			default:
			return '<div class="form-tab" data-id="' . $this->settings[ 'extra' ][ '_tab_id' ] . '">';
				break;

			case 'close':
				$output = '';

				if ( ! empty( $this->settings[ 'extra' ][ '_current_tab' ] ) && ! empty( $this->settings[ 'extra' ]['sections'][ $this->settings[ 'extra' ][ '_current_tab' ] ] ) ) {
					$output .= '<div class="tab-sections"><ul>';
					foreach ( $this->settings[ 'extra' ][ 'sections' ][ $this->settings[ 'extra' ][ '_current_tab' ] ] as $section ) {
						$output .= '<li><a href="#" data-id="' . $section[ 'id' ] . '">' . $section[ 'name' ] . '</a></li>';
					}
					$output .= '</ul></div>';
				}

				$output .= '</div>';

				return $output;

				break;
		}

	}

}
