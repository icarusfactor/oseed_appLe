<?php
/**
 * @package  AppLe
 */

class oseedfeedAppDeactivate
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
