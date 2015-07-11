<?php
/**
 * @class          PV_Framework_Media_Field_Type
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Media_Field_Type extends PV_Framework_Form_Field_Type_Base {

	function __construct( $params ) {
		$this->settings = array_merge(
			array(
				'inputClasses'      => '',
				'label'             => '',
				'desc'              => '',
				'std_value'         => '',
				'buttonLabel'       => __( 'Choose Files', 'provip' ),
				'frameButtonLabel'  => __( 'Choose File', 'provip' ),
				'frameTitle'        => __( 'Choose File', 'provip' ),
				'allowedExtensions' => '*',
				'multiple'          => false,
				'customMethods'     => array()
			),
			$this->settings,
			$params
		);
	}

	public function buttonLabel( $label ) {
		$this->settings[ 'buttonLabel' ] = (string) $label;

		return $this;
	}

	public function frameButtonLabel( $label ) {
		$this->settings[ 'frameButtonLabel' ] = (string) $label;

		return $this;
	}

	public function frameTitle( $title ) {
		$this->settings[ 'frameTitle' ] = $title;

		return $this;
	}

	public function multiple( $enable = true ) {
		$this->settings[ 'multiple' ] = (bool) $enable;

		return $this;
	}

	function display() {
		$output = '';

		$mediaSettings = array(
			'multiple' => $this->settings[ 'multiple' ],
			'title'    => $this->settings[ 'frameTitle' ],
			'button'   => array(
				'text' => $this->settings[ 'frameButtonLabel' ]
			)
		);

		$output .= '<div class="phoenix-media-field" data-frame-settings=\'' . json_encode( $mediaSettings ) . '\'>';

		$output .= '<script type="text/html">' . $this->generateItem( '' ) . '</script>';

		$output .= '<button class="button large" onclick="event.preventDefault();">' . $this->settings[ 'buttonLabel' ] . '</button>';

		$output .= '<div class="links sortable">';
		foreach ( (array) $this->get_value() as $key => $val ) {
			if ( empty( $val ) || ! is_string( $val ) ) {
				continue;
			}
			$output .= $this->generateItem( $val );
		}
		$output .= '</div><br class="clear"/>';


		$output .= '</div>';

		return $output;
	}

	public function allowedExtension( $ex ) {
		$this->settings[ 'allowedExtensions' ] = $ex;

		return $this;
	}

	protected function generateItem( $value ) {

		$classes  = array( 'item' );
		$type     = wp_ext2type( pathinfo( $value, PATHINFO_EXTENSION ) );
		$showText = true;
		switch ( $type ) {
			case 'image':
				$bg         = $value;
				$showText   = false;
				$classes[ ] = 'image';
				break;
			default:
			case null:
				$bg = includes_url() . 'images/media/document.png';
				break;
			case 'audio':
			case 'video':
			case 'document':
			case 'spreadsheet':
			case 'interactive':
			case 'text':
			case 'archive':
			case 'code':
				$bg = includes_url() . 'images/media/' . $type . '.png';
				break;
		}

		return '<div class="' . implode( ' ', $classes ) . '" style="background-image: url(' . $bg . ')">
<input type="hidden" name="' . $this->settings[ 'inputName' ] . '[]" value="' . esc_attr( $value ) . '">
	' . ( $showText ? '<p class="link">' . pathinfo( $value, PATHINFO_BASENAME ) . '</p>' : '' ) . '
	<span class="remove">x</span>
</div>';
	}

}
