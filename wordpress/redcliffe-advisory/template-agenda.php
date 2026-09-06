<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Agenda
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'agenda.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><a href="<?php echo esc_url( rad_url( 'summit' ) ); ?>">Summit</a><span class="sep">·</span><span>Agenda</span></div>
      <h1><?php rad_html( 'agenda.hero.title' ); ?></h1>
      <div class="hero-subhead">A day for world-changing technology and the City of London</div>
      <p class="page-lede">The City Quantum &amp; AI Summit unfolds across a single day at the Mansion House. The shape of the day is set out below; the full speaker line-up is confirmed closer to the date.</p>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'agenda.programme' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="agenda-intro reveal">
        <div class="ai"><div class="k">Date</div><div class="v">7 October <em>2026</em></div></div>
        <div class="ai"><div class="k">Venue</div><div class="v">Mansion House, <em>EC4</em></div></div>
        <div class="ai"><div class="k">Theme</div><div class="v"><em>Connections in Chaos</em></div></div>
      </div>

      <div class="agenda-block reveal">
        <div class="ab-head"><span class="ph">Morning</span><span class="ph-line" aria-hidden="true"></span></div>
        <div class="agenda-row">
          <div class="at">09.00</div>
          <div><div class="as-title">Registration &amp; coffee</div><div class="as-desc">Doors open in the Egyptian Hall.</div></div>
        </div>
        <div class="agenda-row feature">
          <div class="at">09.30</div>
          <div><div class="as-title">Welcome &amp; <em>opening address</em></div><div class="as-desc">Karina Robinson, Founder of The City Quantum &amp; AI Summit, sets the theme for the day.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">09.45</div>
          <div><div class="as-title">Keynote — the state of quantum &amp; AI</div><div class="as-desc">Where the technology actually stands, in plain language.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">10.30</div>
          <div><div class="as-title">Panel — quantum, AI &amp; the future of financial services</div><div class="as-desc">Bankers, investors and scientists on deriving value today and tomorrow.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">11.15</div>
          <div><div class="as-title">Break</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">11.30</div>
          <div><div class="as-title">Panel — dual-use defence &amp; national capability</div><div class="as-desc">Frontier computation as an instrument of sovereign strength.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">12.15</div>
          <div><div class="as-title">In conversation — frontier science &amp; sovereign capital</div><div class="as-desc">The laboratories doing the work, and the institutions that will depend on it.</div></div>
        </div>
      </div>

      <div class="agenda-block reveal">
        <div class="ab-head"><span class="ph">Lunch</span><span class="ph-line" aria-hidden="true"></span></div>
        <div class="agenda-row feature">
          <div class="at">13.00</div>
          <div><div class="as-title">Lunch — the <em>Egyptian Hall</em></div><div class="as-desc">By placement, on the floor of the Mansion House.</div></div>
        </div>
      </div>

      <div class="agenda-block reveal">
        <div class="ab-head"><span class="ph">Afternoon</span><span class="ph-line" aria-hidden="true"></span></div>
        <div class="agenda-row">
          <div class="at">14.00</div>
          <div><div class="as-title">Panel — AI, pharma &amp; the bio-adjacent industries</div><div class="as-desc">The sectors the technology is quietly remaking.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">14.45</div>
          <div><div class="as-title">In conversation — the City of London &amp; the laboratory</div><div class="as-desc">Where the Square Mile meets advanced computation.</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">15.30</div>
          <div><div class="as-title">Break</div></div>
        </div>
        <div class="agenda-row">
          <div class="at">15.45</div>
          <div><div class="as-title">Panel — trust, ethics &amp; sensible D&amp;I in frontier technology</div><div class="as-desc">A fairer world with improved business outcomes.</div></div>
        </div>
        <div class="agenda-row feature">
          <div class="at">16.30</div>
          <div><div class="as-title">Closing <em>address</em></div></div>
        </div>
      </div>

      <div class="agenda-block reveal">
        <div class="ab-head"><span class="ph">Evening</span><span class="ph-line" aria-hidden="true"></span></div>
        <div class="agenda-row">
          <div class="at">17.00</div>
          <div><div class="as-title">Drinks reception</div></div>
        </div>
        <div class="agenda-row feature">
          <div class="at">19.00</div>
          <div><div class="as-title">Summit <em>dinner</em></div><div class="as-desc">By placement, in the Egyptian Hall.</div></div>
        </div>
      </div>

      <div class="agenda-note reveal">Our promise to you — no lingo, no jargon. Clear language for all non-scientists, panels that strive for gender balance, and pricing kept accessible. The programme above is the shape of the day; timings and speakers are confirmed nearer the date.</div>

      <div class="cta-row" style="margin-top:36px;">
        <a class="btn" href="<?php echo esc_url( rad_url( 'contact' ) ); ?>"><span>Enquire about attending</span><span class="arr">→</span></a>
        <a class="btn-link" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span>Back to the Summit</span><span class="arr">↗</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
