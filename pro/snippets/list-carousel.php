        <!-- Indicators -->
        <?php global $de_snippet_list; ?>
        <ol class="carousel-indicators">
        <?php $i = 0; ?>
        <?php while ( direct_list_have_items() ) : direct_list_the_item(); ?>
          <li data-target="#<?php echo $de_snippet_list->owner->settings[ 'attr' ][ 'id' ]; ?>" data-slide-to="<?php echo $i; ?>"<?php echo ( $i ? '' : ' class="active"' ); ?>></li>
          <?php $i ++; ?>
        <?php endwhile; ?>
        </ol>
        <div class="carousel-inner">
        <?php direct_list_rewind_items(); ?>
        <?php $i = 0; ?>
        <?php while ( direct_list_have_items() ) : direct_list_the_item(); ?>
          <div class="item<?php echo ( $i ? '' : ' active' ); ?>">
            <?php direct_editable( 'image', 'postmeta', array( 'options' => 'carousel-image', 'key' => 'slide_image', 'postId' => $direct_list_item->ID, 'default' => 'holder.js/400x300/auto/#666:#777/text:doubleclick to insert image' ) ); ?>
            <div class="container">
              <div class="carousel-caption">
                <?php direct_editable( 'text', 'postmeta', array( 'options' => 'title', 'postId' => $direct_list_item->ID, 'key' => 'slide_title', 'container' => 'h1' ) ); ?>
                <?php direct_editable( 'text', 'postmeta', array( 'options' => 'title', 'postId' => $direct_list_item->ID, 'key' => 'slide_text', 'container' => 'p' ) ); ?>
                <p><?php direct_editable( 'link', 'postmeta', array( 'key' => 'slide_link', 'postId' => $direct_list_item->ID, 'buttonEditLink' => false, 'buttonFollowLink' => false, 'attr' => array( 'class' => 'btn btn-lg btn-primary', 'role' => 'button' ) ) ); direct_editable( 'text', 'postmeta', array( 'options' => 'title', 'postId' => $direct_list_item->ID, 'key' => 'slide_link_text', 'container' => 'span' ) ); ?></a></p>
              </div>
            </div>
          </div>
          <?php $i ++; ?>
        <?php endwhile; ?>
        </div>
        <?php if ( $i ) { ?>
        <a class="left carousel-control" href="#<?php echo $de_snippet_list->owner->settings[ 'attr' ][ 'id' ]; ?>" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
        <a class="right carousel-control" href="#<?php echo $de_snippet_list->owner->settings[ 'attr' ][ 'id' ]; ?>" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
        <?php } ?>
