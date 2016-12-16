<?php

add_shortcode('mvp', 'mvp_content');

function mvp_content() {

  $settings = get_option('mvp_settings');

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $settings['url'] . "/oauth/token");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);

  curl_setopt($ch, CURLOPT_POST, TRUE);

  curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id=' . $settings['client_id'] . '&grant_type=password&username=' . $settings['username'] . '&password=' . $settings['password']);

  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type: application/x-www-form-urlencoded"
  ));

  $response = curl_exec($ch);
  curl_close($ch);

  $config = json_decode($response);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $settings['url'] . "/packages?start_date=" . date("Y-m-d", strtotime('-1 days')));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $headers = array('Authorization: ' . $config->token_type . ' ' . $config->access_token);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  $response = curl_exec($ch);
  curl_close($ch);

  if ($response) {
    require_once MVP_PLUGIN_DIR . '/front/wrapper.php';
    
    $content = mvp_view($response);
  }

  return $content;
}
