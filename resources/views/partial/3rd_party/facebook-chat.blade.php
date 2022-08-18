@if (\Route::currentRouteName() !== 'do.test' && \Route::currentRouteName() !== 'view.advisory')
    <div class="social-contact">
        <a class="social-contact__link social-contact__youtube mb-2" href="{{ __('front/links.youtube', [], \App\Enums\ELanguage::VI) }}" target="_blank" rel="nofollow noopener"> </a>
        <a class="social-contact__link social-contact__facebook mb-2" href="{{ __('front/links.facebook_fanpage', [], \App\Enums\ELanguage::VI) }}" target="_blank" rel="nofollow noopener"> </a>
        <a class="social-contact__link social-contact__messenger" href="javascript:void(0)"></a>
    </div>
    <div class="social-contact__chat-panel collapse pr-3">
        <a class="close">×</a>
        <div class="fb-page"
             data-href="{{ __('front/links.facebook_fanpage', [], \App\Enums\ELanguage::VI) }}"
             data-tabs="messages"
             data-width="400"
             data-height="300"
             data-small-header="true">
            <div class="fb-xfbml-parse-ignore">
                <blockquote></blockquote>
            </div>
        </div>
    </div>
@endif
<script type="text/javascript">
    window.fbAsyncInit = function() {
        FB.init({
            appId: `{{config('services.facebook.client_id')}}`,
            xfbml: true,
            version: 'v3.3'
        });
    };
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/{{ app()->getLocale() === \App\Enums\ELanguage::VI ? 'vi_VN' : 'en_US' }}/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    $('.social-contact__chat-panel').collapse({
        toggle: false
    });
    +function() {
        var showClose = false;
        $('.social-contact__messenger, .social-contact__chat-panel a').click(function() {
            $('.social-contact__chat-panel').collapse('toggle');
            // showClose = !showClose;
            // showClose ? $(this).html('<i class="fa fa-times" aria-hidden="true"></i>') : $(this).html('<i class="fa fa-commenting" aria-hidden="true">');
        });
    }();
</script>
