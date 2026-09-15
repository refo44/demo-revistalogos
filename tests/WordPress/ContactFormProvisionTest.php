<?php
/**
 * Idempotent CF7 form provision (ADR 0010, issue #62).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Contact_Form_Definition;
use Revistalogos_Core\Contact_Form_Integration;

/**
 * When the CF7 post type exists, the plugin creates one managed form and
 * stores its ID. Without CF7, nothing is created. External CF7 is doubled
 * as the CPT; mail still is not sent here.
 *
 * @ticket 62
 */
class ContactFormProvisionTest extends WP_UnitTestCase {

	/**
	 * True when this test registered the CF7 CPT so tear_down can remove it.
	 *
	 * @var bool
	 */
	private $registered_cf7 = false;

	public function set_up() {
		parent::set_up();
		$this->registered_cf7 = false;
		delete_option( Contact_Form_Integration::OPTION_NAME );
		$this->delete_managed_forms();
	}

	public function tear_down() {
		$this->delete_managed_forms();
		delete_option( Contact_Form_Integration::OPTION_NAME );
		if ( $this->registered_cf7 && post_type_exists( Contact_Form_Integration::POST_TYPE ) ) {
			unregister_post_type( Contact_Form_Integration::POST_TYPE );
		}
		$this->registered_cf7 = false;
		parent::tear_down();
	}

	/**
	 * Dado: Contact Form 7 no está activo.
	 * Entonces: no se crea formulario ni se escribe la opción.
	 */
	public function test_provision_is_a_noop_when_cf7_post_type_is_missing() {
		$this->assertFalse( post_type_exists( Contact_Form_Integration::POST_TYPE ) );

		$id = Contact_Form_Integration::maybe_provision();

		$this->assertSame( 0, $id );
		$this->assertFalse( get_option( Contact_Form_Integration::OPTION_NAME ) );
		$this->assertSame( array(), get_posts( array( 'post_type' => Contact_Form_Integration::POST_TYPE, 'post_status' => 'any', 'numberposts' => -1 ) ) );
	}

	/**
	 * Dado: el CPT de CF7 existe y no hay formulario gestionado.
	 * Entonces: se crea uno, se apunta la opción y el correo va al buzón canónico.
	 */
	public function test_provision_creates_managed_form_and_stores_its_id() {
		$this->register_cf7_post_type();

		$id = Contact_Form_Integration::maybe_provision();

		$this->assertGreaterThan( 0, $id );
		$this->assertSame( $id, (int) get_option( Contact_Form_Integration::OPTION_NAME ) );
		$this->assertSame( Contact_Form_Definition::form_title(), get_post( $id )->post_title );
		$this->assertSame( '1', get_post_meta( $id, Contact_Form_Integration::MANAGED_META, true ) );
		$this->assertSame(
			Contact_Form_Definition::form_template(),
			get_post_meta( $id, '_form', true )
		);

		$mail = get_post_meta( $id, '_mail', true );
		$this->assertIsArray( $mail );
		$this->assertSame( Contact_Form_Definition::recipient(), $mail['recipient'] );
		$this->assertFalse( $mail['use_html'] );
	}

	/**
	 * Dado: el formulario gestionado ya existe.
	 * Entonces: un segundo provision no duplica ni pisa la plantilla.
	 */
	public function test_provision_is_idempotent_and_does_not_overwrite_an_existing_form() {
		$this->register_cf7_post_type();
		$first = Contact_Form_Integration::maybe_provision();
		update_post_meta( $first, '_form', '[text* custom-field]' );

		$second = Contact_Form_Integration::maybe_provision();

		$forms = get_posts(
			array(
				'post_type'   => Contact_Form_Integration::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
				'meta_key'    => Contact_Form_Integration::MANAGED_META,
				'meta_value'  => '1',
			)
		);

		$this->assertSame( $first, $second );
		$this->assertCount( 1, $forms );
		$this->assertSame( '[text* custom-field]', get_post_meta( $first, '_form', true ) );
	}

	/**
	 * Dado: la opción apunta a un ID borrado y queda un formulario gestionado.
	 * Entonces: se reutiliza el existente, no se crea otro.
	 */
	public function test_provision_reuses_managed_form_when_option_is_stale() {
		$this->register_cf7_post_type();
		$existing = Contact_Form_Integration::maybe_provision();
		update_option( Contact_Form_Integration::OPTION_NAME, 999999 );

		$reused = Contact_Form_Integration::maybe_provision();

		$this->assertSame( $existing, $reused );
		$this->assertSame( $existing, (int) get_option( Contact_Form_Integration::OPTION_NAME ) );
	}

	private function register_cf7_post_type() {
		if ( post_type_exists( Contact_Form_Integration::POST_TYPE ) ) {
			return;
		}

		register_post_type(
			Contact_Form_Integration::POST_TYPE,
			array(
				'public'   => false,
				'supports' => array( 'title' ),
			)
		);
		$this->registered_cf7 = true;
	}

	private function delete_managed_forms() {
		if ( ! post_type_exists( Contact_Form_Integration::POST_TYPE ) ) {
			return;
		}

		$forms = get_posts(
			array(
				'post_type'   => Contact_Form_Integration::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
			)
		);

		foreach ( $forms as $form ) {
			wp_delete_post( $form->ID, true );
		}
	}
}
