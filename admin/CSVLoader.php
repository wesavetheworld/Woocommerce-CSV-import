<?php

if ( ! session_id() ) session_start();
$post_type	= isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'product';
$separator	= isset( $_REQUEST['separator'] ) ? $_REQUEST['separator'] : '|';
$titeled	= isset( $_REQUEST['titeled'] );
$taxonomy	= isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : 'taxonomy';
$multi_cat	= isset( $_REQUEST['multi_cat'] ) ? $_REQUEST['multicat'] : 'multi_cat';


$cat		= isset( $_REQUEST['jigo_cat'] ) ? $_REQUEST['jigo_cat'] : '';
$jigo_status	= isset( $_REQUEST['jigo_status'] ) ? $_REQUEST['jigo_status'] : '';
$data	= array();
$titles	= array();
if ( isset( $_REQUEST['jigo_load_csv'] ) && isset( $_FILES['upload_file'] ) ) {
	if ( ( $handle = fopen( $_FILES['upload_file']['tmp_name'], 'r' ) ) !== FALSE ) {
		while ( ( $line = fgetcsv($handle, 1024, $separator ) ) !== FALSE )
			$data[] = $line;
		fclose( $handle );
		if ( $titeled ) {
			$titles = $data[0];
			unset( $data[0] );
		} else {
			for( $i = 0; $i < count( $data[0] ); $i++ )
				$titles[] = 'col_' . $i;
		}
	}
	$_SESSION['jigo_csv_titles'] = $titles;
	$_SESSION['jigo_csv_data'] = $data;
} elseif ( isset( $_REQUEST['jigo_load_products_from_csv'] ) && isset( $_SESSION['jigo_csv_titles'] ) ) {
	$titles = $_SESSION['jigo_csv_titles'];
	$data = $_SESSION['jigo_csv_data'];
	unset( $_SESSION['jigo_csv_titles'] );
	unset( $_SESSION['jigo_csv_data'] );
	if ( is_array( $data ) ) {
		$taxonomies = get_object_taxonomies( $post_type );
		$count = 0;
		$i = 0;
		foreach( $data as $cols ) {
			$i++;
			$name = '';
			$content = '';
			$excerpt = '';
			$price = 0;
			$order = '';
			$weight = 0;
			$sku = '';
			$stock = -1;
			$tax = 0;
			$attachments = array();
			$thumbnail = '';
			$custom_values = array();
			$taxo_values = array();
			//
			$multi_cat_value = array();
			$taxo_attribs = array();
			//
			foreach( $cols as $i => $col ) {
				$col_name = isset( $_REQUEST['col_' . $i] ) ? $_REQUEST['col_' . $i] : '';
				if ( $col_name == 'jigo_name' ) {
					$name = $col;
				} elseif ( $col_name == 'jigo_content' ) {
					$content = $col;
				} elseif ( $col_name == 'jigo_excerpt' ) {
					$excerpt = $col;
				} elseif ( $col_name == 'jigo_price' ) {
					$price = $col;
				} elseif ( $col_name == 'jigo_order' ) {
					$order = $col;
				} elseif ( $col_name == 'jigo_weight' ) {
					$weight = (float)$col;
				} elseif ( $col_name == 'jigo_sku' ) {
					$sku = $col;
				} elseif ( $col_name == 'jigo_stock' ) {
					$stock = (int)$col;
				} elseif ( $col_name == 'jigo_tax' ) {
					$tax = (int)$col;
					//
					} elseif ( $col_name == 'multi_cat' ) {
					$multi_cat = $col;
					//
				} elseif ( $col_name == 'jigo_attachment' ) {
					$attachments[] = $col;
				} elseif ( $col_name == 'jigo_thumbnail' ) {
					$thumbnail = $col;
					//
					} elseif ( $col_name == 'attribs' ) {
					$taxo_attribs = $col;
					//
				} else {
					$break = false;
					if ( is_array( $custom_field_defs ) && count( $custom_field_defs ) > 0 ) {
						foreach( $custom_field_defs as $custom_field_def ) {
							if ( $col_name == $custom_field_def['id'] ) {
								$custom_values[$col_name] = $col;
								$break = true;
								break;
							}
						}
					}
					if ( ! $break && is_array( $taxonomies ) && count( $taxonomies ) > 0 ) {
						foreach( $taxonomies as $taxmy ) {
							if ( $col_name == 'jigo_tax_' . $taxmy ) {
								$taxo_values[$taxmy] = $col;
								$break = true;
								break;
							}
						}
					}
				}
			}
			$post = array(
				'comment_status'=> 'open',
				'post_content'	=> $content,
				'post_excerpt'	=> $excerpt,
				'post_status'	=> $jigo_status,
				'post_title'	=> $name,
				'post_type'		=> $post_type,
			);
			$post_id = wp_insert_post( $post );
			if ( $cat > 0 ) {
				wp_set_object_terms( $post_id, (int)$cat, $taxonomy, false );
			}

			update_post_meta( $post_id, 'visibility', 'visible' );
			update_post_meta( $post_id, 'sku', $sku );
			update_post_meta( $post_id, 'regular_price', $price );
			update_post_meta( $post_id, 'weight', $weight );
			update_post_meta( $post_id, 'stock', $stock );
			update_post_meta( $post_id, 'featured', 'no' );
			update_post_meta( $post_id, 'sale_price_dates_from', '' );
			update_post_meta( $post_id, 'sale_price_dates_to', '' );

			//****** need to make a product_data and variation post insert ******//
			//update_post_meta( $post_id, 'jigo_tax_id', $tax );
			//update_post_meta( $post_id, 'jigo_is_downloadable', false );
			//update_post_meta( $post_id, 'jigo_max_downloads', 0 );
			//update_post_meta( $post_id, 'jigo_days_to_expire', 0 );
			//update_post_meta( $post_id, 'jigo_type', 'SIMPLE' );
			//update_post_meta( $post_id, 'jigo_weight', $weight );
			//update_post_meta( $post_id, 'jigo_order', $order );

			////

			$pdata=array();
$pdata['sku'] = $sku;
$pdata['regular_price'] = $price;
$pdata['sale_price'] = '';
$pdata['featured'] = 'no';
$pdata['weight'] = $weight;
$pdata['tax_status'] = $taxable;
$pdata['tax_class'] = '';
$pdata['stock_status'] = 'instock';
$pdata['manage_stock'] = 'no';
$pdata['backorders'] = 'no';
update_post_meta( $post_id, 'product_data', $pdata );




			//
$field= $taxo_attribs;
if((!empty($field)) && (explode(',',$field) !=FALSE))
{
$datas=explode(',',$field);
$attrib=array();
for($i=0;$i<count($datas);++$i)
{
$value=explode(':',$datas[$i]);
if (!empty($value[0]))
{
				if ( taxonomy_exists('pa_'.sanitize_title($value[0])) )
				{
				} else
				{
register_taxonomy( 'pa_'.sanitize_title($value[0]), 'post', array( 'hierarchical' => false, 'label' => 'pa_'.sanitize_title($value[0]) ) );

 $wpdb->insert( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_name' => $value[0], 'attribute_type' => 'text' ), array( '%s', '%s' ) );
}



if(term_exists($value[1], 'pa_'.sanitize_title($value[0])))
{ }
else {
wp_insert_term(	$value[1], 'pa_'.sanitize_title($value[0]), array( 'slug' => $value[1] ) );
}
				wp_set_object_terms( $post_id, $value[1], 'pa_'.sanitize_title($value[0]), true );

/*
$term = $value[1];
$tax = 'pa_'.sanitize_title($value[0]);
$new_term = term_exists($term, $tax);
				if ( ! is_array( $new_term ) )
					$new_term = wp_insert_term(	$term , 'pa_'.sanitize_title($value[0]), array( 'slug' => $term ) );
				wp_set_object_terms( $post_id, $term, $tax, true );
*/


$value_sanitized = sanitize_title($value[0]);
$attrib[$value_sanitized]=
array('name' =>  htmlspecialchars(stripslashes($value[0])),
'value' => $value[1],
'position' => '0',
'visible' => 'yes',
'variation' => 'no',
'is_taxonomy' => 'yes'
);
}
update_post_meta($post_id, 'product_attributes', $attrib);
}


			foreach( $custom_values as $id => $custom_value ) {
				update_post_meta( $post_id, $id, $custom_value );
			}
			}
/////// explode field multicat ',' separator

$prod_cats = explode(',',$multi_cat);
for($i=0;$i<count($prod_cats);++$i)
{
				$new_cat = term_exists( $prod_cats[$i], 'product_cat' );
				if ( ! is_array( $new_cat ) ) {
					wp_insert_term(	$prod_cats[$i], 'product_cat', array( 'slug' => $prod_cats[$i], 'parent'=> $parent) );
					$new_cat = term_exists( $prod_cats[$i], 'product_cat' );
					if($hierarchical_multicat)
					{
					$parent = $new_cat['term_id'];
					}
					}
				wp_set_object_terms( $post_id, (int)$new_cat['term_id'], 'product_cat', true );


			}
			unset($parent);

			foreach( $taxo_values as $tax => $term ) {
				$new_term = term_exists( $term, $tax );
				if ( ! is_array( $new_term ) )
					$new_term = wp_insert_term(	$term, $tax, array( 'slug' => $term ) );
				wp_set_object_terms( $post_id, (int)$new_term['term_id'], $tax, true );
			}


			foreach( $attachments as $url ) {
				//$url = urldecode( $url );
				$base = basename( $url );
				$path = wp_upload_dir();
				$path = $path['path'];
				$dest = $path . '/' . $base;
				copy( $url, $dest );
				$wp_filetype = wp_check_filetype( basename( $dest ), null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => preg_replace('/\.[^.]+$/', '', basename( $dest ) ),
					'post_content' => '',
					'post_status' => 'inherit',
				);
				$attach_id = wp_insert_attachment( $attachment, $dest, $post_id );
				// you must first include the image.php file for the function wp_generate_attachment_metadata() to work
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
			}

			if ( strlen( $thumbnail ) > 0 ) {
				$base = basename( $thumbnail );
				$path = wp_upload_dir();
				$path = $path['path'];
				$dest = $path . '/' . $base;
				copy( $thumbnail, $dest );
				$wp_filetype = wp_check_filetype( basename( $dest ), null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => preg_replace('/\.[^.]+$/', '', basename( $dest ) ),
					'post_content' => '',
					'post_status' => 'inherit',
				);
				$attach_id = wp_insert_attachment( $attachment, $dest, $post_id );
				// you must first include the image.php file for the function wp_generate_attachment_metadata() to work
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta( $post_id, '_thumbnail_id', $attach_id );
			}
			$count++;
		}
		?>
		<div id="message" class="updated"><p>
			<?php printf( __( '%s products have been uploaded', 'jigo_csvl' ), $count );?>
		</p></div><?php
	} else { ?>
		<div id="message" class="error"><p>
			<?php _e( 'No product has been uploaded', 'jigo_csvl' );?>
		</p></div><?php
	}
}
?>
<div class="wrap">

<h2><?php echo __( 'CSV Loader for JigoShop', 'jigo_csvl' );?></h2>

<p>Wait! Wait! Wait! It's a very alpha version, just to test NOT USE IN YOUR SITE, JUST TEST!</p>
<p>To use a hierarchical categorie (parent/children) make a column multi_cat and writing in the fields the hierarchical category, ie: hardware,memory,sodimm,ddr3</p>
<p>I don't know why but if you use hierarchicals categories you need to edit and save a categorie one time to see the childrens (???)</p>
<p>******** pfff hierarchical categorie does not work. I do not know why. I'm tired, I go to bed.********</p>
<p>To import attibuts, make a column attribs, use this format in the fields ie: size:128,kit:yes,cas:7</p>
<p>Actualy just simple type product, no variation,virtual...</p>
<p>Sorry for my bad english, i'm french... ;) </p>
<p> Choice the original, choice Jigoshop!</p>
<p>colin</p>
<p><i>for information: the original plugin i hacked is TheCartPress CSV Loader, license GPL.</i></p>
</color>
<ul class="subsubsub">
</ul><!-- subsubsub -->

<div class="clear"></div>

<form method="post" enctype="multipart/form-data">
	<table class="form-table">
	<tbody>
	<tr valign="top">
	<th scope="row">
		<label for="post_type"><?php _e( 'Post type', 'tcp' )?>:</label>
	</th>
	<td>
		<select name="post_type" id="post_type">

			<option value="product">Product</option>

		</select>
		<input type="submit" name="jigo_load_taxonomies" value="<?php _e( 'Load taxonomies', 'tcp' );?>" class="button-secondary"/>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row">
		<label for="taxonomy"><?php _e( 'Taxonomy by default (product tag is the best)', 'tcp' )?>:</label>
	</th>
	<td>
		<select name="taxonomy" id="taxonomy">
		<?php foreach( get_object_taxonomies( $post_type ) as $taxmy ) : $tax = get_taxonomy( $taxmy );
if (preg_match('/pa_/', $taxmy) == 0)  {?>
		<option value="<?php echo esc_attr( $taxmy );?>"<?php selected( $taxmy, $taxonomy ); ?>><?php echo $tax->labels->name;?></option>
		<?php } ?>
		<?php endforeach;?>
		</select>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row">
		<label for="separator"><?php _e( 'Separator', 'jigo_csvl' );?>:</label>
	</th>
	<td>
		<input type="text" name="separator" id="separator" value="<?php echo $separator;?>" size="2" maxlenght="4"/>
		<label for="titeled"><?php _e( 'Columns title in first line', 'jigo_csvl' );?>:</label>
		<input type="checkbox" name="titeled" id="titeled" <?php checked($titeled);?> size="2" maxlenght="4" checked />
	</td>
	</tr>

	<tr valign="top">
	<th scope="row">
		<label for="hierarchical_multicat"><?php _e( 'Hierarchical categories', 'jigo_csvl' );?>:</label>
	</th>
	<td>
		<label for="titeled"><?php _e( 'Hierarchical Categories', 'jigo_csvl' );?>:</label>
		<input type="checkbox" name="hierarchical_multicat" id="hierarchical_multicat" <?php checked($hierarchical_multicat);?> size="2" maxlenght="4" checked />
	</td>
	</tr>

	<tr valign="top">
	<th scope="row">
		<label for="upload_file" value=""><?php _e( 'file', 'jigo_csvl' );?>:</label>
	</th>
	<td>
		<input type="file" name="upload_file" id="upload_file" />
	</td>
	</tr>
	</tbody>
	</table>
	<span class="submit"><input type="submit" name="jigo_load_csv" id="jigo_load_csv" value="<?php _e( 'Load', 'jigo_csvl' );?>" style="button-secondary" /></span>
	<span><?php _e( 'This action helps you to test if the file is correct. Only 4 rows will be displayed.', 'jigo_csvl' );?></span>
</form>
<?php if ( is_array( $data ) && count( $data ) > 0 ) : ?>
<p><?php _e( 'These lines are the four first products loaded from the CSV file. If you think they are correct continue with the process.', 'jigo_csvl' );?></p>
<table class="widefat fixed" cellspacing="0">
	<?php if ( is_array( $titles ) && count( $titles ) > 0 ) :?>
		<thead>
		<tr scope="col" class="manage-column"><th>&nbsp;</th>
		<?php foreach( $titles as $col ) : ?>
			<th><?php echo $col;?></th>
		<?php endforeach;?>
		</tr>
		</thead>
		<tfoot>
		<tr scope="col" class="manage-column"><th>&nbsp;</th>
		<?php foreach( $titles as $col ) : ?>
			<th><?php echo $col;?></th>
		<?php endforeach;?>
		</tr>
		</tfoot>
	<?php endif;?>
		<tbody>
		<?php foreach( $data as $i => $cols ) :
			if ( $i > 4 ) :
				break;
			else : ?>
				<tr>
					<td><?php echo  $i;?></td>
				<?php foreach( $cols as $col ) : ?>
					<td><?php echo $col;?></td>
				<?php endforeach;?>
				</tr>
			<?php endif;?>
		<?php endforeach;?>
	</tbody>
</table>
<p><?php _e( 'Assign the columns of the CSV file (left column) to the fields of the products (right column).', 'jigo_csvl' );?></p>
<form method="post">
<input type="hidden" name="post_type" value="<?php echo $post_type;?>" />
<input type="hidden" name="taxonomy" value="<?php echo $taxonomy;?>" />
<input type="hidden" name="separator" value="<?php echo isset( $_REQUEST['separator'] ) ? $_REQUEST['separator'] : '|';?>" />
<?php if ( isset( $_REQUEST['titeled'] ) ) :?>
<input type="hidden" name="titeled" value="y"/>
<?php endif;?>
<table class="widefat fixed" cellspacing="0">
<thead>
	<tr scope="col" class="manage-column">
		<th><?php _e( 'Imported columns', 'jigo_csvl' );?></th>
		<th><?php _e( 'JigoShop columns', 'jigo_csvl' );?></th>
	</tr>
</thead>
<tfoot>
	<tr scope="col" class="manage-column">
		<th><?php _e( 'CSV columns', 'jigo_csvl' );?></th>
		<th><?php _e( 'JigoShop columns', 'jigo_csvl' );?></th>
	</tr>
</tfoot>
<tbody>
<?php if ( is_array( $titles ) && count( $titles ) > 0 ) : ?>
	<?php foreach( $titles as $i => $col ) : ?>
		<tr>
			<td><?php echo $col;?></td>
			<td>
			<select name="col_<?php echo $i;?>">
				<option value=""><?php _e( 'None', 'jigo_csvl' );?></option>
				<option value="jigo_name" <?php selected( strtoupper( $col ), 'NAME');?>>Title (<?php _e( 'Title', 'jigo_csvl' );?>)</option>
				<option value="jigo_content" <?php selected( strtoupper( $col ), 'CONTENT');?>>Content (<?php _e( 'Content', 'jigo_csvl' );?>)</option>
				<option value="jigo_excerpt" <?php selected( strtoupper( $col ), 'EXCERPT');?>>Excerpt (<?php _e( 'Excerpt', 'jigo_csvl' );?>)</option>
				<option value="jigo_price" <?php selected( strtoupper( $col ), 'PRICE');?>>Price (<?php _e( 'Price', 'jigo_csvl' );?>)</option>
				<option value="jigo_stock" <?php selected( strtoupper( $col ), 'STOCK');?>>Stock (<?php _e( 'Stock', 'jigo_csvl' );?>)</option>
				<option value="jigo_weight" <?php selected( strtoupper( $col ), 'WEIGHT');?>>Weight (<?php _e( 'Weight', 'jigo_csvl' );?>)</option>
				<option value="jigo_sku" <?php selected( strtoupper( $col ), 'sku');?>>sku (<?php _e( 'SKU', 'jigo_csvl' );?>)</option>
				<option value="jigo_order" <?php selected( strtoupper( $col ), 'ORDER');?>>Order (<?php _e( 'Order', 'jigo_csvl' );?>)</option>
				<option value="jigo_tax" <?php selected( strtoupper( $col ), 'TAX');?>>Tax (<?php _e( 'Tax', 'jigo_csvl' );?>)</option>
				<option value="jigo_attachment" <?php selected( strtoupper( $col ), 'ATTACHMENT');?>>Attachment (<?php _e( 'Attachment', 'jigo_csvl' );?>)</option>
				<option value="jigo_thumbnail" <?php selected( strtoupper( $col ), 'THUMBNAIL');?>>Thumbnail (<?php _e( 'Thumbnail', 'jigo_csvl' );?>)</option>

				<option value="multi_cat" <?php selected( strtoupper( $col ), 'MULTI_CAT');?>>MultiCat (<?php _e( 'multi_cat', 'multi_cat' );?>)</option>

				<option value="attribs" <?php selected( strtoupper( $col ), 'attribs');?>>Attrib (<?php _e( 'Attribs', 'attribs' );?>)</option>

				<?php foreach( get_object_taxonomies( $post_type ) as $taxmy ) : $tax = get_taxonomy( $taxmy ); ?>
				<option value="jigo_tax_<?php echo $taxmy;?>">T <?php echo $tax->labels->name;?></option>
				<?php endforeach;?>


			</select>
			</td>
		</tr>
	<?php endforeach;?>
<?php endif;?>
</tbody>
</table>

<p>
	<label for="jigo_status"><?php _e( 'Set products status to', 'jigo_csvl' )?>:</label>
	<select id="jigo_status" name="jigo_status">
		<option value="publish"><?php _e( 'publish', 'jigo_cvsl' );?></option>
		<option value="draft"><?php _e( 'draft', 'jigo_cvsl' );?></option>
	</select>
</p>
<span class="submit">
	<input type="submit" name="jigo_load_products_from_csv" id="jigo_load_products_from_csv" value="<?php _e( 'Upload', 'jigo_csvl' );?>" class="button-primary" />
	<span><?php _e( 'This action will load the products in the eCommerce. Be patient.', 'jigo_csvl' );?></span>
</span>
</form>
<?php endif;?>
</div>

