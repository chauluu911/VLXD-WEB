import ErrorCode from "../constants/error-code";

class LoadingScreen {
    constructor() {
        this.spinner = {};
    }

    show(target = 'body', msg) {
        if ("activeElement" in document) {
            document.activeElement.blur();
        }

        let targetName = this.$_getName(target);
        let el = $(`.common-loading-${targetName}`);
        if (el.length) {
            el.remove();
        }
        $(target).append($('<div />', {class: `common-loading-${targetName}`}));

        el = $(`.common-loading-${targetName}`);
        el.append($('<div />', {class: 'blob-' + targetName}));
        if (msg) {
            el.append($(`<div class="msg">${msg}</div>`));
        } else {
            el.find('.msg').remove();
        }

        if (this.spinner[targetName] && this.spinner[targetName] != null) {
            this.spinner[targetName].multiple = true
        } else {
            this.spinner[targetName] = {
                el: el,
                multiple: false
            }
        }
    }

    hide(target = 'body') {
        let _this = this;
        let targetName = this.$_getName(target);
        let el = $(`.common-loading-${targetName}`);
        if (!_this.spinner[targetName]) {
            return;
        }
        if (!_this.spinner[targetName].multiple) {
            el.fadeOut();
            delete _this.spinner[targetName];
        } else {
            _this.spinner[targetName].multiple = false
        }
    }

    hideAll() {
        let _this = this;
        Object.keys(this.spinner).forEach((key) => {
            if (!_this.spinner[key]) {
                return;
            }
            _this.spinner[key].el.fadeOut();
            delete _this.spinner[key];
        });
        $('[class*=common-loading]').remove();
    }

    changeMsg(target = 'null', msg = '') {
        let targetName = this.$_getName(target);
        let el = $('.common-loading-' + targetName);
        if (!el.length) {
            return;
        }
        el.find('.msg').remove();
        if (msg) {
            el.append($(`<div class="msg">${msg}</div>`));
        }
    }

    $_getName(target) {
        let targetName = '';
        target.split(' ').forEach((part, index, array) => {
            targetName += (part || 'body').replace('#', 'id-').replace('.', 'class-');
            if (index < array.length - 1) {
                targetName += '-';
            }
        });
        return targetName;
    }
}

class Util {
    constructor() {
        this.i18n = null;
        this.bvModal = null;
        this.loadingScreen = new LoadingScreen();
    }

    withI18n(i18n) {
        this.i18n = i18n;
        return this;
    }

    withBvModal(bvModal) {
        this.bvModal = bvModal;
        return this;
    }

    /**
     *
     * @param type "success", "error"
     * @param title
     * @param content
     * @param options
     */
    showMsg(type, title, content, options = {}) {
        if (window.toastr) {
            const defaultOption = {
                progressBar: true,
                positionClass: 'toast-bottom-right'
            };
            toastr[type](content, title, {...defaultOption, ...options});
        } else if(window.bootbox) {
            let icon = '',
                buttonClass = 'btn-primary';
            if (type === 'success') {
                icon = '<i class="far fa-check-circle text-success"></i>';
                buttonClass = 'btn-success';
            } else if (type === 'error') {
                icon = '<i class="far fa-times-circle text-danger"></i>';
                buttonClass = 'btn-danger';
            } else {
                bootbox.alert({
                    message: content,
                    ...options,
                    callback: options.onHidden || options.callback || (() => {})
                });
                return;
            }
            bootbox.dialog({
                ...options,
                message: `
<div class="row">
    <div class="col-48 text-center">
        <span style="font-size: 64px;">
            ${icon}
        </span>
        ${title ? `<p class="mb-0 font-weight-bold">${title}</p>` : ''}
        <p class="mb-0">
            ${content || ''}
        </p>
    </div>
</div>
                `,
                closeButton: false,
                size: 'sm',
                buttons : {
                    ...(options.buttons || {}),
                    ok: {
                        label: 'OK',
                        className: `rounded-pill ${buttonClass} m-auto border`,
                        callback: options.onHidden || (() => {}),
                    }
                }
            });
        }
    }

    /**
     * show message with input from api response
     * @param apiResponse
     */
    showMsg2(apiResponse) {
        const {error, msg} = apiResponse;
        this.showMsg(error ? 'error' : 'success', null, msg);
    }

    get confirmDefaultOptions() {
        return {
            size: 'sm',
            buttonSize: 'sm',
            okVariant: 'danger',
            okTitle: this.i18n.t('common.button.ok'),
            cancelTitle: this.i18n.t('common.button.cancel'),
            centered: true,
            noCloseOnEsc: true,
            noCloseOnBackdrop: true
        }
    };

    alert(content, callback = () => {}, options = {}) {
        if (window.bootbox) {
            bootbox.confirm({
                message: content || 'No message',
                callback,
                ...options
            });
        }
    }

    /**
     *
     * @param content
     * @param callback
     * @param options
     */
    confirm(content, callback = () => {}, options = {}) {
        if (window.bootbox) {
            bootbox.confirm({
                message: content || 'No message',
                callback,
                ...options
            });
        } else if (this.bvModal) {
            let tmp = this.bvModal.msgBoxConfirm(content, Object.assign(this.confirmDefaultOptions, options));
            if (callback) {
                tmp.then(value => {
                    callback(value);
                })
                    .catch(err => {
                        // An error occurred
                    });
            } else {
                return tmp;
            }
        }
    }

