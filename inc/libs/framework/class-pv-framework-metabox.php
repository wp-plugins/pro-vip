<?php
/**
 * @class          PV_Framework_Metabox
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Metabox {

	/**
	 * @var PV_Framework_Form_Builder
	 */
	var $form_builder;

	private $_id, $_metabox_content, $_callback;

	public
		$title = '',
		$prefix = '',
		$post_types = array( 'post', 'page' ),
		$save_in_single = true;

	/**
	 * @param          $id
	 * @param callable $callback
	 *
	 * @return PV_Framework_Metabox
	 * @throws Exception
	 */
	static public function make( $id, $callback ) {

		global $pagenow;

		if ( ! is_callable( $callback ) ) {
			throw new Exception( sprintf( 'Uncallable callback for %s', __METHOD__ ) );
		}

		return new self( $id, $callback );
	}


	protected function __construct( $id, $callback ) {
		$this->_id          = $id;
		$this->form_builder = new PV_Framework_Form_Builder(
			array(
				'field_name_generator' => array( $this, 'field_name_generator' )
			)
		);
		$this->_callback    = $callback;
		$this->title        = $id;
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	function field_name_generator( $field_id ) {
		return sprintf( '_pv_metabox_%s[%s]', $this->_id, $field_id );
	}

	function add() {


		call_user_func( $this->_callback, $this );

		foreach ( (array) $this->post_types as $post_type ) {
			add_meta_box(
				$this->_id,
				$this->title,
				array( $this, 'callback' ),
				$post_type
			);
		}
	}

	function callback() {
		$vals = $this->get_values();
		$this->form_builder->setValues( $vals );
		$this->_metabox_content = $this->form_builder->getFieldsHTML();
		printf(
			'<div class="%s">%s</div>',
			'pv-metabox-inner',
			$this->_metabox_content
		);
	}

	function get_values() {
		global $post;
		if ( $this->save_in_single ) {
			$items = get_post_meta( $post->ID, $this->prefix . $this->_id, true );

			return $items;
		} else {
			$fields = $this->form_builder->getFieldsList();
			$output = array();
			foreach ( $fields as $fieldId => $fieldObject ) {
				$output[ $fieldId ] = get_post_meta( $post->ID, $this->prefix . $fieldId, true );
			}

			return $output;
		}
	}

	function save( $post_id ) {


		if ( ! empty( $_POST[ '_pv_metabox_' . $this->_id ] ) && is_array( $_POST[ '_pv_metabox_' . $this->_id ] ) ) {

			call_user_func( $this->_callback, $this );

			$data = $_POST[ '_pv_metabox_' . $this->_id ];

			do_action( 'pv_framework_metabox_save', $this->_id, $data );
			do_action( 'pv_framework_metabox_save-' . $this->_id, $data );

			/**
			 * @var $field PV_Framework_Form_Field_Type_Base
			 */

			if ( $this->save_in_single ) {

				$toSave = array();
				foreach ( $this->form_builder->getFieldsList() as $fieldId => $field ) {
					if ( empty( $data[ $fieldId ] ) && empty( $field->defaultFormValue ) ) {
						continue;
					}
					$toSave[ $fieldId ] = empty( $data[ $fieldId ] ) ? $field->defaultFormValue : $data[ $fieldId ];
				}

				update_post_meta( $post_id, $this->_id, $toSave );

			} else {

				foreach ( $this->form_builder->getFieldsList() as $fieldId => $field ) {

					if ( ! isset( $data[ $fieldId ] ) && ! isset( $field->defaultFormValue ) ) {
						continue;
					}

					$val = ! isset( $data[ $fieldId ] ) ? $field->defaultFormValue : $data[ $fieldId ];

					update_post_meta( $post_id, $this->prefix . $fieldId, $val );

				}

			}


		}
	}

}
