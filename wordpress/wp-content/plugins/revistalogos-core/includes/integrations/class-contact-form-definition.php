<?php
/**
 * Canonical public contact form (ADR 0010): fields, recipient and mail.
 * Pure data; Contact Form 7 persistence lives in Contact_Form_Integration.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maquette contract for /contacto/. No recaptcha, no Flamingo, no extra
 * consent checkbox (ADR 0010).
 */
class Contact_Form_Definition {

	const RECIPIENT  = 'revista.cenfiss@gmail.com';
	const FORM_TITLE = 'Contacto';

	/**
	 * Mailbox that receives submissions.
	 *
	 * @return string
	 */
	public static function recipient() {
		return self::RECIPIENT;
	}

	/**
	 * Title shown in Contact Form 7 admin.
	 *
	 * @return string
	 */
	public static function form_title() {
		return self::FORM_TITLE;
	}

	/**
	 * CF7 form template: four required fields from the static maquette.
	 *
	 * @return string
	 */
	public static function form_template() {
		return '<div class="form-group">' . "\n"
			. '<label for="contact-name">Nombre completo *</label>' . "\n"
			. '[text* contact-name id:contact-name autocomplete:name]' . "\n"
			. '</div>' . "\n"
			. '<div class="form-group">' . "\n"
			. '<label for="contact-email">Email *</label>' . "\n"
			. '[email* contact-email id:contact-email autocomplete:email]' . "\n"
			. '</div>' . "\n"
			. '<div class="form-group">' . "\n"
			. '<label for="contact-subject">Asunto *</label>' . "\n"
			. '[text* contact-subject id:contact-subject]' . "\n"
			. '</div>' . "\n"
			. '<div class="form-group">' . "\n"
			. '<label for="contact-message">Mensaje *</label>' . "\n"
			. '[textarea* contact-message id:contact-message 60x6]' . "\n"
			. '</div>' . "\n"
			. '[submit class:btn class:btn--primary "Enviar Mensaje"]' . "\n";
	}

	/**
	 * CF7 mail property. Plain text to the journal mailbox; Reply-To is
	 * the visitor so editors can answer without storing the submission.
	 *
	 * @return array<string, mixed>
	 */
	public static function mail_template() {
		return array(
			'active'             => true,
			'subject'            => '[contact-subject]',
			'sender'             => '[_site_title] <[_site_admin_email]>',
			'recipient'          => self::RECIPIENT,
			'body'               => "Nombre completo: [contact-name]\n"
				. "Email: [contact-email]\n"
				. "Asunto: [contact-subject]\n\n"
				. "Mensaje:\n[contact-message]\n",
			'additional_headers' => 'Reply-To: [contact-email]',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		);
	}
}
