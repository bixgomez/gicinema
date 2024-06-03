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
    </section>

    <footer class="section-outer section-outer--site-footer">
      <div class="section section--site-footer">
        <div class="section-inner section-inner--site-footer">

          <div class="region region__footer-first">
            <div class="region-inner columns">

            <div class="column column--first">
              <div class="title-area title-area--footer">
                <h1 class="site-title"><span class="article">The</span> Grand Illusion</h1>
                <h2 class="site-subtitle">
                  <span class="line-1">Seattle's oldest continuously</span>
                  <span class="line-2">running movie theater</span>
                </h2>
                <a href="/" class="home-link">The Grand Illusion: Seattle's oldest continuously running movie theater</a>
              </div>
            </div>

            <div class="column column--second">
              <div class="footer-menus">

              </div>
            </div>

            </div>
          </div>

          <div class="region region__footer-second">
            <div class="region-inner region-inner__footer-second columns">

              <div class="column column--first">
                <div class="location">
                  <h4>Location</h4>
                  <p>
                    1403 NE 50th St.<br>
                    Seattle, WA 98105<br>
                    206.523.3935
                  </p>
                </div>
              </div>

              <div class="column column--second">
                <div class="email">
                  <h4>Email</h4>
                  <p>
                    <a href="mailto:info@grandillusioncinema.org" target="_blank">info@grandillusioncinema.org</a>
                  </p>
                </div>
                <div class="email">
                  <h4>Rentals</h4>
                  <p>
                    <a href="mailto:rentals@grandillusioncinema.org" target="_blank">rentals@grandillusioncinema.org</a>
                  </p>
                </div>
                <div class="mail">
                  <h4>Mail</h4>
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
                  <form action="http://grandillusioncinema.us2.list-manage.com/subscribe/post?u=c68e502f1bdccca389af3a3a8&amp;id=5baa7ea5de" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">
                    <label for="mce-EMAIL"><h4>Subscribe to our mailing list</h4></label>
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
