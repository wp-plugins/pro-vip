<?php
/**
 * @class          PV_Framework_HTML
 * @version        1
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_HTML {


	public static $htmlVoidElements = array(
		'area'    => 1,
		'base'    => 1,
		'br'      => 1,
		'col'     => 1,
		'command' => 1,
		'embed'   => 1,
		'hr'      => 1,
		'img'     => 1,
		'input'   => 1,
		'keygen'  => 1,
		'link'    => 1,
		'meta'    => 1,
		'param'   => 1,
		'source'  => 1,
		'track'   => 1,
		'wbr'     => 1,
	);

	public static $htmlAttributeOrder = array(
		'type',
		'id',
		'class',
		'name',
		'value',
		'href',
		'src',
		'action',
		'method',
		'selected',
		'checked',
		'readonly',
		'disabled',
		'multiple',
		'size',
		'maxlength',
		'width',
		'height',
		'rows',
		'cols',
		'alt',
		'title',
		'rel',
		'media',
	);


	public static function htmlEncode( $content, $double_encode = true ) {
		defined( 'ENT_SUBSTITUTE' ) or define( 'ENT_SUBSTITUTE', 8 );

		return htmlspecialchars( $content, ENT_QUOTES | ENT_SUBSTITUTE, get_option( 'blog_charset', 'utf-8' ), $double_encode );
	}

	/**
	 * Generates a label tag.
	 *
	 * @param string $content label text. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 *                        such as an image tag. If this is is coming from end users, you should [[encode()]]
	 *                        it to prevent XSS attacks.
	 * @param string $for     the ID of the HTML element that this label is associated with.
	 *                        If this is null, the "for" attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated label tag
	 */
	public static function label( $content, $for = null, $options = array() ) {
		$options[ 'for' ] = $for;

		return self::htmlTag( 'label', $content, $options );
	}

	/**
	 * Generates a checkbox input.
	 *
	 * @param string  $name    the name attribute.
	 * @param boolean $checked whether the checkbox should be checked.
	 * @param array   $options the tag options in terms of name-value pairs. The following options are specially
	 *                         handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute
	 *   is present, a hidden input will be generated so that if the checkbox is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent
	 *   XSS attacks. When this option is specified, the checkbox will be enclosed by a label tag.
	 * - label_options: array, the HTML attributes for the label tag. This is only used when the "label" option is
	 * specified.
	 * - container: array|boolean, the HTML attributes for the container tag. This is only used when the "label" option
	 * is specified. If it is false, no container will be rendered. If it is an array or not, a "div" container will be
	 * rendered around the the radio button.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting checkbox tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated checkbox tag
	 */
	public static function htmlCheckbox( $name, $checked = false, $options = array() ) {
		$options[ 'checked' ] = (boolean) $checked;
		$value                = array_key_exists( 'value', $options ) ? $options[ 'value' ] : '1';
		if ( isset( $options[ 'uncheck' ] ) ) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = self::htmlHiddenInput( $name, $options[ 'uncheck' ] );
			unset( $options[ 'uncheck' ] );
		} else {
			$hidden = '';
		}
		if ( isset( $options[ 'label' ] ) ) {
			$label         = $options[ 'label' ];
			$label_options = isset( $options[ 'label_options' ] ) ? $options[ 'label_options' ] : array();
			$container     = isset( $options[ 'container' ] ) ? $options[ 'container' ] : array( 'class' => 'checkbox' );
			unset( $options[ 'label' ], $options[ 'label_options' ], $options[ 'container' ] );
			$content = self::label( self::htmlInput( 'checkbox', $name, $value, $options ) . ' ' . $label, null, $label_options );
			if ( is_array( $container ) ) {
				return $hidden . self::htmlTag( 'div', $content, $container );
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . self::htmlInput( 'checkbox', $name, $value, $options );
		}
	}

	/**
	 * Generates a hidden input field.
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	public static function htmlHiddenInput( $name, $value = null, $options = array() ) {
		return self::htmlInput( 'hidden', $name, $value, $options );
	}

	/**
	 * Generates an input type of the given type.
	 *
	 * @param string $type    the type attribute.
	 * @param string $name    the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated input tag
	 */
	public static function htmlInput( $type, $name = null, $value = null, $options = array() ) {
		$options[ 'type' ]  = $type;
		$options[ 'name' ]  = $name;
		$options[ 'value' ] = $value === null ? null : (string) $value;

		return self::htmlTag( 'input', '', $options );
	}

	/**
	 * Generates a complete HTML tag.
	 *
	 * @param string $name    the tag name
	 * @param string $content the content to be enclosed between the start and end tags. It will not be HTML-encoded.
	 *                        If this is coming from end users, you should consider [[encode()]] it to prevent XSS
	 *                        attacks.
	 * @param array  $options the HTML tag attributes (HTML options) in terms of name-value pairs.
	 *                        These will be rendered as the attributes of the resulting tag. The values will be
	 *                        HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not
	 *                        be rendered.
	 *
	 * For example when using `['class' => 'my-class', 'target' => '_blank', 'value' => null]` it will result in the
	 * html attributes rendered like this: `class="my-class" target="_blank"`.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated HTML tag
	 * @see htmlBeginTag()
	 * @see htmlEndTag()
	 */
	public static function htmlTag( $name, $content = '', $options = array() ) {
		$html = "<$name" . self::htmlRenderTagAttributes( $options ) . '>';

		return isset( self::$htmlVoidElements[ strtolower( $name ) ] ) ? $html : "$html$content</$name>";
	}

	/**
	 * Renders the HTML tag attributes.
	 *
	 * Attributes whose values are of boolean type will be treated as
	 * [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
	 *
	 * Attributes whose values are null will not be rendered.
	 *
	 * The values of attributes will be HTML-encoded using [[encode()]].
	 *
	 * The "data" attribute is specially handled when it is receiving an array value. In this case,
	 * the array will be "expanded" and a list data attributes will be rendered. For example,
	 *
	 * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
	 *
	 * @return string the rendering result. If the attributes are not empty, they will be rendered
	 * into a string with a leading white space (so that it can be directly appended to the tag name
	 * in a tag. If there is no attribute, an empty string will be returned.
	 */
	public static function htmlRenderTagAttributes( $attributes ) {
		if ( count( $attributes ) > 1 ) {
			$sorted = array();
			foreach ( self::$htmlAttributeOrder as $name ) {
				if ( isset( $attributes[ $name ] ) ) {
					$sorted[ $name ] = $attributes[ $name ];
				}
			}
			$attributes = array_merge( $sorted, $attributes );
		}

		$html = '';
		foreach ( $attributes as $name => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$html .= " $name";
				}
			} elseif ( is_array( $value ) && $name === 'data' ) {
				foreach ( $value as $n => $v ) {
					if ( is_array( $v ) ) {
						$html .= " $name-$n='" . json_encode( $v, JSON_HEX_APOS ) . "'";
					} else {
						$html .= " $name-$n=\"" . self::htmlEncode( $v ) . '"';
					}
				}
			} elseif ( $value !== null ) {
				$html .= " $name=\"" . self::htmlEncode( $value ) . '"';
			}
		}

		return $html;
	}

	/**
	 * Generates a text area input.
	 *
	 * @param string $name    the input name
	 * @param string $value   the input value. Note that it will be encoded using [[encode()]].
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated text area tag
	 */
	public static function htmlTextarea( $name, $value = '', $options = array() ) {
		$options[ 'name' ] = $name;

		return self::htmlTag( 'textarea', self::htmlEncode( $value ), $options );
	}

	/**
	 * Generates a text input field.
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	public static function htmlTextInput( $name, $value = null, $options = array() ) {
		return self::htmlInput( 'text', $name, $value, $options );
	}

	/**
	 * Renders the option tags that can be used by [[dropDownList()]] and [[listBox()]].
	 *
	 * @param string|array $selection  the selected value(s). This can be either a string for single selection
	 *                                 or an array for multiple selections.
	 * @param array        $items      the option data items. The array keys are option values, and the array values
	 *                                 are the corresponding option labels. The array can also be nested (i.e. some
	 *                                 array values are arrays too). For each sub-array, an option group will be
	 *                                 generated whose label is the key associated with the sub-array. If you have a
	 *                                 list of data models, you may convert them into the format described above using
	 *                                 [[self::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array        $tagOptions the $options parameter that is passed to the [[dropDownList()]] or [[listBox()]]
	 *                                 call. This method will take out these elements, if any: "prompt", "options" and
	 *                                 "groups". See more details in [[dropDownList()]] for the explanation of these
	 *                                 elements.
	 *
	 * @return string the generated list options
	 */
	public static function htmlRenderSelectOptions( $selection, $items, &$tagOptions = array() ) {
		$lines        = array();
		$encodeSpaces = self::_arrayRemove( $tagOptions, 'encodeSpaces', false );
		if ( isset( $tagOptions[ 'prompt' ] ) ) {
			$prompt   = $encodeSpaces ? str_replace( ' ', '&nbsp;', self::htmlEncode( $tagOptions[ 'prompt' ] ) ) : self::htmlEncode( $tagOptions[ 'prompt' ] );
			$lines[ ] = self::htmlTag( 'option', $prompt, array( 'value' => '' ) );
		}

		$options = isset( $tagOptions[ 'options' ] ) ? $tagOptions[ 'options' ] : array();
		$groups  = isset( $tagOptions[ 'groups' ] ) ? $tagOptions[ 'groups' ] : array();
		unset( $tagOptions[ 'prompt' ], $tagOptions[ 'options' ], $tagOptions[ 'groups' ] );
		$options[ 'encodeSpaces' ] = self::_arrayGetValue( $options, 'encodeSpaces', $encodeSpaces );

		foreach ( $items as $key => $value ) {
			if ( is_array( $value ) ) {
				$groupAttrs            = isset( $groups[ $key ] ) ? $groups[ $key ] : array();
				$groupAttrs[ 'label' ] = $key;
				$attrs                 = array( 'options' => $options, 'groups' => $groups );
				$content               = self::htmlRenderSelectOptions( $selection, $value, $attrs );
				$lines[ ]              = self::htmlTag( 'optgroup', "\n" . $content . "\n", $groupAttrs );
			} else {
				$attrs               = isset( $options[ $key ] ) ? $options[ $key ] : array();
				$attrs[ 'value' ]    = (string) $key;
				$attrs[ 'selected' ] = $selection !== null &&
				                       ( ! is_array( $selection ) && ! strcmp( $key, $selection )
				                         || is_array( $selection ) && in_array( $key, $selection ) );
				$lines[ ]            = self::htmlTag( 'option', ( $encodeSpaces ? str_replace( ' ', '&nbsp;', self::htmlEncode( $value ) ) : self::htmlEncode( $value ) ), $attrs );
			}
		}

		return implode( "\n", $lines );
	}


	function htmlListBox( $name, $selection = null, $items = array(), $options = array() ) {
		if ( ! array_key_exists( 'size', $options ) ) {
			$options[ 'size' ] = 4;
		}
		if ( ! empty( $options[ 'multiple' ] ) && substr( $name, - 2 ) !== '[]' ) {
			$name .= '[]';
		}
		$options[ 'name' ] = $name;
		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			if ( substr( $name, - 2 ) === '[]' ) {
				$name = substr( $name, 0, - 2 );
			}
			$hidden = self::htmlHiddenInput( $name, $options[ 'unselect' ] );
			unset( $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}
		$selectOptions = self::htmlRenderSelectOptions( $selection, $items, $options );

		return $hidden . self::htmlTag( 'select', "\n" . $selectOptions . "\n", $options );
	}


	public static function htmlDropDownList( $name, $selection = null, $items = array(), $options = array() ) {
		if ( ! empty( $options[ 'multiple' ] ) ) {
			return self::htmlListBox( $name, $selection, $items, $options );
		}
		$options[ 'name' ] = $name;
		unset( $options[ 'unselect' ] );
		$selectOptions = self::htmlRenderSelectOptions( $selection, $items, $options );

		return self::htmlTag( 'select', "\n" . $selectOptions . "\n", $options );
	}


	static function _arrayRemove( &$array, $key, $default = null ) {
		if ( is_array( $array ) && ( isset( $array[ $key ] ) || array_key_exists( $key, $array ) ) ) {
			$value = $array[ $key ];
			unset( $array[ $key ] );

			return $value;
		}

		return $default;
	}

	static function _arrayGetValue( $array, $key, $default = null ) {
		if ( $key instanceof Closure ) {
			return $key( $array, $default );
		}


		if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}

		if ( ( $pos = strrpos( $key, '.' ) ) !== false ) {
			$array = self::_arrayGetValue( $array, substr( $key, 0, $pos ), $default );
			$key   = substr( $key, $pos + 1 );
		}

		if ( is_object( $array ) ) {
			return $array->$key;
		} elseif ( is_array( $array ) ) {
			return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
		} else {
			return $default;
		}
	}

	public static function radio( $name, $checked = false, $options = array() ) {
		$options[ 'checked' ] = (boolean) $checked;
		$value                = array_key_exists( 'value', $options ) ? $options[ 'value' ] : '1';
		if ( isset( $options[ 'uncheck' ] ) ) {
			// add a hidden field so that if the radio button is not selected, it still submits a value
			$hidden = self::htmlHiddenInput( $name, $options[ 'uncheck' ] );
			unset( $options[ 'uncheck' ] );
		} else {
			$hidden = '';
		}
		if ( isset( $options[ 'label' ] ) ) {
			$label         = $options[ 'label' ];
			$label_options = isset( $options[ 'label_options' ] ) ? $options[ 'label_options' ] : array();
			$container     = isset( $options[ 'container' ] ) ? $options[ 'container' ] : array( 'class' => 'radio' );
			unset( $options[ 'label' ], $options[ 'label_options' ], $options[ 'container' ] );
			$content = self::label( self::htmlInput( 'radio', $name, $value, $options ) . ' ' . $label, null, $label_options );
			if ( is_array( $container ) ) {
				return $hidden . self::htmlTag( 'div', $content, $container );
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . self::htmlInput( 'radio', $name, $value, $options );
		}
	}


	public static function htmlRadioList( $name, $selection = null, $items = array(), $options = array() ) {
		$encode      = ! isset( $options[ 'encode' ] ) || $options[ 'encode' ];
		$formatter   = isset( $options[ 'item' ] ) ? $options[ 'item' ] : null;
		$itemOptions = isset( $options[ 'itemOptions' ] ) ? $options[ 'itemOptions' ] : array();
		$lines       = array();
		$index       = 0;
		foreach ( $items as $value => $label ) {
			$checked = $selection !== null &&
			           ( ! is_array( $selection ) && ! strcmp( $value, $selection )
			             || is_array( $selection ) && in_array( $value, $selection ) );
			if ( $formatter !== null ) {
				$lines[ ] = call_user_func( $formatter, $index, $label, $name, $checked, $value );
			} else {
				$lines[ ] = self::radio( $name, $checked, array_merge( $itemOptions, array(
					'value' => $value,
					'label' => $encode ? self::htmlEncode( $label ) : $label,
				) ) );
			}
			$index ++;
		}

		$separator = isset( $options[ 'separator' ] ) ? $options[ 'separator' ] : "\n";
		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$hidden = self::htmlHiddenInput( $name, $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}

		$tag = isset( $options[ 'tag' ] ) ? $options[ 'tag' ] : 'div';
		unset( $options[ 'tag' ], $options[ 'unselect' ], $options[ 'encode' ], $options[ 'separator' ], $options[ 'item' ], $options[ 'itemOptions' ] );

		return $hidden . self::htmlTag( $tag, implode( $separator, $lines ), $options );
	}

	public static function htmlCheckboxList( $name, $selection = null, $items = array(), $options = array() ) {
		if ( substr( $name, - 2 ) !== '[]' ) {
			$name .= '[]';
		}

		$formatter   = isset( $options[ 'item' ] ) ? $options[ 'item' ] : null;
		$itemOptions = isset( $options[ 'itemOptions' ] ) ? $options[ 'itemOptions' ] : array();
		$encode      = ! isset( $options[ 'encode' ] ) || $options[ 'encode' ];
		$lines       = array();
		$index       = 0;
		foreach ( $items as $value => $label ) {
			$checked = $selection !== null &&
			           ( ! is_array( $selection ) && ! strcmp( $value, $selection )
			             || is_array( $selection ) && in_array( $value, $selection ) );
			if ( $formatter !== null ) {
				$lines[ ] = call_user_func( $formatter, $index, $label, $name, $checked, $value );
			} else {
				$lines[ ] = self::htmlCheckbox( $name, $checked, array_merge( $itemOptions, array(
					'value' => $value,
					'label' => $encode ? self::htmlEncode( $label ) : $label,
				) ) );
			}
			$index ++;
		}

		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$name2  = substr( $name, - 2 ) === '[]' ? substr( $name, 0, - 2 ) : $name;
			$hidden = self::htmlHiddenInput( $name2, $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}
		$separator = isset( $options[ 'separator' ] ) ? $options[ 'separator' ] : "\n";

		$tag = isset( $options[ 'tag' ] ) ? $options[ 'tag' ] : 'div';
		unset( $options[ 'tag' ], $options[ 'unselect' ], $options[ 'encode' ], $options[ 'separator' ], $options[ 'item' ], $options[ 'itemOptions' ] );

		return $hidden . self::htmlTag( $tag, implode( $separator, $lines ), $options );
	}

}