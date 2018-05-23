(function ($) {
    var custom_uploader;

    $("input:button[name=dn_sbm_leftside_icon_select_btn] , input:button[name=dn_sbm_rightside_icon_select_btn]").click(function(e) {
        e.preventDefault();

        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        custom_uploader = wp.media({
            title: $(this).val(),
            button: { text: $(this).val() },

            /* ライブラリの一覧は画像のみにする */
            library: { type: "image" },

            /* 選択できる画像は 1 つだけにする */
            multiple: false
        });

        custom_uploader.on("select", function() {
            var images = custom_uploader.state().get("selection");

            /* file の中に選択された画像の各種情報が入っている */
            images.each(function(file){
                /* テキストフォームと表示されたサムネイル画像があればクリア */
                $("input:text[name=dn_sbm_input_leftside_icon]").val("");
                $("#media").empty();

                /* テキストフォームに画像の ID を表示 */
                $("input:text[name=dn_sbm_input_leftside_icon]").val(file.toJSON().url);

                /* プレビュー用に選択されたサムネイル画像を表示 */
                $("#dn_sbm_leftside_icon_img").append('<img src="'+file.attributes.sizes.thumbnail.url+'" />');
            });
        });

        custom_uploader.open();

    });
})(jQuery);
