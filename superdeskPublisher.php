<?php

/**
 * Plugin Name: Superdesk Publisher
 * Plugin URI: 
 * Description: Imports articles in the IPTC ninjs format from the Superdesk newsroom system
 * Version: 0.9
 * Author: Sourcefabric and AdminIT
 */
if (!function_exists('add_action')) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}


define('MVP_VERSION', '0.1');

define('MVP_REQUIRED_WP_VERSION', '4.2');

define('MVP_PLUGIN', __FILE__);

define('MVP_PLUGIN_DIR', untrailingslashit(dirname(MVP_PLUGIN)));

define('MVP_PLUGIN_URL', untrailingslashit(plugins_url('', MVP_PLUGIN)));

define('MVP_DATABASE_VERSION', '2');

define('DB_TABLE_SYNC_POST', 'sync_posts');
define('DB_TABLE_POSTMETA', 'postmeta');
define('DB_TABLE_USERS', 'users');


require_once MVP_PLUGIN_DIR . '/settings.php';

function mvp_database_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . DB_TABLE_SYNC_POST;

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
		guid text NOT NULL,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($sql);

  add_option('MVP_DATABASE_VERSION', MVP_DATABASE_VERSION);
}

function mvp_database_update() {
  if (get_site_option('MVP_DATABASE_VERSION') != MVP_DATABASE_VERSION) {
    mvp_database_install();
  }
}

function saveFile($from, $to) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $from);

  $fp = fopen($to, 'w+');
  curl_setopt($ch, CURLOPT_FILE, $fp);

  curl_exec($ch);

  curl_close($ch);
  fclose($fp);
}

function generatePassword() {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ%./\@';
  $charactersLength = strlen($characters);
  $password = '';
  for ($i = 0; $i < 8; $i++) {
    $password .= $characters[rand(0, $charactersLength - 1)];
  }
  $password = $password . strtotime("now");
  return $password;
}

function saveAttachment($picture, $post_ID, $caption, $alt) {
  $filenameQ = explode("/", $picture['renditions']['original']['media']);
  $filename = $filenameQ[count($filenameQ) - 1];

  $uploadDir = wp_upload_dir();
  saveFile($picture['renditions']['original']['href'], $uploadDir['path'] . "/" . $filename);

  $attachment = array(
      'guid' => $uploadDir['url'] . '/' . basename($filename),
      'post_mime_type' => $picture['mimetype'],
      'post_title' => $caption,
      'post_content' => '',
      'post_excerpt' => $caption,
      'post_status' => 'inherit'
  );

  $attach_id = wp_insert_attachment($attachment, date("Y") . "/" . date("m") . "/" . $filename, $post_ID);

  require_once( ABSPATH . 'wp-admin/includes/image.php' );

  $attach_data = wp_generate_attachment_metadata($attach_id, $uploadDir['path'] . "/" . $filename);

  wp_update_attachment_metadata($attach_id, $attach_data);
  set_post_thumbnail($post_ID, $attach_id);

  update_post_meta($attach_id, '_wp_attachment_image_alt', wp_slash($alt));
}

function savePicture($localPath, $postId, $oldSrc, $associations, $alt) {
  $filenameQ = explode("/", $localPath);
  $filename = $filenameQ[count($filenameQ) - 1];
  $uploadDir = wp_upload_dir();

  $name = null;
  $mimeType = null;
  foreach ($associations as $key => $value) {
    if (isset($value['renditions'])) {
      foreach ($value['renditions'] as $value2) {
        if ($value2['href'] === $oldSrc) {
          $name = $key;
          $mimeType = $value2['mimetype'];
          break 2;
        }
      }
    }
  }
  if ($name == null) {
    $caption = wp_strip_all_tags($alt);
    $alt = wp_strip_all_tags($alt);
  } else {
    $caption = generate_caption_image($associations[$name]);
    $alt = (!empty($associations[$name]['body_text'])) ? wp_strip_all_tags($associations[$name]['body_text']) : '';
  }
  $attachment = array(
      'guid' => $localPath,
      'post_mime_type' => $mimeType == null ? mime_content_type($uploadDir['path'] . "/" . $filename) : $mimeType,
      'post_title' => $caption,
      'post_content' => '',
      'post_excerpt' => $caption,
      'post_status' => 'inherit'
  );


  $attach_id = wp_insert_attachment($attachment, date("Y") . "/" . date("m") . "/" . $filename, $postId);

  require_once( ABSPATH . 'wp-admin/includes/image.php' );

  $attach_data = wp_generate_attachment_metadata($attach_id, $uploadDir['path'] . "/" . $filename);

  wp_update_attachment_metadata($attach_id, $attach_data);
  set_post_thumbnail($postId, $attach_id);

  update_post_meta($attach_id, '_wp_attachment_image_alt', wp_slash($alt));
}

