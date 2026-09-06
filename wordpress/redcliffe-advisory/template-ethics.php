<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Ethics
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'ethics.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="ethics.hero.1nfzvhp"><?php rad_html( 'ethics.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="ethics.hero.0rv2w3m"><?php rad_html( 'ethics.hero.0rv2w3m' ); ?></span></div>
      <h1 data-rad="ethics.hero.title"><?php rad_html( 'ethics.hero.title' ); ?></h1>
      <p class="page-lede" data-rad="ethics.hero.1rg4il9"><?php rad_html( 'ethics.hero.1rg4il9' ); ?></p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'ethics.principles' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside" data-rad="ethics.principles.00jpqgp"><?php rad_html( 'ethics.principles.00jpqgp' ); ?></div>
        <div class="prose-body">
          <p class="lede" data-rad="ethics.principles.03s6u8g"><?php rad_html( 'ethics.principles.03s6u8g' ); ?></p>
          <p data-rad="ethics.principles.0yap24w"><?php rad_html( 'ethics.principles.0yap24w' ); ?></p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'ethics.commitments' ) ) : ?>
<section class="section" style="padding-top:0;">
    <div class="container">
      <div class="section-head reveal">
        <div class="label" data-rad="ethics.commitments.1kmpa3r"><?php rad_html( 'ethics.commitments.1kmpa3r' ); ?></div>
        <h2 data-rad="ethics.commitments.0guybeb"><?php rad_html( 'ethics.commitments.0guybeb' ); ?></h2>
      </div>
      <div class="practice-list reveal">
        <div class="practice-item"><div class="pi-n">01</div><h3 data-rad="ethics.commitments.1ggchhw"><?php rad_html( 'ethics.commitments.1ggchhw' ); ?></h3><p data-rad="ethics.commitments.1hcz5bo"><?php rad_html( 'ethics.commitments.1hcz5bo' ); ?></p></div>
        <div class="practice-item"><div class="pi-n">02</div><h3 data-rad="ethics.commitments.03pq73q"><?php rad_html( 'ethics.commitments.03pq73q' ); ?></h3><p data-rad="ethics.commitments.17ojev1"><?php rad_html( 'ethics.commitments.17ojev1' ); ?></p></div>
        <div class="practice-item"><div class="pi-n">03</div><h3 data-rad="ethics.commitments.0x4oima"><?php rad_html( 'ethics.commitments.0x4oima' ); ?></h3><p data-rad="ethics.commitments.1c1mdyv"><?php rad_html( 'ethics.commitments.1c1mdyv' ); ?></p></div>
      </div>

      <blockquote class="pullquote reveal" data-rad="ethics.commitments.0o4uw88"><?php rad_html( 'ethics.commitments.0o4uw88' ); ?></blockquote>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'ethics.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3 data-rad="ethics.other.1mx1n5x"><?php rad_html( 'ethics.other.1mx1n5x' ); ?></h3>
        <div class="small" data-rad="ethics.other.1cd2rqb"><?php rad_html( 'ethics.other.1cd2rqb' ); ?></div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t" data-rad="ethics.other.1ozkbpl"><?php rad_html( 'ethics.other.1ozkbpl' ); ?></span><span class="d" data-rad="ethics.other.0ng9skz"><?php rad_html( 'ethics.other.0ng9skz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t" data-rad="ethics.other.0011heh"><?php rad_html( 'ethics.other.0011heh' ); ?></span><span class="d" data-rad="ethics.other.0br3zvz"><?php rad_html( 'ethics.other.0br3zvz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t" data-rad="ethics.other.0jt4grq"><?php rad_html( 'ethics.other.0jt4grq' ); ?></span><span class="d" data-rad="ethics.other.10qyuye"><?php rad_html( 'ethics.other.10qyuye' ); ?></span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
