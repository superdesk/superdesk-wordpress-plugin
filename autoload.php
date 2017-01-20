<?php

require_once '../../../wp-load.php';

$json = file_get_contents('php://input');
//$json = file_get_contents('log.txt');

//file_put_contents('log.txt', $json . "\n\n", FILE_APPEND);
$obj = json_decode($json, true);

if ($obj['type'] == 'composite') {

  $settings = get_option('mvp_settings');

  if ($obj['pubstatus'] == 'usable') {
    $content = $obj['description_html'] . "<!--more-->" . $obj['body_html'];

    $guid = wp_strip_all_tags($obj['guid']);

    $sync = $wpdb->get_row("SELECT post_id FROM " . $wpdb->prefix . DB_TABLE_SYNC_POST . " WHERE guid = '" . $guid . "'");

    if ($sync) {
      $edit_post = array(
          'ID' => $sync->post_id,
          'post_title' => wp_strip_all_tags($obj['headline']),
          'post_name' => wp_strip_all_tags($obj['headline']),
          'post_content' => $content,
          'post_content_filtered' => $content
      );

      wp_update_post($edit_post);
    } else {

      if ($settings['author-byline'] && $settings['author-byline'] == 'on') {
        $author_name = str_replace("By ", "", $obj['byline']);

        $authorExist = $wpdb->get_row("SELECT ID user_id FROM " . $wpdb->prefix . DB_TABLE_USERS . " WHERE display_name = '" . wp_strip_all_tags($author_name) . "'");

        if (!$authorExist) {
          $table_name = $wpdb->prefix . DB_TABLE_USERS;

          $userArray = array(
              'user_login' => strtolower(str_replace(" ", "-", $author_name)),
              'user_pass' => generatePassword(),
              'display_name' => wp_strip_all_tags($author_name)
          );

          $author_id = wp_insert_user($userArray);
        } else {
          $author_id = $authorExist->user_id;
        }
      } else {
        $author_id = $settings['author'];
      }

      $postarr = array(
          'post_title' => wp_strip_all_tags($obj['headline']),
          'post_name' => wp_strip_all_tags($obj['headline']),
          'post_content' => $content,
          'post_content_filtered' => $content,
          'post_author' => (int) $author_id,
          'post_status' => $settings['status'],
          'post_category' => $settings['category'],
      );

      $post_ID = wp_insert_post($postarr, true);

      $table_name = $wpdb->prefix . DB_TABLE_SYNC_POST;

      $wpdb->insert(
              $table_name, array(
          'post_id' => $post_ID,
          'guid' => wp_strip_all_tags($obj['guid']),
          'time' => current_time('mysql')
              )
      );


      if ($obj['associations']['featuremedia'] && $obj['associations']['featuremedia']['type'] == 'picture') {
        /* save featured media */
        $picture = $obj['associations']['featuremedia'];

        $filenameQ = explode("/", $picture['renditions']['original']['media']);
        $filename = $filenameQ[1];
        saveFile($picture['renditions']['original']['href'], wp_upload_dir()['path'] . "/" . $filename);

        $attachment = array(
            'guid' => wp_upload_dir()['url'] . '/' . basename($filename),
            'post_mime_type' => $picture['mimetype'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($picture['headline'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, date("Y") . "/" . date("m") . "/" . $filename, $post_ID);

        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attach_data = wp_generate_attachment_metadata($attach_id, wp_upload_dir()['path'] . "/" . $filename);

        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_ID, $attach_id);
      }
    }
  } elseif ($obj['pubstatus'] == 'canceled') {
    /* remove article */
    $guid = wp_strip_all_tags($obj['guid']);

    $sync = $wpdb->get_row("SELECT post_id FROM " . $wpdb->prefix . DB_TABLE_SYNC_POST . " WHERE guid = '" . $guid . "'");

    if ($sync) {

      $edit_post = array(
          'ID' => $sync->post_id,
          'post_status' => 'draft'
      );

      wp_update_post($edit_post);
    }
  }
}
