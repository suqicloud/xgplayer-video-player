<?php
/*
Plugin Name: 西瓜HTML5视频播放器
Plugin URI: https://www.jingxialai.com/4620.html
Description: 基于字节跳动西瓜HTML5播放器实现mp4、m3u8视频的播放，编辑器有快捷键，支持多集，短代码[xgplayer_video url="视频1.mp4,视频2.m3u8"]
Version: 1.6.0
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

// 添加后台设置页面
function wp_xgplayer_add_admin_menu() {
    add_options_page(
        '西瓜播放器设置',
        '西瓜播放器',
        'manage_options',
        'wp-xgplayer-settings',
        'wp_xgplayer_settings_page'
    );
}

add_action('admin_menu', 'wp_xgplayer_add_admin_menu');

// 初始化默认设置
function wp_xgplayer_register_settings() {
    register_setting('wp_xgplayer_settings_group', 'wp_xgplayer_settings', array(
        'sanitize_callback' => 'wp_xgplayer_sanitize_settings'
    ));

    $default_settings = array(
        'disable_contextmenu' => '1',
        'pip' => '1',
        'default_volume' => '0.5',
        'loop' => '0',
        'screenshot' => '1',
        'rotate' => '1',
        'download' => '0',
        'mini' => '1',
        'css_fullscreen' => '0', // 默认禁用
        'playback_rate' => '0',  // 默认禁用
        'miniprogress' => '1',
        'autoplay' => '0',
        'autoplay_muted' => '0',
        'margin_controls' => '0',
        'playsinline' => '1'
    );

    if (false === get_option('wp_xgplayer_settings')) {
        update_option('wp_xgplayer_settings', $default_settings);
    }
}

add_action('admin_init', 'wp_xgplayer_register_settings');

// 清理输入数据
function wp_xgplayer_sanitize_settings($input) {
    $sanitized = array();
    $sanitized['disable_contextmenu'] = isset($input['disable_contextmenu']) ? '1' : '0';
    $sanitized['pip'] = isset($input['pip']) ? '1' : '0';
    $sanitized['default_volume'] = floatval($input['default_volume']);
    $sanitized['loop'] = isset($input['loop']) ? '1' : '0';
    $sanitized['screenshot'] = isset($input['screenshot']) ? '1' : '0';
    $sanitized['rotate'] = isset($input['rotate']) ? '1' : '0';
    $sanitized['download'] = isset($input['download']) ? '1' : '0';
    $sanitized['mini'] = isset($input['mini']) ? '1' : '0';
    $sanitized['css_fullscreen'] = isset($input['css_fullscreen']) ? '1' : '0';
    $sanitized['playback_rate'] = isset($input['playback_rate']) ? '1' : '0';
    $sanitized['miniprogress'] = isset($input['miniprogress']) ? '1' : '0';
    $sanitized['autoplay'] = isset($input['autoplay']) ? '1' : '0';
    $sanitized['autoplay_muted'] = isset($input['autoplay_muted']) ? '1' : '0';
    $sanitized['margin_controls'] = isset($input['margin_controls']) ? '1' : '0';
    $sanitized['playsinline'] = isset($input['playsinline']) ? '1' : '0';
    return $sanitized;
}

// 设置页面内容
function wp_xgplayer_settings_page() {
    $settings = get_option('wp_xgplayer_settings');
    ?>
    <div class="wrap">
        <h1>西瓜播放器设置</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wp_xgplayer_settings_group'); ?>
            <?php do_settings_sections('wp_xgplayer_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">禁用右键</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[disable_contextmenu]" value="1" <?php checked($settings['disable_contextmenu'], '1'); ?> /> 禁用播放器右键菜单</td>
                </tr>
                <tr>
                    <th scope="row">画中画</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[pip]" value="1" <?php checked($settings['pip'], '1'); ?> /> 启用画中画按钮</td>
                </tr>
                <tr>
                    <th scope="row">默认音量</th>
                    <td><input type="number" step="0.1" min="0" max="1" name="wp_xgplayer_settings[default_volume]" value="<?php echo esc_attr($settings['default_volume']); ?>" /> (0 到 1)</td>
                </tr>
                <tr>
                    <th scope="row">循环播放</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[loop]" value="1" <?php checked($settings['loop'], '1'); ?> /> 启用循环播放</td>
                </tr>
                <tr>
                    <th scope="row">截图</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[screenshot]" value="1" <?php checked($settings['screenshot'], '1'); ?> /> 启用截图按钮</td>
                </tr>
                <tr>
                    <th scope="row">旋转</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[rotate]" value="1" <?php checked($settings['rotate'], '1'); ?> /> 启用旋转按钮</td>
                </tr>
                <tr>
                    <th scope="row">下载按钮</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[download]" value="1" <?php checked($settings['download'], '1'); ?> /> 启用下载按钮</td>
                </tr>
                <tr>
                    <th scope="row">迷你小窗</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[mini]" value="1" <?php checked($settings['mini'], '1'); ?> /> 启用迷你小窗按钮</td>
                </tr>
                <tr>
                    <th scope="row">网页全屏</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[css_fullscreen]" value="1" <?php checked($settings['css_fullscreen'], '1'); ?> /> 启用网页全屏按钮</td>
                </tr>
                <tr>
                    <th scope="row">倍速播放</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[playback_rate]" value="1" <?php checked($settings['playback_rate'], '1'); ?> /> 启用倍速播放选项</td>
                </tr>
                <tr>
                    <th scope="row">迷你进度条</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[miniprogress]" value="1" <?php checked($settings['miniprogress'], '1'); ?> /> 启用迷你进度条</td>
                </tr>
                <tr>
                    <th scope="row">自动播放</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[autoplay]" value="1" <?php checked($settings['autoplay'], '1'); ?> /> 启用自动播放（可能需要启用静音播放以绕过浏览器限制）</td>
                </tr>
                <tr>
                    <th scope="row">静音播放</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[autoplay_muted]" value="1" <?php checked($settings['autoplay_muted'], '1'); ?> /> 启用自动静音播放或默认静音</td>
                </tr>
                <tr>
                    <th scope="row">画面和控制栏分离</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[margin_controls]" value="1" <?php checked($settings['margin_controls'], '1'); ?> /> 启用画面和控制栏分离模式</td>
                </tr>
                <tr>
                    <th scope="row">内联播放</th>
                    <td><input type="checkbox" name="wp_xgplayer_settings[playsinline]" value="1" <?php checked($settings['playsinline'], '1'); ?> /> 启用内联播放模式</td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// 确保每一个id不冲突
$xgplayer_instance_count = 0;

// 加载js和css
function xgplayer_enqueue_scripts($is_audio = false) {
    if (!is_admin() && (has_shortcode(get_the_content(), 'xgplayer_video') || has_block('xgplayer/video'))) {
        wp_register_script('xgplayer-core', plugin_dir_url(__FILE__) . 'dist/index.min.js', array(), '3.0.20', true);
        wp_register_script('xgplayer-hls', plugin_dir_url(__FILE__) . 'hls/index.min.js', array('xgplayer-core'), '3.0.20', true);
        wp_register_script('xgplayer-mp4', plugin_dir_url(__FILE__) . 'mp4/index.min.js', array('xgplayer-core'), '3.0.20', true);
        wp_enqueue_script('xgplayer-core');
        wp_enqueue_script('xgplayer-hls');
        wp_enqueue_script('xgplayer-mp4');

        wp_register_style('xgplayer-style', plugin_dir_url(__FILE__) . 'dist/index.min.css', array(), '3.0.20');
        wp_enqueue_style('xgplayer-style');

        if ($is_audio) {
            wp_register_script('xgplayer-music', plugin_dir_url(__FILE__) . 'music/index.min.js', array('xgplayer-core'), '3.0.20', true);
            wp_enqueue_script('xgplayer-music');
            wp_register_style('xgplayer-music-style', plugin_dir_url(__FILE__) . 'music/index.min.css', array(), '3.0.20');
            wp_enqueue_style('xgplayer-music-style');
        }
    }
}
add_action('wp_enqueue_scripts', 'xgplayer_enqueue_scripts');

// 西瓜播放器短代码
function xgplayer_video_shortcode($atts) {
    global $xgplayer_instance_count;
    
    $atts = shortcode_atts(array(
        'url' => '',
        'poster' => '',
    ), $atts, 'xgplayer_video');

    if (empty($atts['url'])) {
        return '<p>错误：未提供视频URL</p>';
    }

    $xgplayer_instance_count++;
    $player_id = 'xgplayer_' . $xgplayer_instance_count;
    $video_url = esc_url($atts['url']);
    $poster_url = esc_url($atts['poster']);
    $is_audio = preg_match('/\.(mp3|m4a|3gp|ogg)$/i', $video_url);
    xgplayer_enqueue_scripts($is_audio);

    $settings = get_option('wp_xgplayer_settings', array(
        'disable_contextmenu' => '1',
        'pip' => '1',
        'default_volume' => '0.5',
        'loop' => '0',
        'screenshot' => '1',
        'rotate' => '1',
        'download' => '0',
        'mini' => '1',
        'css_fullscreen' => '0',
        'playback_rate' => '0',
        'miniprogress' => '1',
        'autoplay' => '0',
        'autoplay_muted' => '0',
        'margin_controls' => '0',
        'playsinline' => '1'
    ));

    $is_bilibili = preg_match('/https?:\/\/www\.bilibili\.com\/video\/BV([0-9a-zA-Z]+)/', $video_url, $matches);
    $bvid = $is_bilibili ? $matches[1] : '';

    ob_start();
    ?>
    <style>
        .xgplayer-video-container {
            width: 100%;
            max-width: 800px;
            height: auto;
            box-sizing: border-box;
            border-radius: 2px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
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
            margin: 2px !important;
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 4px 10px;
            border-radius: 2px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            transition-duration: 0.4s;
            cursor: pointer;
            width: auto;
            height: auto;
        }
        .xgplayer-video-container .episode-button:hover {
            background-color: #0056b3;
        }
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
                <iframe src="https://player.bilibili.com/player.html?bvid=<?php echo esc_attr($bvid); ?>&autoplay=0" 
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
        try {
            var urls = '<?php echo esc_js($atts['url']); ?>'.split(',').filter(url => url.trim() !== '');
            var player<?php echo $xgplayer_instance_count; ?>;
            var isAudio = <?php echo $is_audio ? 'true' : 'false'; ?>;
            var shouldAutoplay = <?php echo $settings['autoplay'] ? 'true' : 'false'; ?>;
            var currentIndex = 0;

            if (urls.length === 0) {
                console.error('No valid video URLs provided for player <?php echo $player_id; ?>');
                return;
            }

            function createPlayer(index) {
                try {
                    if (player<?php echo $xgplayer_instance_count; ?>) {
                        player<?php echo $xgplayer_instance_count; ?>.destroy();
                    }

                    var container = document.getElementById('<?php echo esc_js($player_id); ?>');
                    if (!container) {
                        console.error('Player container <?php echo $player_id; ?> not found');
                        return;
                    }

                    if (isAudio) {
                        player<?php echo $xgplayer_instance_count; ?> = new Player({
                            id: '<?php echo esc_js($player_id); ?>',
                            url: urls[0],
                            mediaType: 'audio',
                            volume: <?php echo floatval($settings['default_volume']); ?>,
                            width: '100%',
                            height: 50,
                            controls: {
                                initShow: true,
                                mode: 'flex'
                            },
                            presets: ['default', window.MusicPreset || {}],
                            ignores: ['playbackrate'],
                            marginControls: <?php echo $settings['margin_controls'] ? 'true' : 'false'; ?>,
                            videoConfig: {
                                crossOrigin: "anonymous"
                            },
                            loop: <?php echo $settings['loop'] ? 'true' : 'false'; ?>,
                            autoplay: <?php echo $settings['autoplay'] ? 'true' : 'false'; ?>,
                            autoplayMuted: <?php echo $settings['autoplay_muted'] ? 'true' : 'false'; ?>,
                            playsinline: <?php echo $settings['playsinline'] ? 'true' : 'false'; ?>
                        });
                    } else {
                        var plugin = (urls[index].substr(-5) === '.m3u8') ? 'HlsJsPlugin' : 'Mp4Plugin';
                        var playerConfig = {
                            id: '<?php echo esc_js($player_id); ?>',
                            url: urls[index],
                            lang: "zh",
                            volume: <?php echo floatval($settings['default_volume']); ?>,
                            playsinline: <?php echo $settings['playsinline'] ? 'true' : 'false'; ?>,
                            autoplay: <?php echo $settings['autoplay'] ? 'true' : 'false'; ?>,
                            autoplayMuted: <?php echo $settings['autoplay_muted'] ? 'true' : 'false'; ?>,
                            poster: '<?php echo esc_js($poster_url); ?>',
                            videoAttributes: {
                                crossOrigin: "anonymous"
                            },
                            width: '100%',
                            plugins: [window[plugin] || {}],
                            loop: <?php echo $settings['loop'] ? 'true' : 'false'; ?>,
                            closeVideoClick: true,
                            marginControls: <?php echo $settings['margin_controls'] ? 'true' : 'false'; ?>,
                            cssFullscreen: <?php echo $settings['css_fullscreen'] ? 'true' : 'false'; ?>,
                            playbackRate: <?php echo $settings['playback_rate'] ? '[0.5, 0.75, 1, 1.5, 2]' : 'false'; ?>
                        };

                        if (<?php echo $settings['disable_contextmenu'] ? 'true' : 'false'; ?>) {
                            playerConfig.enableContextmenu = false;
                            console.log('disable_contextmenu enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['pip'] ? 'true' : 'false'; ?>) {
                            playerConfig.pip = true;
                            console.log('pip enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['screenshot'] ? 'true' : 'false'; ?>) {
                            playerConfig.screenShot = true;
                            console.log('screenshot enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['rotate'] ? 'true' : 'false'; ?>) {
                            playerConfig.rotate = true;
                            console.log('rotate enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['download'] ? 'true' : 'false'; ?>) {
                            playerConfig.download = true;
                            console.log('download enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['mini'] ? 'true' : 'false'; ?>) {
                            playerConfig.mini = true;
                            console.log('mini enabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['css_fullscreen'] ? 'true' : 'false'; ?>) {
                            playerConfig.cssFullscreen = true;
                            console.log('cssFullscreen enabled for player <?php echo $player_id; ?>');
                        } else {
                            playerConfig.cssFullscreen = false;
                            console.log('cssFullscreen disabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['playback_rate'] ? 'true' : 'false'; ?>) {
                            playerConfig.playbackRate = [0.5, 0.75, 1, 1.5, 2];
                            console.log('playbackRate enabled with values [0.5, 0.75, 1, 1.5, 2] for player <?php echo $player_id; ?>');
                        } else {
                            playerConfig.playbackRate = false;
                            console.log('playbackRate disabled for player <?php echo $player_id; ?>');
                        }
                        if (<?php echo $settings['miniprogress'] ? 'true' : 'false'; ?>) {
                            playerConfig.miniprogress = true;
                            console.log('miniprogress enabled for player <?php echo $player_id; ?>');
                        }

                        player<?php echo $xgplayer_instance_count; ?> = new Player(playerConfig);
                    }
                    console.log('Player <?php echo $player_id; ?> initialized successfully');
                } catch (e) {
                    console.error('Error initializing player <?php echo $player_id; ?>: ', e);
                }
            }

            createPlayer(0);

            if (urls.length > 1) {
                var episodeButtons = document.getElementById('episode_buttons_<?php echo $xgplayer_instance_count; ?>');
                if (episodeButtons) {
                    var nextButton = document.createElement('button');
                    nextButton.innerText = '播放下一集';
                    nextButton.classList.add('episode-button');
                    nextButton.addEventListener('click', function() {
                        shouldAutoplay = <?php echo $settings['autoplay'] ? 'true' : 'false'; ?>;
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
                                shouldAutoplay = <?php echo $settings['autoplay'] ? 'true' : 'false'; ?>;
                                currentIndex = index;
                                createPlayer(index);
                            });
                            episodeButtons.appendChild(button);
                        })(i);
                    }
                }
            }
        } catch (e) {
            console.error('Error in player setup for <?php echo $player_id; ?>: ', e);
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
    delete_option('wp_xgplayer_settings');
}
?>