function custom_wpkses_post_tags($tags, $context) {
  if ('post' === $context) {
    $tags['iframe'] = array(
        'style' => true,
        'src' => true,
        'height' => true,
        'width' => true,
        'frameborder' => true,
        'allowfullscreen' => true,
    );
  }
  return $tags;
}

function generate_caption_image($media) {
  $caption = '';
  $settings = get_option('superdesk_settings');
  if (!empty($media['description_text'])) {
    $caption .= wp_strip_all_tags($media['description_text']);
  }

  if (!empty($media['byline'])) {
    if (!empty($caption)) {
      $caption .= ' ';
    }

    $caption .= (empty($settings['separator-caption-image']) ? ':' : $settings['separator-caption-image']) . ' ' . wp_strip_all_tags($media['byline']);
  }

  if (!empty($media['copyrightholder']) && $settings['copyrightholder-image'] == 'on') {
    $caption .= ' / ' . wp_strip_all_tags($media['copyrightholder']);
  }

  if (!empty($media['copyrightnotice']) && $settings['copyrightnotice-image'] == 'on') {
    $caption .= ' ' . wp_strip_all_tags($media['copyrightnotice']);
  }

  return $caption;
}

function embed_src($src) {
  $uploadDir = wp_upload_dir();
  $filename = sha1($src);

  saveFile($src, $uploadDir['path'] . "/" . $filename);
  return $uploadDir['url'] . "/" . $filename;
}

class Image {

  public $src, $alt, $oldSrc;

  public function __construct(array $attrs, $oldSrc) {
    $this->src = $attrs['src'];
    $this->alt = isset($attrs['alt']) ? $attrs['alt'] : '';
    $this->oldSrc = $oldSrc;
    stripQuotes($this->src);
    stripQuotes($this->alt);
  }

}

function stripQuotes(&$value) {
  $value = mb_substr($value, 1, mb_strlen($value) - 2);
}

function embed_images($html, &$image) {
  $result = array();
  preg_match_all('/<img[^>]+>/i', $html, $result);
  if (count($result) > 0) {
    $img = array();
    foreach ($result as $row) {
      if (count($row) > 0) {
        foreach ($row as $img_tag) {
          preg_match_all('/(src|title|alt)=("[^"]*")/i', $img_tag, $img[$img_tag]);
        }
      }
    }

    if (count($img) > 0) {
      foreach ($img as $htmlTag => $src) {
        $attrs = array();
        if (isset($src[1], $src[2])) {
          $oldSrc = '';
          foreach ($src[1] as $key => $attr) {
            $value = $src[2][$key];
            if ($attr === "src") {
              stripQuotes($value);
              $oldSrc = $value;
              $value = '"' . embed_src($value) . '"';
            }

            $attrs[$attr] = $value;
          }

          if ($image == null) {
            $image = new Image($attrs, $oldSrc);
          }

          $newHtmlTag = "<img";
          foreach ($attrs as $attrName => $attrValue) {
            $newHtmlTag .= " " . $attrName . "=" . $attrValue;
          }
          $newHtmlTag .= ">";
          $html = str_replace($htmlTag, $newHtmlTag, $html);
        }
      }
    }
  }

  return $html;
}

add_filter('wp_kses_allowed_html', 'custom_wpkses_post_tags', 10, 2);

wp_oembed_add_provider('#http://(www\.)?youtube\.com/watch.*#i', 'http://www.youtube.com/oembed', true);

register_activation_hook(__FILE__, 'mvp_database_install');
add_action('plugins_loaded', 'mvp_database_update');
