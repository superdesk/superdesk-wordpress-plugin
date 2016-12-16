<?php

if (is_admin()) {
  require_once MVP_PLUGIN_DIR . '/admin/admin.php';
} else {
  require_once MVP_PLUGIN_DIR . '/front/front.php';
}

