<?php
/*
Plugin Name: Speech Balloon Maker
Plugin URI:
Description: This plugin can make speech balloon as you like.
Version: 1.0
Author: densuke
Author URI: https://engineering.dn-voice.info/
License: GPL2

  Copyright 2018 densuke (email : notgeek@dn-voice.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'admin_init', 'dn_sbm_plugin_admin_init' );
add_action( 'admin_menu', 'dn_sbm_add_menu' );

/* 初期化処理 */
function dn_sbm_plugin_admin_init(){
    /* 多言語ファイル読み込み */
    load_plugin_textdomain( 'dn_sbm_lang', false, basename( dirname( __FILE__ ) ) . '/languages' );

    /* スタイルシート登録 */
    wp_register_style( 'dn_sbm_style', plugins_url('styles/dn_sbm_style.css', __FILE__) );

    /* スクリプト登録 */
    wp_register_script( 'dn_sbm_script', plugins_url('scripts/dn_sbm_script.js', __FILE__) , array('jquery'));
}

/* ツールメニューへの追加 */
function dn_sbm_add_menu() {
    $page = add_submenu_page( 'tools.php', 'Speech Balloon Maker','Speech Balloon Makes','install_plugins','dn_sbm_menu', 'dn_sbm_manage_page' );

    /* 設定画面表示時のCSSファイル読み込みをフック */
    add_action( 'admin_print_styles-' . $page, 'dn_sbm_enque_style' );

    /* スクリプト読み込み */
    add_action('admin_head-' . $page, 'dn_sbm_enque_script');
}

/* CSSファイルのロード */
function dn_sbm_enque_style(){
    wp_enqueue_style( 'dn_sbm_style' );
}

/* JSファイルのロード */
function dn_sbm_enque_script(){
    /* メディアライブラリ使用のためのスクリプト読み込み */
    wp_enqueue_media();

    /* プラグインスクリプト読み込み */
    wp_enqueue_script( 'dn_sbm_script' );
}

/* 設定画面作成 */
function dn_sbm_manage_page(){
    $title = __('Speech Balloon Maker Settings', 'dn_sbm_lang');
    $leftside = __('Left side icon image : ', 'dn_sbm_lang');
    $rightside = __('Right side icon image : ', 'dn_sbm_lang');
    $selectimage = __('Select Image', 'dn_sbm_lang');

    echo <<<EOF
    <h2>$title</h2>
    <form>
    <p>
        <span>$leftside</span>
        <input type="text" value="" name="dn_sbm_input_leftside_icon">
        <input type="button" value="$selectimage" name="dn_sbm_leftside_icon_select_btn">
        <span id="dn_sbm_leftside_icon_img"></span>
    </p>
    <p>
        <span>$rightside</span>
        <input type="text" value="" name="dn_sbm_input_rightside_icon">
        <input type="button" value="$selectimage" name="dn_sbm_rightside_icon_select_btn">
        <span id="dn_sbm_rightside_icon_img"></span>
    </p>
    </form>

EOF;
}


?>
