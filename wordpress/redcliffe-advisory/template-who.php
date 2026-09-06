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
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>Who&rsquo;s Who</span></div>
      <h1><?php rad_html( 'who.hero.title' ); ?></h1>
      <p class="page-lede">CEO of Redcliffe Advisory, FCSI (Hon.). Champion of the City of London. Benign disruptor. Connecting the worlds of Finance, Deep Tech and Defence.</p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'who.profile' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="profile reveal">
        <aside class="profile-portrait">
          <div class="photo"><img src="<?php echo esc_url( rad_image_url( 'who.hero.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'who.hero.photo' ) ); ?>" /></div>
          <div class="photo-cap"><span>Karina Robinson</span><span>London · 2026</span></div>
          <div class="roles">
            <h4>Currently</h4>
            <ul>
              <li><span class="k">CEO</span><span class="v">Redcliffe Advisory</span></li>
              <li><span class="k">Founder</span><span class="v">The City Quantum &amp; AI Summit</span></li>
              <li><span class="k">Sr. Advisor</span><span class="v">Multiverse Computing SL</span></li>
              <li><span class="k">NED</span><span class="v">Atlanti — Anglo-Swiss fund management</span></li>
              <li><span class="k">Judge</span><span class="v">EIC Accelerator</span></li>
              <li><span class="k">Exec. Advisor</span><span class="v">Global India Business Corridor (GIBC&nbsp;UK)</span></li>
              <li><span class="k">Advisor</span><span class="v">MissionLink</span></li>
              <li><span class="k">Emeritus Gov.</span><span class="v">London School of Economics</span></li>
            </ul>
          </div>
        </aside>

        <div class="profile-body">
          <div class="role-tag">CEO · Redcliffe Advisory · FCSI (Hon.)</div>
          <p class="lede">Karina connects the worlds of Finance, Deep Tech and Defence — with a deep belief in the power of sensible <em>Diversity &amp; Inclusion</em>.</p>

          <h3 class="cv-h">Quantum &amp; Deep Tech</h3>
          <div class="cv-list">
            <div class="cv-entry"><div class="cv-org">Multiverse Computing SL</div><div class="cv-desc"><span class="cv-role">Senior Advisor.</span> Europe&rsquo;s largest Quantum &amp; AI software firm. Winner of Europe&rsquo;s Future Unicorn Award 2024.</div></div>
            <div class="cv-entry"><div class="cv-org">The City Quantum &amp; AI Summit</div><div class="cv-desc"><span class="cv-role">Founder.</span> Now in its Sixth Anniversary year.</div></div>
            <div class="cv-entry"><div class="cv-org">UKQuantum</div><div class="cv-desc"><span class="cv-role">Member.</span> Working Group on International Cooperation and Trade.</div></div>
            <div class="cv-entry"><div class="cv-org">NATO</div><div class="cv-desc"><span class="cv-role">Contributor.</span> Involved in brainstorming its Quantum Strategy.</div></div>
            <div class="cv-entry"><div class="cv-org">EIC Accelerator</div><div class="cv-desc"><span class="cv-role">Judge.</span> Targeting funds at promising Deep Tech in critical fields like space.</div></div>
            <div class="cv-entry"><div class="cv-org">MissionLink &amp; The Entrepreneurs Network</div><div class="cv-desc"><span class="cv-role">Advisor.</span></div></div>
            <div class="cv-entry"><div class="cv-org">Global India Business Corridor (GIBC&nbsp;UK)</div><div class="cv-desc"><span class="cv-role">Executive Advisor to the Board.</span> Deepening trade and investment between the UK and India.</div></div>
          </div>

          <h3 class="cv-h">The City of London</h3>
          <div class="cv-list">
            <div class="cv-entry"><div class="cv-org">The Lord Mayor&rsquo;s Appeal</div><div class="cv-desc"><span class="cv-role">Chair, Advisory Board.</span> Former Trustee.</div></div>
            <div class="cv-entry"><div class="cv-org">Worshipful Company of International Bankers</div><div class="cv-desc"><span class="cv-role">Past Master.</span></div></div>
            <div class="cv-entry"><div class="cv-org">CISI</div><div class="cv-desc"><span class="cv-role">Honorary Fellow.</span></div></div>
            <div class="cv-entry"><div class="cv-org">Atlanti</div><div class="cv-desc"><span class="cv-role">Non-Executive Director.</span></div></div>
            <div class="cv-entry"><div class="cv-org">London School of Economics</div><div class="cv-desc"><span class="cv-role">Emeritus Governor.</span> Co-Founder of The Inclusion Initiative.</div></div>
          </div>

          <h3 class="cv-h">Earlier career</h3>
          <div class="cv-list">
            <div class="cv-entry"><div class="cv-org">Robinson Hambro</div><div class="cv-desc"><span class="cv-role">Co-Founded with City legend Rupert Hambro CBE.</span> Ran the firm for over a decade.</div></div>
            <div class="cv-entry"><div class="cv-org">Cambridge Quantum</div><div class="cv-desc"><span class="cv-role">Senior Advisor.</span></div></div>
            <div class="cv-entry"><div class="cv-org">Journalism</div><div class="cv-desc"><span class="cv-role">Senior Editor, The Banker.</span> Banking columnist for the International Herald Tribune; correspondent at Bloomberg.</div></div>
            <div class="cv-entry"><div class="cv-org">Morgan Grenfell</div><div class="cv-desc"><span class="cv-role">Began her career at the merchant bank.</span></div></div>
          </div>

          <p class="coda">Educated in Madrid, at the Hotchkiss School in the US, and at the London School of Economics. Fluent in four languages.</p>
          <div class="contact"><a href="<?php echo esc_url( rad_url( 'contact' ) ); ?>">karina.robinson@redcliffeadvisory.com</a></div>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'who.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3>Other <em>rooms</em></h3>
        <div class="small">Continue</div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t">The City Quantum &amp; AI <em>Summit</em></span><span class="d">7 October 2026 — Mansion House.</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t">Chair &amp; CEO <em>Counsel</em></span><span class="d">Counsel for Chairs and Chief Executives</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><span class="n">05</span><span class="t">Articles &amp; <em>long reads</em></span><span class="d">Reports and essays under the Redcliffe Advisory name</span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
