<?php
/**
 * Partners section — shared by the homepage (flat logo wall) and the
 * Partners page (split by group).
 *
 * Args:
 *  - bg      string  section background class, default 'section-dark'
 *  - grouped bool    true = one block per partner group, default false
 *  - heading string  override the section heading, '' hides it
 */
$bg_class = ( isset($args['bg']) && $args['bg'] ) ? $args['bg'] : 'section-dark';
$grouped  = ! empty($args['grouped']);
$heading  = array_key_exists('heading', (array) $args) ? $args['heading'] : bbs_t('Our Partners');

if ( ! function_exists('get_partners_sorted') ) return;

/** Render one logo wall. */
if ( ! function_exists('bbs_render_partner_tiles') ) {
    function bbs_render_partner_tiles( $partners ) {
        echo '<div class="partners-gallery">';
        foreach ( $partners as $p ) {
            $logo_id  = get_field('partner_logo', $p->ID);
            $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
            if ( ! $logo_url ) continue;
            $link = get_field('partner_link', $p->ID);
            $tag  = $link ? 'a' : 'div';
            $attr = $link ? ' href="' . esc_url($link) . '" target="_blank" rel="noopener"' : '';
            printf(
                '<%1$s class="partner-tile%2$s"%3$s title="%4$s"><img src="%5$s" alt="%4$s" loading="lazy"></%1$s>',
                $tag,
                $link ? ' has-link' : '',
                $attr,
                esc_attr($p->post_title),
                esc_url($logo_url)
            );
        }
        echo '</div>';
    }
}

if ( $grouped ) :
    $groups = get_partners_grouped();
    if ( ! $groups ) return;
?>
<section class="section-pad <?php echo esc_attr($bg_class); ?> section-border-top">
  <?php if ( $heading ): ?>
    <h2 class="section-heading"><?php echo esc_html($heading); ?></h2>
  <?php endif; ?>
  <?php foreach ( $groups as $slug => $list ): ?>
    <div class="partners-group">
      <h3 class="partners-group-title"><?php echo esc_html( bbs_t( bbs_partner_groups()[$slug] ) ); ?></h3>
      <?php bbs_render_partner_tiles( $list ); ?>
    </div>
  <?php endforeach; ?>
</section>
<?php
else :
    $partners = get_partners_sorted();
    if ( ! $partners ) return;
?>
<section class="section-pad <?php echo esc_attr($bg_class); ?> section-border-top">
  <?php if ( $heading ): ?>
    <h2 class="section-heading"><?php echo esc_html($heading); ?></h2>
  <?php endif; ?>
  <?php bbs_render_partner_tiles( $partners ); ?>
</section>
<?php endif; ?>
