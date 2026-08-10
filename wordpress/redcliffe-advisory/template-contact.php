<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Contact
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'contact.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>Contact</span></div>
      <h1><?php rad_html( 'contact.hero.title' ); ?></h1>
      <p class="page-lede"><?php rad_html( 'contact.hero.lede' ); ?></p>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'contact.form' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="contact-layout reveal">
        <aside class="contact-panel">
          <div class="label">Contact details</div>
          <dl>
            <div>
              <dt>Email</dt>
              <dd><?php rad_html( 'contact.details.email' ); ?></dd>
            </div>
            <div>
              <dt>Location</dt>
              <dd><?php rad_html( 'contact.details.location' ); ?></dd>
            </div>
            <div>
              <dt>Response</dt>
              <dd><?php rad_html( 'contact.details.response' ); ?></dd>
            </div>
          </dl>
        </aside>

        <?php get_template_part( 'template-parts/contact-form' ); ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php get_footer(); ?>
