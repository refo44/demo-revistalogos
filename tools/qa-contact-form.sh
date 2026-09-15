#!/usr/bin/env bash
# Local Docker QA: Contact Form 7 on /contacto/ (ADR 0010, issue #62).
# Never points at production.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

cli() {
	docker compose run --rm wpcli wp "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

echo "== php -l =="
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/integrations/class-contact-form-definition.php
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/integrations/class-contact-form-integration.php
docker compose run --rm --entrypoint php wpcli -l wp-content/themes/revistalogos/page-contacto.php

echo "== Contact Form 7 from WordPress.org =="
if ! cli plugin is-installed contact-form-7; then
	cli plugin install contact-form-7 --activate
else
	cli plugin activate contact-form-7 >/dev/null
fi
cli plugin is-active contact-form-7 || fail "contact-form-7 must be active"

echo "== provision =="
cli option delete revistalogos_contact_form_id >/dev/null 2>&1 || true
FORM_ID="$(cli eval 'echo (int) \Revistalogos_Core\Contact_Form_Integration::maybe_provision();')"
[[ "$FORM_ID" =~ ^[1-9][0-9]*$ ]] || fail "expected a form ID, got: $FORM_ID"
STORED="$(cli option get revistalogos_contact_form_id)"
[[ "$STORED" == "$FORM_ID" ]] || fail "option $STORED != provisioned $FORM_ID"
AGAIN="$(cli eval 'echo (int) \Revistalogos_Core\Contact_Form_Integration::maybe_provision();')"
[[ "$AGAIN" == "$FORM_ID" ]] || fail "second provision must reuse $FORM_ID, got: $AGAIN"
MANAGED="$(cli post meta get "$FORM_ID" _les_managed_contact_form)"
[[ "$MANAGED" == "1" ]] || fail "provisioned form must be marked managed, got: $MANAGED"

RECIPIENT="$(cli eval "\$mail = get_post_meta( $FORM_ID, '_mail', true ); echo isset(\$mail['recipient']) ? \$mail['recipient'] : '';")"
[[ "$RECIPIENT" == "revista.cenfiss@gmail.com" ]] || fail "unexpected recipient: $RECIPIENT"

FORM="$(cli eval "echo get_post_meta( $FORM_ID, '_form', true );")"
echo "$FORM" | grep -q '\[text\* contact-name' || fail "missing required name field"
echo "$FORM" | grep -q '\[email\* contact-email' || fail "missing required email field"
echo "$FORM" | grep -q '\[text\* contact-subject' || fail "missing required subject field"
echo "$FORM" | grep -q '\[textarea\* contact-message' || fail "missing required message field"
echo "$FORM" | grep -qi recaptcha && fail "form must not include recaptcha" || true

echo "== HTTP /contacto/ =="
HTML="$(curl -sS 'http://localhost:8080/contacto/')"
echo "$HTML" | grep -q 'wpcf7' || fail "/contacto/ must render CF7"
echo "$HTML" | grep -q 'Nombre completo' || fail "/contacto/ must show Nombre completo"
echo "$HTML" | grep -q 'privacidad' || fail "/contacto/ must link to the privacy notice"
echo "$HTML" | grep -q 'Puede escribirnos directamente' && fail "/contacto/ must not show the mailto fallback" || true
echo "$HTML" | grep -qi recaptcha && fail "/contacto/ must not load recaptcha" || true

echo "PASS: contact form QA"
