<?php
/**
 * @class          PV_Framework_Admin_Menu
 * @version        1
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access


/**
 * Class PV_Framework_Admin_Menu
 */
class PV_Framework_Admin_Menu {

	protected static $menus = array();

	public static function init() {
		static $inited = false;
		if ( $inited ) {
			return false;
		}
		$inited = true;
		add_action( 'admin_menu', array( __CLASS__, 'proceedMenu' ), 9999999 );
	}

	protected function __construct() {
		$this::$menus[ ] = $this;
	}

	public static function proceedMenu() {

		/**
		 * @var $menu $this
		 */
		foreach ( self::$menus as $menu ) {

			$pageTitle = $menu->pageTitle;
			$menuSlug  = $menu->menuSlug;

			if (
				empty( $pageTitle )
				|| empty( $menuSlug )
			) {
				continue;
			}

			$callArgs = array();

			$function = 'add_menu_page';

			if ( ! empty( $menu->parentSlug ) ) {
				$callArgs[ ] = $menu->parentSlug;
			}

			$callArgs[ ] = $menu->pageTitle;
			$callArgs[ ] = ! empty( $menu->menuTitle ) ? $menu->menuTitle : $menu->pageTitle;
			$callArgs[ ] = $menu->capability;
			$callArgs[ ] = $menu->menuSlug;
			$callArgs[ ] = $menu->callback;

			if ( ! empty( $menu->parentSlug ) ) {
				$function = 'add_submenu_page';
			} else {
				$callArgs[ ] = $menu->iconUrl;
				$callArgs[ ] = empty( $menu->position ) ? null : $menu->position;
			}


			$call = call_user_func_array( $function, $callArgs );

			if ( is_callable( $menu->onLoad ) ) {
				add_action( 'load-' . $call, $menu->onLoad );
			}

		}
	}

	protected $_settings = array(
		'pageTitle'  => '',
		'menuTitle'  => '',
		'capability' => 'manage_options',
		'iconUrl'    => '',
		'position'   => '',
		'menuSlug'   => '',
		'parentSlug' => '',
		'callback'   => '__return_empty_string',
		'onLoad'     => '__return_empty_string'
	);

	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function onLoad( $callback ) {
		$this->_settings[ 'onLoad' ] = $callback;

		return $this;
	}

	public function pageTitle( $title ) {
		$this->_settings[ 'pageTitle' ] = (string) $title;

		return $this;
	}

	public function menuTitle( $title ) {
		$this->_settings[ 'menuTitle' ] = (string) $title;

		return $this;
	}

	public function capability( $capability ) {
		$this->_settings[ 'capability' ] = (string) $capability;

		return $this;
	}

	public function iconUrl( $iconUrl ) {
		$this->_settings[ 'iconUrl' ] = (string) $iconUrl;

		return $this;
	}

	public function position( $position ) {
		$this->_settings[ 'position' ] = (string) $position;

		return $this;
	}

	public function menuSlug( $menuSlug ) {
		$this->_settings[ 'menuSlug' ] = (string) $menuSlug;

		return $this;
	}

	public function parentSlug( $parentSlug ) {
		$this->_settings[ 'parentSlug' ] = (string) $parentSlug;

		return $this;
	}

	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function callback( $callback ) {
		$this->_settings[ 'callback' ] = $callback;

		return $this;
	}


	public function __set( $name, $val ) {
		if ( isset( $this->_settings[ $name ] ) ) {
			$this->_settings[ $name ] = $val;

			return $this;
		}
		throw new Exception;
	}

	public function __get( $name ) {
		if ( isset( $this->_settings[ $name ] ) ) {
			return $this->_settings[ $name ];
		}
		throw new Exception;
	}

	function __isset( $k ) {
		return isset( $this->_settings[ $k ] );
	}

	static function make() {
		return new self;
	}

}
