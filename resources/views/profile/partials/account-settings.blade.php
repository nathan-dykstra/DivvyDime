<section class="space-top-sm">
    <header>
        <h3>{{ __('Account') }}</h3>
    </header>

    <ul>
        <li class="item-list-selector" x-data="" x-on:click.prevent="$dispatch('open-modal', 'change-user-password')">
            <div>
                <i class="fa-solid fa-key icon"></i>
                {{ __('Change Password') }}
            </div>
        </li>
        <li class="item-list-selector" onclick="submitLogOutForm()">
            <form id="log-out-form" method="post" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
            <div>
                <i class="fa-solid fa-right-from-bracket icon"></i>
                {{ __('Log Out') }}
            </div>
        </li>
        <li class="item-list-selector warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            <div>
                <i class="fa-solid fa-trash-can icon"></i>
                {{ __('Delete Account') }}
            </div>
        </li>
    </ul>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="space-bottom-sm">
            @csrf
            @method('delete')

            <div>
                <h3>{{ __('Are you sure you want to delete your account?') }}</h3>
                <p class="text-shy">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Any Groups or Expenses that you participated in will be updated to show a "DivvyDime User". Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="screen-reader-only" />
                <x-text-input id="password" name="password" type="password" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button type="submit">{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="change-user-password" :show="$errors->updatePassword->isNotEmpty()" focusable>
        <form method="post" action="{{ route('password.update') }}" class="space-bottom-sm">
            @csrf
            @method('put')

            <div>
                <h3>{{ __('Change your password') }}</h3>
                <p class="text-shy">
                    {{ __('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </div>

            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')"/>
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" />
                <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-primary-button>
                <x-primary-button class="primary-color-btn" type="submit">{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
</section>
