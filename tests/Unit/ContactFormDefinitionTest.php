<?php
/**
 * Public contact form contract (ADR 0010, issue #62).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Contact_Form_Definition;

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core/includes/integrations/class-contact-form-definition.php';

/**
 * The CF7 form matches the maquette: four required fields, mail only,
 * no recaptcha, no Flamingo, no extra consent checkbox.
 *
 * @ticket 62
 */
class ContactFormDefinitionTest extends TestCase {

	/**
	 * Dado: el formulario público de contacto.
	 * Entonces: el destinatario es el buzón canónico de la revista.
	 */
	public function test_mail_goes_only_to_the_journal_mailbox() {
		$mail = Contact_Form_Definition::mail_template();

		$this->assertSame( 'revista.cenfiss@gmail.com', Contact_Form_Definition::recipient() );
		$this->assertSame( 'revista.cenfiss@gmail.com', $mail['recipient'] );
		$this->assertFalse( $mail['use_html'] );
		$this->assertSame( 'Reply-To: [contact-email]', $mail['additional_headers'] );
	}

	/**
	 * Dado: la maqueta de /contacto/.
	 * Entonces: Nombre, Email, Asunto y Mensaje son obligatorios.
	 */
	public function test_form_template_has_the_four_required_maquette_fields() {
		$form = Contact_Form_Definition::form_template();

		$this->assertStringContainsString( 'Nombre completo *', $form );
		$this->assertStringContainsString( 'Email *', $form );
		$this->assertStringContainsString( 'Asunto *', $form );
		$this->assertStringContainsString( 'Mensaje *', $form );
		$this->assertStringContainsString( '[text* contact-name', $form );
		$this->assertStringContainsString( '[email* contact-email', $form );
		$this->assertStringContainsString( '[text* contact-subject', $form );
		$this->assertStringContainsString( '[textarea* contact-message', $form );
		$this->assertStringContainsString( 'Enviar Mensaje', $form );
	}

	/**
	 * Dado: ADR 0010.
	 * Entonces: sin reCAPTCHA, sin Flamingo y sin checkbox de consentimiento.
	 */
	public function test_definition_excludes_recaptcha_flamingo_and_consent_checkbox() {
		$form = Contact_Form_Definition::form_template();
		$mail = Contact_Form_Definition::mail_template();
		$blob = $form . json_encode( $mail ) . Contact_Form_Definition::form_title();

		$this->assertStringNotContainsString( 'recaptcha', strtolower( $blob ) );
		$this->assertStringNotContainsString( 'flamingo', strtolower( $blob ) );
		$this->assertStringNotContainsString( '[acceptance', $form );
		$this->assertStringNotContainsString( 'checkbox', strtolower( $form ) );
	}

	/**
	 * Dado: la plantilla de contacto del theme.
	 * Entonces: CF7 se lee de la opción y el mailto queda como fallback.
	 */
	public function test_contact_template_uses_option_and_keeps_mailto_fallback() {
		$template = $this->repo_file_contents(
			'wordpress/wp-content/themes/revistalogos/page-contacto.php'
		);

		$this->assertStringContainsString( 'revistalogos_contact_form_id', $template );
		$this->assertStringContainsString( 'contact-form-7', $template );
		$this->assertStringContainsString( 'mailto:revista.cenfiss@gmail.com', $template );
		$this->assertStringContainsString( 'Aviso de Privacidad', $template );
	}

	/**
	 * @param string $relative Path from the repository root.
	 */
	private function repo_file_contents( $relative ) {
		$path = dirname( __DIR__, 2 ) . '/' . $relative;
		$this->assertFileIsReadable( $path );

		$contents = file_get_contents( $path );
		$this->assertNotFalse( $contents );

		return $contents;
	}
}
