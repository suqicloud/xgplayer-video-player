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
                            type: 'textbox',
                            name: 'xgposter_url',
                            label: '封面图地址 (可选)',
                            value: '', // 初始值为空
                            minWidth: 300
                        },
                        {
                            type: 'label',
                            text: '请在上面输入视频的完整链接，多个视频就每行一个。'
                        },
                        {
                            type: 'label',
                            text: '如果需要封面图，填写对应的图片地址，留空就不显示封面。'
                        }
                    ],
                    onsubmit: function(e) {
                        var videoUrls = e.data.xgvideo_urls.split('\n').map(function(url) {
                            return url.trim();
                        }).join(',');

                        // 获取封面图地址，如果填写了，则添加poster参数
                        var posterUrl = e.data.xgposter_url.trim();
                        var shortcode = '[xgplayer_video url="' + videoUrls + '"';

                        // 如果封面图地址不为空，添加poster参数
                        if (posterUrl) {
                            shortcode += ' poster="' + posterUrl + '"';
                        }

                        shortcode += ']';

                        // 插入内容
                        editor.insertContent(shortcode);
                    }
                });
            }
        });
    });
})();
