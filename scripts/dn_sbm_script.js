(function ($) {
    var custom_uploader;

    $("input:button[name=dn_sbm_leftside_icon_select_btn]").click(function(e) {
        e.preventDefault();

        open_custom_uploader($(this).val() ,
                             $("input:text[name=dn_sbm_input_leftside_icon]"));

    });

    $("input:button[name=dn_sbm_rightside_icon_select_btn]").click(function(e) {
        e.preventDefault();

        open_custom_uploader($(this).val() ,
                             $("input:text[name=dn_sbm_input_rightside_icon]"));
    });

    function open_custom_uploader( titlelabel , $textform ){
        custom_uploader = wp.media({
            title: titlelabel,
            button: { text: titlelabel },

            /* ライブラリの一覧は画像のみにする */
            library: { type: "image" },

            /* 選択できる画像は 1 つだけにする */
            multiple: false
        });

        custom_uploader.on("select", function() {
            var images = custom_uploader.state().get("selection");

            /* file の中に選択された画像の各種情報が入っている */
            images.each(function(file){
                /* テキストフォームに画像のURLを表示 */
                $textform.val(file.toJSON().url);
            });
        });

        custom_uploader.open();
    }

})(jQuery);
