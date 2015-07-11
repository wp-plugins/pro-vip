<?php
/**
 * @class          PV_Framework_HTML_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_HTML_Field_Type extends PV_Framework_Form_Field_Type_Base {

	public $html;

	function __construct( $params ) {
		parent::__construct( $params );
		$this->settings[ 'callback' ] = array( __CLASS__, 'fieldWrapper' );
	}

	public static function fieldWrapper( PV_Framework_Form_Field_Type_Base $field ) {
		return $field->display();
	}

	function display() {
		$html = '';
		$arg  = $this->settings[ 'extra' ]['_htmlArg'];
		if ( is_callable( $arg ) ) {
			ob_start();
			$call   = call_user_func( $arg );
			$buffer = ob_get_clean();
			if ( ! empty( $call ) ) {
				$html = $call;
			} else {
				$html = $buffer;
			}
		} else if ( is_string( $arg ) ) {
			$html = $arg;
		} else {
			throw new InvalidArgumentException;
		}

		return $html;
	}

}
