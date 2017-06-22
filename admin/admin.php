<?php
add_action('admin_menu', 'superdesk_admin_actions');

add_filter('plugin_action_links_superdesk/superdeskPublisher.php', '_plugin_action_links');

//var_dump(get_post_format_strings());die();

function superdesk_admin_actions() {
  add_options_page('SUPERDESK', 'Superdesk Publisher', 'manage_options', __FILE__, 'superdesk_admin');
}

function _plugin_action_links($links) {
  $links[] = '<a href="' . esc_url(get_page_url()) . '">Settings</a>';
  return $links;
}

function get_page_url() {

  $args = array('page' => 'superdesk/admin/admin');

  $url = add_query_arg($args, admin_url('options-general.php'));

  return $url;
}

function superdesk_admin() {
  $post_formats = get_post_format_strings();

  if (isset($_POST['url'])) {
    $resultArray = array();
    if (isset($_POST['post-formats-value'], $_POST['post-formats-input'])) {
      foreach ($_POST['post-formats-value'] as $key => $value) {
        if (!isset($_POST['post-formats-input'][$key])) {
          continue;
        }
        if (isset($post_formats[$value])) {
          $inputValue = $_POST['post-formats-input'][$key];
          if (!isset($resultArray[$inputValue]) && !empty($inputValue))
            $resultArray[$inputValue] = $value;
        }
      }
    }
        
    $settings = array(
        'url' => $_POST['url'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'client_id' => $_POST['client_id'],
        'status' => $_POST['status'],
        'author' => $_POST['author'],
        'author-byline' => $_POST['author-byline'],
        'byline-words' => $_POST['byline-words'],
        'display-copyright' => $_POST['display-copyright'],
        'import-keywords' => $_POST['import-keywords'],
        'convert-services' => $_POST['convert-services'],
        'subject-type' => $_POST['subject-type'],
        'category' => $_POST['category'],
        'separator-caption-image' => $_POST['separator-caption-image'],
        'copyrightholder-image' => $_POST['copyrightholder-image'],
        'copyrightnotice-image' => $_POST['copyrightnotice-image'],
        'separator-located' => $_POST['separator-located'],
        'convert-slugline' => $_POST['convert-slugline'],
        'slugline-separator' => $_POST['slugline-separator'],
        'slugline-ignored' => $_POST['slugline-ignored'],
        'priority_threshhold' => $_POST['priority_threshhold'],
        'download-images' => $_POST['download-images'],
        'post-formats' => $_POST['download-images'],
        'post-formats-table' => $resultArray,
        'location-modifier' => $_POST['location-modifier'],
        'update-log-option' => $_POST['update-log-option'],
        'update-log-date-format' => $_POST['update-log-date-format'],
        'update-log-text' => $_POST['update-log-text'],
        'update-log-position' => $_POST['update-log-position'],
    );
    update_option('superdesk_settings', $settings);
  } else if (get_option('superdesk_settings')) {
    $settings = get_option('superdesk_settings');
  } else {
    $settings = array(
        'url' => '',
        'client_id' => '',
        'username' => '',
        'password' => '',
        'status' => 'publish',
        'author' => '',
        'author-byline' => '',
        'byline-words' => '',
        'display-copyright' => '',
        'import-keywords' => '',
        'convert-services' => '',
        'subject-type' => '',
        'category' => '',
        'separator-caption-image' => '',
        'copyrightholder-image' => '',
        'copyrightnotice-image' => '',
        'separator-located' => '',
        'convert-slugline' => '',
        'slugline-separator' => '',
        'slugline-ignored' => '',
        'priority_threshhold' => '',
        'download-images' => '',
        'post-formats' => '',
        'post-formats-table' => array(),
        'location-modifier' => 'standard',
        'update-log-option' => 'off',
        'update-log-date-format' => 'Y-m-d H:i',
        'update-log-text' => 'This article was updated at',
        'update-log-position' => 'off'
    );
  }
  $statuses = array(
      'publish' => 'Published',
      'draft' => 'Draft'
  );

  $authors = array();

  $categories = array();

  $args = array(
      'hide_empty' => 0,
  );
  $all_categories = get_categories($args);

  foreach ($all_categories as $category) {
    $categories[$category->cat_ID] = $category->cat_name;
  }

  $all_users = get_users();
  foreach ($all_users as $user) {
    $authors[$user->ID] = $user->data->display_name;
  }

  function make_table_row($format, $text_value, $formats) {
    $new_element = '<tr><td><input type="text" name="post-formats-input[]" onkeyup="debounce(validateInput, 250);" class="regular-text" value="' . esc_html($text_value) . '" /></td><td><select name="post-formats-value[]">';
    foreach ($formats as $key => $value) {
      $new_element .= '<option ' . ($key === $format ? 'selected="selected" ' : '') . 'value="' . esc_html($key) . '">' . esc_html($value) . '</option>';
    }
    $new_element .= '</select></td><td><a href="#" onclick="removeThisRow(this); return false;">Delete</a></td></tr>';
    return $new_element;
  }
  ?>
  <div class="wrap">
      <h2>Superdesk Publisher</h2>
      <form action="" method="POST">
          <table class="form-table">
              <tbody>
                  <tr>
                      <th>Autoload</th>
                      <td><?php echo get_site_url(); ?>/wp-content/plugins/superdesk-wordpress-plugin/autoload.php</td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="url">SD (content API) URL</label>
                      </th>
                      <td>
                          <input type="text" name="url" id="url" class="regular-text code" value="<?php echo($settings['url']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="client_id">Client ID</label>
                      </th>
                      <td>
                          <input type="text" name="client_id" id="client_id" class="regular-text" value="<?php echo($settings['client_id']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="username">Username</label>
                      </th>
                      <td>
                          <input type="text" name="username" id="username" class="regular-text" value="<?php echo($settings['username']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="password">Password</label>
                      </th>
                      <td>
                          <input type="password" name="password" id="password" class="regular-text" value="<?php echo($settings['password']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
			Default status of ingested articles/posts
                      </th>
                      <td>
                          <fieldset>
                              <?php
                              foreach ($statuses as $key => $value) {
                                ?>
                                <label for="status-<?php echo($key); ?>">
                                    <input type="radio" name="status" id="status-<?php echo($key); ?>" value="<?php echo($key); ?>"<?php
                                    if ($key == $settings['status']) {
                                      echo(' checked');
                                    }
                                    ?>> <?php echo($value); ?>
                                </label>
                                <br>
                                <?php
                              }
                              ?>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="author">Default author</label>
                      </th>
                      <td>
                          <select name="author" id="author">
                              <?php
                              foreach ($authors as $key => $value) {
                                ?>
                                <option value="<?php echo($key); ?>"<?php
                                if ($key == $settings['author']) {
                                  echo(' selected');
                                }
                                ?>><?php echo($value); ?></option>
                                        <?php
                                      }
                                      ?>
                          </select>
                          <br /><br />
                          <label>
                              <input type="checkbox" name="author-byline" <?php echo ($settings['author-byline']) ? "checked" : ""; ?>> Show byline in posts
                          </label>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="byline-words">Choose the words replace from the byline</label>
                      </th>
                      <td>
                          <input type="text" name="byline-words" id="byline-words" class="regular-text" value="<?php echo($settings['byline-words']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Choose words to replace from the byline
                      </th>
                      <td>
                          <fieldset>
                              <label for="status-off">
                                  <input type="radio" name="display-copyright" id="status-off" value="off"<?php
                                  if ($settings['display-copyright'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="status-on">
                                  <input type="radio" name="display-copyright" id="status-on" value="on"<?php
                                  if ($settings['display-copyright'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Import SD keywords as WP tags
                      </th>
                      <td>
                          <fieldset>
                              <label for="off">
                                  <input type="radio" name="import-keywords" id="off" value="off"<?php
                                  if ($settings['import-keywords'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="on">
                                  <input type="radio" name="import-keywords" id="on" value="on"<?php
                                  if ($settings['import-keywords'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Import Superdesk categories as Wordpress categories
                      </th>
                      <td>
                          <fieldset>
                              <label for="off">
                                  <input type="radio" name="convert-services" id="off" value="off"<?php
                                  if ($settings['convert-services'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="on">
                                  <input type="radio" name="convert-services" id="on" value="on"<?php
                                  if ($settings['convert-services'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Import Superdesk subjects as
                      </th>
                      <td>
                          <fieldset>
                              <label for="tags">
                                  <input type="radio" name="subject-type" id="tags" value="tags"<?php
                                  if ($settings['subject-type'] == 'tags' || !$settings['subject-type'] || $settings['subject-type'] == null) {
                                    echo(' checked');
                                  }
                                  ?>> Wordpress tags
                              </label>
                              <br>
                              <label for="categories">
                                  <input type="radio" name="subject-type" id="categories" value="categories"<?php
                                  if ($settings['subject-type'] == 'categories') {
                                    echo(' checked');
                                  }
                                  ?>> Wordpress categories
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="category">Default category</label>
                      </th>
                      <td>
                          <select name="category[]" id="category" multiple>
                              <?php
                              foreach ($categories as $key => $value) {
                                ?>
                                <option value="<?php echo($key); ?>"<?php
                                if (isset($settings['category']) && is_array($settings['category'])) {
                                  if (in_array($key, $settings['category'])) {
                                    echo(' selected');
                                  }
                                }
                                ?>><?php echo($value); ?></option>
                                        <?php
                                      }
                                      ?>
                          </select>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="separator-caption-image">Separator for caption image</label>
                      </th>
                      <td>
                          <input type="text" name="separator-caption-image" id="separator-caption-image" class="regular-text" value="<?php echo($settings['separator-caption-image']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
			Display the image copyright holder in the caption
                      </th>
                      <td>
                          <fieldset>
                              <label for="copyrightholder-image-off">
                                  <input type="radio" name="copyrightholder-image" id="copyrightholder-image-off" value="off"<?php
                                  if ($settings['copyrightholder-image'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="copyrightholder-image-on">
                                  <input type="radio" name="copyrightholder-image" id="copyrightholder-image-on" value="on"<?php
                                  if ($settings['copyrightholder-image'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
			Display the image copyright notice in the caption
                      </th>
                      <td>
                          <fieldset>
                              <label for="copyrightnotice-image-off">
                                  <input type="radio" name="copyrightnotice-image" id="copyrightnotice-image-off" value="off"<?php
                                  if ($settings['copyrightnotice-image'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="copyrightnotice-image-on">
                                  <input type="radio" name="copyrightnotice-image" id="copyrightnotice-image-on" value="on"<?php
                                  if ($settings['copyrightnotice-image'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="separator-located">Separator between the location and name</label>
                      </th>
                      <td>
                          <input type="text" name="separator-located" id="separator-located" class="regular-text" value="<?php echo($settings['separator-located']); ?>">
                      </td>
                  </tr>
              		 <tr>
                 		    <th scope="row">
                                       Display location as
              		   </th>
              		   <td>
              			 <fieldset>
                              <label for="location-modifier-all-caps">
                                  <input type="radio" name="location-modifier" id="location-modifier-all-caps" value="all-caps"<?php
                                  if ($settings['location-modifier'] == 'all-caps') {
                                    echo(' checked');
                                  }
                                  ?>> ALL CAPS
                              </label>
                              <br>
                              <label for="location-modifier-standard">
                                  <input type="radio" name="location-modifier" id="location-modifier-standard" value="standard"<?php
                                  if (!isset($settings['location-modifier']) || $settings['location-modifier'] != 'all-caps') {
                                    echo(' checked');
                                  }
                                  ?>> Standard case
                              </label>
                          </fieldset>
                		   </td>
                		</tr>
                  <tr>
                      <th scope="row">
                          Convert slugline keywords into WP tags
                      </th>
                      <td>
                          <fieldset>
                              <label for="convert-slugline-off">
                                  <input type="radio" name="convert-slugline" id="convert-slugline-off" value="off"<?php
                                  if ($settings['convert-slugline'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="convert-slugline-on">
                                  <input type="radio" name="convert-slugline" id="convert-slugline-on" value="on"<?php
                                  if ($settings['convert-slugline'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="slugline-separator">Slugline value separator</label>
                      </th>
                      <td>
                          <input type="text" name="slugline-separator" id="slugline-separator" class="regular-text" value="<?php echo($settings['slugline-separator']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="slugline-ignored">Keywords to be ignored</label>
                      </th>
                      <td>
                          <input type="text" name="slugline-ignored" id="slugline-ignored" class="regular-text" value="<?php echo($settings['slugline-ignored']); ?>">
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          <label for="priority_threshhold">Priority threshhold of </label>
                      </th>
                      <td>
                          <select name="priority_threshhold" id="priority_threshhold">
                              <option value="0"> </option>
                              <?php
                              for ($i = 1; $i <= 6; $i++) {
                                ?>
                                <option value="<?php echo($i); ?>"<?php
                                if (isset($settings['priority_threshhold']) && $settings['priority_threshhold'] == $i) {
                                  echo(' selected');
                                }
                                ?>><?php echo($i); ?></option>
                                        <?php
                                      }
                                      ?>
                          </select>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Download images from SD
                      </th>
                      <td>
                          <fieldset>
                              <label for="download-images-off">
                                  <input type="radio" name="download-images" id="download-images-off" value="off"<?php
                                  if ($settings['download-images'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="download-images-on">
                                  <input type="radio" name="download-images" id="download-images-on" value="on"<?php
                                  if ($settings['download-images'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Add update logs to articles 
                      </th>
                      <td>
                          <fieldset>
                              <label for="update-log-option-off">
                                  <input type="radio" name="update-log-option" id="update-log-option-off" value="off"<?php
                                  if (!isset($settings['update-log-option']) || $settings['update-log-option'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="update-log-option-on">
                                  <input type="radio" name="update-log-option" id="update-log-option-on" value="on"<?php
                                  if ($settings['update-log-option'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Update log position in article
                      </th>
                      <td>
                          <fieldset>
                              <label for="update-log-position-off">
                                  <input type="radio" name="update-log-position" id="update-log-position-off" value="off"<?php
                                  if (!isset($settings['update-log-position']) || $settings['update-log-position'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> append
                              </label>
                              <br>
                              <label for="update-log-position-on">
                                  <input type="radio" name="update-log-position" id="update-log-position-on" value="on"<?php
                                  if ($settings['update-log-option'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> prepend
                              </label>
                          </fieldset>
                      </td>
                  </tr>

                  <tr>
                      <th scope="row">
                          <label for="update-log-date-format">Update log date format</label>
                      </th>
                      <td>
                          <input type="text" name="update-log-date-format" id="update-log-date-format" class="regular-text" value="<?php echo($settings['update-log-date-format']); ?>">
                      </td>
                  </tr>
                   <tr>
                      <th scope="row">
                          <label for="update-log-text">Update log date text</label>
                      </th>
                      <td>
                          <input type="text" name="update-log-text" id="update-log-text" class="regular-text" value="<?php echo(!isset($settings['update-log-text']) ? 'This article was updated at' : $settings['update-log-text']); ?>"> 

                          <?php
                          echo date(!empty($settings['update-log-date-format']) ? $settings['update-log-date-format'] : 'Y-m-d H:i'); ?>.
                      </td>
                  </tr>
                  <tr>
                      <th scope="row">
                          Match Superdesk Content Profiles with Wordpress Post Formats 
                      </th>
                      <td>
                          <fieldset>
                              <label for="post-formats-off">
                                  <input type="radio" name="post-formats" id="post-formats-off" value="off"<?php
                                  if ($settings['post-formats'] == 'off') {
                                    echo(' checked');
                                  }
                                  ?>> off
                              </label>
                              <br>
                              <label for="post-formats-on">
                                  <input type="radio" name="post-formats" id="post-formats-on" value="on"<?php
                                  if ($settings['post-formats'] == 'on') {
                                    echo(' checked');
                                  }
                                  ?>> on
                              </label>
                          </fieldset>
                      </td>
                  </tr>
                  <tr>
                      <td colspan="2">
                          <table>
                              <thead>
                                  <tr>
                                      <th>Superdesk Content Profile Name</th>
                                      <th>Wordpress Post Format</th>
                                      <th><a href="javascript:rowAddFunction();">Add row</a></th>
                                  </tr>
                              </thead>
                              <tbody id="post-format-tbody">
                                  <?php
                                  if (isset($settings['post-formats-table']) and is_array($settings['post-formats-table'])) {
                                    foreach ($settings['post-formats-table'] as $key => $value) {
                                      echo make_table_row($value, $key, $post_formats);
                                    }
                                  }
                                  ?>
                              </tbody>
                          </table>
                      </td>
                  </tr>
              </tbody>
          </table>
          <p class="submit">
              <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
          </p>
      </form>
  </div>

  <script type="text/javascript">
    var select_options = <?php echo json_encode($post_formats); ?>;
    var $ = jQuery;

    var rowAddFunction = function () {
        var element = $("#post-format-tbody");
        var new_element = '<tr>\n\
  <td><input type="text" name="post-formats-input[]" class="regular-text" />\n\
  </td><td>\n\
  <select name="post-formats-value[]">';
        $.each(select_options, function (key, value) {
            new_element += '<option value="' + key + '">' + value + '</option>';
        });
        new_element += '</select></td><td><a href="#" onclick="removeThisRow(this); return false;">Delete</a></td></tr>';
        element.append(new_element);
    };

    function debounce(func, wait, immediate) {
        console.log(func);
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate)
                    func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow)
                func.apply(context, args);
        };
    }

    var removeThisRow = function (el) {
        var element = $(el).parent()
                .parent();
        element.remove();
    };

    var validateInput = function () {
        var el = this;
        var element = $(el);
        var text_value = element.val();
        var found = false;
        var input_elements = $("#post-format-tbody input");
        $.each(input_elements, function (_, input_element) {
            if (el !== input_element) {
                if (text_value === $(input_element).val()) {
                    found = true;
                    return false;
                }
            }
        });

        if (found) {
            if (!element.hasClass('spwp-input-error')) {
                element.addClass('spwp-input-error');
            }
        } else {
            if (element.hasClass('spwp-input-error')) {
                element.removeClass('spwp-input-error');
            }
        }

        $("#submit").prop('disabled', found);
    };

    $(document).ready(function () {
        rowAddFunction();
        var validateDebounce = debounce(validateInput, 250);
        $("#post-format-tbody input").live('keyup', validateDebounce);
    });

  </script>
  <style type="text/css">
      .spwp-input-error{
          border-color: red !important;
      }
  </style>
  <?php
}
