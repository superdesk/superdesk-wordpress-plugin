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
  if (isset($_POST['url'])) {
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
        'convert-keywords' => $_POST['convert-keywords'],
        'import-keywords' => $_POST['import-keywords'],
        'convert-services' => $_POST['convert-services'],
        'subject-type' => $_POST['subject-type'],
        'category' => $_POST['category'],
        'separator-caption-image' => $_POST['separator-caption-image'],
        'copyrightholder-image' => $_POST['copyrightholder-image'],
        'copyrightnotice-image' => $_POST['copyrightnotice-image'],
        'separator-located' => $_POST['separator-located'],
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
        'convert-keywords' => '',
        'import-keywords' => '',
        'convert-services' => '',
        'subject-type' => '',
        'category' => '',
        'separator-caption-image' => '',
        'copyrightholder-image' => '',
        'copyrightnotice-image' => '',
        'separator-located' => '',
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
              Status of the ingested articles/posts
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
              Display the copyright information
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
              Convert slugline keywords into WP tags
            </th>
            <td>
              <fieldset>
                <label for="off">
                  <input type="radio" name="convert-keywords" id="off" value="off"<?php
                  if ($settings['convert-keywords'] == 'off') {
                    echo(' checked');
                  }
                  ?>> off
                </label>
                <br>
                <label for="on">
                  <input type="radio" name="convert-keywords" id="on" value="on"<?php
                  if ($settings['convert-keywords'] == 'on') {
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
                  if (in_array($key, $settings['category'])) {
                    echo(' selected');
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
              Display the copyrightholder image
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
              Display the copyrightnotice image
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
        </tbody>
      </table>
      <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
      </p>
    </form>
  </div>
  <?php
}
