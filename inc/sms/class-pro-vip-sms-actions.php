<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pro_VIP_SMS_Actions {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {

		add_action( 'pro_vip_purchase_file_form_after', array( $this, 'addPhoneNumberField' ) );
		add_action( 'pro_vip_plans_form_table_end', array( $this, 'addPhoneNumberField' ) );

		if ( pvGetOption( 'sms_on_file_purchase', 'yes' ) == 'yes' ) {
			add_action( 'pro_vip_file_purchase_complete', array( $this, 'purchaseComplete' ), 10, 2 );
		}

		if ( pvGetOption( 'sms_on_plan_purchase', 'yes' ) == 'yes' ) {
			add_action( 'pro_vip_plan_purchase_complete', array( $this, 'purchaseComplete' ), 10, 2 );
		}


		add_action( 'pro_vip_single_file_purchase', array( $this, 'savePhoneNumber' ), 10, 3 );
	}

	public function purchaseComplete( Pro_VIP_Payment $payment, $tags ) {
		if ( empty( $payment->custom[ 'phone-number' ] ) || ! is_numeric( $payment->custom[ 'phone-number' ] ) ) {
			return false;
		}

		switch ( current_action() ) {
			case 'pro_vip_file_purchase_complete':
				$msg = pvGetOption( 'sms_file_purchase_text', __( 'Dear member {{name}}
Your purchase was successful.
{{site-name}}', 'provip' ) );
				break;
			case 'sms_plan_purchase_text':
				$msg = pvGetOption( 'sms_file_purchase_text', __( 'Dear member {{name}}
Your purchase was successful.
{{site-name}}', 'provip' ) );
				break;
		}


		if ( empty( $msg ) || ! is_string( $msg ) ) {
			return false;
		}

		$msg = self::filterTags( $msg, $tags );

		/**
		 * @var $sms ssmss
		 */
		global $sms;
		$sms->to  = array( $payment->custom[ 'phone-number' ] );
		$sms->msg = $msg;
		$sms->SendSMS();

	}


	public function addPhoneNumberField() {

		switch ( current_action() ) {

			case 'pro_vip_purchase_file_form_after':
			case 'pro_vip_plans_form_table_end':

				?>
				<tr>
					<td class="title">
						<label for="pv-phone-number">
							<strong><?= __( 'Phone', 'provip' ) ?></strong>
						</label>
					</td>
					<td class="input">
						<input name="pv-phone-number" id="pv-phone-number" value="" type="text"/>
					</td>
				</tr>
				<?php

				break;

		}
	}

	public function savePhoneNumber( $fileId, $fileIndex, Pro_VIP_Payment $payment ) {
		if ( ! empty( $_REQUEST[ 'pv-phone-number' ] ) && is_numeric( $_REQUEST[ 'pv-phone-number' ] ) ) {
			$payment->custom[ 'phone-number' ] = $_REQUEST[ 'pv-phone-number' ];
		}
	}


	public static function filterTags( $content, $tags = array() ) {

		$defaultTags = array(
			'site-name' => get_option( 'blogname' ),
			'site-url'  => site_url( '/' )
		);

		$tags = $defaultTags + $tags;

		foreach ( $tags as $tag => $value ) {
			$search  = $tag;
			$content = str_replace( '{{' . $search . '}}', $value, $content );
		}

		return $content;
	}
}
