<template>
    <div :class="errors && errors.length > 0 ? 'is-invalid' : 'is-valid'">
        <div class="drop-area text-center" v-show="pImageUrl == null">
            <label class="w-100 m-0 p-4">
                <slot name="text-drop-image">
                    <span style="border: 1px solid #ced4da; padding: 15px; cursor: pointer;" class="input-image float-left pt-3">{{ $t('common.upload') }}</span><br>
                </slot>
                <input class="drop-area__input" type="file" accept="image/*" @change="onBannerFileChange" ref="fileInputEl" hidden="" :disabled="disabled">
            </label>
        </div>
        <div class="mt-3" v-show="pImageUrl != null">
            <div class="mb-2" style="text-align: center;" v-if="disableSize">
                <span class="font-weight-bold">Width:</span>&nbsp{{size.width}}px&nbsp-&nbsp<span class="font-weight-bold">Height:</span>&nbsp{{size.height}}px</span>
            </div>
            <div style="position: relative">
                <img :src="pImageUrl" alt="selected banner" @crop="pCropImage" class="img-fluid" ref="bannerImgEl">
                <a v-if="!disabled && changeable" class="text-decoration-none" href="javascript:void(0)" @click="resetSelectedFile"
               style="font-size: 60px;font-weight: bold;color: #ed1c23;position: absolute;top: -5px;right: 0;line-height: 45px;">&times;</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                pImageUrl: this.imageUrl,
                selectedFile: this.file,
                files: null,
                cropper: null,
                size: {
                    width: 0,
                    height: 0,
                }
            }
        },
        props: {
            indexImg: {
                type: Number,
                default: null
            },
            disabled: {
                type: Boolean,
                default: false
            },
            imageUrl: String,
            file: Object,
            aspectRatios: {
                type: Array,
                default: () => [{name: '1:1', value: 1 / 1}]
            },
            changeable: {
                type: Boolean,
                default: true,
            },
            imageConfig: {
                type: Object,
                default: () => {},
            },
            errors: {
                type: Array,
                default: () => ([])
            },
            disableSize: {
                type: Boolean,
                default: false,
            }
        },
        watch: {
            disabled(newVal) {
                if (newVal) {
                    this.$_disableCropper();
                } else {
                    this.$_enableCropper();
                }
            },
            // imageUrl(newVal, oldVal) {
            //     this.pImageUrl = newVal;
            //     setTimeout(() => this.$_initCropper(null, newVal), 300);
            // }
        },
        mounted() {
            if (this.pImageUrl) {
                setTimeout(() => this.$_initCropper(null, this.pImageUrl), 300);
            }
        },
        methods: {
            pCropImage() {
                let getData = this.cropper.getData();
                this.size.width = Math.ceil(getData.width);
                this.size.height = Math.ceil(getData.height);
                this.$emit('crop-image', getData, this.indexImg);
            },
            createCropper(file) {
                this.$_destroyCropper();

                let done = (url) => {
                    this.$refs.fileInputEl.value = '';
                    //this.$refs.bannerImgEl.src = url;
                    this.pImageUrl = url;
                    setTimeout(() => this.$nextTick(this.$_initCropper(file, url)), 300);
                    this.selectedFile = file;
                };
                let reader;
                let url;

                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = e => {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            },
            onBannerFileChange(event) {
                this.files = event.target.files;

                let file = this.files[0];
                this.selectedFile = file;
                this.createCropper(file);

                this.$emit('file-changed', this.files);
            },
            resetSelectedFile() {
                this.pImageUrl = null;
                this.files = null;
                this.selectedFile = null;
                this.$_destroyCropper();
                let indexImg = this.indexImg;
                this.$emit('cropper-reset', {indexImg});
            },
            $_enableCropper() {
                if (this.cropper) {
                    this.cropper.enable();
                }
            },
            $_disableCropper() {
                if (this.cropper) {
                    this.cropper.disable();
                }
            },
            $_destroyCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },
            $_initCropper(file, imgUrl) {
                // this.cropper = new Cropper(this.$refs.bannerImgEl, {
                //     viewMode: 2,
                //     autoCropArea: this.disable == false ? 1 : 0,
                //     autoCrop: this.disable == false ? true : false,
                //     initialAspectRatio: this.disable == false ? this.aspectRatios[0].value : null,
                //     aspectRatio: this.disable == false ? this.aspectRatios[0].value : null,
                //     checkOrientation: false,
                //     zoomable: false,
                // });
                this.cropper = new Cropper(this.$refs.bannerImgEl, {
                    viewMode: 2,
                    autoCropArea: 1,
                    initialAspectRatio: this.aspectRatios[0].value,
                    aspectRatio: this.aspectRatios[0].value,
                    checkOrientation: false,
                    zoomable: false,
                });
                if (this.disabled) {
                    this.$_destroyCropper();
                }
                let indexImg = this.indexImg;
                this.$emit('cropper-created', {file, imageUrl: imgUrl, indexImg});
            },
            validate() {
                if (this.cropper != null) {
                    let imageData = this.cropper.getImageData();
                    let getData = this.cropper.getData();
                    if (getData.height < this.imageConfig.minHeight || getData.height > this.imageConfig.maxHeight || getData.width < this.imageConfig.minWidth || getData.width > this.imageConfig.maxWidth) {
                        return false;
                    }else {
                        return getData;
                    }
                }
            },
            val() {
                if (!this.cropper) {
                    return Promise.resolve(null);
                }
                let canvas = this.cropper.getCroppedCanvas();
                return new Promise(resolve => {
                    if (!canvas) {
                        resolve(null);
                        return;
                    }
                    canvas.toBlob(blob => {
                        resolve(blob);
                    }, this.selectedFile ? this.selectedFile.type : 'image/jpeg', 1);
                });
            },
            setRatio(value, url, file = null) {
                this.aspectRatios[0] = value;
                this.$_destroyCropper();
                this.pImageUrl = url;
                if (this.pImageUrl) {
                    setTimeout(() => this.$_initCropper(file, this.pImageUrl), 300);
                };
            },
        }
    }
</script>

<style scoped>
    img {
        max-width: 100%;
    }
</style>
