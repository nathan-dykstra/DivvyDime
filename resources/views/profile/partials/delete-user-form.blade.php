<section class="space-top-sm">
    <header>
        <h3>{{ __('Delete Account') }}</h3>

        <p class="text-shy">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="container space-bottom-sm">
            @csrf
            @method('delete')

            <div>
                <h3>{{ __('Are you sure you want to delete your account?') }}</h3>

                <p class="text-shy">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="screen-reader-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>

                <x-danger-button type="submit">{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
