window.common = {
	loading: {
		spinner: {},
		show(target = 'body') {
			let targetName = '';
			target.split(' ').forEach((part, index, array) => {
				targetName += (part || 'body').replace('#', 'id-').replace('.', 'class-');
				if (index < array.length - 1) {
					targetName += '-';
				}
			});
			let el = $('.common-loading-' + targetName);
			if(el.length) {
				el.remove();
			}

			$(target).append($('<div />', { class: 'common-loading-' + targetName }));

			el = $('.common-loading-' + targetName);
			el.append($('<div />', { class: 'blob-' + targetName }));
			el.fadeIn();

			if(this.spinner[targetName] && this.spinner[targetName] != null) {
				this.spinner[targetName].multiple = true
			} else {
				this.spinner[targetName] = {
					el: el,
					multiple: false
				}
			}
		},
		hide: function(target = 'body') {
			let targetName = '';
			target.split(' ').forEach((part, index, array) => {
				targetName += (part || 'body').replace('#', 'id-').replace('.', 'class-');
				if (index < array.length - 1) {
					targetName += '-';
				}
			});
			let el = $('.common-loading-' + targetName);
			if (!this.spinner[targetName]) {
				return;
			}
			if(!this.spinner[targetName].multiple) {
				let _this = this;
				setTimeout(function() {
					el.fadeOut();
					delete _this.spinner[targetName];
				}, 250)
			} else {
				this.spinner[targetName].multiple = false
			}
		},
		hideAll: function() {
			let _this = this;
			Object.keys(this.spinner).forEach((key) => {
				setTimeout(function() {
					_this.spinner[key].el.fadeOut();
					delete _this.spinner[key];
				}, 250)
			});
		}
	},
	twoDigitNumber: (number) => number < 10 ? '0' + number : number,
	makeUserCantGoBack() {
		history.pushState(null, null, location.href);
		window.onpopstate = function () {
			history.go(1);
		}
	},
	askUserWhenLeavePage() {
		if (!window.onbeforeunload) {
			// this text is not really important, modern browser will use its default message
			window.onbeforeunload = () => 'Are you sure you want to leave this page?';
		}
	},
	getDateString(date, separator = '/') {
		return this.twoDigitNumber(date.getDate()) + separator + this.twoDigitNumber(date.getMonth() + 1) + separator + date.getFullYear()
	},
	getTimeString(date, separator = ':', getSecond = false) {
		return this.twoDigitNumber(date.getHours()) + separator + this.twoDigitNumber(date.getMinutes()) + (getSecond ? separator + this.twoDigitNumber(date.getSeconds()) : '');
	},
    getDateTimeString(date, showSecond = false, swapDateTime = false) {
	    let time = this.getTimeString(date, ':', showSecond);
	    let day = this.getDateString(date, '/');
		return swapDateTime ? `${day} ${time}` : `${time} ${day}`;
	},
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
    },
    temporaryData(key, data, remove = false) {
        if (!key) {
            return null;
        }
        if (typeof sessionStorage === "undefined") {
            return this.cookie(key, data, remove);
        }

        return this.sessionStorage(key, data, remove);
    },
    sessionStorage(key, data, remove = false) {
        if (remove) {
            sessionStorage.removeItem(key);
            return;
        }
        if (typeof data !== "undefined") {
            sessionStorage.setItem(key, JSON.stringify(data));
        } else {
            let sessionData = sessionStorage.getItem(key);
            if (sessionData) {
                return JSON.parse(sessionData);
            }
            return null;
        }
    },
    cookie(key, data, remove = false) {
        if (remove) {
            Cookies.remove(key);
            return;
        }
        if (typeof data !== "undefined") {
            Cookies.set(key, data, {path: '/'});
        } else {
            return Cookies.get(key);
        }
    },
	alert(title, message, callback = () => {}, bootboxOptions = {}) {
		if (arguments.length === 1) {
			return bootbox.alert({
                ...bootboxOptions,
                message: title || 'No message',
            });
		}
		if ($.isFunction(message)) {
			return bootbox.alert({
                ...bootboxOptions,
                message: title || 'No message',
                callback: message,
            });
		}

		bootbox.alert({
            ...bootboxOptions,
			title: title,
			message: message || 'No message',
			callback: callback,
		});
	},
	confirm(title, message, callback = () => {}, bootboxOptions = {}) {
		if (arguments.length === 1) {
			return bootbox.confirm({
                ...bootboxOptions,
                message: title || 'No message',
                callback
            });
		}
		if ($.isFunction(message)) {
			return bootbox.confirm({
                ...bootboxOptions,
                message: title || 'No message',
                callback: message,
            });
		}

		bootbox.confirm({
            ...bootboxOptions,
			title: title,
			message: message || 'No message',
			callback: callback,
		});
	},
	standardizedVietnamPhoneFormat(number) {
		let allowSpecialCharacterRegex = /[()\-\s]/g;
		return number.replace(allowSpecialCharacterRegex, '').replace(/\+84/g, '0');
	},
	validateVietnamPhone(number) {
		number = this.standardizedVietnamPhoneFormat(number);
		let numberRegex = /(\+84|0)([35789]\d{8}|2\d{9})$/g;
		return numberRegex.test(number);
	},
	validateString(str) {
		let lengthRegex = /.{1,}/g;
		let specialCharacterRegex = /[$&+,:;=?@#|<>.\-^*()%!]+/g;
		return !specialCharacterRegex.test(str) && lengthRegex.test(str);
	},
	validateEmail(email) {
		let emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return emailRegex.test(email);
	},
	openElementInFullscreen(selector) {
		let el = $(selector).get(0);
		if (!el) {
			return false;
		}
		if (el.requestFullscreen) {
			el.requestFullscreen();
			return true;
		}
		if (el.mozRequestFullScreen) { /* Firefox */
			el.mozRequestFullScreen();
			return true;
		}
		if (el.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
			el.webkitRequestFullscreen();
			return true;
		}
		if (elem.msRequestFullscreen) { /* IE/Edge */
			el.msRequestFullscreen();
			return true;
		}

		return false;
	},
	closeFullScreen() {
		if (document.exitFullscreen) {
			document.exitFullscreen();
			return true;
		}
		if (document.mozCancelFullScreen) { /* Firefox */
			document.mozCancelFullScreen();
			return true;
		}
		if (document.webkitExitFullscreen) { /* Chrome, Safari and Opera */
			document.webkitExitFullscreen();
			return true;
		}
		if (document.msExitFullscreen) { /* IE/Edge */
			document.msExitFullscreen();
			return true;
		}

		return false;
	},
	checkFirebaseReady() {
		return new Promise((resolve) => {
			let firebaseReady = () => {
				if (firebase && firebase.apps && firebase.apps.length) {
					resolve();
				} else {
					setTimeout(firebaseReady, 1000);
				}
			};
			firebaseReady();
		});
    },
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
    },
    replaceString(search, replace, subject) {
        if (!Array.isArray(search)) {
            search = [search];
        }
        if (!Array.isArray(replace)) {
            replace = [replace];
        }
        for (let i = 0; i < search.length; i++) {
            let regExp = new RegExp(`${search[i]}`);
            subject = subject.replace(regExp, replace[i % replace.length]);
        }
        return subject;
    },
    getUrlQueries(url, key = null) {
        let tmp = url.split('?');
        if (!tmp.length || tmp.length < 2) {
            return key ? null : [];
        }
        tmp = tmp[1];
        let queries = tmp.split('&');
        let result = {};
        for (let i = 0; i < queries.length; i++) {
            let query = queries[i];
            query = query.split('=');
            if (key && query[0] === key) {
                return typeof query[1] !== 'undefined' ? query[1] : null;
            }
            result[query[0]] = query.length !== 2 ? null : query[1];
        }
        return result;
    },
    getUrlWithQueries(pathname, queries = {}) {
        let queriesString = Object.keys(queries)
            .filter((key) => {
                return queries[key] !== null && queries[key] !== undefined && queries[key] !== '';
            })
            .map((key) => {
                return `${key}=${queries[key]}`;
            })
            .join('&');
        return `${pathname.split('?')[0]}?${queriesString}`;
    },
    switchCase(str, toCase) {
	    let arr = str.split(/(?=[A-Z])|[_\\s-](?=[A-Z])/);
	    switch (toCase) {
            case 'snake':
                return arr.join('_').toLowerCase();
            case 'kebab':
                return arr.join('-').toLowerCase();
            case 'camel':
                return arr.map((part) => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase()).join('');
        }
        return str;
    },
    scrollTo(selector, offset = 0) {
        let target = $(selector);
        // Does a scroll target exist?
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top + offset
            }, 1000, function () {
                // Callback after animation
                // Must change focus!
                let $target = $(target);
                $target.focus();
                if ($target.is(":focus")) { // Checking if the target was focused
                    return false;
                } else {
                    $target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
                    $target.focus(); // Set focus again
                };
            });
        }
    }
};
