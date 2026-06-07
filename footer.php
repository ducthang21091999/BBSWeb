<?php
/**
 * Bluebells Studios — footer.php
 */
$contact_email   = get_option('bluebells_contact_email');
$contact_phone   = get_option('bluebells_contact_phone');
$contact_address = get_option('bluebells_contact_address');
$logo_url = has_custom_logo() ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : '';

$social = [
    'facebook' => get_option('bluebells_social_facebook', ''),
    'tiktok'   => get_option('bluebells_social_tiktok', ''),
    'youtube'  => get_option('bluebells_social_youtube', ''),
];
?>

<footer id="site-footer">
<div class="footer-inner">
  <div class="footer-grid">

    <!-- LEFT: Brand / Follow / Copy / Tagline -->
    <div class="footer-brand">
      <a href="<?php echo home_url('/'); ?>" class="footer-logo">
        <?php if ( $logo_url ): ?>
          <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="footer-logo-img">
        <?php else: ?>
          Bluebells Studios
        <?php endif; ?>
      </a>

      <p class="footer-section-label"><?php bbs_e('Follow Us'); ?></p>
      <div class="footer-social-icons">
        <a href="<?php echo esc_url($social['facebook'] ?: '#'); ?>" target="_blank" rel="noopener" aria-label="Facebook" class="footer-social-link">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/>
          </svg>
        </a>
        <a href="<?php echo esc_url($social['tiktok'] ?: '#'); ?>" target="_blank" rel="noopener" aria-label="TikTok" class="footer-social-link">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V8.93a8.16 8.16 0 0 0 4.77 1.52V7a4.85 4.85 0 0 1-1.84-.31z"/>
          </svg>
        </a>
        <a href="<?php echo esc_url($social['youtube'] ?: '#'); ?>" target="_blank" rel="noopener" aria-label="YouTube" class="footer-social-link">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M23.5 6.5c-.3-1-1-1.8-2-2C19.5 4 12 4 12 4s-7.5 0-9.5.5c-1 .3-1.8 1-2 2C0 8.5 0 12 0 12s0 3.5.5 5.5c.3 1 1 1.8 2 2C4.5 20 12 20 12 20s7.5 0 9.5-.5c1-.3 1.8-1 2-2 .5-2 .5-5.5.5-5.5s0-3.5-.5-5.5zM9.5 15.5v-7l6 3.5-6 3.5z"/>
          </svg>
        </a>
      </div>

      <p class="footer-copy">© 2022 Bluebells Studios Co., Ltd. All rights reserved.</p>
    </div>

    <!-- RIGHT: Contact info -->
    <div class="footer-contact-col">
      <p class="footer-section-label"><?php bbs_e('Contact Information'); ?></p>
      <ul class="footer-contact">
        <?php if ( $contact_address ): ?>
        <li class="footer-contact-row">
          <span class="footer-contact-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h2M9 13h2M9 17h2M13 9h2M13 13h2M13 17h2"/>
            </svg>
          </span>
          <span><?php echo esc_html($contact_address); ?></span>
        </li>
        <?php endif; ?>

        <?php if ( $contact_phone ): ?>
        <li class="footer-contact-row">
          <span class="footer-contact-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/>
            </svg>
          </span>
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
        </li>
        <?php endif; ?>

        <?php if ( $contact_email ): ?>
        <li class="footer-contact-row">
          <span class="footer-contact-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="5" width="18" height="14" rx="2"/>
              <path d="M3 7l9 6 9-6"/>
            </svg>
          </span>
          <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

  </div>
</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
