<?php
/*
Plugin Name: 西瓜HTML5视频播放器
Plugin URI: https://www.jingxialai.com/4620.html
Description: 集成字节跳动西瓜视频播放器实现mp4、m3u8视频的播放，编辑器有快捷键，支持多集，短代码[xgplayer_video url="视频1.mp4,视频2.m3u8"]
Version: 1.4
Author: Summer
Author URI: https://www.jingxialai.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

// 经典编辑器快捷键
function wp_xgplayer_register_tinymce_plugin($plugin_array) {
    $plugin_array['wp_xgplayer_button'] = plugin_dir_url(__FILE__) . 'wp-xgplayer-tinymce.js';
    return $plugin_array;
}

function wp_xgplayer_add_tinymce_button($buttons) {
    array_push($buttons, 'wp_xgplayer_button');
    return $buttons;
}

function wp_xgplayer_add_tinymce_plugin() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }

    if (get_user_option('rich_editing') !== 'true') {
        return;
    }

    add_filter('mce_external_plugins', 'wp_xgplayer_register_tinymce_plugin');
    add_filter('mce_buttons', 'wp_xgplayer_add_tinymce_button');
}

add_action('init', 'wp_xgplayer_add_tinymce_plugin');
// 经典编辑器快捷键结束


// Gutenberg编辑器快捷引入
function wp_xgplayer_gutenberg_register_block() {
    wp_register_script(
        'wp-xgplayer-block',
        plugins_url('wp-xgplayer-gutenberg-block.js', __FILE__),
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element')
    );

    register_block_type('xgplayer/video', array(
        'editor_script' => 'wp-xgplayer-block',
    ));
}

add_action('init', 'wp_xgplayer_gutenberg_register_block');
// Gutenberg编辑器快捷引入结束


// 确保每一个id不冲突
$xgplayer_instance_count = 0;

// 加载js和css
function xgplayer_enqueue_scripts() {
    if (!is_admin() && has_shortcode(get_the_content(), 'xgplayer_video')) {
    wp_register_script('xgplayer-core', plugin_dir_url(__FILE__) . 'dist/index.min.js', array(), '3.0.20', true);
    wp_register_script('xgplayer-hls', plugin_dir_url(__FILE__) . 'hls/index.min.js', array('xgplayer-core'), '3.0.20', true);
    wp_register_script('xgplayer-mp4', plugin_dir_url(__FILE__) . 'mp4/index.min.js', array('xgplayer-core'), '3.0.20', true);
    wp_enqueue_script('xgplayer-core');
    wp_enqueue_script('xgplayer-hls');
    wp_enqueue_script('xgplayer-mp4');

    wp_register_style('xgplayer-style', plugin_dir_url(__FILE__) . 'dist/index.min.css', array(), '3.0.20');
    wp_enqueue_style('xgplayer-style');
    }
}
add_action('wp_enqueue_scripts', 'xgplayer_enqueue_scripts');

// 西瓜播放器相关开始
function xgplayer_video_shortcode($atts) {
    global $xgplayer_instance_count;
    
    $atts = shortcode_atts(array(
        'url' => ''
    ), $atts, 'xgplayer_video');

    $xgplayer_instance_count++;

    // 获取视频源
    $player_id = 'xgplayer_' . $xgplayer_instance_count;
    $video_url = esc_url($atts['url']);

    // 判断是否为Bilibili视频链接
    $is_bilibili = preg_match('/https?:\/\/www\.bilibili\.com\/video\/BV([0-9a-zA-Z]+)/', $video_url, $matches);
    $bvid = $is_bilibili ? $matches[1] : '';

    ob_start();
    ?>
    <style>
        .xgplayer-video-container {
            /* background-color: #fff;*/
            width: 100%; /* 宽度 */
            max-width: 800px; /* 最大宽度 */
            height: auto; /* 高度 */
            box-sizing: border-box;
            border-radius: 2px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* 阴影效果 */
        }
        .xgplayer-video-wrapper {
               width: 100%;
               height: auto;
        }
        .xgplayer-video-container .episode-buttons {
            margin-top: 10px;
            text-align: center;     
        }
        .xgplayer-video-container .episode-button {
            margin: 2px !important;/*防止某些主题覆盖播放器按钮*/
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 4px 10px; /* 调整按钮的内边距 */
            border-radius: 2px;
            text-align: center;
            text-decoration: none;
            font-size: 14px; /* 调整按钮的字体大小 */
            transition-duration: 0.4s;
            cursor: pointer;
            width: auto; /* 自动宽度 */
            height: auto; /* 自动高度 */
        }
        .xgplayer-video-container .episode-button:hover {
            background-color: #0056b3;
        }
        /*播放器控制栏里面重新调用，防止某些主题覆盖*/
        .xgplayer .btn-text span {
            display: inline-block;
            min-width: 52px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            background: rgba(0, 0, 0, .38);
            border-radius: 12px;
        }
    </style>
    <div class="xgplayer-video-container">
        <div class="xgplayer-video-wrapper">
            <?php if ($is_bilibili): ?>
                <!-- 如果是Bilibili链接，用iframe方式嵌入 -->
                <iframe src="//player.bilibili.com/player.html?bvid=<?php echo esc_attr($bvid); ?>&autoplay=0" 
                        scrolling="no" 
                        border="0" 
                        frameborder="no" 
                        framespacing="0" 
                        allowfullscreen="true" 
                        width="100%" 
                        height="500px">
                </iframe>
            <?php else: ?>
                <div id="<?php echo esc_attr($player_id); ?>" class="xgplayer"></div>
            <?php endif; ?>
        </div>
        <div class="episode-buttons-wrapper">
            <div id="episode_buttons_<?php echo $xgplayer_instance_count; ?>" class="episode-buttons"></div>
        </div>
    </div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var urls = '<?php echo esc_js($atts['url']); ?>'.split(',');
        var player<?php echo $xgplayer_instance_count; ?>;
        var shouldAutoplay = false;
        var currentIndex = 0;

        function createPlayer(index) {
            if (player<?php echo $xgplayer_instance_count; ?>) {
                player<?php echo $xgplayer_instance_count; ?>.destroy();
            }

            //播放器参数
            var plugin = (urls[index].substr(-5) === '.m3u8') ? 'HlsJsPlugin' : 'Mp4Plugin';//只支持m3u8和mp4
            player<?php echo $xgplayer_instance_count; ?> = new Player({
                id: '<?php echo esc_js($player_id); ?>',
                url: urls[index],
                playsinline: true,
                rotateFullscreen: true,
                enableContextmenu: false,//禁用右键
                autoplay: shouldAutoplay,//因为增加了多个视频，所以选择shouldAutoplay根据视频判断是否自动播放
                'type': 'auto',
                "playbackRate": [
                    0.5,
                    1,
                    1.5,
                    2
                ],
                "screenShot": true,
                "videoAttributes": {
                    "crossOrigin": "anonymous"
                },
                "pip": true,
                "closeVideoClick": true,
                "plugins": [window[plugin]],
                "width": '100%',
            });
        }

        createPlayer(0);

        //根据视频源添加播放下一集和选集按钮
        if (urls.length > 1) {
            var episodeButtons = document.getElementById('episode_buttons_<?php echo $xgplayer_instance_count; ?>');
            var nextButton = document.createElement('button');
            nextButton.innerText = '播放下一集';
            nextButton.classList.add('episode-button');
            nextButton.addEventListener('click', function() {
                shouldAutoplay = true;
                currentIndex = (currentIndex + 1) % urls.length;
                createPlayer(currentIndex);
            });
            episodeButtons.appendChild(nextButton);

            for (var i = 0; i < urls.length; i++) {
                (function(index) {
                    var button = document.createElement('button');
                    button.innerText = '第' + (index + 1) + '集';
                    button.classList.add('episode-button');
                    button.addEventListener('click', function() {
                        shouldAutoplay = true;
                        currentIndex = index;
                        createPlayer(index);
                    });
                    episodeButtons.appendChild(button);
                })(i);
            }
        }
    });
</script>
    <?php
    return ob_get_clean();
}
add_shortcode('xgplayer_video', 'xgplayer_video_shortcode');

// 卸载插件
register_uninstall_hook(__FILE__, 'wp_xgplayer_plugin_uninstall');

// 卸载插件删除相关函数
function wp_xgplayer_plugin_uninstall() {
    remove_filter('mce_external_plugins', 'wp_xgplayer_register_tinymce_plugin');
    remove_filter('mce_buttons', 'wp_xgplayer_add_tinymce_button');

    unregister_block_type('xgplayer/video');
    remove_action('wp_enqueue_scripts', 'xgplayer_enqueue_scripts');

    // 删除短代码
    //remove_shortcode('xgplayer_video');
}

?>
