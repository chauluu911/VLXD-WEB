@extends('back.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/back/login.css') }}" type="text/css"/>
@endpush

@section('body-class') container-fluid d-flex-center @endsection
@section('body-style') background-color: #ffffff; min-height: 100vh; @endsection

@section('body')
    <div class="row">
        <div class="col-48" style="width: 100%; max-width: 500px; min-width: 320px;">
            <div>
                <form class="" method="POST" action="{{ route('back.login',[],false) }}">
                    @csrf
                    <div class="div--login row" style="background-color: white">
                        <div class="col-48 d-flex justify-content-center mb-4">
                            <img class="img-fluid" width="200px" src="/images/logo.jpg" alt="{{ __('common/site.title') }}">
                        </div>
                        @error('email')
                        <div class="alert alert-danger w-100 mx-3" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror

                        <div class="col-48 p-0 mb-2 px-3">
                            <label for="inputEmail" class="sr-only">{{ __('auth.form.email') }}</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text text-primary border-primary" id="basic-addon1">
                                        <i class="fas fa-user-alt"></i>
                                    </span>
                                </div>
                                <input type="text"
                                       id="input-email"
                                       name="email"
                                       class="form-control input__email @error('email') is-invalid @enderror"
                                       placeholder="{{ __('auth.form.email') }}"
                                >
                            </div>
                        </div>
                        @error('password')
                        <div class="alert alert-danger w-100 mx-3" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                        <div class="col-48 p-0 mb-2 px-3">
                            <label for="inputPassword" class="sr-only">{{ __('auth.form.password') }}</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text text-primary border-primary">
                                        <i class="fas fa-key"></i>
                                    </span>
                                </div>
                                <input type="password"
                                       id="input-password"
                                       name="password"
                                       class="input__password form-control"
                                       placeholder="{{ __('auth.form.password') }}"
                                       autocomplete="current-password"
                                >

                                <div class="input-group-append">
                                    <span class="input-group-text text-primary border-primary">
                                        <i id="show-pass" class="fas fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="checkbox mb-2" style="padding: 0px 25px !important">
                            <label class="label-checkbox" for="remember">{{ __('auth.form.remember-me') }}
                              <input type="checkbox" name="remember" id="remember" value="remember-me" {{ old('remember') ? 'checked' : '' }}>
                              <span class="checkmark border-primary"></span>
                            </label>
                        </div>

                        <button class="btn--login font-weight-bold bg-primary text-white"
                                onclick="this.form.submit(); this.disabled = true;"
                                type="submit"
                                style="margin: 0px 25px !important"
                        >
                            {{ __('auth.form.login') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('body-scripts')
    <script>

        $(document).ready(function() {
             $('#input-email').keyup(function() {
                this.value = this.value.toLowerCase();
            });
            $('#show-pass').on('click', function() {
                if ($('#show-pass').hasClass('fa-eye-slash')) {
                    $('#input-password').removeAttr('type', 'password');
                    $('#input-password').attr('type', 'text');
                    $('#show-pass').removeClass('fa-eye-slash');
                    $('#show-pass').addClass('fa-eye');
                }else {

                    $('#input-password').removeAttr('type', 'text');
                    $('#input-password').attr('type', 'password');
                    $('#show-pass').removeClass('fa-eye');
                    $('#show-pass').addClass('fa-eye-slash');
                }
            });
        });
    </script>
@endpush
