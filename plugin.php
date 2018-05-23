<?php
/*
Plugin Name: Speech Balloon Maker
Plugin URI:
Description: This plugin can make speech balloon as you like.
Version: 1.0.1
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

if( !class_exists('dn_sbm_baloonmakerclass') ){
    class dn_sbm_baloonmakerclass{
        /* 翻訳言語格納用 */
        private $lang;

        /*設定パラメータ定義 */
        private static $settinggrp = 'dn-sbm-settings-group';
        private static $settingparams = [
            'l_icon' => 'dn_sbm_input_leftside_icon'  ,
            'r_icon' => 'dn_sbm_input_rightside_icon' ,
            'style'  => 'dn_sbm_input_balloon_style'  ,
            'l_name' => 'dn_sbm_input_leftside_name'  ,
            'r_name' => 'dn_sbm_input_rightside_name' ,
            'qtag'   => 'dn_sbm_input_useqtag'
        ];

        function __construct() {
            add_action( 'plugins_loaded', array( $this , 'dn_sbm_plugin_init' ) );
            add_action( 'admin_menu', array( $this , 'dn_sbm_add_menu' ) );
            add_action( 'wp_enqueue_scripts', array( $this , 'dn_sbm_balloon_style_load') );
        	add_action( 'admin_init', array( $this , 'dn_sbm_registersettings' ) );

            add_shortcode('fuki-l' , array( $this , 'fukidashiLeftFunc' ) );
            add_shortcode('balloon-l' , array( $this , 'fukidashiLeftFunc' ) );
            add_shortcode('fuki-r' , array( $this , 'fukidashiRightFunc' ) );
            add_shortcode('balloon-r' , array( $this , 'fukidashiRightFunc' ) );

            if( ! get_option( self::$settingparams['qtag'] ) ){
                add_action('admin_print_footer_scripts', array( $this , 'dn_sbm_add_qtag' ) );
            }

            if(function_exists('register_uninstall_hook')) {
                register_uninstall_hook (__FILE__, array('dn_sbm_baloonmakerclass','dn_sbm_uninstall') );
            }
        }

        /* 初期化処理 */
        function dn_sbm_plugin_init(){
            /* 多言語ファイル読み込み */
            load_plugin_textdomain( 'dn_sbm_lang', false, basename( dirname( __FILE__ ) ) . '/languages' );
            $this->dn_sbm_load_strings();

            /* スクリプト登録 */
            wp_register_script( 'dn_sbm_script', plugins_url('scripts/dn_sbm_script.js', __FILE__) , array('jquery'));

            /* CSS登録 */
            wp_register_style( 'dn_sbm_setting_style', plugins_url('styles/dn_sbm_setting_style.css', __FILE__) );

        }

        private function dn_sbm_load_strings(){
            $this->lang['title'] = __('Speech Balloon Maker Settings', 'dn_sbm_lang');
            $this->lang['speakerimage'] = __('Image for balloon', 'dn_sbm_lang');
            $this->lang['leftside'] = __('Icon image display at left side : ', 'dn_sbm_lang');
            $this->lang['rightside'] = __('Icon image display at right side : ', 'dn_sbm_lang');
            $this->lang['selectimage'] = __('Select Image', 'dn_sbm_lang');
            $this->lang['usage'] = __('Usage', 'dn_sbm_lang');
            $this->lang['copypaste'] = __('Copy this shortcode and Paste on your post edit.', 'dn_sbm_lang');
            $this->lang['balloon_l'] = __('balloon-l', 'dn_sbm_lang');
            $this->lang['balloon_r'] = __('balloon-r', 'dn_sbm_lang');
            $this->lang['thisissample'] = __('This is a Sample.', 'dn_sbm_lang');
            $this->lang['stylesstr'] = __('Select style', 'dn_sbm_lang');
            $this->lang['defaultstr'] = __('default', 'dn_sbm_lang');
            $this->lang['borderstr'] = __('border', 'dn_sbm_lang');
            $this->lang['imaginestr'] = __('imagine', 'dn_sbm_lang');
            $this->lang['curvestr'] = __('curve', 'dn_sbm_lang');
            $this->lang['nametitlestr'] = __('Name under icon', 'dn_sbm_lang');
            $this->lang['leftsidename'] = __('Left side icon name', 'dn_sbm_lang');
            $this->lang['rightsidename'] = __('Right side icon name', 'dn_sbm_lang');
            $this->lang['availableparam'] = __('Available parameters', 'dn_sbm_lang');
            $this->lang['youcanuse'] = __('You can use these parameters to change style individually.', 'dn_sbm_lang');
            $this->lang['qtagstr'] = __('Quick tag', 'dn_sbm_lang');
            $this->lang['donotuseqtag'] = __('Do not display Quick Tag at Edit Post', 'dn_sbm_lang');
        }

        function dn_sbm_add_menu() {
            /* ツールメニューへの追加 */
            $title = __('Speech Balloon Maker', 'dn_sbm_lang');
            //$page = add_submenu_page( 'tools.php', 'Speech Balloon Maker','Speech Balloon Makes','install_plugins','dn_sbm_menu', 'dn_sbm_manage_page' );
            $page = add_management_page( $title, $title , 'install_plugins','dn_sbm_menu', array( $this , 'dn_sbm_manage_page') );

            /* 設定画面表示時のCSSファイル読み込みをフック */
            add_action( 'admin_print_styles-' . $page, array( $this , 'dn_sbm_enque_style' ) );

            /* スクリプト読み込み */
            add_action('admin_head-' . $page, array( $this , 'dn_sbm_enque_script' ) );
        }

        /* ふきだし用のスタイルシートは全ページでロードする */
        function dn_sbm_balloon_style_load(){
            wp_register_style( 'dn_sbm_balloon_style', plugins_url('styles/dn_sbm_balloon_style.css', __FILE__) );

            wp_enqueue_style( 'dn_sbm_balloon_style' );
        }

        /* 管理画面用CSSファイルのロード */
        function dn_sbm_enque_style(){
            $this->dn_sbm_balloon_style_load();

            wp_enqueue_style( 'dn_sbm_setting_style' );
        }

        /* JSファイルのロード */
        function dn_sbm_enque_script(){
            /* メディアライブラリ使用のためのスクリプト読み込み */
            wp_enqueue_media();

            /* プラグインスクリプト読み込み */
            wp_enqueue_script( 'dn_sbm_script' );
        }

        /* 設定値の初期登録 */
        function dn_sbm_registersettings(){
            foreach( self::$settingparams as $key => $val ){
                register_setting( self::$settinggrp, $val );
            }
        }

        /* 設定画面作成 */
        function dn_sbm_manage_page(){
        ?>
            <h2><?php echo $this->lang['title']; ?></h2>
            <form method="post" action="options.php">
            <?php settings_fields( self::$settinggrp ); ?>
            <?php do_settings_sections( self::$settinggrp ); ?>
            <hr>
            <h3><?php echo $this->lang['speakerimage']; ?></h3>
            <p>
                <span><?php echo $this->lang['leftside']; ?></span>
                <input type="text" value="<?php echo esc_attr( get_option( self::$settingparams['l_icon'] , '' ) ); ?>" name="<?php echo self::$settingparams['l_icon']; ?>">
                <input type="button" value="<?php echo $this->lang['selectimage']; ?>" name="dn_sbm_leftside_icon_select_btn">
            </p>
            <p>
                <span><?php echo $this->lang['rightside']; ?></span>
                <input type="text" value="<?php echo esc_attr( get_option( self::$settingparams['r_icon'] , '' ) ); ?>" name="<?php echo self::$settingparams['r_icon']; ?>">
                <input type="button" value="<?php echo $this->lang['selectimage']; ?>" name="dn_sbm_rightside_icon_select_btn">
            </p>

            &nbsp;

            <h3><?php echo $this->lang['stylesstr']; ?></h3>
            <p>
                <input type="radio" name="<?php echo self::$settingparams['style']; ?>" id="dn_sbm_input_balloon_style_default" value="default" <?php if( 'default' == get_option( self::$settingparams['style'],'default') ) echo 'checked'; ?> >
                <label for="dn_sbm_input_balloon_style_default"><?php echo $this->lang['defaultstr']; ?></label>
                <input type="radio" name="<?php echo self::$settingparams['style']; ?>" id="dn_sbm_input_balloon_style_border" value="border" <?php if( 'border' == get_option( self::$settingparams['style'],'default') ) echo 'checked'; ?>>
                <label for="dn_sbm_input_balloon_style_border"><?php echo $this->lang['borderstr']; ?></label>
                <input type="radio" name="<?php echo self::$settingparams['style']; ?>" id="dn_sbm_input_balloon_style_imagine" value="imagine" <?php if( 'imagine' == get_option( self::$settingparams['style'],'default') ) echo 'checked'; ?>>
                <label for="dn_sbm_input_balloon_style_imagine"><?php echo $this->lang['imaginestr']; ?></label>
        <!--
                <input type="radio" name="dn_sbm_input_balloon_style" id="dn_sbm_input_balloon_style_curve" value="curve" <?php if( 'curve' == get_option( 'dn_sbm_input_balloon_style','default') ) echo 'checked'; ?>>
                <label for="dn_sbm_input_balloon_style_curve"><?php echo $this->curvestr; ?></label>
        -->
            </p>

            &nbsp;

            <h3><?php echo $this->lang['nametitlestr']; ?></h3>
            <p>
                <span><?php echo $this->lang['leftsidename']; ?></span>
                <input type="text" value="<?php echo esc_attr( get_option( self::$settingparams['l_name'] , '' ) ); ?>" name="<?php echo self::$settingparams['l_name']; ?>">
            </p>
            <p>
                <span><?php echo $this->lang['rightsidename']; ?></span>
                <input type="text" value="<?php echo esc_attr( get_option( self::$settingparams['r_name'] , '' ) ); ?>" name="<?php echo self::$settingparams['r_name']; ?>">
            </p>

            &nbsp;

            <h3><?php echo $this->lang['qtagstr']; ?></h3>
            <p>
                <span><?php echo $this->lang['donotuseqtag']; ?></span>
                <input type="checkbox" value="1" name="<?php echo self::$settingparams['qtag']; ?>" <?php if(get_option( self::$settingparams['qtag'] ) == 1) echo 'checked'; ?>>
            </p>

            &nbsp;

            <?php submit_button(); ?>

            </form>

            <hr>
            <h2><?php echo $this->lang['usage']; ?></h2>
            <p><?php echo $this->lang['copypaste']; ?></p>
            <div class="dn_sbm_usage_box">
                <p><input type="text" class="dn_sbm_shortcode_sample" readonly onclick="this.select();" value="<?php echo '[' . $this->lang['balloon_r'] . ']' . $this->lang['thisissample'] . '[/' . $this->lang['balloon_r'] . ']'; ?>"></p>
                <?php echo $this->fukidashiFunc('', $this->lang['thisissample'] , 'right'); ?>
                <p><input type="text"  class="dn_sbm_shortcode_sample" readonly onclick="this.select();" value="<?php echo '[' . $this->lang['balloon_l'] . ']' . $this->lang['thisissample'] . '[/' . $this->lang['balloon_l'] . ']'; ?>"></p>
                <?php echo $this->fukidashiFunc('', $this->lang['thisissample'] , 'left'); ?>
            </div>

            &nbsp;

            <h3><?php echo $this->lang['availableparam']; ?></h3>
            <p><?php echo $this->lang['youcanuse']; ?>
                <ul class="dn_sbm_ul">
                    <li>img</li>
                    <li>style : 'default' , 'border' , 'imagine'</li>
                    <li>name</li>
                </ul>
            </p>
            <p>
                exp. <input type="text" class="dn_sbm_shortcode_sample" readonly onclick="this.select();" value='[fuki-r img="https://sample.com/sample.png" name="sam" style="border"]<?php echo $this->lang['thisissample']; ?>[/fuki-r]'>
            </p>

        <?php
        }

        /** ふきだしショートコードここから **/
        function fukidashiFunc($attr, $content , $direction){
            if( strcasecmp($direction , "left") ){
                $option_icon = self::$settingparams['l_icon'];
                $option_name = self::$settingparams['l_name'];
            }else if( strcasecmp($direction , "right") ){
                $option_icon = self::$settingparams['r_icon'];
                $option_name = self::$settingparams['r_name'];
            }
            $option_style = self::$settingparams['style'];

            /* スタイルの設定 */
            if( array_key_exists('style' , $attr) &&
              ( $attr['style'] == 'default' || $attr['style'] == 'border' || $attr['style'] == 'imagine' ) ){
                $mystyle = $attr['style'];
            }else{
                $mystyle = esc_attr( get_option( $option_style,'default') );
            }

            /* アイコン下の名前設定 */
            $myname = esc_attr( array_key_exists('name' , $attr) ? $attr['name'] : get_option( $option_name , '') );

            /* ふきだしの中身設定 */
        	$content = preg_replace('/\<p\>|\<\/p\>/', '', $content);

            /* アイコンイメージの設定 */
            $imgsrc = esc_attr( empty( $tmp = get_option( $option_icon )) ? plugins_url('images/dn_sbm_default.png', __FILE__) : $tmp );
            if( array_key_exists('img' , $attr) ){
                $imgsrc = esc_attr( $attr['img'] );
            }

            /* ふきだしタグの書き出し */
        	return '<div class="dn_sbm_fukidashi ' . $direction . ' ' . $mystyle . '">
                    <div class="dn_sbm_fukidashiiconbox">
                    <img class="dn_sbm_fukidashiimg" style="background-image:url(' .$imgsrc. ');">
                    <br>
                    <span class="dn_sbm_fukidashiname">' . $myname . '</span>
                    </div>
                    <p class="dn_sbm_fukidashicontent">' . $content . '</p>
                    </div>';
        }

        function fukidashiLeftFunc($attr, $content = null){
        	return $this->fukidashiFunc($attr, $content ,"left");
        }

        function fukidashiRightFunc($attr, $content = null){
        	return $this->fukidashiFunc($attr, $content ,"right");
        }
        /** ふきだしショートコードここまで **/

        /* クイックタグ追加 */
        function dn_sbm_add_qtag() {
            if (wp_script_is('quicktags')){
        ?>
                <script type="text/javascript">
                /** 書式 : QTags.addButton('ID', 'ボタンのラベル', '開始タグ', '終了タグ', 'アクセスキー', 'タイトル', プライオリティ); **/
                QTags.addButton('fuki-r','<?php echo $this->lang['balloon_r']; ?>','[<?php echo $this->lang['balloon_r']; ?>]','[/<?php echo $this->lang['balloon_r']; ?>]');
                QTags.addButton('fuki-l','<?php echo $this->lang['balloon_l']; ?>','[<?php echo $this->lang['balloon_l']; ?>]','[/<?php echo $this->lang['balloon_l']; ?>]');
                </script>
        <?php
            }
        }
        /* クイックタグ追加ここまで */

        /* アンインストール時の処理 */
        static function dn_sbm_uninstall(){
            foreach( self::$settingparams as $key => $val ){
                unregister_setting( self::$settinggrp, $val );
                delete_option($val);
            }
        }

    }

    $dn_sbm_class = new dn_sbm_baloonmakerclass();
}

?>
