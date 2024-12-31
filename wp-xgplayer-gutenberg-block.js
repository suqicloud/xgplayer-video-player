(function() {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var TextareaControl = wp.components.TextareaControl;
    var Fragment = wp.element.Fragment;
    var BlockControls = wp.editor.BlockControls;
    var BlockAlignmentToolbar = wp.editor.BlockAlignmentToolbar;

    registerBlockType('xgplayer/video', {
        title: '西瓜视频播放器',
        icon: 'media-video',
        category: 'common',
        attributes: {
            videoURLs: {
                type: 'string',
                source: 'text',
                selector: 'textarea',
            }
        },

        edit: function(props) {
            var updateVideoURLs = function(newVideoURLs) {
                props.setAttributes({ videoURLs: newVideoURLs });
            };

            return el(Fragment, null, [
                el(BlockControls, { key: 'controls' }, el(BlockAlignmentToolbar, {
                    value: props.attributes.align,
                    onChange: function(newAlign) {
                        props.setAttributes({ align: newAlign });
                    },
                    controls: ['left', 'center', 'right', 'full'],
                })),
                el(TextareaControl, {
                    label: '视频链接（每行一个）',
                    value: props.attributes.videoURLs,
                    onChange: updateVideoURLs,
                    style: { height: '200px' },
                })
            ]);
        },

        save: function(props) {
            return el('div', { className: 'wp-block-wp-xgplayer' }, '[xgplayer_video url="' + props.attributes.videoURLs.replace(/\n/g, ',') + '"]');
        },
    });
})();