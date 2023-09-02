<?php


function my_theme_enqueue_scripts() {
    wp_enqueue_style('main-css', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.3', 'all');

    wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.1', true);
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');

add_action( 'after_setup_theme', 'theme_register_nav_menu' );

function theme_register_nav_menu() {
	register_nav_menu( 'footer', 'Ссылки в подвале' );
}

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 200, 200 );

}

if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'product-mobile', 400, 400, true );
	add_image_size( 'product-desktop', 200, 200, true );
}



// woocommerce_product_options_pricing

add_action('woocommerce_product_options_pricing', 'add_wc_custom_fields');
function add_wc_custom_fields () {

    global $post;

    $image = wp_get_attachment_image_src(get_post_meta ( $post->ID , '_custom_image', true ), 'product-desktop');

    echo '<div class="options_group">';

    echo '<div class="options_group">';
    echo '    <p class="form-field _custom_image_field ">';
    echo '        <label for="_custom_image">Изображение</label>';
    echo '        ';
    echo '        <input type="file" class="short" ' . ($image ? 'style="display: none;"' : '') . ' name="_custom_image" id="_custom_image" value="null" placeholder="Изображение" accept=".jpg,.jpeg,.png,image/jpeg,image/png">';
    echo '        <img id="custom-image" src="' . ($image ? $image[0] : '') . '" style="' . (!$image ? 'display: none;' : '') . 'object-fit:contain;" alt="custom-image" width="200" height="200">';
    echo '        <br><button id="remove-custom-image" ' . (!$image ? 'style="display: none;"' : '') . ' type="button" class="button button-primary button-large">Remove</button>';
    echo '    </p>';
    echo '    ';
    wp_nonce_field( '_custom_image', '_custom_image_nonce' );

    echo '
        <script>
            document.querySelector("form[name=post]").enctype = "multipart/form-data";

            var fLoader = document.querySelector("#_custom_image");
            var imagePreview = document.querySelector("#custom-image");
            var removeImageButton = document.querySelector("#remove-custom-image");

            fLoader.onchange = function () {
                if (fLoader.files[0]) image.readAsDataURL(fLoader.files[0]);
            }

            const image = new FileReader();

            image.onload = function() {
                imagePreview.src = image.result;
                fLoader.style.display = "none";
                imagePreview.style.display = "block";
                removeImageButton.style.display = "block";
            };

            removeImageButton.onclick = removeImage;

            function removeImage () {
                fLoader.style.display = "block";
                imagePreview.style.display = "none";
                removeImageButton.style.display = "none";
                fLoader.value = null;
            }

        </script>
    ';
    echo '</div>';

    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'                => '_date_of_adding',
        'label'             => __( 'Дата добавления', 'woocommerce' ),
        'placeholder'       => 'Дата добавления',
        'desc_tip'          => 'true',
        'type'              => 'date',
        'custom_attributes' => array( 'required' => 'required' )
     ) );

    woocommerce_wp_select( array(
        'id'      => '_select_product_type',
        'label'   => 'Тип продукта',
        'options' => array(
           -1   => __( 'Не выбран', 'woocommerce' ),
           0   => __( 'Редкий (rare)', 'woocommerce' ),
           1   => __( 'Частый (frequent)', 'woocommerce' ),
           2 => __( 'Необычный (unusual)', 'woocommerce' ),
        ),
    ) );
    echo '</div>';

    

    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'                => '_weight_field',
        'label'             => __( 'Вес (г.)', 'woocommerce' ),
        'placeholder'       => 'Вес (г.)',
        'desc_tip'          => 'true',
        'type'              => 'namber',
        'custom_attributes' => array( 'required' => 'required' )
    ) );
    woocommerce_wp_text_input( array(
        'id'                => '_calory_field',
        'label'             => __( 'Калорийность (Ккал)', 'woocommerce' ),
        'placeholder'       => 'Калорийность (Ккал)',
        'desc_tip'          => 'true',
        'type'              => 'namber',
        'custom_attributes' => array( 'required' => 'required' )
    ) );
    echo '</div>';

    echo '<div class="options_group">';
    echo '<button id="clear-custom-fields" type="button" class="button button-primary button-large">Очистить поля</button>';
    echo '</div>';

    echo '
        <script>
            document.querySelector("#clear-custom-fields").onclick = function () {
                if ( confirm("Вы уверены, что хотите очистить поля?") ) {
                    var fields = document.querySelectorAll("[name=_date_of_adding],[name=_weight_field],[name=_calory_field]");
                    var select = document.querySelector("[name=_select_product_type]");

                    fields.forEach(function (field) {
                        field.value = null;
                    });

                    select.selectedIndex = 0;

                    removeImage();
                }
            }

            var newButton = document.createElement("button");
            newButton.textContent = "UPDATE";
            newButton.className = "button button-primary button-large custom-submit";
            newButton.type = "submit";
            document.querySelector("#publish").remove();
            document.querySelector("#publishing-action").appendChild(newButton);

            newButton.onclick = function () {
                document.querySelector("#publishing-action > .spinner").style.visibility = "visible";
            }
        </script>

        <style>
            .custom-submit {
                background: #6EF1B6 !important;
                border: none !important;
                color: #100E05 !important;
                border-radius: 12px !important;
                transition: transform 0.3s ease-in-out;
            }
            .custom-submit:hover {
                transform: rotate(365deg);
            }
        </style>
    ';
}

add_action( 'woocommerce_process_product_meta', 'save_wc_custom_fields', 10 );
function save_wc_custom_fields( $post_id ) {
	$product = wc_get_product( $post_id );

    if (
        isset( $_POST['_custom_image_nonce'] )
        && wp_verify_nonce( $_POST['_custom_image_nonce'], '_custom_image' )
        && current_user_can( 'edit_post', $post_id )
    ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $attachment_id = media_handle_upload( '_custom_image', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            $attachment_id = null;
        }

        $product->update_meta_data( '_custom_image', $attachment_id ); // json_encode($_FILES['_custom_image']['name'])
    }

    $date_field = isset( $_POST['_date_of_adding'] ) ? sanitize_text_field( $_POST['_date_of_adding'] ) : '';
	$product->update_meta_data( '_date_of_adding', $date_field );

    $type_field = isset( $_POST['_select_product_type'] ) ? sanitize_text_field( $_POST['_select_product_type'] ) : '';
	$product->update_meta_data( '_select_product_type', $type_field );

    $weight_field = isset( $_POST['_weight_field'] ) ? sanitize_text_field( $_POST['_weight_field'] ) : '';
	$product->update_meta_data( '_weight_field', $weight_field );

    $calory_field = isset( $_POST['_calory_field'] ) ? sanitize_text_field( $_POST['_calory_field'] ) : '';
	$product->update_meta_data( '_calory_field', $calory_field );

    $product->save();
}

add_action( 'template_redirect', 'checking_current_user' );
function checking_current_user() 
{
  global $post;

  global $current_user;
  
  $page_id = 18;

  if ( 
      ( $post->post_parent == $page_id || is_page( $page_id ) )
      &&  
      ( !in_array('administrator', $current_user->roles) )
     ) 
  {
    wp_safe_redirect( site_url('/wp-login.php?redirect_to=upravlenie') );
    exit;
  }
  
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'api/v1', '/new-product', array(
      'methods' => 'post',
      'callback' => 'newProductForm',
    ) );
} );