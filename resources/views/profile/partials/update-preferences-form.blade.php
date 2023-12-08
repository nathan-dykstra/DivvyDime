<section>
    <header>
        <h3>{{ __('Preferences') }}</h3>

        <p class="text-shy">{{ __("Update your app theme and email notification preferences.") }}</p>
    </header>

    <form method="post" action="{{ route('user-preferences.update') }}" class="space-top-sm">
        @csrf
        @method('patch')

        <div>
            <x-input-label :value="__('Theme')" />
            <div class="theme-container" id="theme-container">
                <div class="theme-setting" id="light-setting" data-theme="system" onclick="setTheme(this, 'system')">
                    <i class="fa-solid fa-gear" id="dark-setting-icon"></i>
                    {{ __('System') }}
                </div>
                <div class="theme-setting-gap"></div>
                <div class="theme-setting" id="light-setting" data-theme="light" onclick="setTheme(this, 'light')">
                    <i class="fa-solid fa-sun" id="light-setting-icon"></i>
                    {{ __('Light') }}
                </div>
                <div class="theme-setting-gap"></div>
                <div class="theme-setting" id="light-setting" data-theme="dark" onclick="setTheme(this, 'dark')">
                    <i class="fa-solid fa-moon" id="dark-setting-icon"></i>
                    {{ __('Dark') }}
                </div>
            </div>
        </div>

        <div>
            <x-input-label :value="__('Email Preference')" />
            
        </div>

        <div class="btn-container-start">
            <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p 
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<style>
    .theme-container {
        display: flex;
        justify-content: space-between;
        flex-direction: row;
        align-items: center;
        width: 100%;
        margin-top: 4px;
    }

    .theme-setting {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        height: 100%;
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        background-color: var(--background);
        color: var(--text-heading);
        padding: 8px 16px;
        transition: border 0.3s ease-in-out, background-color 0.3s ease-in-out, outline 0.1s;
    }

    .theme-setting-active {
        outline: 3px solid var(--blue-hover); /* Your outline color */
    }

    .theme-setting-active:hover {
        border: 1px solid var(--border-grey) !important; 
    }

    .theme-setting, .theme-setting-gap {
        flex: 1;
    }

    .theme-setting-gap {
        flex-grow: 0;
        margin: 0 10px;
    }

    .theme-setting:hover {
        background-color: var(--primary-grey-hover);
        border: 1px solid var(--blue-hover); /* TODO: Change this */
        cursor: pointer;
    }
</style>

<script>
    const themeContainer = document.getElementById("theme-container");

    const themeBtns = Array.from(themeContainer.children);
    themeBtns.forEach(btn => {
        if (btn.dataset.theme === theme) {
            btn.classList.add("theme-setting-active")
        } else {
            btn.classList.remove("theme-setting-active");
        }
    });
</script>
