<div class="container margin-bottom-lg">
    <div class="restrict-max-width">
        <section>
            <header>
                @if ($group)
                    <h3>{{ __('Details') }}</h3>
                    <p class="text-shy">{{ __("Update your group name and image.") }}</p>                    
                @else
                    <p class="text-shy">{{ __("Give your group a name, and upload an image if you would like.") }}</p>   
                @endif
            </header>
            <form method="post" action="{{ route('groups.store') }}" class="space-top-sm">
                @csrf

                <div>
                    <x-input-label for="group_name" :value="__('Name')" />
                    <x-text-input id="group_name" name="name" type="text" :value="old('name', $group?->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="btn-container-start">
                    <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </section>
    </div>
</div>