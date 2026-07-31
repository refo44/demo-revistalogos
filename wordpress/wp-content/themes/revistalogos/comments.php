<?php
/**
 * Comments template, required by WordPress. Comments are disabled
 * globally for the academic journal (ADR 0011 §3); no output needed.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
