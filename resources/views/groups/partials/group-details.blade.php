<div class="container margin-bottom-lg">
    <div class="restrict-max-width">
        <section>
            <header>
                @if ($group)
                    <h3>{{ __('Details') }}</h3>
                @else
                    <p class="text-shy">{{ __("Give your group a name. You can add an image and invite people to the group after saving.") }}</p>   
                @endif
            </header>

            @if ($group)
                <div class="profile-img-container">
                    <div class="update-image update-group-image" tabindex="0">
                        <div class="group-img-lg-container">
                            <img class="group-img-lg" id="profile-img" src="{{ $group->group_image_url}}" alt="{{ __('Group image for ') . $group->name }}">
                        </div>
                        <x-blur-background-button class="profile-img-update-btn" icon="fa-solid fa-pen-to-square icon" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-group-image')">{{ __('Update') }}</x-blur-background-button>
                        <x-blur-background-button class="profile-img-delete-btn warning-hover" icon="fa-solid fa-trash-can icon" onclick="submitDeleteGroupImageForm()">{{ __('Delete') }}</x-blur-background-button>
                    </div>

                    <div class="mobile-profile-img-btns-container">
                        <x-primary-button class="expense-round-btn mobile-profile-img-btn" icon="fa-solid fa-pen-to-square icon" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-group-image')">{{ __('Update') }}</x-primary-button>
                        <x-primary-button class="expense-round-btn mobile-profile-img-btn warning-hover" icon="fa-solid fa-trash-can icon" onclick="submitDeleteGroupImageForm()">{{ __('Delete') }}</x-primary-button>
                    </div>
                </div>

                <form id="delete-group-img-form" action="{{ route('images.delete-group', $group) }}" method="post">
                    @csrf
                    @method('delete')
                </form>

                <x-modal name="upload-group-image" id="upload-group-image" :show="false" focusable>
                    <div class="space-bottom-sm">
                        <div>
                            <h3>{{ __('Upload an image') }}</h3>
                            <p class="text-shy">
                                {{ __('Supports JPEG, JPG, and PNG file types. Maximum file size is 5MB.') }}
                            </p>
                        </div>

                        <x-dropzone formAction="{{ route('images.upload-group', $group) }}" formId="group-img-form" previewsId="group-img-previews" />

                        <div class="btn-container-end">
                            <x-secondary-button x-on:click="$dispatch('close')" onclick="clearGroupUploader()">{{ __('Cancel') }}</x-secondary-button>
                            <x-primary-button class="primary-color-btn" onclick="submitGroupImage()">{{ __('Upload') }}</x-primary-button>
                        </div>
                    </div>
                </x-modal>
            @endif

            <form method="post" action="{{ $group ? route('groups.update', $group) : route('groups.store') }}" class="space-top-sm">
                @csrf
                @if ($group)
                    @method('patch')
                @endif

                <div>
                    <x-input-label for="group-name" :value="__('Name')" />
                    <x-text-input id="group-name" name="name" type="text" :value="old('name', $group?->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="btn-container-start">
                    <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    function submitDeleteGroupImageForm() {
        document.getElementById('delete-group-img-form').submit();
    }
</script>
