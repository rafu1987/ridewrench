@section('title', __('auth.resetPasswordTitle'))

<x-app-layout>
    <div class="card card-lift mx-auto" style="max-width:520px">
        <div class="card-body p-3 p-md-4">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3">
                    <i class="fa-light fa-sharp fa-lock-keyhole"></i>
                </div>

                <h1 class="h3 fw-bold">
                    {{ __('auth.resetPasswordTitle') }}
                </h1>

                <p class="text-muted mb-0">
                    {{ __('auth.resetPasswordSubtitle') }}
                </p>
            </div>

            <form method="post" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                <label for="reset-password" class="form-label">
                    {{ __('auth.newPassword') }}
                </label>

                <input
                    id="reset-password"
                    class="form-control mb-3 @error('password') is-invalid @enderror"
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                @error('password')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <label for="reset-password-repeat" class="form-label">
                    {{ __('auth.repeatPassword') }}
                </label>

                <input
                    id="reset-password-repeat"
                    class="form-control mb-3"
                    type="password"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                <button class="btn btn-dark w-100">
                    {{ __('auth.saveNewPassword') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
