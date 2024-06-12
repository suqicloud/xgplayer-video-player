(function() {
    tinymce.PluginManager.add('wp_xgplayer_button', function(editor, url) {
        editor.addButton('wp_xgplayer_button', {
            text: '西瓜视频',
            icon: false,
            onclick: function() {
                editor.windowManager.open({
                    title: '西瓜视频',
                    body: [
                        {
                            type: 'textbox',
                            name: 'xgvideo_urls',
                            label: '视频链接(一行一个)',
                            multiline: true,
                            minWidth: 300,
                            minHeight: 100
                        },
                        {
                            type: 'label',
                            text: '请在上面输入视频的完整链接，多个视频就每行一个。'
                        }
                    ],
                    onsubmit: function(e) {
                        var videoUrls = e.data.xgvideo_urls.split('\n').map(function(url) {
                            return url.trim();
                        }).join(',');

                        editor.insertContent('[xgplayer_video url="' + videoUrls + '"]');
                    }
                });
            }
        });
    });
})();
