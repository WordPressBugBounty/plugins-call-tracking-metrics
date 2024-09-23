<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CTMOptions {

  public function __construct() {
    $this->ctm_host = "https://api.calltrackingmetrics.com";
    add_action('admin_init', array(&$this, 'init_plugin'));
    add_action('admin_menu', array(&$this, 'create_ctm_options'));
  }

  function init_plugin() {
    register_setting("call-tracking-metrics", "ctm_api_key");
    register_setting("call-tracking-metrics", "ctm_api_secret");
    register_setting("call-tracking-metrics", "ctm_api_active_key");
    register_setting("call-tracking-metrics", "ctm_api_active_secret");
    register_setting("call-tracking-metrics", "ctm_api_auth_account");
    register_setting("call-tracking-metrics", "call_track_account_script");
    register_setting("call-tracking-metrics", "ctm_api_dashboard_enabled");
    register_setting("call-tracking-metrics", "ctm_api_tracking_enabled");
    register_setting("call-tracking-metrics", "ctm_api_cf7_enabled");
    register_setting("call-tracking-metrics", "ctm_api_gf_enabled");
    register_setting("call-tracking-metrics", "ctm_api_cf7_logs");
    register_setting("call-tracking-metrics", "ctm_api_gf_logs");
  }

  function dashboard_enabled() {
    return get_option('ctm_api_dashboard_enabled', true);
  }
  function tracking_enabled() {
    return get_option('ctm_api_tracking_enabled', true);
  }
  function cf7_enabled() {
    return get_option('ctm_api_cf7_enabled', true);
  }
  function cf7_active() {
    return is_plugin_active('contact-form-7/wp-contact-form-7.php');
  }
  function gf_enabled() {
    return get_option('ctm_api_gf_enabled', true);
  }
  function gf_active() {
    return is_plugin_active('gravityforms/gravityforms.php');
  }
  
  function get_tracking_script() {
    if ( !$this->authorizing() || !$this->authorized() ) {
      if ( substr(get_option('call_track_account_script'),0,2) == '//' ) {
        $ctm_s = '<script data-cfasync="false" async src="' . get_option('call_track_account_script') . '"></script>';
      } else {
        $ctm_s = get_option('call_track_account_script');
      }
      return $ctm_s;
    } else {
      return '<script data-cfasync="false" async src="//' . get_option('ctm_api_auth_account') . '.tctm.co/t.js"></script>';
    }
  }

  function authorizing() {
    return ( get_option("ctm_api_key") && get_option("ctm_api_secret") );
  }
  function authorized() {
    $this->update_account();
    return ( get_option("ctm_api_auth_account") || !get_option("ctm_api_key") || !get_option("ctm_api_secret") );
  }
  function unauthorized_msg() {
    return 'Invalid credentials. Please check your <a target="_blank" href="https://app.calltrackingmetrics.com/accounts/edit#account-api">Account Settings</a> and try again.';
  }

  function update_account() {
    if ( get_option("ctm_api_auth_account") && ( get_option("ctm_api_key") == get_option("ctm_api_active_key") ) && ( get_option("ctm_api_secret") == get_option("ctm_api_active_secret") ) && $this->authorizing() ) {
      return;
    } else {
      update_option("ctm_api_active_key", get_option("ctm_api_key"));
      update_option("ctm_api_active_secret", get_option("ctm_api_secret"));
      update_option("ctm_api_auth_account", "");
      if (!$this->authorizing()) {
        return;
      } else {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->ctm_host . "/api/v1/accounts/current.json");
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Basic " . base64_encode(get_option('ctm_api_key') . ":" . get_option('ctm_api_secret'))));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        if (!is_bool($res)) {
          $data = json_decode($res, true);
          if ($data && isset($data["account"]))
            update_option("ctm_api_auth_account", $data["account"]);
        }
        curl_close($curl);
      }
    }
  }//update_account

  public function create_ctm_options() {
    if (current_user_can('manage_options')) {
      add_options_page(
        'CallTrackingMetrics', 
        'CallTrackingMetrics', 
        'administrator', 
        'call-tracking-metrics', 
        array(&$this,'create_ctm_settings_page')
      );
    }
  }

  function create_ctm_settings_page() {
    if ( !current_user_can('manage_options') ) { 
      wp_die( 'You do not have sufficient permissions to access this page.' ); 
    }
    $authorizing = $this->authorizing();
    $authorized = $this->authorized();   

?>
<div class="wrap">
    <?php
      if ( isset($_GET['savedata']) ) {
        if ($_GET['savedata'] == true) {
          echo '<div id="message" class="updated"><p>Settings saved</p></div>';
        }
      }
    ?>
  <a href="https://www.calltrackingmetrics.com" target="_blank" id="ctm-logo"></a>
  <form method="POST" name="ctmo" action="options-general.php?page=call-tracking-metrics&savedata=true">

    <?php wp_nonce_field( 'update_ctm_options_a', '_ctmo_nonce' ); ?>
    <input type="hidden" name="ctm___ous" id="ctm___ous" />
    <?php //settings_fields( 'call-tracking-metrics' ); ?>

    <div class="ctm-field">
      <h3>Account <small>enter your account details to use this plugin</small></h3>
      <?php if (!$authorizing) { ?>
      <div class="ctm-card">
        <p style="margin-bottom:5px">Enter your <b>CallTrackingMetrics account details</b> below to get started.</p>
        <p><b>Don't have an account?</b> <b><a target="_blank" href="https://calltrackingmetrics.com/plans">Sign up</a></b> now &mdash; it only takes a few minutes.</p>
      <?php } elseif (!$authorized) { ?>
      <div class="ctm-card" style="padding-top: 0">
        <div class="ctm-error"><span><?php echo $this->unauthorized_msg(); ?></span></div>
      <?php } else { ?>
      <div class="ctm-card">
      <?php } ?>
        <div class="ctm-field">
          <strong><label for="ctm_api_key"><?php _e("Access Key"); ?></label></strong><br/>
          <input class="regular-text" type="text" id="ctm_api_key" name="ctm_api_key" value="<?php echo get_option('ctm_api_key'); ?>" autocomplete="off"/>
        </div>
        <div class="ctm-field">
          <strong><label for="ctm_api_secret"><?php _e("Secret Key"); ?></label></strong><br/>
          <input class="regular-text" type="password" id="ctm_api_secret" name="ctm_api_secret" value="<?php echo get_option('ctm_api_secret'); ?>"/>
        </div>
        <span class="hint">These can found in the API Integration section of your <a target="_blank" href="https://app.calltrackingmetrics.com/accounts/edit#account-api">Account Settings</a>.</span>
        
        <footer>
          <input type="submit" class="ctm-button callout" value="<?php _e('Save Changes') ?>" />
        </footer>
      </div>
    </div>
    <div class="ctm-field"<?php if ($authorizing && $authorized) { ?> style="display:none"<?php } ?>>
      <h3>Tracking Code <small>Manually install the tracking code on this website</small></h3>
      <div class="ctm-card">
        <p>If you do not wish to use the API, you may manually enter your account's <a target="_blank" href="https://app.calltrackingmetrics.com/accounts/tracking_script_settings">tracking code</a> below.</p>
        <div class="ctm-field">
          <input type="text" id="call_track_account_script" class="regular-text" name="call_track_account_script" value="<?php if ( substr(get_option('call_track_account_script'),0,2) == '//' ) { echo htmlspecialchars( '<script async src="' . get_option('call_track_account_script') . '"></script>' ); } else { echo htmlspecialchars( get_option('call_track_account_script') ); } ?>" style="width:100%;text-align:center;padding:7px;font-size:13px;max-width:400px">
        </div>
        <footer>
          <input type="submit" class="ctm-button callout" value="<?php _e('Save Changes') ?>" />
        </footer>
      </div>
    </div>
    <div class="ctm-field"<?php if (!$authorizing || !$authorized) { ?> style="display:none"<?php } ?>>
      <h3>Settings <small>customize the behavior of this plugin</small></h3>
      <div class="ctm-card">
        <div class="ctm-field">
          <label><input type="checkbox" id="ctm_api_dashboard_enabled" name="ctm_api_dashboard_enabled" value='1' <?php echo checked(1, $this->dashboard_enabled(), false) ?>/>Show <b>call statistics</b> in the WordPress Dashboard</label>
          <span class="hint">Displays a simple widget in your <a href="/wp-admin/index.php">WordPress Dashboard</a> showing call volume by day for the last 30 days.</span>
        </div>
        <div class="ctm-field">
          <label><input type="checkbox" id="ctm_api_tracking_enabled" name="ctm_api_tracking_enabled" value='1' <?php echo checked(1, $this->tracking_enabled(), false) ?>/>Install the <b>tracking code</b> on this website automatically</label>
          <span class="hint">This code can also be found on your <a target="_blank" href="https://app.calltrackingmetrics.com/accounts/tracking_script_settings">Tracking Code</a> page.</span>
          <?php if ($authorizing && $authorized && get_option('ctm_api_auth_account')) { ?><input type="text" class="regular-text" value='<?php echo $this->get_tracking_script(); ?>' disabled style="width:100%;text-align:center;padding:7px;font-size:13px;max-width:400px"><?php } ?>
        </div>
        
        <footer>
          <input type="submit" class="ctm-button callout" value="<?php _e('Save Changes') ?>" />
        </footer>
      </div>
    </div>
    <div class="ctm-field"<?php if (!$authorizing || !$authorized) { ?> style="display:none"<?php } ?>>
      <h3>Integrations <small>send your WordPress forms into CallTrackingMetrics automatically</small></h3>
      <div class="ctm-card">
        <p>These integrations do not require <i>any extra setup</i> &mdash; simply create a form with a phone number field, <b>submit the form at least once</b>, and <a href="https://app.calltrackingmetrics.com/form_reactors">a FormReactor will appear in your CallTrackingMetrics account</a> automatically so you can customize how to react to form submissions.</p>
        <div class="ctm-field">
          <label><input type="checkbox" id="ctm_api_cf7_enabled" name="ctm_api_cf7_enabled" value='1' <?php echo checked(1, $this->cf7_enabled(), false) ?>/>Enable <b>Contact Form 7</b> integration</label>
          <span class="hint">Contact Form 7 uses a simple markup structure to embed forms anywhere on your WordPress website.</span>
          <span class="hint"><?php if (!$this->cf7_active()) { ?><a class="ctm-btn" href="https://wordpress.org/plugins/contact-form-7/">Install</a><?php } else { ?><a class="ctm-btn" href="/wp-admin/admin.php?page=wpcf7">Settings</a> <a class="ctm-btn" href="https://wordpress.org/plugins/contact-form-7/">Website</a><?php } ?> <a class="ctm-btn" href="http://contactform7.com/support/">Support</a><?php if ($this->cf7_active()) { ?> <a class="ctm-btn" id="ctm-cf7-logs-btn" href="#">Logs <span>&#9662;</span></a><?php } ?></span>
        </div>
      <?php if ( $this->cf7_enabled() ) : ?>
        <div class="ctm-field">
          <div class="notice notice-info is-dismissible notice-info-grey"><p>Note: It is required to use a form that captures a telephone number (input type="tel") in order for Contact Form 7 to integrate properly with our FormReactor. For more information, see <a href="https://www.calltrackingmetrics.com/help/1545745-using-the-wordpress-plugin/" target="_blank">Using the CallTrackingMetrics WordPress Plugin</a></p></div>
        </div>
        <div class="ctm-field">
          <div class="notice notice-info is-dismissible notice-info-grey"><p>Note: If you will request international (non-U.S.) phone numbers with your Contact Form 7 forms, we recommend using the plugin <a href="https://wordpress.org/plugins/international-telephone-input-for-contact-form-7/" target="_blank">International Telephone Input for Contact Form 7</a> to avoid possible formatting issues with our FormReactor. Both [tel] and [intl_tel] are now supported as phone inputs.</p></div>
        </div>
      <?php endif; ?>
        <div class="ctm-field" id="ctm-cf7-logs-list" style="display:none">
          <div class="ctm-list" data-logs="<?php echo htmlspecialchars(get_option("ctm_api_cf7_logs")) ?>"></div>
        </div>
        <div class="ctm-field">
          <label><input type="checkbox" id="ctm_api_gf_enabled" name="ctm_api_gf_enabled" value='1' <?php echo checked(1, $this->gf_enabled(), false) ?>/>Enable <b>Gravity Forms</b> integration</label>
          <span class="hint">Gravity Forms are created using a drag-and-drop editor with support for over 30 input types.</span>
          <span class="hint"><?php if (!$this->gf_active()) { ?><a class="ctm-btn" href="http://www.gravityforms.com/">Install</a><?php } else { ?><a class="ctm-btn" href="/wp-admin/admin.php?page=gf_settings">Settings</a> <a class="ctm-btn" href="http://www.gravityforms.com/">Website</a><?php } ?> <a class="ctm-btn" href="https://www.gravityhelp.com/support/">Support</a><?php if ($this->gf_active()) { ?> <a class="ctm-btn" id="ctm-gf-logs-btn" href="#">Logs <span>&#9662;</span></a><?php } ?></span>
        </div>
      <?php if ( $this->gf_enabled() ) : ?>
        <div class="ctm-field">
          <div class="notice notice-info is-dismissible notice-info-grey"><p>Note: It is required to use a form that captures a telephone number (input type="tel") in order for Gravity Forms to integrate properly with our FormReactor. For more information, see <a href="https://www.calltrackingmetrics.com/help/1545745-using-the-wordpress-plugin/" target="_blank">Using the CallTrackingMetrics WordPress Plugin</a></p></div>
        </div>
      <?php endif; ?>
        <div class="ctm-field" id="ctm-gf-logs-list" style="display:none">
          <div class="ctm-list" data-logs="<?php echo htmlspecialchars(get_option("ctm_api_gf_logs")) ?>"></div>
        </div>
        <footer>
          <input type="submit" class="ctm-button callout" value="<?php _e('Save Changes') ?>" />
        </footer>
        
        <script>
          function install_logs(log_btn, log_list) {
            var btn = document.getElementById(log_btn);
            var list = document.getElementById(log_list);
            var list_elem = list.getElementsByClassName("ctm-list")[0];
            
            var logs = JSON.parse(list_elem.getAttribute("data-logs") || "[]");
            for (var i = logs.length - 1; i >= 0; i--) {
              var row = document.createElement("div");
              row.className = "ctm-row";
              row.textContent = logs[i].message;
              row.innerHTML = row.innerHTML + "<span class='ctm-date'>" + logs[i].date + "</span>";
              list_elem.appendChild(row);
            }
            
            btn.addEventListener("click", function(e) {
              e.preventDefault();
              
              if (list.style.display == "none") {
                list.style.display = "block";
                btn.getElementsByTagName("span")[0].innerHTML = "&#x00D7;";
              } else {
                list.style.display = "none";
                btn.getElementsByTagName("span")[0].innerHTML = "&#9662;";
              }
            });
          }
          install_logs("ctm-cf7-logs-btn", "ctm-cf7-logs-list");
          install_logs("ctm-gf-logs-btn", "ctm-gf-logs-list");
        </script>
      </div>
    </div>
  </form>
</div>
<?php
  }

  function update_ctm_options() {
    if ( !current_user_can('manage_options') ) { 
      wp_die( 'You do not have sufficient permissions to access this page.' ); 
    }

    if ( check_admin_referer( 'update_ctm_options_a', '_ctmo_nonce' ) ) {

      $ctm_api_key = '';
      if ( isset($_POST['ctm_api_key']) ) {
        $ctm_api_key = sanitize_text_field($_POST['ctm_api_key']);
      }
      update_option('ctm_api_key', $ctm_api_key);

      $ctm_api_secret = '';
      if ( isset($_POST['ctm_api_secret']) ) {
        $ctm_api_secret = sanitize_text_field($_POST['ctm_api_secret']);
      }
      update_option('ctm_api_secret', $ctm_api_secret);

      $call_track_account_script = '';
      if ( isset($_POST['call_track_account_script']) ) {
        $escdl = strlen(htmlspecialchars($_POST['call_track_account_script']));
        if ( (strpos($_POST['call_track_account_script'], 'tctm.co') !== false) && ($escdl < 200) ) { 
          $ctas = str_replace('\"', '"', $_POST['call_track_account_script']);
          $matches = preg_match('/(src=["\'](.*?)["\'])/', $ctas, $match);
          if ($matches) { 
            $split = preg_split('/["\']/', $match[0]);
            $src = $split[1];
            $call_track_account_script = $src;
          }
        }
      }
      update_option('call_track_account_script', $call_track_account_script);

      $ctm_api_dashboard_enabled = '';
      if ( isset($_POST['ctm_api_dashboard_enabled']) ) {
        $ctm_api_dashboard_enabled = intval($_POST['ctm_api_dashboard_enabled']) ?: '';
      }
      update_option('ctm_api_dashboard_enabled', $ctm_api_dashboard_enabled);

      $ctm_api_tracking_enabled = '';
      if ( isset($_POST['ctm_api_tracking_enabled']) ) {
        $ctm_api_tracking_enabled = intval($_POST['ctm_api_tracking_enabled']) ?: '';
      }
      update_option('ctm_api_tracking_enabled', $ctm_api_tracking_enabled);

      $ctm_api_cf7_enabled = '';
      if ( isset($_POST['ctm_api_cf7_enabled']) ) {
        $ctm_api_cf7_enabled = intval($_POST['ctm_api_cf7_enabled']) ?: '';
      }
      update_option('ctm_api_cf7_enabled', $ctm_api_cf7_enabled);

      $ctm_api_gf_enabled = '';
      if ( isset($_POST['ctm_api_gf_enabled']) ) {
        $ctm_api_gf_enabled = intval($_POST['ctm_api_gf_enabled']) ?: '';
      }
      update_option('ctm_api_gf_enabled', $ctm_api_gf_enabled);

    } 
  }//update_ctm_options

}//CTMOptions

$create__ctmoptions = new CTMOptions();
if ( isset($create__ctmoptions) ) : 
  if ( ($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['ctm___ous'])) ) {
    add_action( 'admin_init', array( $create__ctmoptions, 'update_ctm_options' ) );
  }
endif;
