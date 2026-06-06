<?php
/**
 * Contact section — used both at bottom of home page and as body of /contact page.
 *
 * Configurable options (read from Settings → Contact Info):
 *  - bluebells_contact_email
 *  - bluebells_contact_phone
 *  - bluebells_contact_address
 */
$contact_email   = get_option('bluebells_contact_email');
$contact_phone   = get_option('bluebells_contact_phone');
$contact_address = get_option('bluebells_contact_address');
$logo_url = has_custom_logo() ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : '';
?>
<section class="contact-section section-gray">
  <div class="contact-inner">
    <span class="contact-label">Work With Us</span>
    <h2 class="contact-headline">Let's craft beautiful films together.</h2>

    <div class="contact-grid">
      <!-- LEFT: studio info -->
      <div class="contact-info">
        <?php if ( $logo_url ): ?>
          <img src="<?php echo esc_url($logo_url); ?>" alt="Bluebells Studios" class="contact-logo">
        <?php endif; ?>
        <?php if ( $contact_email ): ?>
          <p class="contact-info-row">
            <span class="contact-info-label">Email</span>
            <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
          </p>
        <?php endif; ?>
        <?php if ( $contact_phone ): ?>
          <p class="contact-info-row">
            <span class="contact-info-label">Phone</span>
            <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
          </p>
        <?php endif; ?>
        <?php if ( $contact_address ): ?>
          <p class="contact-info-row">
            <span class="contact-info-label">Address</span>
            <span class="contact-info-value"><?php echo esc_html($contact_address); ?></span>
          </p>
        <?php endif; ?>
      </div>

      <!-- RIGHT: contact form -->
      <form class="contact-form" id="bluebells-contact-form" novalidate>
        <label class="contact-field">
          <span>Name</span>
          <input type="text" name="name" required autocomplete="name">
        </label>
        <label class="contact-field">
          <span>Email</span>
          <input type="email" name="email" required autocomplete="email">
        </label>
        <label class="contact-field">
          <span>Message</span>
          <textarea name="message" rows="5" required></textarea>
        </label>
        <input type="text" name="website" class="contact-hp" tabindex="-1" autocomplete="off">
        <button type="submit" class="btn-primary contact-submit">Send Message</button>
        <div class="contact-form-msg" role="status" aria-live="polite"></div>
      </form>
    </div>
  </div>
</section>
