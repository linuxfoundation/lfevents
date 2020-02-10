<?php
// phpcs:ignoreFile
namespace WpSvgAutocrop;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use ReflectionClass;

class Core {

	public function __construct() {
		$this->load_controllers(
			array( 'Upload', )
		);
	}

	public function load_controllers( $controllers ) {
		$namespace = $this->get_namespace();

		foreach ( $controllers as $name ) {
			$this->handle_instance( sprintf( "{$namespace}\Controllers\%s", $name ) );
		}
	}

	public function get_namespace() {
		return ( new ReflectionClass( $this ) )->getNamespaceName();
	}

	private function handle_instance( $class ) {
		return new $class();
	}
}
