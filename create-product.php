<?php
/*
Template Name: Create product
Template Post Type: page
*/

global $current_user;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['product-name'])) {
        $errors['product-name']= 'Не указано название';
    }
    
    $name = sanitize_text_field($_POST['product-name']);
    
    if (!isset($_POST['product-price'])) {
        $errors['product-price']= 'Не указана цена';
    }
    
    $price = sanitize_text_field($_POST['product-price']);
    
    if (!isset($_POST['product-date'])) {
        $errors['product-date']= 'Не указана дата размещения';
    }
    
    $date = sanitize_text_field($_POST['product-date']);
    
    if (!isset($_POST['product-weight'])) {
        $errors['product-weight']= 'Не указан вес';
    }
    
    $weight = sanitize_text_field($_POST['product-weight']);
    
    if (!isset($_POST['product-calory'])) {
        $errors['product-calory']= 'Не указана калорийность';
    }
    
    $calory = sanitize_text_field($_POST['product-calory']);
    
    if (!isset($_POST['product-type'])) {
        $errors['product-type']= 'Не указан тип продукта';
    }
    
    $type = sanitize_text_field($_POST['product-type']);
    
    if (count($errors) === 0) {
        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_type'  => 'product',
            'post_status'   => 'publish',
            'post_date'  => $date
        ]);
    
        $product = wc_get_product( $post_id );
    
        $product->update_meta_data( '_date_of_adding', $date );
    
        $product->update_meta_data( '_select_product_type', $type );
    
        $product->update_meta_data( '_weight_field', $weight );
    
        $product->update_meta_data( '_calory_field', $calory );
    
        $product->update_meta_data( '_regular_price', $price );
    
        
        if (
            isset( $_POST['_custom_image_nonce'] )
            && wp_verify_nonce( $_POST['_custom_image_nonce'], 'product-image' )
            && current_user_can( 'edit_post', $post_id )
        ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
            $attachment_id = media_handle_upload( 'product-image', 0 );
    
            if ( is_wp_error( $attachment_id ) ) {
                $attachment_id = null;
            }
            
            $product->update_meta_data( '_thumbnail_id', $attachment_id );
            $product->update_meta_data( '_custom_image', $attachment_id );
        }
    
        $product->save();

        header('Location:'.get_permalink().'?result=true');
    }
}

get_header('create-product');

?>

        <?php
            if (isset($_GET['result'])) {
        ?>
        <h2 class="form-add_text">
            ✅ Товар добавлен
        </h2>
        <?php
            }
        ?>

        <form action="" method="POST" class="form-add_form" id="add-product-form" enctype="multipart/form-data">

            <div class="form-add_preview">
                <img id="image-preview" src="" alt="preview-image" style="display: none;" class="form-add_preview-image" width="200" height="200">
            </div>

            <label class="form-add_preview-button">
                <input id="image-upload" type="file" name="product-image" class="form-add_preview-button-input">
                Загрузить изображение
            </label>

            <?php wp_nonce_field( 'product-image', '_custom_image_nonce' ); ?>

            <label class="form_field">
                <input type="text" name="product-name" placeholder="Название товара" class="form_field-input" required>
                <p class="form_field-placeholder">Название товара</p>
            </label>

            <div class="form_fields-row">
                <label class="form_field">
                    <input type="cost" name="product-price" placeholder="Цена (€)" class="form_field-input" required>
                    <p class="form_field-placeholder">Цена (€)</p>
                </label>
                <label class="form_field">
                    <input type="date" name="product-date" placeholder="Дата добавления" class="form_field-input" required>
                    <p class="form_field-placeholder">Дата добавления</p>
                </label>
            </div>


            <div class="form_fields-row">
                <label class="form_field">
                    <input type="number" name="product-weight" placeholder="Вес (г)" class="form_field-input" required>
                    <p class="form_field-placeholder">Вес (г)</p>
                </label>
                <label class="form_field">
                    <input type="number" name="product-calory" placeholder="Калории (Ккал)" class="form_field-input" required>
                    <p class="form_field-placeholder">Калории (Ккал)</p>
                </label>
            </div>

            <label class="form_field">
                <select name="product-type" placeholder="Тип продукта" class="form_field-input">
                    <option value="-1" selected>Не выбран</option>
                    <option value="0">Редкий (rare)</option>
                    <option value="1">Частый (frequent)</option>
                    <option value="2">Необычный (unusual)</option>
                </select>
                <p class="form_field-placeholder">Тип продукта</p>
            </label>

            <button type="submit" class="form-add_submit">Добавить товар</button>
        </form>
    </main>

<?php
wp_footer();
?>
</body>
</html>