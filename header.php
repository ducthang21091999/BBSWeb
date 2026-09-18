<?php
/**
 * Bluebells Studios — header.php
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>


<header id="site-header">
  <div class="header-inner">
    <a href="<?php echo home_url('/'); ?>" class="site-logo">
      <?php if ( has_custom_logo() ):
        $logo_id  = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
      ?>
        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="site-logo-img">
      <?php else: ?>
        <span class="site-logo-text">Blue<span>bells</span> Studio</span>
      <?php endif; ?>
    </a>

    <nav class="site-nav" id="site-nav">
      <?php
      // Menu is editable in Appearance → Menus (location: Primary Navigation).
      // Falls back to the built-in list when no menu is assigned yet.
      if ( has_nav_menu('primary') ) {
          wp_nav_menu([
              'theme_location' => 'primary',
              'container'      => false,
              'menu_class'     => 'nav-links',
              'depth'          => 1,
              'fallback_cb'    => 'bbs_nav_fallback',
          ]);
      } else {
          bbs_nav_fallback();
      }
      ?>
    </nav>

    <?php
    // Language switcher — always visible outside the mobile menu
    $current_lang = bbs_current_lang();
    $target_lang  = $current_lang === 'vi' ? 'en' : 'vi';
    $switch_url   = bbs_lang_switch_url($target_lang);
    ?>
    <a href="<?php echo esc_url($switch_url); ?>" class="lang-switch" aria-label="Switch language"><?php echo esc_html(strtoupper($target_lang)); ?></a>

    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>
