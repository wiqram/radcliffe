<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: The City of London
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'city.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>The City of London</span></div>
      <h1><?php rad_html( 'city.hero.title' ); ?></h1>
      <div class="hero-subhead">A native</div>
      <p class="page-lede">A place with centuries-old history at the cutting edge of technology — where the City of London meets the laboratory.</p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'city.intro' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside">The Square Mile</div>
        <div class="prose-body">
          <p class="lede">Champion of the City of London. Benign Disruptor.</p>
          <p>The City of London has always been a confederation — of livery companies, regulators, central banks, insurance markets, and increasingly of the laboratories and venture houses pulling advanced computation into financial life. Redcliffe Advisory operates inside this ecosystem with the fluency that only a native can bring.</p>
          <p>She chaired the <em>Lord Mayor&rsquo;s Appeal Advisory Board</em>, is a Past Master of the <em>Worshipful Company of International Bankers</em>, and an Honorary Fellow of the <em>CISI</em>. The City Quantum &amp; AI Summit is held each year at the Mansion House, home to Lord &amp; Lady Mayors of the City of London.</p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'city.quantum' ) ) : ?>
<section class="section on-navy">
    <div class="container">
      <div class="section-head reveal">
        <div class="label">Our Quantum Future</div>
        <h2>The next sovereignty<br />will be <em>computed</em></h2>
      </div>
      <div class="summit-feature" style="border-color:var(--rule-d);">
        <div class="text-side" style="padding-right:56px;">
          <p style="font-family:var(--serif);font-size:19px;line-height:1.6;color:var(--cream);max-width:46ch;">Quantum and AI are not adjacent technologies but converging instruments of national capability — reorganising financial services, dual-use defence, pharma and the basic geometry of trust between states.</p>
          <p style="font-family:var(--serif);font-size:19px;line-height:1.6;color:rgba(246,248,251,0.82);max-width:46ch;">Redcliffe Advisory sits between the laboratories doing the work and the institutions who will depend on it — leveraging the UK&rsquo;s position as home to the second-largest number of quantum start-ups in the world.</p>
        </div>
        <div class="photo-side" style="border-left-color:var(--rule-d);background:#06101F;">
          <img src="<?php echo esc_url( rad_image_url( 'city.feature.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'city.feature.photo' ) ); ?>" style="object-position:center 30%;" />
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'city.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3>Other <em>rooms</em></h3>
        <div class="small">Continue</div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t">The City Quantum &amp; AI <em>Summit</em></span><span class="d">7 October 2026 — Mansion House.</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t">Who&rsquo;s Who — <em>Karina Robinson</em></span><span class="d">Founder of The City Quantum &amp; AI Summit; champion of the City of London</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t">Chair &amp; CEO <em>Counsel</em></span><span class="d">Counsel for Chairs and Chief Executives</span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php get_footer(); ?>
