<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Who’s Who
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'who.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="who.hero.1nfzvhp"><?php rad_html( 'who.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="who.hero.06kle1y"><?php rad_html( 'who.hero.06kle1y' ); ?></span></div> <h1 data-rad="who.hero.title"><?php rad_html( 'who.hero.title' ); ?></h1> <p class="page-lede" data-rad="who.hero.0hxd4qm"><?php rad_html( 'who.hero.0hxd4qm' ); ?></p> </div> </section>
<?php endif; ?> <?php if ( rad_section_enabled( 'who.profile' ) ) : ?>
<section class="section"> <div class="container"> <div class="profile reveal"> <aside class="profile-portrait"> <div class="photo"><img src="<?php echo esc_url( rad_image_url( 'who.hero.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'who.hero.photo' ) ); ?>" data-rad-img="who.hero.photo" /></div> <div class="photo-cap"><span data-rad="who.profile.167aimu"><?php rad_html( 'who.profile.167aimu' ); ?></span><span data-rad="who.profile.0rb0bjv"><?php rad_html( 'who.profile.0rb0bjv' ); ?></span></div> <div class="roles"> <h4 data-rad="who.profile.0v1u3by"><?php rad_html( 'who.profile.0v1u3by' ); ?></h4> <ul data-rad-list="who.currently"> <?php rad_list( 'who.currently' ); ?> </ul> </div> </aside> <div class="profile-body"> <div class="role-tag" data-rad="who.profile.0qaodcf"><?php rad_html( 'who.profile.0qaodcf' ); ?></div> <p class="lede" data-rad="who.profile.16ta0o5"><?php rad_html( 'who.profile.16ta0o5' ); ?></p> <h3 class="cv-h" data-rad="who.profile.093jaiy"><?php rad_html( 'who.profile.093jaiy' ); ?></h3> <div class="cv-list" data-rad-list="who.quantum"> <?php rad_list( 'who.quantum' ); ?> </div> <h3 class="cv-h" data-rad="who.profile.16oxrst"><?php rad_html( 'who.profile.16oxrst' ); ?></h3> <div class="cv-list" data-rad-list="who.city"> <?php rad_list( 'who.city' ); ?> </div> <h3 class="cv-h" data-rad="who.profile.1jzrthg"><?php rad_html( 'who.profile.1jzrthg' ); ?></h3> <div class="cv-list" data-rad-list="who.earlier"> <?php rad_list( 'who.earlier' ); ?> </div> <p class="coda" data-rad="who.profile.0c0179p"><?php rad_html( 'who.profile.0c0179p' ); ?></p> <div class="contact"><a href="<?php echo esc_url( rad_url( 'contact' ) ); ?>" data-rad="who.profile.02nxgh6"><?php rad_html( 'who.profile.02nxgh6' ); ?></a></div>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'who.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3 data-rad="who.other.1mx1n5x"><?php rad_html( 'who.other.1mx1n5x' ); ?></h3>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t" data-rad="who.other.0jt4grq"><?php rad_html( 'who.other.0jt4grq' ); ?></span><span class="d" data-rad="who.other.10qyuye"><?php rad_html( 'who.other.10qyuye' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t" data-rad="who.other.0011heh"><?php rad_html( 'who.other.0011heh' ); ?></span><span class="d" data-rad="who.other.0br3zvz"><?php rad_html( 'who.other.0br3zvz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><span class="n">05</span><span class="t" data-rad="who.other.0dfz3lc"><?php rad_html( 'who.other.0dfz3lc' ); ?></span><span class="d" data-rad="who.other.0t0wkvl"><?php rad_html( 'who.other.0t0wkvl' ); ?></span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
