<?php
/**
 * Bluebells Studio — footer.php
 */
?>

<footer id="site-footer">
<div class="footer-inner">
  <div class="footer-top">
    <div>
      <a href="<?php echo home_url('/'); ?>" class="footer-logo">
        <?php if ( has_custom_logo() ):
          $logo_id  = get_theme_mod('custom_logo');
          $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        ?>
          <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="footer-logo-img">
        <?php else: ?>
          Bluebells Studio
        <?php endif; ?>
      </a>
      <p class="footer-copy">© <?php echo date('Y'); ?> Bluebells Studio Co., Ltd.<br>Ho Chi Minh City, Vietnam.</p>
    </div>
    <div class="footer-nav-col">
      <span class="footer-nav-label">Explore</span>
      <a href="<?php echo get_post_type_archive_link('film'); ?>">Films</a>
      <a href="<?php echo home_url('/partners'); ?>">Partners</a>
      <a href="<?php echo home_url('/contact'); ?>">Contact</a>
    </div>
    <div class="footer-nav-col">
      <span class="footer-nav-label">Follow</span>
      <a href="#" target="_blank" rel="noopener">Facebook</a>
      <a href="#" target="_blank" rel="noopener">TikTok</a>
      <a href="#" target="_blank" rel="noopener">YouTube</a>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-legal">
      <a href="<?php echo home_url('/privacy-policy'); ?>">Privacy Policy</a>
      <a href="<?php echo home_url('/terms'); ?>">Terms of Use</a>
      <a href="<?php echo home_url('/copyright'); ?>">Copyright Notice</a>
    </div>
    <span class="footer-tagline">Film is our language.</span>
  </div>
</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
