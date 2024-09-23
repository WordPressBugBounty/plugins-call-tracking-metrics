<?php
if ( ! defined( 'ABSPATH' ) ) exit;

include_once(ABSPATH.'wp-admin/includes/plugin.php');

class CallTrackingMetrics {

  public function __construct() {
    $this->ctm_host = "https://api.calltrackingmetrics.com";
    if ( get_option("ctm_api_tracking_enabled") || !$this->authorized() ) {
      add_action('wp_head', array(&$this, 'print_tracking_script'), 10);
    }
    add_action('init', array(&$this, 'form_init'));
    add_action('admin_menu', array(&$this, 'attach_ctm_dashboard'));
    if ( get_option('ctm_api_gf_enabled', true) ) {
      add_filter('gform_confirmation', array(&$this, 'gf_confirmation'), 10, 1);
    }
    if ( get_option('ctm_api_cf7_enabled', true) ) {
      add_action('wp_footer', array(&$this, 'cf7_confirmation'), 10, 1);
    }
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

  function form_init() {
    if ( ($this->cf7_enabled()) && ($this->cf7_active()) ) {
      add_action('wpcf7_before_send_mail', array(&$this, 'submit_cf7'), 10, 2);
    }
    if ( ($this->gf_enabled()) && ($this->gf_active()) ) {
      add_action('gform_after_submission', array(&$this, 'submit_gf'), 10, 2);
    }
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
  function print_tracking_script() {
    if ( !is_admin() ) {
      echo $this->get_tracking_script();
    }
  }

  function cf7_confirmation() {
?>
<script type='text/javascript'>
document.addEventListener( 'wpcf7mailsent', function( event ) {
  try { __ctm.tracker.trackEvent("", " ", "form"); __ctm.tracker.popQueue(); } catch(e) { console.log(e); }
}, false );
</script>
<?php
  }
  
  /**
	 * Send the CF7 submission to CTM.
	 *
	 * @param WPCF7_ContactForm $contact_form The current contact form.
	 * @param bool              $abort        Whether to abort the sending or not.
	 */
  function submit_cf7($form, &$abort) {
    // If the submission is due to be aborted, don't continue with the CTM submission.
		if ( true === $abort ) {
			return;
		}

    $this->cf7_log("Submitting...");
    
    $title = $form->title();
    $entry = $form->form_scan_shortcode();
    $dataObject = WPCF7_Submission::get_instance();
    $data  = $dataObject->get_posted_data();
    $form_id = 'wpcf7-'.$dataObject->get_contact_form()->id();
    
    $fields = array();
    $labels = array();
    $sublabels = array();
    
    foreach ($entry as $field) {
      if ($field["basetype"] == "tel") {
        $phone = $data[$field["name"]];
      } elseif ($field["basetype"] == "intl_tel") {
        $intl_check = $data[$field["name"]];
        $intl_regex = "/^\+(?:[0-9]?){6,14}[0-9]$/";
        if (preg_match($intl_regex, $intl_check)) {
          $phone = $intl_check;
        }
      } elseif ($field["basetype"] == "email") {
        $email = $data[$field["name"]];
      } elseif ($field["name"] == "your-name") {
        $name = $data[$field["name"]];
      } elseif (in_array($field["basetype"], array("checkbox", "radio"))) {
        $fields[$field["name"]] = $data[$field["name"]];
      } elseif ($field["basetype"] == "quiz") {
        $hash = $data["_wpcf7_quiz_answer_" . $field["name"]];
        foreach ($field["raw_values"] as $answer) {
          $answer_pos = strpos($answer, "|");
          if ($answer_pos !== false) {
            if ($hash == wp_hash(wpcf7_canonicalize(substr($answer, $answer_pos + 1)), 'wpcf7_quiz')) {
              $fields[$field["name"]] = $data[$field["name"]];
              $labels[$field["name"]] = substr($answer, 0, $answer_pos);
              break;
            }
          }
        }
      } elseif ($field["name"] != "" && in_array($field["basetype"], array("text", "textarea", "select", "url", "number", "date"))) {
        $fields[$field["name"]] = $data[$field["name"]];
      }
    }
    
    $this->cf7_log("Title: " . $title . ", Name: " . $name . ", Phone: " . $phone . ", Email: " . $email);

    $fr_data = array(
      "type" => "Contact Form 7",
      "id" => $form_id,
      "title" => $title,
      "name" => $name,
      "phone" => $phone,
      "email" => $email,
      "fields" => $fields,
      "labels" => $labels,
      "sublabels" => $sublabels
    );

   /**
		 * Allow other plugins to programmatically add/remove FormReactor data.
		 *
		 * @param array              $fr_data The current form reactor fields.
		 * @param \WPCF7_ContactForm $form    The current Contact Form 7 instance.
		 *
		 * @return array
		 */
		$fr_data = apply_filters( 'ctm_cf7_formreactor_data', $fr_data, $form );
 
    $this->send_formreactor($fr_data);
  }

  function gf_confirmation($confirmation) {
    if (is_array($confirmation)) {
      if ($confirmation["redirect"]) {
        $code = "window.location = \"" . $confirmation["redirect"] . "\";";
        $confirmation = "";
      } else {
        return $confirmation;
      }
    } elseif (strpos($confirmation, "<script") !== false && strpos($confirmation, "function gf_ctm_redirect(") !== false) {
      $code = "gf_ctm_redirect_old();";
    } else {
      $code = "";
    }
    
    // parse out the account ID from the tracking script using regex
    // in case they used their own custom embed code rather than authenticating through the API
    if (preg_match('/(\d+).tctm.co\/t.js/', $this->get_tracking_script(), $matches) == 1) {
      $tracker = $matches[1];
    } else {
      $this->gf_log("WARNING: Tracking code is missing in the plugin.");
    }
    
    // all one line so WP doesn't insert <p> tags around the code and break it
    return $confirmation . "<script>(function() { if (window.location != window.parent.location) { var tracker = document.createElement('script'); tracker.setAttribute('src', '//$tracker.tctm.co/t.js'); document.head.appendChild(tracker); } __ctm_http_requests = []; (function(open) { XMLHttpRequest.prototype.open = function() { __ctm_http_requests.push(this); this.addEventListener(\"readystatechange\", function() { if (this.readyState == 4) { var index = __ctm_http_requests.indexOf(this); if (index > -1) __ctm_http_requests.splice(index, 1); } }, false); open.apply(this, arguments); }; })(XMLHttpRequest.prototype.open); window.__ctm_loaded = window.__ctm_loaded || []; window.__ctm_loaded.push(function() { try { __ctm.tracker.trackEvent('', ' ', 'form'); __ctm.tracker.popQueue(); } catch(e) {} if (typeof gf_ctm_redirect != 'undefined') { gf_ctm_redirect_old = gf_ctm_redirect; gf_ctm_redirect = function() {}; } var send_time = (new Date()).getTime(); var redirect = setInterval(function() { if (__ctm_http_requests.length == 0 || (new Date()).getTime() - send_time > 5000) { clearInterval(redirect); $code } }, 10); }); })();</script>";
  }
  
  function submit_gf($entry, $form) {
    $this->gf_log("Submitting...");
    
    if ($entry["form_id"] != $form["id"]) return;
    if (!$form["is_active"]) return;
    
    $country_code = "";
    $custom = array();
    foreach ($form["fields"] as $field) {
      if ($field["type"] == "name") {
        if(!isset($name)) {
          $name = trim($entry[$field["id"] . ".3"] . " " . $entry[$field["id"] . ".6"]);
        }
      } elseif ($field["type"] == "phone") {
        if(!isset($phone)) {
          $phone = $entry[$field["id"]];
        }
        if ($field["phoneFormat"] == "standard") $country_code = "1";
      } elseif ($field["type"] == "email") {
        $email = $entry[$field["id"]];
      } elseif (isset($field["id"]) && is_int($field["id"])) {
        $custom[$field["id"]] = $field;
      }
    }
    
    $this->gf_log("Title: " . $form["title"] . ", Name: " . $name . ", Phone: " . $phone . ", Email: " . $email);
    
    // phone numbers are required for FormReactors
    if (!isset($phone) || strlen($phone) <= 0) {
      $this->gf_log("No phone number set");
      return;
    }
    
    $fields = array();
    $labels = array();
    $sublabels = array();
    
    foreach ($entry as $field => $value) {
      $id = intval($field);
      if (!isset($custom[$id])) continue;
      
      $field = $custom[$id];
      $sublabel = NULL;
      
      // file uploads are not supported
      if ($field["type"] == "fileupload") continue;
      
      if ($field["type"] == "checkbox") {
        // checkboxes use separate "12.1" "12.2" IDs for each input in a list with ID = 12, but process all of them together
        unset($custom[$id]);
        
        $new_value = array();
        $sublabel = array();
        foreach ($field["inputs"] as $index => $checkbox) {
          if (isset($entry[$checkbox["id"]]) && $entry[$checkbox["id"]] == $field["choices"][$index]["value"]) {
            $new_value[] = $entry[$checkbox["id"]];
            $sublabel[] = $checkbox["label"];
          }
        }
        $value = $new_value;
        
      } elseif ($field["type"] == "list") {
        $value = unserialize($value);
        if (!$value || count($value) == 0) continue;
        
        $sublabel = array();
        foreach ($value[0] as $label => $ignore) $sublabel[] = $label;
        
        $new_value = array();
        foreach ($value as $index => $row) {
          $new_row = array();
          foreach ($row as $label) $new_row[] = $label;
          $new_value[] = $new_row;
        }
        $value = $new_value;
        
      } elseif (isset($field["choices"]) && is_array($field["choices"])) {
        // convert the value into an array
        $new_value = array();
        $pos = 0; $value_length = strlen($value);
        $sublabel = array();
        
        while ($pos < $value_length) {
          $best = NULL; $best_length = 0;
          
          foreach ($field["choices"] as $choice) {
            $choice_length = strlen($choice["value"]);
            if ($choice_length <= $value_length - $pos && substr_compare($value, $choice["value"], $pos, $choice_length) == 0) {
              if (!$best || $choice_length >= $best_length) {
                $best = $choice; $best_length = $choice_length;
              }
            }
          }
          if ($best) {
            $new_value[] = $best["value"];
            $sublabel[] = $best["text"];
            
            $pos += $best_length;
          } elseif ($pos == 0 && $field["type"] == "radio" && $field["enableOtherChoice"] && $field["enableChoiceValue"]) {
            $new_value = $value;
            break;
          }
          
          // move pos up to past the next comma
          $new_pos = strpos($value, ",", $pos);
          if ($new_pos === FALSE) break;
          $pos = $new_pos + 1;
        }
        
        $value = $new_value;
        
      } elseif (!is_string($value)) continue;
      
      $fields["field_" . $id] = $value;
      $labels["field_" . $id] = $field["label"];
      if ($sublabel) $sublabels["field_" . $id] = $sublabel;
    }

    $fr_data = array(
      "type" => "Gravity Forms",
      "id" => $form["id"],
      "title" => $form["title"],
      "name" => $name,
      "country_code" => $country_code,
      "phone" => $phone,
      "email" => $email,
      "fields" => $fields,
      "labels" => $labels,
      "sublabels" => $sublabels
    );

   /**
		 * Allow other plugins to programmatically add/remove FormReactor data.
		 *
		 * @param array $fr_data The current form reactor fields.
		 * @param array $entry   The Entry object.
		 * @param array $form    The Form object.
		 *
		 * @return array
		 */
		$fr_data = apply_filters( 'ctm_gf_formreactor_data', $fr_data, $entry, $form );
    
    $this->send_formreactor($fr_data);
  }
  
  public function send_formreactor($form) {
    $enabled = array(
      "Gravity Forms" => $this->gf_enabled(),
      "Contact Form 7" => $this->cf7_enabled()
    );
    if (!($form && isset($form["type"]) && isset($enabled[$form["type"]]) && $enabled[$form["type"]])) {
      $this->form_log($form["type"], "Form integration is not enabled");
      return;
    }
    
    // phone numbers are required for FormReactors
    if (!isset($form["phone"]) || strlen($form["phone"]) <= 0) {
      $this->form_log($form["type"], "No phone number set");
      return;
    }
    
    $form_reactor = array();
    if (isset($form["country_code"]))
      $form_reactor["country_code"] = $form["country_code"];
    $form_reactor["phone_number"] = $form["phone"];
    
    if (isset($form["name"]) && strlen($form["name"]) > 3)
      $form_reactor["caller_name"] = $form["name"];
    if (isset($form["email"]) && strlen($form["email"]) > 5)
      $form_reactor["email"] = $form["email"];
    
    $data = array();
    $data["form_reactor"] = $form_reactor;
    $data["field"] = $form["fields"];
    $data["label"] = $form["labels"];
    $data["labels"] = $form["sublabels"];
    $data["type"] = $form["type"];
    $data["id"] = $form["id"];
    $data["name"] = $form["title"];
    $data["__ctm_api_authorized__"] = "1";
    $data["visitor_sid"] = $_COOKIE["__ctmid"];
    $data["domain"] = $_SERVER['HTTP_HOST'] ?: "";
    
    $data_json = json_encode($data);
    //$this->debug($data_json);
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->ctm_host . "/api/v1/formreactor/" . $form["id"]);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Basic " . base64_encode(get_option('ctm_api_key') . ":" . get_option('ctm_api_secret'))));
    curl_setopt($curl, CURLOPT_POST, strlen($data_json));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
    $output = curl_exec($curl);
    
    $this->form_log($form["type"], "Form POST submission returned: " . $output);
    
    curl_close($curl);
  }
  
  function authorizing() {
    return ( get_option("ctm_api_key") && get_option("ctm_api_secret") );
  }
  function authorized() {
    $this->update_account();
    return ( get_option("ctm_api_auth_account") || !get_option("ctm_api_key") || !get_option("ctm_api_secret") );
  }
  function activate_msg() {
    return 'Enter your CallTrackingMetrics account details on the <a href="' . site_url() . '/wp-admin/options-general.php?page=call-tracking-metrics">Settings page</a> to get started.';
  } 
  function unauthorized_msg() {
    return 'Invalid credentials. Please check your <a href="' . site_url() . '/wp-admin/options-general.php?page=call-tracking-metrics">account settings</a> and try again.';
  }
  function unavailable_msg() {
    return 'CallTrackingMetrics data temporarily unavailable. Please try again later.';
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

  public function attach_ctm_dashboard() {
    if (current_user_can('manage_options')) {
      if ($this->dashboard_enabled())
        add_action('wp_dashboard_setup', array(&$this, 'install_dashboard_widget'));
    }
  }

  function install_dashboard_widget() {
    wp_add_dashboard_widget("ctm_dash", "CallTrackingMetrics", array(&$this, 'admin_dashboard_plugin'));
  }
  
  // show a snapshot of recent call activity and aggregate stats
  function admin_dashboard_plugin() {
    $ctm_stats_cache = 'ctm_stats_cache';
    //10-minute cache
    $ctm_stats_cache_timeout = 10 * MINUTE_IN_SECONDS;
    $stats = get_transient( $ctm_stats_cache );
    if( $stats === false ) {
      $stats = $this->update_stats();
      set_transient( $ctm_stats_cache, $stats, $ctm_stats_cache_timeout );
    } 
    $dates = array();
    for ($count = 0; $count <= 30; ++$count) {
      array_push( $dates, date('Y-m-d', strtotime('-' . $count . ' days') ) );
    }
    if (!$this->authorizing()) {
      echo $this->activate_msg();
    } elseif (!$this->authorized()) {
      echo $this->unauthorized_msg();
    } elseif ( (isset($stats['authentication'])) && ($stats['authentication'] == 'failed') ) { 
      echo $this->unavailable_msg();
    } else { ?>
    <div class="ctm-dash"
         data-dates='<?php echo json_encode($dates); ?>'
         data-today="<?php echo date('Y-m-d') ?>"
         data-start="<?php echo date('Y-m-d', strtotime('-30 days')); ?>"
         data-stats='<?php echo json_encode($stats)?>'>
    </div>
    <script id="ctm-dash-template" type="text/x-mustache">
      <div style="height:250px" class="stats"></div>
      <h3 class="ctm-stat total_calls">Total Calls: {{total_calls}}</h3>
      <h3 class="ctm-stat total_unique_calls">Total Callers: {{total_unique_calls}}</h3>
      <h3 class="ctm-stat average_call_length">Average Call Time: {{average_call_length}}</h3>
      <h3 class="ctm-stat top_call_source">Top Call Source: {{top_call_source}}</h3>
    </script>
    <script>
      if(!Array.prototype.indexOf){Array.prototype.indexOf=function(e){"use strict";if(this==null){throw new TypeError}var t=Object(this);var n=t.length>>>0;if(n===0){return-1}var r=0;if(arguments.length>1){r=Number(arguments[1]);if(r!=r){r=0}else if(r!=0&&r!=Infinity&&r!=-Infinity){r=(r>0||-1)*Math.floor(Math.abs(r))}}if(r>=n){return-1}var i=r>=0?r:Math.max(n-Math.abs(r),0);for(;i<n;i++){if(i in t&&t[i]===e){return i}}return-1}}
      jQuery(function($) {
        var dashTemplate = Mustache.compile($("#ctm-dash-template").html());
        var stats = $.parseJSON($("#ctm_dash .ctm-dash").attr("data-stats"));
        var startDate = $("#ctm_dash .ctm-dash").attr("data-start");
        var endDate = $("#ctm_dash .ctm-dash").attr("data-today");
        var categories = $.parseJSON($("#ctm_dash .ctm-dash").attr("data-dates")).reverse();

        $("#ctm_dash .ctm-dash").html(dashTemplate(stats));
        var data = [], calls = (stats && stats.stats) ? stats.stats.calls : [];
        for (var i = 0, len = categories.length; i < len; ++i) {
          data.push(0); // zero fill
        }
        for (var c in calls) {
          data[categories.indexOf(c)] = calls[c][0];
        }
        var series = [{
                        name: 'Calls', data: data,
                        pointInterval: 24 * 3600 * 1000,
                        pointStart: Date.parse(categories[0])
                      }];
        var chart = new Highcharts.Chart({
          credits: { enabled: false },
          chart: { type: 'column', renderTo: $("#ctm_dash .stats").get(0), plotBackgroundColor:null, backgroundColor: 'transparent' },
          yAxis: { min: 0, title: { text: "Calls" } },
          title: { text: 'Last 30 Days' },
          legend: { enabled: false },
          xAxis: {
            type: 'datetime',
            minRange: 30 * 24 * 3600000 // last 30 days
          },
          series: series
        });
      });
    </script>
    <?php } ?>
    <?php
  }
  
  function update_stats() {
    $this->update_account();
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->ctm_host . "/api/v1/accounts/" . get_option('ctm_api_auth_account') . "/reports.json?s=" . date('Y-m-d', strtotime('-30 days')) . "&e=" . date('Y-m-d', strtotime('-0 days')) . "&interval=day");
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Basic " . base64_encode(get_option('ctm_api_key') . ":" . get_option('ctm_api_secret'))));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    curl_close($curl);
    $decoded = json_decode($res, true);
    return $decoded;
  }
  
  function cf7_log($message) {
    $logs = json_decode(get_option("ctm_api_cf7_logs"), true);
    if (!is_array($logs)) $logs = array();
    while (count($logs) >= 20) array_shift($logs);
    
    array_push($logs, array("message" => $message, "date" => date("c")));
    update_option("ctm_api_cf7_logs", json_encode($logs));
  }
  
  function gf_log($message) {
    $logs = json_decode(get_option("ctm_api_gf_logs"), true);
    if (!is_array($logs)) $logs = array();
    while (count($logs) >= 20) array_shift($logs);
    
    array_push($logs, array("message" => $message, "date" => date("c")));
    update_option("ctm_api_gf_logs", json_encode($logs));
  }
  
  function form_log($type, $message) {
    if ($type == "Contact Form 7") {
      $this->cf7_log($message);
    } elseif ($type == "Gravity Forms") {
      $this->gf_log($message);
    }
  }

  function debug($data) {
    ob_start(); var_dump($data); $contents = ob_get_contents(); ob_end_clean(); error_log($contents);
  }

}//CallTrackingMetrics

function create__calltrackingmetrics() {
  $create__calltrackingmetrics = new CallTrackingMetrics();
  //include wp-admin settings/options page
  require_once( trailingslashit(dirname(__FILE__)) . 'ctm-options.php' );
}
add_action('plugins_loaded', "create__calltrackingmetrics");
