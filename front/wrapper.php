<?php

function mvp_view($data) {
  if (!empty($data)) {
    $tmp = json_decode($data);

    foreach ($tmp->_items as $article) {
      if ($article->pubstatus == "usable") {
        ?>
        <div class="article">
          <h2><?php echo $article->headline ?></h2>
          <?php echo $article->body_html; ?>
        </div>
        <?php
      }
      
    }
  }
}