    /**
     *
     * @param objectName
     * @param callback
     */
    confirmDelete(objectName, callback) {
        let msg = this.i18n.t('common.confirmation.delete', {objectName: objectName});
        return this.confirm(msg, callback);
    }

    confirmResetPassword(objectName, callback) {
        let msg = this.i18n.t('common.confirmation.resetPassword', {objectName: objectName});
        return this.confirm(msg, callback);
    }

    confirmApprove(objectName, callback) {
        let msg = this.i18n.t('common.confirmation.approve', {objectName: objectName});
        return this.confirm(msg, callback);
    }

    get(options) {
        let {failCb, isModal = false, ...reqOptions} = options;

        if (reqOptions.data && reqOptions.data instanceof FormData) {
            reqOptions.processData = false;
            reqOptions.contentType = false;
        }
        return $.get({
            ...reqOptions,
        })
            .fail(error => {
                console.error('error', error);
                switch (error.status) {
                    case 401:
                        document.getElementById('logout-form').submit();
                        break;
                    case 500:
                        if (process.env.APP_DEBUG) {
                            console.error(error);
                        }
                        if (!isModal) {
                            this.showMsg('error', null, error.responseJSON.msg);
                            return;
                        }
                        failCb({}, error.responseJSON.msg);
                        break;
                }
            });
    }

    getFront(options) {
        let {failCb, isModal = false, ...reqOptions} = options;
        return $.get({
            ...reqOptions,
        })
            .fail(error => {
                console.error('error', error);
                switch (error.status) {
                    case 401:
                        document.getElementById('logout-form').submit();
                        break;
                    case 500:
                        if (process.env.APP_DEBUG) {
                            console.error(error);
                        }
                        if (!isModal) {
                            this.showMsg('error', null, error.responseJSON.msg);
                            return;
                        }
                        failCb({}, error.responseJSON.msg);
                        break;
                }
            });
    }

    post(options) {
        let {errorModel, failCb, isModal = false, ...reqOptions} = options;
        if (errorModel) {
            for (let p in errorModel) {
                errorModel[p] = null;
            }
        }
        let fillErrorModel = function(errorData) {
            for (let p in errorModel) {
                if (errorData[p]) {
                    errorModel[p] = errorData[p];
                } else {
                    errorModel[p] = null;
                }
            }
        };

        if (reqOptions.data && reqOptions.data instanceof FormData) {
            reqOptions.processData = false;
            reqOptions.contentType = false;
        }
        return $.post({
            ...reqOptions,
        }).done(response => {
            let {error, msg, data} = response;
            if (error === ErrorCode.VALIDATION_ERROR && data) {
                if (failCb) {
                    failCb(data, msg);
                    return;
                }
                fillErrorModel(data);
                return false;
            }
        }).fail((error) => {
            switch (error.status) {
                case 401:
                    document.getElementById('logout-form').submit();
                    break;
                case 422:
                    let errors = error.responseJSON.errors;
                    if (failCb) {
                        failCb(errors, error.responseJSON.msg);
                        return;
                    }
                    fillErrorModel(errors);
                    break;
                case 419:
                    window.location.reload();
                    break;
                case 429:
                    if (process.env.APP_DEBUG) {
                        console.error(error);
                    }
                    if (!isModal) {
                        this.showMsg('error', null, "Quá nhiều yêu cầu, vui lòng đợi trong giây lát!");
                        return;
                    }
                    failCb({}, error.responseJSON.msg);
                    break;
                case 500:
                    if (process.env.APP_DEBUG) {
                        console.error(error);
                    }
                    if (!isModal) {
                        this.showMsg('error', null, error.responseJSON.msg);
                        return;
                    }
                    failCb({}, error.responseJSON.msg);
                    break;
            }
        });
    }

    askUserWhenLeavePage(enable = true) {
        if (enable && !window.onbeforeunload) {
            // this text is not really important, modern browser will use its default message
            window.onbeforeunload = () => 'Are you sure you want to leave this page?';
        } else if (!enable) {
            window.onbeforeunload = null;
        }
    }

    makeTextareaAutoHeight(selector, minHeight) {
        let el = $(selector);
        let resize = (el) => {
            let elHeight = el.scrollHeight;
            $(el).css('height', '1px');
            $(el).css('height', `${elHeight < minHeight ? minHeight : elHeight}px`);
        };
        for (let i = 0; i < el.length; i++) {
            $(el.get(i)).on('input', () => resize(el.get(i)));
            resize(el.get(i));
        }
    }

    getMultipartFormData(selector) {
        let form = $(selector);
        if (form.length === 0) {
            return null;
        }
        if (form.length > 1) {
            throw 'Too much form with that selector';
        }

        let result = new FormData();

        let serialize = form.serializeArray();
        serialize.forEach((item) => {
            result.append(item.name, item.value);
        });
        let fileInput = $(`${selector} input[type="file"]`);
        for (let i = 0; i < fileInput.length; i++) {
            for (let j = 0; j < fileInput[i].files.length; j++) {
                result.append(fileInput[i].name, fileInput[i].files[j]);
            }
        }
        return result;
    }
}

const util = new Util();

export default util;
