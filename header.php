<?php
/**
 * Bluebells Studio — header.php
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

<?php
// Active page detection
$is_home  = is_front_page();
$is_films = is_post_type_archive('film') || is_singular('film');
?>

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
      <div class="nav-links">
        <a href="<?php echo home_url('/'); ?>"<?php if($is_home) echo ' class="active"'; ?>>Home</a>
        <a href="<?php echo get_post_type_archive_link('film'); ?>"<?php if($is_films) echo ' class="active"'; ?>>Movies</a>
        <a href="<?php echo home_url('/contact'); ?>">Contact</a>
      </div>
      <a href="#" class="lang-switch">EN</a>
    </nav>

    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>
