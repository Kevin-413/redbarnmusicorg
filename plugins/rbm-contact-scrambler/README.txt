=== RBM Contact Scrambler ===

Standalone, reusable click-to-call/text/email links for one configured public phone number and
email address. No dependency on Avada, Slick Popup, Forminator, or RBAdmin.

Contact values are obfuscated client-side with the eScrambler Scramble Stack: split -> rotate ->
XOR -> encode -> shuffle -> rebuild. eScrambler uses layered client-side obfuscation to make
automated harvesting and casual source inspection more difficult. Publicly displayed contact
information can still be recovered by a determined visitor or automated browser - this is
obfuscation, not encryption or secure storage.

== Settings ==

Settings > RBM Contact Scrambler
- Phone Number (rbm_contact_phone)
- Email Address (rbm_contact_email)

The settings page also shows a live Shortcode & Preview table (Phone / Text / Email sections)
with Copy buttons for all nine shortcode forms.

== Shortcodes ==

Mode contract (all three shortcodes):
  mode="value"  clickable configured value (default; also used when mode is omitted or blank)
  mode="text"   clickable custom text supplied with text="..."
  mode="none"   assembled value displayed as plain text, no link

[rbm_phone mode="value"]                    Clickable tel: link showing the phone number.
[rbm_phone mode="text" text="Call Us"]      Clickable tel: link with custom text.
[rbm_phone mode="none"]                     Plain text, no tel: link.

[rbm_text mode="value"]                     Clickable sms: link showing the phone number.
[rbm_text mode="text" text="Text Us"]       Clickable sms: link with custom text.
[rbm_text mode="none"]                      Plain text, no sms: link.

[rbm_email mode="value"]                    Clickable mailto: link showing the email address.
[rbm_email mode="text" text="Email Us"]     Clickable mailto: link with custom text.
[rbm_email mode="none"]                     Plain text, no mailto: link.

[rbm_phone] / [rbm_text] alone display only the phone number (no "Text us at" prefix - labels
belong to the surrounding page content).

An unknown, non-blank mode value renders nothing (fails safely). Any shortcode renders nothing
usable if its setting is empty (fails safely, no broken link).


