<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Chair Advisory
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'practice.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="practice.hero.1nfzvhp"><?php rad_html( 'practice.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="practice.hero.00tp5xa"><?php rad_html( 'practice.hero.00tp5xa' ); ?></span></div>
      <h1 data-rad="practice.hero.title"><?php rad_html( 'practice.hero.title' ); ?></h1>
      <div class="hero-subhead" data-rad="practice.hero.12cq8at"><?php rad_html( 'practice.hero.12cq8at' ); ?></div>
      <p class="page-lede" data-rad="practice.hero.12n1n25"><?php rad_html( 'practice.hero.12n1n25' ); ?></p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'practice.intro' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside" data-rad="practice.intro.0fiz36i"><?php rad_html( 'practice.intro.0fiz36i' ); ?></div>
        <div class="prose-body">
          <p class="lede" data-rad="practice.intro.16gwfvj"><?php rad_html( 'practice.intro.16gwfvj' ); ?></p>
          <p data-rad="practice.intro.1pvnw96"><?php rad_html( 'practice.intro.1pvnw96' ); ?></p>
          <p data-rad="practice.intro.1sfilw9"><?php rad_html( 'practice.intro.1sfilw9' ); ?></p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'practice.areas' ) ) : ?>
<section class="section" style="padding-top:0;">
    <div class="container">
      <div class="section-head reveal">
        <div class="label" data-rad="practice.areas.0k476yl"><?php rad_html( 'practice.areas.0k476yl' ); ?></div>
        <h2 data-rad="practice.areas.0q11fr8"><?php rad_html( 'practice.areas.0q11fr8' ); ?></h2>
      </div>
      <div class="practice-list reveal">
        <div class="practice-item">
          <div class="pi-n">01</div>
          <h3 data-rad="practice.areas.0qjwi1i"><?php rad_html( 'practice.areas.0qjwi1i' ); ?></h3>
          <p data-rad="practice.areas.1caurm6"><?php rad_html( 'practice.areas.1caurm6' ); ?></p>
        </div>
        <div class="practice-item">
          <div class="pi-n">02</div>
          <h3 data-rad="practice.areas.0dnpvmn"><?php rad_html( 'practice.areas.0dnpvmn' ); ?></h3>
          <p data-rad="practice.areas.08kr0m0"><?php rad_html( 'practice.areas.08kr0m0' ); ?></p>
        </div>
        <div class="practice-item">
          <div class="pi-n">03</div>
          <h3 data-rad="practice.areas.1f4nnnq"><?php rad_html( 'practice.areas.1f4nnnq' ); ?></h3>
          <p data-rad="practice.areas.1mbwxhs"><?php rad_html( 'practice.areas.1mbwxhs' ); ?></p>
        </div>
        <div class="practice-item">
          <div class="pi-n">04</div>
          <h3 data-rad="practice.areas.1gdzum1"><?php rad_html( 'practice.areas.1gdzum1' ); ?></h3>
          <p data-rad="practice.areas.12xhmcx"><?php rad_html( 'practice.areas.12xhmcx' ); ?></p>
        </div>
      </div>

      <blockquote class="pullquote reveal" data-rad="practice.areas.01mvday"><?php rad_html( 'practice.areas.01mvday' ); ?></blockquote>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'practice.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3 data-rad="practice.other.1mx1n5x"><?php rad_html( 'practice.other.1mx1n5x' ); ?></h3>
        <div class="small" data-rad="practice.other.1cd2rqb"><?php rad_html( 'practice.other.1cd2rqb' ); ?></div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t" data-rad="practice.other.1ozkbpl"><?php rad_html( 'practice.other.1ozkbpl' ); ?></span><span class="d" data-rad="practice.other.0ng9skz"><?php rad_html( 'practice.other.0ng9skz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t" data-rad="practice.other.0jt4grq"><?php rad_html( 'practice.other.0jt4grq' ); ?></span><span class="d" data-rad="practice.other.10qyuye"><?php rad_html( 'practice.other.10qyuye' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'city' ) ); ?>"><span class="n">04</span><span class="t" data-rad="practice.other.0pa6spk"><?php rad_html( 'practice.other.0pa6spk' ); ?></span><span class="d" data-rad="practice.other.1ngcrxc"><?php rad_html( 'practice.other.1ngcrxc' ); ?></span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
