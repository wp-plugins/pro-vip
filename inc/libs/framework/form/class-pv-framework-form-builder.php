<?php
/**
 * @class          PV_Framework_Form_Builder
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access


class PV_Framework_Form_Builder {

	private
		$currentTab,
		$sections = array(),
		$settings,
		$_tabs = array(),
		$_fields = array();

	private $_fieldTypes = array(
		'tab'           => 'PV_Framework_Tab_Field_Type',
		'html'          => 'PV_Framework_HTML_Field_Type',
		'text'          => 'PV_Framework_Text_Field_Type',
		'textarea'      => 'PV_Framework_Textarea_Field_Type',
		'select'        => 'PV_Framework_Select_Field_Type',
		'radio'         => 'PV_Framework_Radio_Field_Type',
		'checkbox'      => 'PV_Framework_Checkbox_Field_Type',
		'multicheckbox' => 'PV_Framework_Multicheckbox_Field_Type',
		'wp_editor'     => 'PV_Framework_WP_Editor_Field_Type',
		'image'         => 'PV_Framework_Multiselect_Field_Type',
		'media'         => 'PV_Framework_Media_Field_Type',
		'query_builder' => 'PV_Framework_Query_Builder_Field_Type'
	);

	public function __construct( $config = array() ) {

		$this->settings = array_merge(
			array(
				'field_name_generator' => false,
				'values'               => array()
			),
			(array) $config
		);
	}

	public function field_name( $field_id ) {
		if ( is_callable( $this->settings[ 'field_name_generator' ] ) ) {
			return call_user_func( $this->settings[ 'field_name_generator' ], $field_id );
		}

		return $field_id;
	}

	public function setValues( $values ) {
		$this->settings[ 'values' ] = $values;
	}

	public function get_tabs_array() {
		return $this->_tabs;
	}

	public function valueHandler( $field_name ) {
		return isset( $this->settings[ 'values' ][ $field_name ] ) ? $this->settings[ 'values' ][ $field_name ] : null;
	}

	public function getAllFields() {
		return $this->_fields;
	}

	/**
	 * @param $fieldId
	 *
	 * @return bool|PV_Framework_Form_Field_Type_Base
	 */
	public function getField( $fieldId ) {
		/**
		 * @var $field PV_Framework_Form_Field_Type_Base
		 */
		foreach ( $this->getFieldsList() as $id => $field ) {
			if ( $id === $fieldId ) {
				return $field;
			}
		}

		return false;
	}

	public function getFieldsList() {
		$output = array();
		foreach ( $this->getAllFields() as $field ) {
			if ( ! is_object( $field ) || empty( $field->settings[ 'id' ] ) ) {
				continue;
			}
			$output[ $field->settings[ 'id' ] ] = $field;
		}

		return $output;
	}


	public function getFieldsHTML() {

		$fields = $this->getAllFields();

		$output = '';

		/**
		 * @var $field PV_Framework_Form_Field_Type_Base
		 */
		foreach ( $fields as $field ) {


			$output .= $field->render();
		}


		return $output;
	}

	/**
	 * @param $type
	 * @param $args
	 *
	 * @return PV_Framework_Form_Field_Type_Base|bool
	 */
	public function addField( $type, $args ) {
		if ( ! array_key_exists( $type, $this->_fieldTypes ) ) {
			return false;
		}
		$class            = $this->_fieldTypes[ $type ];
		$object           = new $class( $args );
		$this->_fields[ ] = $object;

		return $object;
	}

	public function openTab( $id, $label, $extra = array() ) {
		$this->currentTab   = $id;
		$this->_tabs[ $id ] = $label;

		$extra[ '_tab_type' ]  = 'open';
		$extra[ '_tab_id' ]    = $id;
		$extra[ '_tab_label' ] = $label;

		return $this->addField(
			'tab',
			array(
				'callback'            => null,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra
			)
		);


	}


	public function closeTab() {
//		$this->_fields[ ] = array(
//			'type' => 'close_tab'
//		);

		$extra                   = array();
		$extra[ '_tab_type' ]    = 'close';
		$extra[ '_current_tab' ] = $this->currentTab;
		$extra[ '_sections' ]    = $this->sections;


		return $this->addField(
			'tab',
			array(
				'callback'            => null,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra
			)
		);

	}

	public function openSection( $id, $label ) {
		$this->sections[ $this->currentTab ][ ] = array(
			'name' => $label,
			'id'   => $id
		);

		return '<div class="section" data-id="' . esc_attr( $id ) . '">';
	}

	public function closeSection() {
		return '</div>';
	}

	public function html( $arg, $extra = array() ) {

		$extra[ '_htmlArg' ] = $arg;

		return $this->addField(
			'html',
			array(
				'callback'            => null,
				'htmlArg'             => $arg,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra
			)
		);
	}

	/**
	 * @param       $id
	 * @param array $extra
	 * @param null  $callback
	 *
	 * @return bool|PV_Framework_Text_Field_Type
	 */
	public function textfield( $id, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'text',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}

	public function textarea( $id, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'textarea',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}


	public function wpEditor( $id, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'wp_editor',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}

	/**
	 * @param       $id
	 * @param array $options
	 * @param array $extra
	 * @param null  $callback
	 *
	 * @return bool|PV_Framework_Multiselect_Field_Type
	 */
	public function image( $id, array $options, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'image',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'formBuilderSettings' => $this->settings,
				'options'             => $options,
				'extra'               => $extra,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}

	/**
	 * @param       $id
	 * @param array $extra
	 * @param null  $callback
	 *
	 * @return bool|PV_Framework_Media_Field_Type
	 * @throws Exception
	 */
	public function media( $id, Array $extra = array(), $callback = null ) {
		global $wp_version;
		if ( version_compare( '3.5', $wp_version, '>' ) ) {
			throw new Exception( 'Wordpress 3.5+ is required for media field.' );
		}
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'media',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'formBuilderSettings' => $this->settings,
				'extra'               => $extra,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);


	}

	public function add_query_builder( $id, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'query_builder',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'extra'               => $extra,
				'formBuilderSettings' => $this->settings,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}


	public function checkbox( $id, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'checkbox',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'extra'               => $extra,
				'formBuilderSettings' => $this->settings,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}

	public function multicheckbox( $id, $options, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'multicheckbox',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'extra'               => $extra,
				'options'             => $options,
				'formBuilderSettings' => $this->settings,
				'id'                  => $id,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);

	}

	public function dropdown( $id, $options, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'select',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'extra'               => $extra,
				'formBuilderSettings' => $this->settings,
				'id'                  => $id,
				'options'             => $options,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}

	public function add_radio( $id, $options, Array $extra = array(), $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = null;
		}

		return $this->addField(
			'radio',
			array(
				'inputName'           => $this->field_name( $id ),
				'callback'            => $callback,
				'extra'               => $extra,
				'formBuilderSettings' => $this->settings,
				'id'                  => $id,
				'options'             => $options,
				'valueHandler'        => array( $this, 'valueHandler' )
			)
		);
	}


}
