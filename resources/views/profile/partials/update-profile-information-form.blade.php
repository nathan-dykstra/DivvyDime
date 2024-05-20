<section>
    <header>
        <h3>{{ __('Profile') }}</h3>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <div class="profile-img-container">
        <div class="update-image update-profile-image" tabindex="0">
            <div class="profile-img-lg-container">
                <img class="profile-img-lg" id="profile-img" src="{{ $user->profile_image_url }}" alt="{{ __('Profile image for ') . $user->username }}">
            </div>
            <x-blur-background-button class="profile-img-update-btn" icon="fa-solid fa-pen-to-square icon" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-profile-image')">{{ __('Update') }}</x-blur-background-button>
            <x-blur-background-button class="profile-img-delete-btn warning-hover" icon="fa-solid fa-trash-can icon" onclick="submitDeleteProfileImageForm()">{{ __('Delete') }}</x-blur-background-button>
        </div>

        <div class="mobile-profile-img-btns-container">
            <x-primary-button class="expense-round-btn mobile-profile-img-btn" icon="fa-solid fa-pen-to-square icon" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-profile-image')">{{ __('Update') }}</x-primary-button>
            <x-primary-button class="expense-round-btn mobile-profile-img-btn warning-hover" icon="fa-solid fa-trash-can icon" onclick="submitDeleteProfileImageForm()">{{ __('Delete') }}</x-primary-button>
        </div>
    </div>

    <form id="delete-profile-img-form" action="{{ route('images.delete-profile') }}" method="post">
        @csrf
        @method('delete')
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-top-sm">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" :value="old('username', $user->username)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-warning">
                        {{ __('Your email address is unverified.') }}

                        <x-link-button form="send-verification">{{ __('Click here to re-send the verification email.') }}</x-link-button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="btn-container-start">
            <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
        </div>
    </form>

    <x-modal name="upload-profile-image" :show="false" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Upload an image') }}</h3>
                <p class="text-shy">
                    {{ __('Supports JPEG, JPG, and PNG file types. Maximum file size is 5MB.') }}
                </p>
            </div>

            <x-dropzone formAction="{{ route('images.upload-profile') }}" formId="profile-img-form" previewsId="profile-img-previews" />

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')" onclick="clearProfileUploader()">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="submitProfileImage()">{{ __('Upload') }}</x-primary-button>
            </div>
        </div>
    </x-modal>
</section>

<script>
    function submitDeleteProfileImageForm() {
        document.getElementById('delete-profile-img-form').submit();
    }
</script>
