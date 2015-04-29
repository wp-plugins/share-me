<?php

class shareMe {

    static $popinWidth = 545;
    static $popinHight = 433;

    /*
     *  PLUGIN INSTALL
     */

    public static function sm_activation() {

        global $wpdb;
        $table = SM_TABLE_PREFIX . "social_list";
        $structure = "CREATE TABLE  IF NOT EXISTS  $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        name VARCHAR(80) NOT NULL,
        status INT NOT NULL,
	UNIQUE KEY id (id)
         );";
        $wpdb->query($structure);

        // Populate table
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 2, 'facebook', 1));
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 3, 'twitter', 1));
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 4, 'googleplus', 1));
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 5, 'tumblr', 1));
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 6, 'linkedin', 1));
        $wpdb->query($wpdb->prepare("INSERT INTO $table (`id`, `name`, `status`)VALUES ( %d, %s, %d )", 7, 'blogger', 1));

        $table = SM_TABLE_PREFIX . "config";
        $structure = "CREATE TABLE  IF NOT EXISTS  $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        theme VARCHAR(32) NOT NULL,
        h_pos VARCHAR(32) NOT NULL,
        v_pos VARCHAR(32) NOT NULL,
        size INT NOT NULL,
	UNIQUE KEY id (id)
    );";
        $wpdb->query($structure);
        $wpdb->query($wpdb->prepare("INSERT INTO $table  (`id`, `theme`, `h_pos`, `v_pos`, `size`) VALUES ( %d, %s,%s,%s, %d )", 1, 'cercle', 'left', 'up', 32));
    }

    /*
     *  PLUGIN UNINSTALL
     */

    public static function sm_deactivation() {

        global $wpdb;
        $table = SM_TABLE_PREFIX . "config";
        $structure = "drop table if exists $table";
        $wpdb->query($structure);

        $table = SM_TABLE_PREFIX . "social_list";
        $structure = "drop table if exists $table";
        $wpdb->query($structure);
    }

    /*
     * Add plugin's Css and Js to head
     */

    public static function sm_add_style_script() {
        wp_enqueue_style('sm_style', plugins_url('/assets/css/style.css', __FILE__));
        wp_enqueue_script('sm_script', plugins_url('/assets/js/scripts.js', __FILE__));
    }

    public static function sm_admin_style_script() {
        wp_enqueue_style('admin_css', plugins_url('/assets/css/admin-style.css', __FILE__), false, '1.0.0');
    }

    public static function sm_getSocialShare($content) {

        global $wpdb;
        $sm_theme = "circle";
        $sm_h_pos = "right";
        $sm_v_pos = "up";
        $sm_size = "32";
        $socialList = array();

        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . SM_TABLE_PREFIX . "config WHERE %d", 1));
        foreach ($data as $item) {

            $sm_theme = $item->theme;
            $sm_h_pos = $item->h_pos;
            $sm_v_pos = $item->v_pos;
            $sm_size = $item->size;
        }
        $socials = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . SM_TABLE_PREFIX . "social_list WHERE %d", 1));
        if (count($socials) > 0) {
            foreach ($socials as $social) {
                $socialList[] = $social;
            }
        }

        $path = SM_URL . '/assets/images/' . $sm_theme . '/';

        $shares = self::sm_createShareButtons($socialList, $path, $sm_h_pos, $sm_size);

        if ($sm_v_pos == 'up') {
            return $shares . $content;
        } else {
            return $content . $shares;
        }
    }

    /*
     * Generate share buttons
     */

    public static function sm_createShareButtons($socialList, $path, $sm_h_pos, $sm_size) {

        $shares = "<div id='share-me'><ul class='share-" . $sm_h_pos . "'>";
        foreach ($socialList as $item) {

            if ($item->status == 0) {
                continue;
            }

            $shares.='<li>';
            $shares.=self::sm_getLink($item->name);
            $shares.='<img alt = "" src = "' . $path . $item->name . '.png"  height="' . $sm_size . 'px"  width="' . $sm_size . 'px">';
            $shares.='</a>';
            $shares.='</li>';
        }
        return $shares .= "</ul></div><br/>";
    }

    /*
     * Get sharing links
     */

    public static function sm_getLink($type) {
        global $post;
        $blogName = get_option('blogname');
        switch ($type) {
            case 'facebook': $link = '<a href="http://www.facebook.com/sharer.php?u=' . apply_filters("the_permalink", get_permalink()) . '&t=' . urlencode(get_the_title()) . '" alt="Share on Facebook" title="Share on Facebook"   onclick="return smWindowpop(this.href,' . self::$popinWidth . ',' . self:: $popinHight . ')">';
                break;
            case 'twitter': $link = '<a href="http://twitter.com/share?text=' . urlencode(get_the_title()) . '-&url=' . apply_filters("the_permalink", get_permalink()) . '&via=' . $blogName . '" alt="Tweet This Post" title="Tweet This Post"  onclick="return smWindowpop(this.href,' . self::$popinWidth . ',' . self::$popinHight . ')">';
                break;
            case 'googleplus': $link = '<a href="https://plusone.google.com/_/+1/confirm?hl=fr-FR&url=' . apply_filters("the_permalink", get_permalink()) . '" alt="Share on Google+" title="Share on Google+"  target="_blank" onclick="return smWindowpop(this.href,' . self::$popinWidth . ',' . self::$popinHight . ')">';
                break;
            case 'tumblr': $thumbID = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
                $link = '<a href="http://www.tumblr.com/share/photo?source=' . urlencode($thumbID[0]) . '&caption=' . urlencode(get_the_title()) . '&clickthru=' . urlencode(get_permalink()) . '" title="Share on Tumblr"  onclick="return smWindowpop(this.href,' . self::$popinWidth . ',' . self::$popinHight . ')"> ';
                break;
            case 'linkedin': $link = '<a href="http://www.linkedin.com/shareArticle?mini=true&url=' . apply_filters("the_permalink", get_permalink()) . '&title=' . urlencode(get_the_title()) . '&source=' . $blogName . '" onclick="return smWindowpop(this.href, ' . self::$popinWidth . ',' . self::$popinHight . ')">';
                break;
            case 'blogger': $link = '<a href="https://www.blogger.com/blog-this.g?u=' . apply_filters("the_permalink", get_permalink()) . '&n=' . urlencode(get_the_title()) . '&t=' . $blogName . '" alt="Share on Blogger" title="Share on Blogger" onclick="return smWindowpop(this.href, ' . self::$popinWidth . ',' . self::$popinHight . ')">';
                break;
            default: $link = '';
                break;
        }
        return $link;
    }

    /*
     * Set image in header for post share
     */

    public static function sm_get_post_image() {
        global $post;

        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_parent' => $post->ID
        );
        $images = get_posts($args);
        $src = array();
        foreach ($images as $image) {
            $src[] = wp_get_attachment_url($image->ID, array(120, 120));
        }
        if ($src) {
            $postImage = $src [0];
        }
        if (empty($postImage)) {
            $postImage = SM_URL . '/assets/css/images/logo_big.png';
        }
        echo '<meta property="og:image" content="' . $postImage . '"/>';
    }

    /*
     * Add Share-Me to Admin menu
     */

    public static function sm_admin_menu() {
        add_menu_page('Share Me', 'Share Me', 'manage_options', 'share-me/admin-share-me.php', '', plugins_url('/assets/css/images/logo_small.png', __FILE__), 100);
    }

}
