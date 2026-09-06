<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( rad_page_slug() ); ?>">
<?php wp_body_open(); ?>
<div class="mobile-bar">
    <a class="ghost" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span data-rad="global.top.1xnyvfl"><?php rad_html( 'global.top.1xnyvfl' ); ?></span><span class="arr">→</span></a>
    <a class="primary" href="<?php echo esc_url( rad_url( 'contact' ) ); ?>"><span data-rad="global.top.1r6gmmr"><?php rad_html( 'global.top.1r6gmmr' ); ?></span><span class="arr">↗</span></a>
  </div>

  <?php if ( rad_section_enabled( 'global.announcement' ) ) : ?>
<a class="announce" href="<?php echo esc_url( rad_setting_url( 'global.announcement.url', rad_url( 'agenda' ) ) ); ?>" aria-label="The City Quantum and AI Summit, 7 October 2026">
    <span class="a-dot" aria-hidden="true"></span>
    <span class="a-text" data-rad="global.announcement.text"><?php rad_html( 'global.announcement.text' ); ?></span>
    <span class="a-cta" data-rad="global.announcement.cta"><?php rad_html( 'global.announcement.cta' ); ?></span>
  </a>
<?php endif; ?>

  <header class="header" id="header">
    <div class="container header-inner">
      <a class="brand" href="<?php echo esc_url( rad_url( 'home' ) ); ?>" aria-label="Redcliffe Advisory — home">
        <img class="brand-logo" src="<?php echo esc_url( rad_image_url( 'global.top.1dz4tej' ) ); ?>" width="132" height="37" alt="<?php echo esc_attr( rad_image_alt( 'global.top.1dz4tej' ) ); ?>" data-rad-img="global.top.1dz4tej" />
      </a>
      <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-label="Open menu">
        <span>Menu</span><span class="lines" aria-hidden="true"><span></span><span></span></span>
      </button>
      <?php get_template_part( 'template-parts/navigation' ); ?>
    </div>
  </header>
