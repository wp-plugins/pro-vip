<?php
/**
 * @class          Pro_VIP_Admin_Settings
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Settings {

	public $helper;


	public static function init() {
		return new self;
	}

	protected function __construct() {
		$this->helper[ 'yesNo' ] = array(
			'yes' => __( 'Yes', 'provip' ),
			'no'  => __( 'No', 'provip' )
		);
		$this->helper[ 'noYes' ] = array(
			'no'  => __( 'No', 'provip' ),
			'yes' => __( 'Yes', 'provip' )
		);

		$panel = PV_Framework_Panel_Factory::make(
			'wpVIP',
			array(
				$this,
				'settingsPage'
			)
		);
		$panel->menu
			->pageTitle( __( 'Settings', 'provip' ) )
			->menuTitle( __( 'Settings', 'provip' ) )
			->parentSlug( Pro_VIP_Admin::$menuSlug )
			->menuSlug( 'provip_settings' );
		Pro_VIP::$ajax->on( 'admin.panel.repeater.add', array( $this, 'ajaxRepeaterInsert' ) );
		add_action( sprintf( 'pv_panel_%s_save', 'wpVIP' ), array( $this, 'onPanelSave' ), 10, 2 );
		add_filter( sprintf( 'pv_save_panel_%s_data_array', 'wpVIP' ), array( $this, 'filterSaveData' ) );
	}

	public function settingsPage( PV_Framework_Panel $panel ) {

		$fields = $panel->form_builder;
		$pages  = array();
		/**
		 * @var $page WP_Post
		 */
		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}
		$this->helper[ 'wpPages' ] = $pages;

		$postTypes = array();
		$index     = 0;
		foreach ( get_post_types( array(), 'objects' ) as $id => $postType ) {
			$postTypes[ $id ] = $postType->labels->name;
			$index ++;
		}
		if ( $index > 0 ) {
			unset( $id, $postType );
		}
		$this->helper[ 'postTypes' ] = $postTypes;

		$roles = $GLOBALS[ 'wp_roles' ]->role_names;
		unset( $roles[ 'administrator' ] );
		$this->helper[ 'wpRoles' ] = $roles;

		do_action( 'pro_vip_settings_before', $panel );


		foreach (
			array(
				'generalSettings',
				'vipSettings',
				'pagesSettings',
				'emailSettings',
				'smsSettings',
				'gatewaySettings',
				'apiSettings',
				'guestPurchase',
				'otherSettings'
			) as $tab
		) {

			do_action( 'pro_vip_settings_before_' . $tab );

			call_user_func( array( $this, $tab ), $fields );

			do_action( 'pro_vip_settings_after_' . $tab );

		}


		do_action( 'pro_vip_settings_after', $panel );
	}


	public function plansField( PV_Framework_Multicheckbox_Field_Type $field ) {
		return $field->wrap(
			Pro_VIP::loadView(
				'admin/settings/plans-field',
				array( 'field' => $field ),
				true
			)
		);
	}

	public function vipLevelsPlans( PV_Framework_Multicheckbox_Field_Type $field ) {
		return $field->wrap(
			Pro_VIP::loadView(
				'admin/settings/vip-levels-field',
				array( 'field' => $field ),
				true
			)
		);
	}


	public function gatewaysField( PV_Framework_Multicheckbox_Field_Type $field ) {
		return $field->wrap(
			Pro_VIP::loadView(
				'admin/settings/gateways',
				array( 'field' => $field ),
				true
			)
		);
	}

	public function ajaxRepeaterInsert() {

		if ( empty( $_POST[ 'id' ] ) || ! is_string( $_POST[ 'id' ] ) || ! in_array( $_POST[ 'id' ], array(
				'plans',
				'levels'
			) )
		) {
			return 0;
		}

		$index = get_option( '_provip_' . $_POST[ 'id' ] . '_index', 0 );
		$index ++;

		if ( ! update_option( '_provip_' . $_POST[ 'id' ] . '_index', $index ) ) {
			return 0;
		}

		return array(
			'status'       => 1,
			'currentIndex' => $index
		);
	}

	public function gatewaysSettingsField() {
		$output = '<div class="gateway-settings">';

		/**
		 * @var $gateway Pro_VIP_Payment_Gateway
		 */
		foreach ( Pro_VIP_Payment_Gateway::getAllGateways() as $gateway ) {
			$formBuilder = new PV_Framework_Form_Builder( array(
				'values'               => get_option( 'wv_' . $gateway->id . '_settings', array() ),
				'field_name_generator' => create_function( '$id', 'return "gateway-settings[' . $gateway->id . '][$id]";' )
			) );
			$gateway->adminSettings( $formBuilder );
			$output .= '<div data-id="' . $gateway->id . '"><h2>' . sprintf( _x( '%s Settings', 'Gateway Settings', 'provip' ), $gateway->adminLabel ) . '</h2>' . $formBuilder->getFieldsHTML() . '</div>';
		}
		$output .= '</div>';

		echo $output;
	}

	public function onPanelSave( $panelId, $data ) {
		do_action( 'pro_vip_settings_save', $data );
		foreach ( Pro_VIP_Payment_Gateway::getAllGatewaysList() as $id => $name ) {
			if ( ! empty( $_POST[ 'gateway-settings' ][ $id ] ) && is_array( $_POST[ 'gateway-settings' ][ $id ] ) ) {
				update_option( 'wv_' . $id . '_settings', stripslashes_deep( $_POST[ 'gateway-settings' ][ $id ] ) );
			}
		}
	}

	public function filterSaveData( $data ) {
		$data = apply_filters( 'pro_vip_settings_save_data', $data );

		return $data;
	}


	protected function smsSettings( PV_Framework_Form_Builder $fields ) {

		$fields->openTab( 'sms', __( 'SMS', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );

		if ( in_array( 'wp-sms/wp-sms.php', get_option( 'active_plugins', array() ) ) ) {

			$fields->html( '<p>' . sprintf( __( '<a href="%s">WordPress SMS</a> Plugin is not installed.' ), 'https://wordpress.org/plugins/wp-sms/' ) . '</p>' );

		}


		$fields->dropdown(
			'sms_on_file_purchase',
			$this->helper[ 'yesNo' ]
		)->label( __( 'SMS on file purchase', 'provip' ) );

		$fields
			->textarea( 'sms_file_purchase_text' )
			->label( __( 'File purchase text', 'provip' ) )
			->std_val( __( 'Dear member {{name}}
Your purchase was successful.
{{site-name}}', 'provip' ) );


		$fields->dropdown(
			'sms_on_plan_purchase',
			$this->helper[ 'yesNo' ]
		)->label( __( 'SMS on plan purchase', 'provip' ) );
		$fields
			->textarea( 'sms_plan_purchase_text' )
			->label( __( 'Plan purchase text', 'provip' ) )
			->std_val( __( 'Dear member {{name}}
Your purchase was successful.
{{site-name}}', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );
		$fields->closeTab();


	}


	protected function generalSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'general', __( 'General', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );


		$fields->multicheckbox( 'default_vip_roles', $this->helper[ 'wpRoles' ] )->label( __( 'Default VIP Roles', 'provip' ) );

		$fields->html( '<h3>' . __( 'Currency Settings', 'provip' ) . '</h3>' );

		$currency = array();
		foreach ( Pro_VIP_Currency::getAll() as $k => $v ) {
			$currency[ $k ] = $v[ 'name' ] . ' (' . $v[ 'symbol' ] . ')';
		}
		unset( $k, $v );

		$fields->dropdown( 'currency', $currency )->label( __( 'Default Currency', 'provip' ) );

		$fields->dropdown( 'currency_pos', array(
			'left'        => __( 'Left (£99.99)', 'provip' ),
			'right'       => __( 'Right (99.99£)', 'provip' ),
			'left_space'  => __( 'Left with space (£ 99.99)', 'provip' ),
			'right_space' => __( 'Right with space (99.99 £)', 'provip' )
		) )->label( __( 'Currency Position', 'provip' ) );

		$fields->textfield( 'price_thousand_sep' )->std_val( ',' )->label( __( 'Thousand Separator', 'provip' ) );
		$fields->textfield( 'price_decimal_sep' )->std_val( ',' )->label( __( 'Decimal Separator', 'provip' ) );
		$fields->textfield( 'price_num_decimals' )->std_val( '2' )->label( __( 'Number of Decimals', 'provip' ) );
		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}

	protected function vipSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'vip_settings', __( 'VIP Settings', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );

		$fields
			->dropdown( 'default_vip_level', array( 0 => '-' ) + pvGetLevels() )
			->desc( __( "If you don't want to use vip levels just create one and select it as default vip level.", 'provip' ) )
			->label( __( 'Default VIP Level', 'provip' ) );
		$fields->multicheckbox( 'vip_levels', array(), array(), array( $this, 'vipLevelsPlans' ) );
		$fields->multicheckbox( 'plans', array(), array(), array( $this, 'plansField' ) );
		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );
		$fields->closeTab();
	}

	protected function pagesSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'pages', __( 'Pages', 'provip' ) );
		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );

		$fields
			->dropdown( 'plans_page', $this->helper[ 'wpPages' ] )
			->desc( __( 'Put this shortcode in the page content. [pv-plans-form]', 'provip' ) )
			->label( __( 'Purchase Plan', 'provip' ) );

		$fields
			->dropdown( 'success_page', $this->helper[ 'wpPages' ] )
			->desc( __( 'This is the page buyers are sent to after completing their purchases. The [pv-receipt] short code should be on this page.', 'provip' ) )
			->label( __( 'Success Page', 'provip' ) );


		$fields
			->dropdown( 'failed_page', $this->helper[ 'wpPages' ] )
			->label( __( 'Failed Payment Page', 'provip' ) );

		$fields
			->dropdown( 'payments_page', $this->helper[ 'wpPages' ] )
			->desc( __( 'The [pv-user-payments] short code should be on this page.', 'provip' ) )
			->label( __( "User's Payments", 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );
		$fields->closeTab();
	}

	protected function emailSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'emails', __( 'Emails', 'provip' ) );
		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );


		$fields->media( 'email_template_logo' )->multiple( false )->label( __( 'Email Logo', 'provip' ) );

		$fields->wpEditor( 'email_footer' )->label( __( 'Email Footer', 'provip' ) );

		$fields->textfield( 'email_from_name' )->label( __( 'From Name', 'provip' ) )->std_val( get_option( 'blogname', '' ) );
		$fields->textfield( 'email_from' )->label( __( 'From Email', 'provip' ) )->std_val( get_option( 'admin_email', '' ) );

		$fields->html( __( '<h3>Account Purchase</h3>', 'provip' ) );


		$fields
			->textfield( 'email_purchase_account_subject' )
			->label( __( 'Purchase Account Subject', 'provip' ) )
			->std_val( __( 'Account Purchase', 'provip' ) );

		$fields
			->wpEditor( 'email_purchase_account_receipt' )
			->label( __( 'Account Purchase Receipt', 'provip' ) )
			->std_val( Pro_VIP_Email::getMail( 'account-purchase' ) )
			->desc( __( "Available template tags: <br/>
<code>{{first-name}}</code>: Paymenter's first name<br/>
<code>{{last-name}}</code>: Paymenter's last name<br/>
<code>{{payment-amount}}</code>: Payment Amount<br/>
<code>{{payment-gateway}}</code>: Payment Gateway<br/>
<code>{{payment-date}}</code>: Payment Date<br/>
<code>{{name}}</code>: Paymenter's full name<br/>
<code>{{plan-name}}</code>: Purchased plan name<br/>
<code>{{plan-days}}</code>: Purchased plan days<br/>
<code>{{plan-price}}</code>: Purchased plan price<br/>
<code>{{expire-date}}</code>: Purchased plan expire date<br/>
<code>{{expire-human-time}}</code>: Purchased plan expire human date<br/>
<code>{{level-name}}</code>: Purchased level name<br/>
", 'provip' ) );


		$fields->html( '<hr/>' );


		$fields->html( __( '<h3>File Purchase</h3>', 'provip' ) );


		$fields
			->textfield( 'email_purchase_file_subject' )
			->label( __( 'Purchase File Subject', 'provip' ) )
			->std_val( __( 'File Purchase', 'provip' ) );

		$fields
			->wpEditor( 'email_file_purchase_receipt' )
			->label( __( 'File Purchase Receipt', 'provip' ) )
			->std_val( Pro_VIP_Email::getMail( 'file-purchase' ) )
			->desc( __( "Available template tags: <br/>
<code>{{first-name}}</code>: Paymenter's first name<br/>
<code>{{last-name}}</code>: Paymenter's last name<br/>
<code>{{name}}</code>: Paymenter's full name<br/>
<code>{{payment-amount}}</code>: Payment Amount<br/>
<code>{{payment-gateway}}</code>: Payment Gateway<br/>
<code>{{payment-date}}</code>: Payment Date<br/>
<code>{{file-name}}</code>: Purchased file name<br/>
<code>{{download-link}}</code>: Purchased file download link<br/>
", 'provip' ) );


		$fields->html( '<hr/>' );


		$fields->html( __( '<h3>Custom Payment</h3>', 'provip' ) );


		$fields
			->textfield( 'email_custom_payment_subject' )
			->label( __( 'Custom Payment Subject', 'provip' ) )
			->std_val( __( 'Successful Payment', 'provip' ) );

		$fields
			->wpEditor( 'email_custom_payment_receipt' )
			->label( __( 'Custom Payment Receipt', 'provip' ) )
			->std_val( Pro_VIP_Email::getMail( 'custom-payment' ) )
			->desc( __( "Available template tags: <br/>
<code>{{first-name}}</code>: Paymenter's first name<br/>
<code>{{last-name}}</code>: Paymenter's last name<br/>
<code>{{name}}</code>: Paymenter's full name<br/>
<code>{{payment-amount}}</code>: Payment Amount<br/>
<code>{{payment-gateway}}</code>: Payment Gateway<br/>
<code>{{payment-date}}</code>: Payment Date<br/>
", 'provip' ) );


		$fields->html( '<hr/>' );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}

	protected function gatewaySettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'gateways', __( 'Payment Gateway', 'provip' ) );
		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );


		$fields->html( array( $this, 'gatewaysSettingsField' ) );


		$fields->multicheckbox( 'gateways', array(), array(), array( $this, 'gatewaysField' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}

	protected function otherSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'other', __( 'Other', 'provip' ) );


		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );

		$fields
			->dropdown( 'delete_expired_users', $this->helper[ 'yesNo' ] )
			->label( __( 'Delete Expired Users from Database', 'provip' ) );


		$fields
			->textfield( 'encryption_key' )
			->std_val( wp_generate_password( 24, false ) )
			->label( __( 'Encryption Key', 'provip' ) );

		$fields
			->multicheckbox( 'filter_content_post_types_metabox', $this->helper[ 'postTypes' ] )
			->std_val( array( 'post' ) )
			->label( __( 'Filter content metabox in post types', 'provip' ) );

		$fields
			->wpEditor( 'login_msg' )
			->std_val( __( 'You have to login first.<br/>[pv-login-form]' ) )
			->label( __( 'Filter content metabox in post types', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}

	protected function apiSettings( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'api', __( 'API', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );


		$fields->dropdown(
			'enable_api',
			$this->helper[ 'noYes' ]
		)->label( __( 'Enable API', 'provip' ) );

		$fields->dropdown(
			'user_authentication_api',
			$this->helper[ 'noYes' ]
		)->label( __( 'User Authentication API', 'provip' ) );


		$fields
			->textfield( 'api_key' )
			->std_val( wp_generate_password( 24, false ) )
			->label( __( 'API Key', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}


	protected function guestPurchase( PV_Framework_Form_Builder $fields ) {
		$fields->openTab( 'guest_purchase', __( 'Guest Purchase', 'provip' ) );

		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_before_in', $fields );

		$fields->dropdown(
			'single_file_guest_purchase',
			$this->helper[ 'yesNo' ]
		)->label( __( 'Enable single file guest purchase', 'provip' ) );

		$fields->textfield(
			'single_file_guest_purchase_download_time'
		)->label( __( 'Download Time (in hours)', 'provip' ) )->std_val( 48 );


		do_action( 'pro_vip_settings_' . __FUNCTION__ . '_after_in', $fields );

		$fields->closeTab();
	}
}
