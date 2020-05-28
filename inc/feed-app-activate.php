<?php
/**
 * @package  AppLe
 */

class oseedfeedAppActivate
{
	public static function activate() {
		flush_rewrite_rules();
	}
}
