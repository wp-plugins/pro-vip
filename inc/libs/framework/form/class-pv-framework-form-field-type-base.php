<?php
/**
 * @class          PV_Framework_Field_Type_Base
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

/**
 * Class PV_Framework_Form_Field_Type_Base
 */
abstract class PV_Framework_Form_Field_Type_Base {

	public
		$settings = array();


	function __construct( $params ) {
		$this->settings = array_merge(
			array(
				'inputClasses'  => '',
				'label'         => '',
				'hide'          => false,
				'desc'          => '',
				'std_value'     => '',
				'customMethods' => array()
			),
			$this->settings,
			$params
		);
	}

	function label( $label ) {
		$this->settings[ 'label' ] = $label;

		return $this;
	}


	/**
	 * @param null $name
	 * @param null $value
	 *
	 * @return PV_Framework_Form_Field_Type_Base|mixed
	 */
	public function option( $name = null, $value = null ) {
		if ( $value === null && is_string( $name ) ) {
			return isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : null;
		}
		$this->settings[ $name ] = $value;

		return $this;
	}

	function __call( $method, $params ) {
		if ( isset( $this->settings[ 'customMethods' ] ) && is_callable( $this->settings[ 'customMethods' ] ) ) {
			call_user_func_array( $this->settings[ 'customMethods' ], $params );
		} else {
			throw new Exception;
		}
	}

	/**
	 * @param $desc
	 *
	 * @return $this
	 */
	function desc( $desc ) {
		$this->settings[ 'desc' ] = $desc;

		return $this;
	}

	public function hide() {
		$this->settings[ 'hide' ] = true;

		return $this;
	}

	function value( $val ) {
		$this->settings[ 'value' ] = $val;

		return $this;
	}

	function inputClasses( $classes ) {

		$classes = is_array( $classes ) ? $classes : explode( ' ', $classes );

		$this->settings[ 'inputClasses' ] .= implode( ' ', $classes ) . ' ';

		return $this;
	}

	function get_value() {
		if ( isset( $this->settings[ 'valueHandler' ] ) && is_callable( $this->settings[ 'valueHandler' ] ) ) {
			$call = call_user_func( $this->settings[ 'valueHandler' ], $this->settings[ 'id' ] );

			return $call === null ? $this->settings[ 'std_value' ] : $call;
		}

		return isset( $this->settings[ 'value' ] ) && $this->settings[ 'value' ] !== null ? $this->settings[ 'value' ] : $this->settings[ 'std_value' ];
	}


	/**
	 * @param $val
	 *
	 * @return $this
	 */
	function std_val( $val ) {
		$this->settings[ 'std_value' ] = $val;

		return $this;
	}


	function wrap( $field ) {
		$type = get_class( $this );
		$type = ltrim( rtrim( $type, '_Field_Type' ), 'PV_Framework_' );
		$type = strtolower( $type );

		$containerClasses    = array( 'pv-form-group' );
		$containerClasses[ ] = $type;
		if ( ! empty( $this->settings[ 'id' ] ) && is_string( $this->settings[ 'id' ] ) ) {
			$containerClasses[ ] = $this->settings[ 'id' ];
		}
		$customClass = $this->option( 'containerClass' );
		if ( ! empty( $customClass ) && is_string( $customClass ) ) {
			$containerClasses[ ] = $customClass;
		}

		return '<div class="' . implode( ' ', $containerClasses ) . '"> ' . $field . '  </div>';
	}

	function render() {
		$field = $this->display();

		if ( ! empty( $this->settings[ 'hide' ] ) && $this->settings[ 'hide' ] ) {
			return '';
		}

		if ( $this->settings[ 'callback' ] !== null ) {
			return call_user_func( $this->settings[ 'callback' ], $this );
		} else if ( isset( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ] ) && is_callable( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ] ) ) {
			return call_user_func( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ], $this );
		} else {
			$output = '';
			if ( ! empty( $this->settings[ 'label' ] ) ) {
				$output .= sprintf( '<label>%s</label>', $this->settings[ 'label' ] );
			}
			$output .= $field;
			if ( ! empty( $this->settings[ 'desc' ] ) ) {
				$output .= '<p class="description">' . $this->settings[ 'desc' ] . '</p>';
			}

			return $this->wrap( $output );
		}
	}

	function __toString() {
		$return = $this->render();



		return (string) $return;
	}

	/**
	 * @return string
	 */
	abstract function display();

}
