# Inventario de plugins de terceros aprobados

Lista de instalación de plugins de terceros (ADR 0006 §Reglas, ADR 0009 §3).
Los plugins de terceros **no se versionan** en este repositorio; se instalan
desde el repositorio oficial de WordPress.org en cada entorno. Añadir un plugin
nuevo requiere un ADR aceptado.

## Contact Form 7

- **Propósito:** formulario de contacto público de `page-contacto`.
- **ADR:** 0010.
- **Versión instalada (Docker local, 2026-08-18):** 6.1.7 (activo). No se versiona en este repositorio; se instala desde WordPress.org.
- **Fuente:** <https://wordpress.org/plugins/contact-form-7/>.
- **Configuración requerida (vinculante):**
  - Destinatario: `revista.cenfiss@gmail.com`.
  - Solo envío por correo; **sin** almacenamiento en base de datos.
  - **Sin** Flamingo. **Sin** Google reCAPTCHA.
  - Antispam por **honeypot** provisto por `revistalogos-core`
    (integración propia; no se instala plugin extra de honeypot).
  - Enlace al aviso de privacidad (`/privacidad/`) junto al formulario.
  - Tras crear el formulario en el admin de CF7, guardar su ID en la opción
    `revistalogos_contact_form_id` (p. ej.
    `wp option update revistalogos_contact_form_id <ID>`): la plantilla
    `page-contacto.php` renderiza el formulario desde esa opción y, si falta
    el plugin o la opción, muestra el fallback accesible con `mailto:`.
  - Campos del formulario según la maqueta: Nombre completo*, Email*,
    Asunto*, Mensaje* (obligatorios); el honeypot lo inyecta
    `revistalogos-core` automáticamente.
  - Checkbox de consentimiento solo si lo aprueba la asesoría legal o lo exige
    el contenido canónico.
  - Cloudflare Turnstile es respaldo aprobado por ADR 0010 **solo** si el
    honeypot resulta insuficiente; requiere decisión del propietario, revisión
    de privacidad y documentación de configuración. No se activa por defecto.
- **Impacto de privacidad:** recoge nombre/correo/mensaje y los envía por
  correo (Google como destinatario, ADR 0011 §7); sin cookies para el
  visitante (verificar en runtime).
- **Verificación tras actualización:** comprobar que sigue sin almacenar en BD,
  que el formulario envía, y que no introduce cookies ni peticiones a terceros.
- **Retirada:** desactivar y borrar el plugin; la página de contacto conserva
  el prose canónico y muestra el fallback accesible del theme (datos de
  contacto y mailto).

## WP Statistics

- **Propósito:** analítica propia, autoalojada y sin cookies (ADR 0011 §2.1).
- **ADR:** 0011.
- **Versión instalada (Docker local, 2026-08-18):** 14.16.10 (activo). No se versiona en este repositorio; se instala desde WordPress.org.
- **Fuente:** <https://wordpress.org/plugins/wp-statistics/>.
- **Configuración requerida (vinculante, ADR 0011 §2.1):**
  1. Sin cookies y sin almacenamiento en el cliente para visitantes anónimos —
     verificar en la instalación y tras cada actualización mayor.
  2. Sin IP en claro: hash con sal rotatoria diaria activado.
  3. Sin integraciones externas (Search Console u otras).
  4. Sin complementos de pago. El conteo de descargas de PDF puede no existir
     en la versión gratuita: **no** se asume; si resulta imprescindible se
     propondrá código propio acotado en `revistalogos-core` (requiere
     requisito confirmado).
  5. Purga periódica de datos antiguos configurada (crecimiento de BD en
     hosting compartido).
- **Impacto de privacidad:** datos agregados en el servidor propio; sin
  transferencia a terceros.
- **Verificación tras actualización:** repetir la verificación de cookies /
  almacenamiento cliente / IP en claro con navegador y red (no se declara
  cumplido sin evidencia).
- **Retirada:** desactivar y borrar; purgar sus tablas si se abandona
  definitivamente.

## Vetados (recordatorio operativo)

ACF o cualquier field builder, page builders, Flamingo, Google reCAPTCHA,
suites SEO, plugins de relaciones, plugins honeypot adicionales, optimización o
seguridad «todo en uno», analítica o cookies no aprobadas, GA4 (aplazado con
precondiciones en ADR 0011 §2.2).
