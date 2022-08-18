<script type="application/javascript">
    @php
        $redirectWhenTokenExpired = !Illuminate\Support\Str::startsWith(\Illuminate\Support\Facades\Request::url(), 'back');
    @endphp
    // default message for ajax
    $.ajaxSetup({
        error: function(jqXHR, textStatus) {
            var message;
            if (jqXHR.status === {{ \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED }}
                || jqXHR.status === 419 {{--csrf token mismatch--}}) {
                @if ($redirectWhenTokenExpired)
                    message = '@lang('The login session has expired')';
                    if (!$(".bootbox.modal:contains('" + message + "')").length) {
                        common.alert(message, () => {
                            window.location.href = '{{ route('login', ['continue' => \Illuminate\Support\Facades\Request::url()]) }}';
                        });
                    }
                @else
                    message = '@lang('The login session has expired. Please login and try again')';
                    if (!$(".bootbox.modal:contains('" + message + "')").length) {
                        common.alert(message, () => {
                            // TODO: Show login form
                        });
                    }
                @endif
            }
        }
    });

    // default bootbox lang
    var lang = document.documentElement.lang.substr(0, 2);
    bootbox.addLocale('{{ \App\Enums\ELanguage::EN }}', {
        OK: '{{ __('common.close', [], \App\Enums\ELanguage::EN) }}',
        CANCEL: '{{ __('common.cancel', [], \App\Enums\ELanguage::EN) }}',
        CONFIRM: '{{ __('common.confirm',[], \App\Enums\ELanguage::EN) }}',
    });
    bootbox.addLocale('{{ \App\Enums\ELanguage::VI }}', {
        OK: '{{ __('common.ok', [], \App\Enums\ELanguage::VI) }}',
        CANCEL: '{{ __('common.cancel', [], \App\Enums\ELanguage::VI) }}',
        CONFIRM: '{{ __('common.confirm',[], \App\Enums\ELanguage::VI) }}',
    });
    bootbox.setLocale(lang);

    // response conflict between bootbox and bootstrap modal
    $(document).on('hidden.bs.modal', '.modal', function() {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });
    var $itemCreatedAtList = $('.localize-item-created-at');
    for (var i = 0; i < $itemCreatedAtList.length; i++) {
        var $itemCreatedAt = $($itemCreatedAtList.get(i));
        try {
            var createdAt = new Date($itemCreatedAt.data('time'));
            if (!isNaN(createdAt.getTime())) {
                $itemCreatedAt.text(common.getDateString(createdAt));
            }
        } catch (e) { }
    }
</script>
