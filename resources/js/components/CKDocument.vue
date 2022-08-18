<template>
    <div class="bg-white">
        <div ref="descriptionToolbarWrapperEl"></div>
        <ckeditor
            :editor="ckEditorBuild"
            :value="value"
            :config="ckEditorConfig"
            :class="ckClass"
            :style="ckStyle"
            :disabled="readOnly"
            :placeholder="placeholder"
            @input="onInput"
            @ready="onEditorReady"></ckeditor>
        <slot name="content"></slot>
    </div>
</template>

<script>
    import DocumentEditor from '@ckeditor/ckeditor5-build-decoupled-document';
    import '@ckeditor/ckeditor5-build-decoupled-document/build/translations/vi';
    import '@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr';

    const colors = [
        {
            color: 'hsl(0, 0%, 0%)',
            label: 'Black'
        },
        {
            color: 'hsl(0, 0%, 30%)',
            label: 'Dim grey'
        },
        {
            color: 'hsl(0, 0%, 60%)',
            label: 'Grey'
        },
        {
            color: 'hsl(0, 0%, 90%)',
            label: 'Light grey'
        },
        {
            color: 'hsl(0, 0%, 100%)',
            label: 'White',
            hasBorder: true
        },
        {
            color: '#D2392C',
            label: 'Red'
        },
        {
            color: 'hsl(0, 75%, 60%)',
            label: 'Red'
        },
        {
            color: 'hsl(30, 75%, 60%)',
            label: 'Orange'
        },
        {
            color: 'hsl(60, 75%, 60%)',
            label: 'Yellow'
        },
        {
            color: 'hsl(90, 75%, 60%)',
            label: 'Light green'
        },
        {
            color: 'hsl(120, 75%, 60%)',
            label: 'Green'
        },
        {
            color: '#4c752e',
            label: 'Green'
        },
        {
            color: 'hsl(150, 75%, 60%)',
            label: 'Aquamarine'
        },

        {
            color: 'hsl(180, 75%, 60%)',
            label: 'Turquoise'
        },
        {
            color: '#49A5B8',
            label: 'Turquoise'
        },
        {
            color: 'hsl(210, 75%, 60%)',
            label: 'Light blue'
        },
        {
            color: 'hsl(240, 75%, 60%)',
            label: 'Blue'
        },
        {
            color: 'hsl(270, 75%, 60%)',
            label: 'Purple'
        }
    ];
    const defaultCkConfig = {
        language: $('html').attr('lang'),
        toolbar: [
            "fontsize", "fontfamily", "fontColor", "fontBackgroundColor",
            "|",
            "bold", "italic", "underline", "strikethrough",
            "|",
            "alignment",
            "|",
            "numberedList","bulletedList",
            "|",
            "indent", "outdent",
            "|",
            "link", "blockquote", "ckfinder", "mediaEmbed", "insertTable",
            "|",
            "undo", "redo"
        ],
        ckfinder: {
            uploadUrl: `/ckfinder/connector?command=QuickUpload&type=Images&responseType=json`,
            options: {
                resourceType: 'Images'
            }
        },
        mediaEmbed: {
            previewsInData: true,
        },
        fontColor: {
            colors: colors,
        },
        fontBackgroundColor: {
            colors: colors,
        }
    };

    export default {
        props: {
            readOnly: {
                default: false
            },
            value: {
                type: String
            },
            placeholder: {
                type: String
            },
            ckClass: {
                type: [Object, Array]
            },
            ckStyle: {
                type: [Object, Array]
            },
            config: {
                type: Object
            },
            mode: {
                type: String,
                default: 'normal', // 'normal', 'text-only'
            }
        },
        data() {
            return {
                ckEditorBuild: DocumentEditor,
            }
        },
        computed: {
            ckEditorConfig() {
                return {
                    ...defaultCkConfig,
                    ...(this.mode === 'text-only' ? {
                        toolbar: [
                            "fontsize", "fontfamily",
                            "|",
                            "bold", "italic", "underline",
                            "|",
                            "link",
                        ]
                    } : {}),
                    ...{placeholder: this.placeholder},
                    ...this.config,
                }
            }
        },
        methods: {
            onEditorReady(event) {
                this.$refs.descriptionToolbarWrapperEl.appendChild(event.ui.editor.ui.view.toolbar.element);
            },
            onInput($event) {
                // Add alt attribute to img tag
                let $content = $($event);
                if ($content.find('img:not([alt])').length) {
                    $content.find('img:not([alt])').attr('alt', 'Takamart');
                    $event = $('<div />').append($content).html();
                }

                this.$emit('input', $event);
            }
        }
    }
</script>

<style scoped>

</style>
