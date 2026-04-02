<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Cinema_Theme
 */

?>

        </div>
      </div>
    </main>

    <footer class="section-outer section-outer--site-footer">
      <div class="section section--site-footer">
        <div class="section-inner section-inner--site-footer">

          <div class="region region__footer-first">
            <div class="region-inner columns">

            <div class="column column--first">
              <div class="title-area title-area--footer">
                <a class="title-grid title-grid--footer" href="/">              
                  <div class="logo"></div>
                  <p class="site-title" aria-hidden="true"><span class="article">The</span> Grand Illusion</p>
                  <!-- <a href="/" class="home-link">The Grand Illusion: Seattle's oldest continuously running movie theater</a> -->
                </a>
              </div>
            </div>

            </div>
          </div>

          <div class="region region__footer-second">
            <div class="region-inner region-inner__footer-second columns">

              <div class="column column--first">
                <div class="email">
                  <h2>Email</h2>
                  <p>
                    <a href="mailto:info@grandillusioncinema.org">info@grandillusioncinema.org</a>
                  </p>
                </div>
                <div class="email">
                  <h2>Rentals</h2>
                  <p>
                    <a href="mailto:rentals@grandillusioncinema.org">rentals@grandillusioncinema.org</a>
                  </p>
                </div>
                <div class="mail">
                  <h2>Mail</h2>
                  <p>
                    4730 University Way NE #1330<br>
                    Seattle, WA 98105
                  </p>
                </div>
              </div>

              <div class="column column--third column--last">
              <div class="newsletter-form-wrapper">

                <!-- Begin MailChimp Signup Form -->
                <div id="mc_embed_signup">
                  <form action="https://grandillusioncinema.us2.list-manage.com/subscribe/post?u=c68e502f1bdccca389af3a3a8&amp;id=5baa7ea5de" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">
                    <h2><label for="mce-EMAIL">Subscribe to our mailing list</label></h2>
                    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
                    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                  </form>
                </div>
                <!--End mc_embed_signup-->

                <p>
                  Join our email community to get the latest
                  news from the Grand Illusion.
                </p>

              </div>
            </div>

            </div>
          </div>

          <div class="region region__footer-third">
            <div class="region-inner">
              <?php echo "&copy; " . date("Y") . " The Grand Illusion Cinema"; ?>
            </div>
          </div>

        </div>
      </div>
    </footer>
  </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
