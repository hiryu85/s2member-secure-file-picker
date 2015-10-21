<?php
/*
Plugin Name: s2member Secure-File picker 
Description: A plugin for uploading files to the secure-files location of the <a href="http://www.s2member.com/2496.html" target="_blank">s2member WordPress Membership plugin</a>
Author: Mirko Chialastri
Version: 0.0.1
Author URI: m.chialastri@tingadv.com
Tags: s2member, secure, files, downloads, security, enabled
*/


add_action( 'admin_init', 'S2FilePicker_init', 1 );

function S2FilePicker_init() {
		
	if ( is_plugin_active( 's2member/s2member.php' ) ) {
		
		/**/
		if ( !is_dir( $files_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['files_dir'] ) )
			if ( is_writable( dirname ( c_ws_plugin__s2member_utils_dirs::strip_dir_app_data( $files_dir ) ) ) )
				mkdir ( $files_dir, 0777, true );
		/**/
		if ( is_dir( $files_dir ) && is_writable( $files_dir ) )
			if ( !file_exists( $htaccess = $files_dir . '/.htaccess' ) || !apply_filters ( 'ws_plugin__s2member_preserve_files_dir_htaccess', false, get_defined_vars() ) )
				file_put_contents( $htaccess, trim( c_ws_plugin__s2member_utilities::evl( file_get_contents( $GLOBALS['WS_PLUGIN__']["s2member"]["c"]['files_dir_htaccess'] ) ) ) );
		
		add_action( 'media_buttons', 'S2FilePicker_btn', 20 );
		add_action( 'media_upload_s2fpicker', 'S2FilePicker_media_upload_handler' );
		add_action( 'media_upload_type_S2FilePicker_frame', 'S2FilePicker_media_upload_content' );	
	} else {
		
		add_action('admin_notices', 'S2FilePicker_warning');
		
	}

}


function S2FilePicker_btn( $editor_id = 'content' ) {
	
	printf('
		<a href="%s" id="add_s2fpicker" class="button thickbox add_s2fpicker" title="%s">
			Link s2member file
		</a>
		',
		esc_url(get_upload_iframe_src( 's2fpicker' )),
		__( 'Add s2member Secure-File', 's2sfu')

	);
	
}


function S2FilePicker_warning() {
	
	echo "
	<div id='s2sfu-warning' class='updated fade'><p><strong>" . __( 's2member secure-file Uploader is almost ready.' ) . "</strong> " . sprintf( __( 'You must install and activate the <a href="%1$s">s2member WordPress Membership Plugin</a> for it to work.' ), "http://www.s2member.com/2496.html" ) . "</p></div>
	";
	
}


function S2FilePicker_media_upload_handler() {
	add_filter( 'media_upload_tabs', '__return_false' );

	wp_enqueue_style('media');
	wp_iframe('S2FilePicker_frame');
}

function S2FilePicker_frame() {
	media_upload_header();

	$path  = WP_PLUGIN_DIR. '/s2member-files';
	$files = glob($path . '/*.{mp3,mp4,avi,mpeg,doc,docx,pdf,txt}', GLOB_BRACE);
	$html  = '<form action="" id="s2member-files-archive-form">';

	foreach ($files as $i => $file) {
		$html .= '<input type="checkbox" data-filename="' .basename($file). '" class="file" name="file[]" />';
		$html .= basename($file);
	}

	$html .= '
		<br />
		<br />
		<button type="button" onclick="javascript:s2member_files_pick();">'. esc_attr(__('Link selected files')) .'</button>
		<script>
		function s2member_files_pick() {
			jQuery("#s2member-files-archive-form").find("input[type=checkbox]:checked").each(function(index, el) {
				var $check = jQuery(el);
				parent.send_to_editor(\'[s2Link download="\' +$check.data("filename")+ \'" download_key="true" /]\');
			});
			
			parent.tb_remove();
		}
		</script>
	</form>
	';

    echo $html;
}


function S2FilePicker_S2Link_shortcode($atts,  $content = null){
    extract(shortcode_atts(
        array(
        	'download'     => false,
            'download_key' => 0
        ),
        $atts
    ));

    $return_string = '';

    if (!$content){
        $content = basename($download);
    }

    if($download && $download_key){
        $return_string = '<a href="'.do_shortcode('[s2File download="'.$download.'" download_key="true" /]').'">'.$content.'</a>';
    } elseif($download){
        $return_string = '<a href="'.do_shortcode('[s2File download="'.$download.'" /]').'">'.$content.'</a>';
    }

    return $return_string;
}
add_shortcode('s2Link', 'S2FilePicker_S2Link_shortcode');


