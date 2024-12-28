<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Data_Stores;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of compatibility package.
	 *
	 * @access private
	 * @var    object
	 */
	private $package = null;

	private function __construct( $package = null ) {
		$this->package = $package;

		if ( ! jet_engine()->modules->is_module_active( 'data-stores' ) ) {
			return;
		}

		add_filter( 'jet-engine/data-stores/store/data', array( $this, 'set_translated_store' ), 10, 2 );
		// https://github.com/Crocoblock/issues-tracker/issues/9567
		add_action( 'jet-engine/data-stores/after-remove-from-store', array( $this, 'remove_ip_store_translations' ), 10, 3 );
	}

	public function set_translated_store( $store, $store_id ) {

		if ( empty( $store ) ) {
			return $store;
		}

		$store_instance = \Jet_Engine\Modules\Data_Stores\Module::instance()->stores->get_store( $store_id );

		if ( $store_instance->is_user_store() || $store_instance->get_arg( 'is_cct' ) ) {
			return $store;
		}

		$store = array_map( function( $item ) {

			if ( ! is_array( $item ) ) {
				$item = apply_filters( 'wpml_object_id', $item, get_post_type( $item ), true );
			}

			return $item;
		}, $store );

		return $store;
	}

	public function remove_ip_store_translations( $post_id, $store, $factory ) {
		if ( $factory->is_user_store() || $factory->get_type()->type_id() !== 'user_ip' ) {
			return;
		}
		
		$type = apply_filters( 'wpml_element_type', get_post_type( $post_id ) );
		$trid = apply_filters( 'wpml_element_trid', false, $post_id, $type );
		
		$translations = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );

		foreach ( $translations as $translation ) {
			$id = $translation->element_id ?? 0;

			if ( ! $id || $id === $post_id ) {
				continue;
			}
			
			$factory->get_type()->remove( $store, $id );
		}
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance( $package = null ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $package );
		}

		return self::$instance;

	}
	
}
