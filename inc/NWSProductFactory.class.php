<?php
/*
 * Factory from which all NWS products will be served from.
 * If a product does not have a handler, it will be ignored.
 */

class NWSProductFactory {
	// Class name is the WMO header corresponding to the product
	public static function autoloader($class_name) {
		include('inc/products/' . $class_name '.php');
	}
}

spl_autoload_register('NWSProductFactory::autoloader');