<?php
/*
  Plugin Name: WordPress Importer
  Plugin URI: http://wordpress.org/extend/plugins/wordpress-importer/
  Description: Import posts, pages, comments, custom fields, categories, tags and more from a WordPress export file.
  Author: wordpressdotorg
  Author URI: http://wordpress.org/
  Version: 0.6.1
  Text Domain: wordpress-importer
  License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if (!defined('WP_LOAD_IMPORTERS')) {
    return;
}

/** Display verbose errors */
define('IMPORT_DEBUG', false);

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if (!class_exists('WP_Importer')) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    require_once $class_wp_importer;
}

// include WXR file parsers
require_once ELESSI_THEME_PATH . '/admin/importer/parsers.php';

/**
 * WordPress Importer class for managing the import process of a WXR file
 *
 * @package WordPress
 * @subpackage Importer
 */
if (class_exists('WP_Importer')) {

    class WP_Import extends WP_Importer {

        public $max_wxr_version = 1.2; // max. supported WXR version
        public $id; // WXR attachment ID
        // information to import from WXR file
        public $version;
        public $authors = array();
        public $posts = array();
        public $terms = array();
        public $categories = array();
        public $tags = array();
        public $base_url = '';
        // mappings from old information to new
        public $processed_authors = array();
        public $author_mapping = array();
        public $processed_terms = array();
        public $processed_posts = array();
        public $post_orphans = array();
        public $processed_menu_items = array();
        public $menu_item_orphans = array();
        public $missing_menu_items = array();
        public $fetch_attachments = false;
        public $url_remap = array();
        public $featured_images = array();

        public function __construct() {
            $this->processed_authors = isset($_SESSION['nasa_import']['processed_authors']) ? $_SESSION['nasa-import']['processed_authors'] : array();
            $this->author_mapping = isset($_SESSION['nasa_import']['author_mapping']) ? $_SESSION['nasa-import']['author_mapping'] : array();
            $this->processed_terms = isset($_SESSION['nasa_import']['processed_terms']) ? $_SESSION['nasa-import']['processed_terms'] : array();
            $this->processed_posts = isset($_SESSION['nasa_import']['processed_posts']) ? $_SESSION['nasa-import']['processed_posts'] : array();
            $this->post_orphans = isset($_SESSION['nasa_import']['post_orphans']) ? $_SESSION['nasa-import']['post_orphans'] : array();
            $this->processed_menu_items = isset($_SESSION['nasa_import']['processed_menu_items']) ? $_SESSION['nasa-import']['processed_menu_items'] : array();
            $this->menu_item_orphans = isset($_SESSION['nasa_import']['menu_item_orphans']) ? $_SESSION['nasa-import']['menu_item_orphans'] : array();
            $this->missing_menu_items = isset($_SESSION['nasa_import']['missing_menu_items']) ? $_SESSION['nasa-import']['missing_menu_items'] : array();
            $this->fetch_attachments = isset($_SESSION['nasa_import']['fetch_attachments']) ? $_SESSION['nasa-import']['fetch_attachments'] : array();
            $this->url_remap = isset($_SESSION['nasa_import']['url_remap']) ? $_SESSION['nasa-import']['url_remap'] : array();
            $this->featured_images = isset($_SESSION['nasa_import']['featured_images']) ? $_SESSION['nasa-import']['featured_images'] : array();

            parent::__construct();
        }

        public function session_processed() {
            $_SESSION['nasa_import'] = array(
                'processed_authors' => $this->processed_authors,
                'author_mapping' => $this->author_mapping,
                'processed_terms' => $this->processed_terms,
                'processed_posts' => $this->processed_posts,
                'post_orphans' => $this->post_orphans,
                'processed_menu_items' => $this->processed_menu_items,
                'menu_item_orphans' => $this->menu_item_orphans,
                'missing_menu_items' => $this->missing_menu_items,
                'fetch_attachments' => $this->fetch_attachments,
                'url_remap' => $this->url_remap,
                'featured_images' => $this->featured_images,
            );
        }

        /**
         * Registered callback public function for the WordPress Importer
         *
         * Manages the three separate stages of the WXR import process
         */
        public function dispatch() {
            $this->header();

            $step = empty($_GET['step']) ? 0 : (int) $_GET['step'];
            switch ($step) {
                case 0:
                    $this->greet();
                    break;
                case 1:
                    check_admin_referer('import-upload');
                    if ($this->handle_upload())
                        $this->import_options();
                    break;
                case 2:
                    check_admin_referer('import-wordpress');
                    $this->fetch_attachments = (!empty($_POST['fetch_attachments']) && $this->allow_fetch_attachments() );
                    $this->id = (int) $_POST['import_id'];
                    $file = get_attached_file($this->id);
                    set_time_limit(0);
                    $this->import($file);
                    break;
            }

            $this->footer();
        }

        /**
         * The main controller for the actual import stage.
         *
         * @param string $file Path to the WXR file for importing
         */
        public function import($file) {
            add_filter('import_post_meta_key', array($this, 'is_valid_meta_key'));
            add_filter('http_request_timeout', array(&$this, 'bump_request_timeout'));

            $this->import_start($file);

            $this->get_author_mapping();

            wp_suspend_cache_invalidation(true);
            $this->process_categories();
            $this->process_tags();
            $this->process_terms();
            $this->process_posts();
            wp_suspend_cache_invalidation(false);

            // update incorrect/missing information in the DB
            $this->backfill_parents();
            $this->backfill_attachment_urls();
            $this->remap_featured_images();

            $this->import_end();
        }

        /**
         * Parses the WXR file and prepares us for the task of processing parsed data
         *
         * @param string $file Path to the WXR file for importing
         */
        public function import_start($file) {
            if (!is_file($file)) {
                echo '<p><strong>' . esc_html__('Sorry, there has been an error.', 'elessi-theme') . '</strong><br />';
                echo esc_html__('The file does not exist, please try again.', 'elessi-theme') . '</p>';
                $this->footer();
                die();
            }

            $import_data = $this->parse($file);

            if (is_wp_error($import_data)) {
                echo '<p><strong>' . esc_html__('Sorry, there has been an error.', 'elessi-theme') . '</strong><br />';
                echo esc_html($import_data->get_error_message()) . '</p>';
                $this->footer();
                die();
            }

            $this->version = $import_data['version'];
            $this->get_authors_from_import($import_data);
            $this->posts = $import_data['posts'];
            $this->terms = $import_data['terms'];
            $this->categories = $import_data['categories'];
            $this->tags = $import_data['tags'];
            $this->base_url = esc_url($import_data['base_url']);

            wp_defer_term_counting(true);
            wp_defer_comment_counting(true);

            do_action('import_start');
        }

        /**
         * Performs post-import cleanup of files and the cache
         */
        public function import_end() {
            wp_import_cleanup($this->id);

            wp_cache_flush();
            foreach (get_taxonomies() as $tax) {
                delete_option("{$tax}_children");
                _get_term_hierarchy($tax);
            }

            wp_defer_term_counting(false);
            wp_defer_comment_counting(false);

            echo '<p>' . esc_html__('Done. Please wait for continuing to import data', 'elessi-theme') . '</p>';

            do_action('import_end');
        }

        /**
         * Handles the WXR upload and initial parsing of the file to prepare for
         * displaying author import options
         *
         * @return bool False if error uploading or invalid file, true otherwise
         */
        public function handle_upload() {
            $file = wp_import_handle_upload();

            if (isset($file['error'])) {
                echo '<p><strong>' . esc_html__('Sorry, there has been an error.', 'elessi-theme') . '</strong><br />';
                echo esc_html($file['error']) . '</p>';
                return false;
            } else if (!file_exists($file['file'])) {
                echo '<p><strong>' . esc_html__('Sorry, there has been an error.', 'elessi-theme') . '</strong><br />';
                printf(__('The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'elessi-theme'), esc_html($file['file']));
                echo '</p>';
                return false;
            }

            $this->id = (int) $file['id'];
            $import_data = $this->parse($file['file']);
            if (is_wp_error($import_data)) {
                echo '<p><strong>' . esc_html__('Sorry, there has been an error.', 'elessi-theme') . '</strong><br />';
                echo esc_html($import_data->get_error_message()) . '</p>';
                return false;
            }

            $this->version = $import_data['version'];
            if ($this->version > $this->max_wxr_version) {
                echo '<div class="error"><p><strong>';
                printf(__('This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'elessi-theme'), esc_html($import_data['version']));
                echo '</strong></p></div>';
            }

            $this->get_authors_from_import($import_data);

            return true;
        }

        /**
         * Retrieve authors from parsed WXR data
         *
         * Uses the provided author information from WXR 1.1 files
         * or extracts info from each post for WXR 1.0 files
         *
         * @param array $import_data Data returned by a WXR parser
         */
        public function get_authors_from_import($import_data) {
            if (!empty($import_data['authors'])) {
                $this->authors = $import_data['authors'];
                // no author information, grab it from the posts
            } else {
                foreach ($import_data['posts'] as $post) {
                    $login = sanitize_user($post['post_author'], true);
                    
                    if (empty($login)) {
                        echo '<p class="nasa-error">';
                        
                        printf(__('Failed to import author %s. Their posts will be attributed to the current user.', 'elessi-theme'), esc_html($post['post_author']));
                        echo '</p><br />';
                        
                        continue;
                    }

                    if (!isset($this->authors[$login])) {
                        $this->authors[$login] = array(
                            'author_login' => $login,
                            'author_display_name' => $post['post_author']
                        );
                    }
                }
            }
        }

        /**
         * Display pre-import options, author importing/mapping and option to
         * fetch attachments
         */
        public function import_options() {
            $j = 0;
            ?>
            <form action="<?php echo admin_url('admin.php?import=wordpress&amp;step=2'); ?>" method="post">
                <?php wp_nonce_field('import-wordpress'); ?>
                <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />

                <?php if (!empty($this->authors)) : ?>
                    <h3><?php esc_html_e('Assign Authors', 'elessi-theme'); ?></h3>
                    <p><?php esc_html_e('To make it easier for you to edit and save the imported content, you may want to reassign the author of the imported item to an existing user of this site. For example, you may want to import all the entries as <code>admin</code>s entries.', 'elessi-theme'); ?></p>
                    <?php if ($this->allow_create_users()) : ?>
                        <p><?php printf(__('If a new user is created by WordPress, a new password will be randomly generated and the new user&#8217;s role will be set as %s. Manually changing the new user&#8217;s details will be necessary.', 'elessi-theme'), esc_html(get_option('default_role'))); ?></p>
                    <?php endif; ?>
                    <ol id="authors">
                        <?php foreach ($this->authors as $author) : ?>
                            <li><?php $this->author_select($j++, $author); ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>

                <?php if ($this->allow_fetch_attachments()) : ?>
                    <h3><?php esc_html_e('Import Attachments', 'elessi-theme'); ?></h3>
                    <p>
                        <input type="checkbox" value="1" name="fetch_attachments" id="import-attachments" />
                        <label for="import-attachments"><?php esc_html_e('Download and import file attachments', 'elessi-theme'); ?></label>
                    </p>
                <?php endif; ?>

                <p class="submit"><input type="submit" class="button" value="<?php esc_attr_e('Submit', 'elessi-theme'); ?>" /></p>
            </form>
            <?php
        }

        /**
         * Display import options for an individual author. That is, either create
         * a new user based on import info or map to an existing user
         *
         * @param int $n Index for each author in the form
         * @param array $author Author information, e.g. login, display name, email
         */
        public function author_select($n, $author) {
            esc_html_e('Import author:', 'elessi-theme');
            
            echo ' <strong>' . esc_html($author['author_display_name']);
            
            echo $this->version != '1.0' ? ' (' . esc_html($author['author_login']) . ')' : '</strong><br />';

            if ($this->version != '1.0') {
                echo '<div style="margin-left:18px">';
            }

            $create_users = $this->allow_create_users();
            if ($create_users) {
                if ($this->version != '1.0') {
                    esc_html_e('or create new user with login name:', 'elessi-theme');
                    $value = '';
                } else {
                    esc_html_e('as a new user:', 'elessi-theme');
                    $value = esc_attr(sanitize_user($author['author_login'], true));
                }

                echo ' <input type="text" name="user_new[' . $n . ']" value="' . $value . '" /><br />';
            }

            if (!$create_users && $this->version == '1.0') {
                esc_html_e('assign posts to an existing user:', 'elessi-theme');
            } else {
                esc_html_e('or assign posts to an existing user:', 'elessi-theme');
            }
            
            wp_dropdown_users(array('name' => "user_map[$n]", 'multi' => true, 'show_option_all' => esc_html__('- Select -', 'elessi-theme')));
            
            echo '<input type="hidden" name="imported_authors[' . $n . ']" value="' . esc_attr($author['author_login']) . '" />';

            if ($this->version != '1.0') {
                echo '</div>';
            }
        }

        /**
         * Map old author logins to local user IDs based on decisions made
         * in import options form. Can map to an existing user, create a new user
         * or falls back to the current user in case of error with either of the previous
         */
        public function get_author_mapping() {
            if (!isset($_POST['imported_authors'])) {
                return;
            }

            $create_users = $this->allow_create_users();

            foreach ((array) $_POST['imported_authors'] as $i => $old_login) {
                // Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
                $santized_old_login = sanitize_user($old_login, true);
                $old_id = isset($this->authors[$old_login]['author_id']) ? intval($this->authors[$old_login]['author_id']) : false;

                if (!empty($_POST['user_map'][$i])) {
                    $user = get_userdata(intval($_POST['user_map'][$i]));
                    if (isset($user->ID)) {
                        if ($old_id) {
                            $this->processed_authors[$old_id] = $user->ID;
                        }
                        
                        $this->author_mapping[$santized_old_login] = $user->ID;
                    }
                } else if ($create_users) {
                    if (!empty($_POST['user_new'][$i])) {
                        $user_id = wp_create_user($_POST['user_new'][$i], wp_generate_password());
                    } else if ($this->version != '1.0') {
                        $user_data = array(
                            'user_login' => $old_login,
                            'user_pass' => wp_generate_password(),
                            'user_email' => isset($this->authors[$old_login]['author_email']) ? $this->authors[$old_login]['author_email'] : '',
                            'display_name' => $this->authors[$old_login]['author_display_name'],
                            'first_name' => isset($this->authors[$old_login]['author_first_name']) ? $this->authors[$old_login]['author_first_name'] : '',
                            'last_name' => isset($this->authors[$old_login]['author_last_name']) ? $this->authors[$old_login]['author_last_name'] : '',
                        );
                        $user_id = wp_insert_user($user_data);
                    }

                    if (!is_wp_error($user_id)) {
                        if ($old_id) {
                            $this->processed_authors[$old_id] = $user_id;
                        }
                        
                        $this->author_mapping[$santized_old_login] = $user_id;
                    } else {
                        echo '<p class="nasa-error">';
                        printf(__('Failed to create new user for %s. Their posts will be attributed to the current user.', 'elessi-theme'), esc_html($this->authors[$old_login]['author_display_name']));
                        
                        if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                            echo ' ' . $user_id->get_error_message();
                        }
                        
                        echo '</p><br />';
                    }
                }

                // failsafe: if the user_id was invalid, default to the current user
                if (!isset($this->author_mapping[$santized_old_login])) {
                    if ($old_id) {
                        $this->processed_authors[$old_id] = (int) get_current_user_id();
                    }
                    
                    $this->author_mapping[$santized_old_login] = (int) get_current_user_id();
                }
            }
        }

        /**
         * Create new categories based on import information
         *
         * Doesn't create a new category if its slug already exists
         */
        public function process_categories() {
            $this->categories = apply_filters('wp_import_categories', $this->categories);

            if (empty($this->categories)) {
                return;
            }

            foreach ($this->categories as $cat) {
                // if the category already exists leave it alone
                $term_id = term_exists($cat['category_nicename'], 'category');
                
                if ($term_id) {
                    if (is_array($term_id)) {
                        $term_id = $term_id['term_id'];
                    }
                    
                    if (isset($cat['term_id'])) {
                        $this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
                    }
                    
                    continue;
                }

                $category_parent = empty($cat['category_parent']) ? 0 : category_exists($cat['category_parent']);
                $category_description = isset($cat['category_description']) ? $cat['category_description'] : '';
                $catarr = array(
                    'category_nicename' => $cat['category_nicename'],
                    'category_parent' => $category_parent,
                    'cat_name' => $cat['cat_name'],
                    'category_description' => $category_description
                );

                $id = wp_insert_category($catarr);
                if (!is_wp_error($id)) {
                    if (isset($cat['term_id'])) {
                        $this->processed_terms[intval($cat['term_id'])] = $id;
                    }
                } else {
                    echo '<p class="nasa-error">';
                    printf(__('Failed to import category %s', 'elessi-theme'), esc_html($cat['category_nicename']));
                    
                    if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                        echo ': ' . $id->get_error_message();
                    }
                    
                    echo '</p><br />';
                    
                    continue;
                }
            }

            unset($this->categories);
        }

        /**
         * Create new post tags based on import information
         *
         * Doesn't create a tag if its slug already exists
         */
        public function process_tags() {
            $this->tags = apply_filters('wp_import_tags', $this->tags);

            if (empty($this->tags)) {
                return;
            }

            foreach ($this->tags as $tag) {
                // if the tag already exists leave it alone
                $term_id = term_exists($tag['tag_slug'], 'post_tag');
                if ($term_id) {
                    
                    if (is_array($term_id)) {
                        $term_id = $term_id['term_id'];
                    }
                    
                    if (isset($tag['term_id'])) {
                        $this->processed_terms[intval($tag['term_id'])] = (int) $term_id;
                    }
                    
                    continue;
                }

                $tag_desc = isset($tag['tag_description']) ? $tag['tag_description'] : '';
                $tagarr = array('slug' => $tag['tag_slug'], 'description' => $tag_desc);

                $id = wp_insert_term($tag['tag_name'], 'post_tag', $tagarr);
                if (!is_wp_error($id)) {
                    if (isset($tag['term_id'])) {
                        $this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
                    }
                } else {
                    echo '<p class="nasa-error">';
                    printf(__('Failed to import post tag %s', 'elessi-theme'), esc_html($tag['tag_name']));
                    
                    if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                        echo ': ' . $id->get_error_message();
                    }
                    
                    echo '</p><br />';
                    continue;
                }
            }

            unset($this->tags);
        }

        /**
         * Create new terms based on import information
         *
         * Doesn't create a term its slug already exists
         */
        public function process_terms() {
            global $wpdb;
            
            $this->terms = apply_filters('wp_import_terms', $this->terms);

            if (empty($this->terms)) {
                return;
            }

            foreach ($this->terms as $term) {
                // if the term already exists in the correct taxonomy leave it alone
                $term_id = term_exists($term['slug'], $term['term_taxonomy']);
                
                if ($term_id) {
                    if (is_array($term_id)) {
                        $term_id = $term_id['term_id'];
                    }
                    
                    if (isset($term['term_id'])) {
                        $this->processed_terms[intval($term['term_id'])] = (int) $term_id;
                    }
                    
                    continue;
                }

                if (empty($term['term_parent'])) {
                    $parent = 0;
                } else {
                    $parent = term_exists($term['term_parent'], $term['term_taxonomy']);
                    if (is_array($parent)) {
                        $parent = $parent['term_id'];
                    }
                }
                $description = isset($term['term_description']) ? $term['term_description'] : '';
                $termarr = array('slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent));

                if (!taxonomy_exists($term['term_taxonomy']) && substr($term['term_taxonomy'], 0, 3) == 'pa_') {
                    $attribute_name = substr($term['term_taxonomy'], 3);
                    // Create the taxonomy
                    if (!in_array($attribute_name, wc_get_attribute_taxonomies())) {
                        $attribute = array(
                            'attribute_label' => ucfirst($attribute_name),
                            'attribute_name' => $attribute_name,
                            'attribute_type' => 'select',
                            'attribute_orderby' => 'menu_order',
                            'attribute_public' => 1
                        );
                        $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute);
                        delete_transient('wc_attribute_taxonomies');
                    }

                    register_taxonomy(
                        $term['term_taxonomy'], apply_filters('woocommerce_taxonomy_objects_' . $term['term_taxonomy'], array('product')), apply_filters('woocommerce_taxonomy_args_' . $term['term_taxonomy'], array(
                            'hierarchical' => true,
                            'show_ui' => false,
                            'query_var' => true,
                            'rewrite' => false,
                        ))
                    );
                }

                $id = wp_insert_term($term['term_name'], $term['term_taxonomy'], $termarr);
                if (!is_wp_error($id)) {
                    if (isset($term['term_id'])) {
                        $this->processed_terms[intval($term['term_id'])] = $id['term_id'];
                    }
                } else {
                    echo '<p class="nasa-error">';
                    printf(__('Failed to import %s %s', 'elessi-theme'), esc_html($term['term_taxonomy']), esc_html($term['term_name']));
                    
                    if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                        echo ': ' . $id->get_error_message();
                    }
                    
                    echo '</p><br />';
                    
                    continue;
                }
            }

            unset($this->terms);
        }

        /**
         * Create new posts based on import information
         *
         * Posts marked as having a parent which doesn't exist will become top level items.
         * Doesn't create a new post if: the post type doesn't exist, the given post ID
         * is already noted as imported or a post with the same title and date already exists.
         * Note that new/updated terms, comments and meta are imported for the last of the above.
         */
        public function process_posts() {
            $this->posts = apply_filters('wp_import_posts', $this->posts);

            foreach ($this->posts as $post) {
                $post = apply_filters('wp_import_post_data_raw', $post);

                if (!post_type_exists($post['post_type'])) {
                    echo '<p class="nasa-error">';
                    printf(__('Failed to import &#8220;%s&#8221;: Invalid post type %s', 'elessi-theme'), esc_html($post['post_title']), esc_html($post['post_type']));
                    echo '</p>';
                    do_action('wp_import_post_exists', $post);
                    continue;
                }

                if (isset($this->processed_posts[$post['post_id']]) && !empty($post['post_id'])) {
                    continue;
                }

                if ($post['status'] == 'auto-draft') {
                    continue;
                }

                if ('nav_menu_item' == $post['post_type']) {
                    $this->process_menu_item($post);
                    
                    continue;
                }

                $post_type_object = get_post_type_object($post['post_type']);

                $post_exists = post_exists($post['post_title'], '', $post['post_date']);
                if ($post_exists && get_post_type($post_exists) == $post['post_type']) {
                    printf(__('%s &#8220;%s&#8221; already exists.', 'elessi-theme'), $post_type_object->labels->singular_name, esc_html($post['post_title']));

                    $comment_post_ID = $post_id = $post_exists;
                } else {
                    $post_parent = (int) $post['post_parent'];
                    if ($post_parent) {
                        // if we already know the parent, map it to the new local ID
                        if (isset($this->processed_posts[$post_parent])) {
                            $post_parent = $this->processed_posts[$post_parent];
                            // otherwise record the parent for later
                        } else {
                            $this->post_orphans[intval($post['post_id'])] = $post_parent;
                            $post_parent = 0;
                        }
                    }

                    // map the post author
                    $author = sanitize_user($post['post_author'], true);
                    $author = isset($this->author_mapping[$author]) ? $this->author_mapping[$author] : (int) get_current_user_id();

                    $postdata = array(
                        'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
                        'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
                        'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
                        'post_status' => $post['status'], 'post_name' => $post['post_name'],
                        'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
                        'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
                        'post_type' => $post['post_type'], 'post_password' => $post['post_password']
                    );

                    $original_post_ID = $post['post_id'];
                    $postdata = apply_filters('wp_import_post_data_processed', $postdata, $post);

                    if ('attachment' == $postdata['post_type']) {
                        $remote_url = !empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

                        // try to use _wp_attached file for upload folder placement to ensure the same location as the export site
                        // e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
                        $postdata['upload_date'] = $post['post_date'];
                        if (isset($post['postmeta'])) {
                            foreach ($post['postmeta'] as $meta) {
                                if ($meta['key'] == '_wp_attached_file') {
                                    if (preg_match('%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches)) {
                                        $postdata['upload_date'] = $matches[0];
                                    }
                                    
                                    break;
                                }
                            }
                        }

                        $comment_post_ID = $post_id = $this->process_attachment($postdata, $remote_url);
                    } else {
                        $comment_post_ID = $post_id = wp_insert_post($postdata, true);
                        do_action('wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post);
                    }

                    if (is_wp_error($post_id)) {
                        echo '<p class="nasa-error">';
                        printf(__('Failed to import %s &#8220;%s&#8221;', 'elessi-theme'), $post_type_object->labels->singular_name, esc_html($post['post_title']));
                        
                        if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                            echo ': ' . $post_id->get_error_message();
                        }
                        
                        echo '</p>';
                        continue;
                    }

                    if ($post['is_sticky'] == 1) {
                        stick_post($post_id);
                    }
                }

                // map pre-import ID to local ID
                $this->processed_posts[intval($post['post_id'])] = (int) $post_id;

                if (!isset($post['terms'])) {
                    $post['terms'] = array();
                }

                $post['terms'] = apply_filters('wp_import_post_terms', $post['terms'], $post_id, $post);

                // add categories, tags and other terms
                if (!empty($post['terms'])) {
                    $terms_to_set = array();
                    foreach ($post['terms'] as $term) {
                        // back compat with WXR 1.0 map 'tag' to 'post_tag'
                        $taxonomy = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
                        $term_exists = term_exists($term['slug'], $taxonomy);
                        $term_id = is_array($term_exists) ? $term_exists['term_id'] : $term_exists;
                        if (!$term_id) {
                            $t = wp_insert_term($term['name'], $taxonomy, array('slug' => $term['slug']));
                            if (!is_wp_error($t)) {
                                $term_id = $t['term_id'];
                                do_action('wp_import_insert_term', $t, $term, $post_id, $post);
                            } else {
                                echo '<p class="nasa-error">';
                                printf(__('Failed to import %s %s', 'elessi-theme'), esc_html($taxonomy), esc_html($term['name']));
                                
                                if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                                    echo ': ' . $t->get_error_message();
                                }
                                
                                echo '</p>';
                                do_action('wp_import_insert_term_failed', $t, $term, $post_id, $post);
                                continue;
                            }
                        }
                        $terms_to_set[$taxonomy][] = intval($term_id);
                    }

                    foreach ($terms_to_set as $tax => $ids) {
                        $tt_ids = wp_set_post_terms($post_id, $ids, $tax);
                        do_action('wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post);
                    }
                    unset($post['terms'], $terms_to_set);
                }

                if (!isset($post['comments'])) {
                    $post['comments'] = array();
                }

                $post['comments'] = apply_filters('wp_import_post_comments', $post['comments'], $post_id, $post);

                // add/update comments
                if (!empty($post['comments'])) {
                    $num_comments = 0;
                    $inserted_comments = array();
                    foreach ($post['comments'] as $comment) {
                        $comment_id = $comment['comment_id'];
                        $newcomments[$comment_id]['comment_post_ID'] = $comment_post_ID;
                        $newcomments[$comment_id]['comment_author'] = $comment['comment_author'];
                        $newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
                        $newcomments[$comment_id]['comment_author_IP'] = $comment['comment_author_IP'];
                        $newcomments[$comment_id]['comment_author_url'] = $comment['comment_author_url'];
                        $newcomments[$comment_id]['comment_date'] = $comment['comment_date'];
                        $newcomments[$comment_id]['comment_date_gmt'] = $comment['comment_date_gmt'];
                        $newcomments[$comment_id]['comment_content'] = $comment['comment_content'];
                        $newcomments[$comment_id]['comment_approved'] = $comment['comment_approved'];
                        $newcomments[$comment_id]['comment_type'] = $comment['comment_type'];
                        $newcomments[$comment_id]['comment_parent'] = $comment['comment_parent'];
                        $newcomments[$comment_id]['commentmeta'] = isset($comment['commentmeta']) ? $comment['commentmeta'] : array();
                        if (isset($this->processed_authors[$comment['comment_user_id']])) {
                            $newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
                        }
                    }
                    
                    ksort($newcomments);

                    foreach ($newcomments as $key => $comment) {
                        // if this is a new post we can skip the comment_exists() check
                        if (!$post_exists || !comment_exists($comment['comment_author'], $comment['comment_date'])) {
                            if (isset($inserted_comments[$comment['comment_parent']]))
                                $comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
                            $comment = wp_filter_comment($comment);
                            $inserted_comments[$key] = wp_insert_comment($comment);
                            do_action('wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post);

                            foreach ($comment['commentmeta'] as $meta) {
                                $value = maybe_unserialize($meta['value']);
                                add_comment_meta($inserted_comments[$key], $meta['key'], $value);
                            }

                            $num_comments++;
                        }
                    }
                    
                    unset($newcomments, $inserted_comments, $post['comments']);
                }

                if (!isset($post['postmeta'])) {
                    $post['postmeta'] = array();
                }

                $post['postmeta'] = apply_filters('wp_import_post_meta', $post['postmeta'], $post_id, $post);

                // add/update post meta
                if (!empty($post['postmeta'])) {
                    foreach ($post['postmeta'] as $meta) {
                        $key = apply_filters('import_post_meta_key', $meta['key'], $post_id, $post);
                        $value = false;

                        if ('_edit_last' == $key) {
                            if (isset($this->processed_authors[intval($meta['value'])])) {
                                $value = $this->processed_authors[intval($meta['value'])];
                            } else {
                                $key = false;
                            }
                        }

                        if ($key) {
                            // export gets meta straight from the DB so could have a serialized string
                            if (!$value) {
                                $value = maybe_unserialize($meta['value']);
                            }

                            add_post_meta($post_id, $key, $value);
                            do_action('import_post_meta', $post_id, $key, $value);

                            // if the post has a featured image, take note of this in case of remap
                            if ('_thumbnail_id' == $key) {
                                $this->featured_images[$post_id] = (int) $value;
                            }
                        }
                    }
                }
            }

            unset($this->posts);
        }

        /**
         * Attempt to create a new menu item from import data
         *
         * Fails for draft, orphaned menu items and those without an associated nav_menu
         * or an invalid nav_menu term. If the post type or term object which the menu item
         * represents doesn't exist then the menu item will not be imported (waits until the
         * end of the import to retry again before discarding).
         *
         * @param array $item Menu item details from WXR file
         */
        public function process_menu_item($item) {
            // skip draft, orphaned menu items
            if ('draft' == $item['status']) {
                return;
            }

            $menu_slug = false;
            if (isset($item['terms'])) {
                // loop through terms, assume first nav_menu term is correct menu
                foreach ($item['terms'] as $term) {
                    if ('nav_menu' == $term['domain']) {
                        $menu_slug = $term['slug'];
                        break;
                    }
                }
            }

            // no nav_menu term associated with this menu item
            if (!$menu_slug) {
                esc_html_e('Menu item skipped due to missing menu slug', 'elessi-theme');
                echo '<br />';
                return;
            }

            $menu_id = term_exists($menu_slug, 'nav_menu');
            if (!$menu_id) {
                printf(__('Menu item skipped due to invalid menu slug: %s', 'elessi-theme'), esc_html($menu_slug));
                echo '<br />';
                return;
            } else {
                $menu_id = is_array($menu_id) ? $menu_id['term_id'] : $menu_id;
            }

            $menuItem = array();
            foreach ($item['postmeta'] as $meta) {
                $menuItem[$meta['key']] = $meta['value'];
            }

            if (isset($menuItem['_menu_item_type'])) {
                if ('taxonomy' == $menuItem['_menu_item_type'] && isset($this->processed_terms[intval($menuItem['_menu_item_object_id'])])) {
                    $menuItem['_menu_item_object_id'] = $this->processed_terms[intval($menuItem['_menu_item_object_id'])];
                } elseif ('post_type' == $menuItem['_menu_item_type'] && isset($this->processed_posts[intval($menuItem['_menu_item_object_id'])])) {
                    $menuItem['_menu_item_object_id'] = $this->processed_posts[intval($menuItem['_menu_item_object_id'])];
                } elseif ('custom' != $menuItem['_menu_item_type']) {
                    // associated object is missing or not imported yet, we'll retry later
                    $this->missing_menu_items[] = $item;
                    return;
                }
            } else {
                $this->missing_menu_items[] = $item;
                return;
            }

            if (isset($this->processed_menu_items[intval($menuItem['_menu_item_menu_item_parent'])])) {
                $menuItem['_menu_item_menu_item_parent'] = $this->processed_menu_items[intval($menuItem['_menu_item_menu_item_parent'])];
            } else if ($menuItem['_menu_item_menu_item_parent']) {
                $this->menu_item_orphans[intval($item['post_id'])] = (int) $menuItem['_menu_item_menu_item_parent'];
                $menuItem['_menu_item_menu_item_parent'] = 0;
            }

            // wp_update_nav_menu_item expects CSS classes as a space separated string
            $menuItem['_menu_item_classes'] = maybe_unserialize($menuItem['_menu_item_classes']);
            if (is_array($menuItem['_menu_item_classes'])) {
                $menuItem['_menu_item_classes'] = implode(' ', $menuItem['_menu_item_classes']);
            }
            $args = array(
                'menu-item-object-id' => $menuItem['_menu_item_object_id'],
                'menu-item-object' => $menuItem['_menu_item_object'],
                'menu-item-parent-id' => $menuItem['_menu_item_menu_item_parent'],
                'menu-item-position' => intval($item['menu_order']),
                'menu-item-type' => $menuItem['_menu_item_type'],
                'menu-item-title' => $item['post_title'],
                'menu-item-url' => $menuItem['_menu_item_url'],
                'menu-item-description' => $item['post_content'],
                'menu-item-attr-title' => $item['post_excerpt'],
                'menu-item-target' => $menuItem['_menu_item_target'],
                'menu-item-classes' => $menuItem['_menu_item_classes'],
                'menu-item-xfn' => $menuItem['_menu_item_xfn'],
                'menu-item-status' => $item['status']
            );

            $id = wp_update_nav_menu_item($menu_id, 0, $args);
            if ($id && !is_wp_error($id)) {
                $this->processed_menu_items[intval($item['post_id'])] = (int) $id;
            }
        }

        /**
         * Process missing menu items
         */
        public function process_missing_menu_items() {
            if (!empty($this->missing_menu_items)) {
                foreach ($this->missing_menu_items as $item) {
                    $this->process_menu_item($item);
                }
            }
        }

        /**
         * If fetching attachments is enabled then attempt to create a new attachment
         *
         * @param array $post Attachment post details from WXR
         * @param string $url URL to fetch attachment from
         * @return int|WP_Error Post ID on success, WP_Error otherwise
         */
        public function process_attachment($post, $url) {
            if (!$this->fetch_attachments) {
                return new WP_Error('attachment_processing_error', esc_html__('Fetching attachments is not enabled', 'elessi-theme'));
            }

            // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
            if (preg_match('|^/[\w\W]+$|', $url)) {
                $url = rtrim($this->base_url, '/') . $url;
            }

            $upload = $this->fetch_remote_file($url, $post);
            if (is_wp_error($upload)) {
                return $upload;
            }

            $info = wp_check_filetype($upload['file']);
                    
            if ($info) {
                $post['post_mime_type'] = $info['type'];
            } else {
                return new WP_Error('attachment_processing_error', esc_html__('Invalid file type', 'elessi-theme'));
            }

            $post['guid'] = $upload['url'];

            // as per wp-admin/includes/upload.php
            $post_id = wp_insert_attachment($post, $upload['file']);
            wp_update_attachment_metadata($post_id, wp_generate_attachment_metadata($post_id, $upload['file']));

            // remap resized image URLs, works by stripping the extension and remapping the URL stub.
            if (preg_match('!^image/!', $info['type'])) {
                $parts = pathinfo($url);
                $name = basename($parts['basename'], ".{$parts['extension']}"); // PATHINFO_FILENAME in PHP 5.2

                $parts_new = pathinfo($upload['url']);
                $name_new = basename($parts_new['basename'], ".{$parts_new['extension']}");

                $this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
            }

            return $post_id;
        }

        /**
         * Attempt to download a remote file attachment
         *
         * @param string $url URL of item to fetch
         * @param array $post Attachment details
         * @return array|WP_Error Local file location details on success, WP_Error otherwise
         */
        public function fetch_remote_file($url, $post) {
            // extract the file name and extension from the url
            $file_name = basename($url);

            // get placeholder file in the upload dir with a unique, sanitized filename
            $upload = wp_upload_bits($file_name, 0, '', $post['upload_date']);
            if ($upload['error']) {
                return new WP_Error('upload_dir_error', $upload['error']);
            }

            // fetch the remote url and write it to the placeholder file
            $remote_response = wp_safe_remote_get($url, array(
                'timeout' => 300,
                'stream' => true,
                'filename' => $upload['file'],
            ));

            $headers = wp_remote_retrieve_headers($remote_response);

            // request failed
            if (!$headers) {
                @unlink($upload['file']);
                return new WP_Error('import_file_error', __('Remote server did not respond', 'elessi-theme'));
            }

            $remote_response_code = wp_remote_retrieve_response_code($remote_response);

            // make sure the fetch was successful
            if ($remote_response_code != '200') {
                @unlink($upload['file']);
                return new WP_Error('import_file_error', sprintf(__('Remote server returned error response %1$d %2$s', 'elessi-theme'), esc_html($remote_response_code), get_status_header_desc($remote_response_code)));
            }

            $filesize = filesize($upload['file']);

            if (isset($headers['content-length']) && $filesize != $headers['content-length']) {
                @unlink($upload['file']);
                return new WP_Error('import_file_error', __('Remote file is incorrect size', 'elessi-theme'));
            }

            if (0 == $filesize) {
                @unlink($upload['file']);
                return new WP_Error('import_file_error', __('Zero size file downloaded', 'elessi-theme'));
            }

            $max_size = (int) $this->max_attachment_size();
            if (!empty($max_size) && $filesize > $max_size) {
                @unlink($upload['file']);
                return new WP_Error('import_file_error', sprintf(__('Remote file is too large, limit is %s', 'elessi-theme'), size_format($max_size)));
            }

            // keep track of the old and new urls so we can substitute them later
            $this->url_remap[$url] = $upload['url'];
            $this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
            // keep track of the destination if the remote url is redirected somewhere else
            if (isset($headers['x-final-location']) && $headers['x-final-location'] != $url) {
                $this->url_remap[$headers['x-final-location']] = $upload['url'];
            }

            return $upload;
        }

        /**
         * Attempt to associate posts and menu items with previously missing parents
         *
         * An imported post's parent may not have been imported when it was first created
         * so try again. Similarly for child menu items and menu items which were missing
         * the object (e.g. post) they represent in the menu
         */
        public function backfill_parents() {
            global $wpdb;

            // find parents for post orphans
            foreach ($this->post_orphans as $child_id => $parent_id) {
                $local_child_id = $local_parent_id = false;
                
                if (isset($this->processed_posts[$child_id])) {
                    $local_child_id = $this->processed_posts[$child_id];
                }
                
                if (isset($this->processed_posts[$parent_id])) {
                    $local_parent_id = $this->processed_posts[$parent_id];
                }

                if ($local_child_id && $local_parent_id) {
                    $wpdb->update($wpdb->posts, array('post_parent' => $local_parent_id), array('ID' => $local_child_id), '%d', '%d');
                }
            }

            // all other posts/terms are imported, retry menu items with missing associated object
            $missing_menu_items = $this->missing_menu_items;
            foreach ($missing_menu_items as $item) {
                $this->process_menu_item($item);
            }

            // find parents for menu item orphans
            foreach ($this->menu_item_orphans as $child_id => $parent_id) {
                $local_child_id = $local_parent_id = 0;
                
                if (isset($this->processed_menu_items[$child_id])) {
                    $local_child_id = $this->processed_menu_items[$child_id];
                }
                
                if (isset($this->processed_menu_items[$parent_id])) {
                    $local_parent_id = $this->processed_menu_items[$parent_id];
                }

                if ($local_child_id && $local_parent_id) {
                    update_post_meta($local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id);
                }
            }
        }

        /**
         * Use stored mapping information to update old attachment URLs
         */
        public function backfill_attachment_urls() {
            global $wpdb;
            // make sure we do the longest urls first, in case one is a substring of another
            uksort($this->url_remap, array(&$this, 'cmpr_strlen'));

            foreach ($this->url_remap as $from_url => $to_url) {
                // remap urls in post_content
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url));
                // remap enclosure urls
                $result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url));
            }
        }

        /**
         * Update _thumbnail_id meta to new, imported attachment IDs
         */
        public function remap_featured_images() {
            // cycle through posts that have a featured image
            foreach ($this->featured_images as $post_id => $value) {
                if (isset($this->processed_posts[$value])) {
                    $new_id = $this->processed_posts[$value];
                    // only update if there's a difference
                    if ($new_id != $value) {
                        update_post_meta($post_id, '_thumbnail_id', $new_id);
                    }
                }
            }
        }

        /**
         * Parse a WXR file
         *
         * @param string $file Path to WXR file for parsing
         * @return array Information gathered from the WXR file
         */
        public function parse($file) {
            $parser = new WXR_Parser();
            return $parser->parse($file);
        }

        // Display import page title
        public function header() {
            echo '<div class="wrap">';
            // screen_icon();
            echo '<h2>' . esc_html__('Import WordPress', 'elessi-theme') . '</h2>';

            $updates = get_plugin_updates();
            $basename = plugin_basename(__FILE__);
            if (isset($updates[$basename])) {
                $update = $updates[$basename];
                echo '<div class="error"><p><strong>';
                printf(__('A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'elessi-theme'), $update->update->new_version);
                echo '</strong></p></div>';
            }
        }

        // Close div.wrap
        public function footer() {
            echo '</div>';
        }

        /**
         * Display introductory text and file upload form
         */
        public function greet() {
            echo '<div class="narrow">';
            echo '<p>' . esc_html__('Howdy! Upload your WordPress eXtended RSS (WXR) file and we&#8217;ll import the posts, pages, comments, custom fields, categories, and tags into this site.', 'elessi-theme') . '</p>';
            echo '<p>' . esc_html__('Choose a WXR (.xml) file to upload, then click Upload file and import.', 'elessi-theme') . '</p>';
            wp_import_upload_form('admin.php?import=wordpress&amp;step=1');
            echo '</div>';
        }

        /**
         * Decide if the given meta key maps to information we will want to import
         *
         * @param string $key The meta key to check
         * @return string|bool The key if we do want to import, false if not
         */
        public function is_valid_meta_key($key) {
            // skip attachment metadata since we'll regenerate it from scratch
            // skip _edit_lock as not relevant for import
            if (in_array($key, array('_wp_attached_file', '_wp_attachment_metadata', '_edit_lock'))) {
                return false;
            }
            
            return $key;
        }

        /**
         * Decide whether or not the importer is allowed to create users.
         * Default is true, can be filtered via import_allow_create_users
         *
         * @return bool True if creating users is allowed
         */
        public function allow_create_users() {
            return apply_filters('import_allow_create_users', true);
        }

        /**
         * Decide whether or not the importer should attempt to download attachment files.
         * Default is true, can be filtered via import_allow_fetch_attachments. The choice
         * made at the import options screen must also be true, false here hides that checkbox.
         *
         * @return bool True if downloading attachments is allowed
         */
        public function allow_fetch_attachments() {
            return apply_filters('import_allow_fetch_attachments', true);
        }

        /**
         * Decide what the maximum file size for downloaded attachments is.
         * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
         *
         * @return int Maximum attachment file size to import
         */
        public function max_attachment_size() {
            return apply_filters('import_attachment_size_limit', 0);
        }

        /**
         * Added to http_request_timeout filter to force timeout at 60 seconds during import
         * @return int 60
         */
        public function bump_request_timeout($val) {
            return 60;
        }

        // return the difference in length between two strings
        public function cmpr_strlen($a, $b) {
            return strlen($b) - strlen($a);
        }

    }

} // class_exists( 'WP_Importer' )