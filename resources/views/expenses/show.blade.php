<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ $expense->name }}
    </x-slot>

    <x-slot name="back_btn">
        <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-arrow-left" onclick="window.history.back()" />
    </x-slot>

    <x-slot name="header_title">
        {{ $expense->name }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('expenses.edit', $expense)">{{ __('Edit') }}</x-primary-button>
        <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create')">{{ __('New Expense') }}</x-primary-button>
    </x-slot>

    <x-slot name="overflow_options">
        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-expense-images')">
            <i class="fa-solid fa-images"></i>
            <div>{{ __('Add Images') }}</div>
        </div>
        <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')">
            <i class="fa-solid fa-trash-can"></i>
            <div>{{ __('Delete') }}</div>
        </div>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <a class="dropdown-item" href="{{ route('expenses.edit', $expense) }}">
            <i class="fa-solid fa-pen-to-square"></i>
            <div>{{ __('Edit') }}</div>
        </a>
        <a class="dropdown-item" href="{{ route('expenses.create') }}">
            <i class="fa-solid fa-receipt"></i>
            <div>{{ __('New Expense') }}</div>
        </a>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'expense-created')
        <x-session-status>{{ __('Expense created.') }}</x-session-status>
    @elseif (session('status') === 'expense-updated')
        <x-session-status>{{ __('Expense updated.') }}</x-session-status>
    @elseif (session('status') === 'expense-images-uploaded')
        <x-session-status>{{ __('Images uploaded.') }}</x-session-status>
    @elseif (session('status') === 'expense-image-deleted')
        <x-session-status>{{ __('Image deleted.') }}</x-session-status>
    @elseif (session('status') === 'max-images-reached')
        <x-session-status innerClass="text-warning">{{ __('Images uploaded. You can only add up to ') . $max_images_allowed . __(' images!') }}</x-session-status>
    @elseif (session('status') === 'expense-note-updated')
        <x-session-status>{{ __('Note updated.') }}</x-session-status>
    @endif

    <!-- Content -->

    <h1>{{ __('$') . $expense->amount }}</h1>
    <div class="expense-info-date-group-category">
        <div class="text-shy text-thin-caps expense-info-date">{{ $expense->formatted_date }}</div>
        <a class="info-chip info-chip-link info-chip-grey" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
        <!--<a class="">{{ __('Category') }}</a>--> <!-- TODO: display expense category -->
    </div>

    <div class="expense-info-container margin-top-lg">
        <div>
            <div class="expense-info-breakdown">
                <div class="expense-info-breakdown-left">
                    <div class="profile-img-sm-container">
                        <img class="profile-img" src="{{ $expense->payer_user->profile_image_url }}" alt="{{ __('Profile image for ') . $expense->payer_user->username }}"/>
                    </div>
                    <div class="expense-info-breakdown-line-container">
                        <div class="expense-info-breakdown-line"></div>
                    </div>
                </div>

                <div class="expense-info-breakdown-right">
                    <div class="expense-info-breakdown-payer-container">
                        <div class="expense-info-breakdown-payer">
                                @if ($expense->payer === auth()->user()->id)
                                    {{ __('You') }}
                                @else
                                    <span class="bold-username">{{ $expense->payer_user->username }}</span>
                                @endif
                            </span>
                            @if ($expense->is_reimbursement)
                                {{ __(' received ') }}
                            @else
                                {{ __(' paid ') }}
                            @endif
                            {{ __('$') . $expense->amount }}
                        </div>
                    </div>

                    <div class="space-top-xs">
                        @foreach ($participants as $participant)
                            <div class="expense-info-participant text-shy">
                                @if ($participant->id === auth()->user()->id)
                                    {{ __('You') }}
                                @else
                                    <span class="bold-username">{{ $participant->username }}</span>
                                @endif
                                @if ($participant->id !== $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' receive ') : __(' receives ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
                                @endif
                                @if ($participant->id === $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' keep ') : __(' keeps ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
                                    {{ __(' and ') }}
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' receive ') : __(' receives ') }}
                                    @endif
                                    {{ __('$') . number_format($expense->amount - $participant->share, 2) }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- TODO: expense category spending graph -->
    </div>

    <div class="expense-note-media-container margin-top-lg">
        <div class="container">
            <div class="btn-container-apart">
                <h4>{{ __('Note') }}</h4>

                @if ($expense->note)
                    <x-icon-button id="expense-update-note-btn" class="text-small" icon="fa-solid fa-pen-to-square icon" onclick="showExpenseNoteForm()">{{ __('Edit') }}</x-icon-button>
                @endif
            </div>
            
            <div class="margin-top-sm">
                @if ($expense->note)
                    <div class="text-small" id="expense-note">
                        <p class="p-no-margin">{!! nl2br(e($expense->note)) !!}</p>
                    </div>
                @else
                    <button id="expense-add-note-btn" class="expense-empty-note" onclick="showExpenseNoteForm()">
                        {{ __('Click to add') }}
                    </button>
                @endif

                <form id="expense-update-note-form" method="post" action="{{ route('expenses.update-note', $expense) }}" class="hidden">
                    @csrf
                    @method('patch')

                    <x-input-label for="expense-note-textarea" class="screen-reader-only" :value="__('Note')" />
                    <x-text-area class="p-no-margin" id="expense-note-textarea" name="expense-note" maxlength="65535" :value="$expense->note ?? ''" />
                    <x-input-error :messages="$errors->get('expense-note')" />

                    <div class="btn-container-start">
                        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                        <x-primary-button onclick="hideExpenseNoteForm()">{{ __('Cancel') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
            <h4>{{ __('Images') }}</h4>

            <div class="expense-image-previews-container margin-top-sm">
                @foreach ($expense_images as $index => $image)
                    <div class="expense-img-preview-container expense-img-trigger">
                        <img class="expense-img-preview" src="{{ $image->expense_image_url }}" alt="{{ __('Expense image') }}" x-data="" x-on:click.prevent="$dispatch('open-modal', 'view-image-gallery')" onclick="setModalImage(this, {{ $index }})" tabindex="0">
                        <x-blur-background-button class="expense-remove-img-btn" icon="fa-solid fa-sm fa-xmark" data-expense-img-id="{{ $image->id }}" onclick="removeExpenseImage(event, this)" />

                        <form id="expense-remove-img-form" action="" method="post">
                            @csrf
                            @method('delete')
                        </form>
                    </div>
                @endforeach

                @unless ($expense->images()->count() >= $max_images_allowed)
                    <button class="expense-add-image-btn" icon="fa-solid fa" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-expense-images')">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                @endunless
            </div>
        </div>
    </div>

    <div class="horizontal-center margin-top-lg">
        <div class="expense-info-added-date text-shy">
            <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->created_date . __(' at ') . $expense->created_time }}">
                {{ __('Added ') }}
                <span class="width-content">{{ $expense->formatted_created_date }}</span>
                {{ __(' by ') }}<span class="bold-username">{{ $expense->creator_user->username }}</span>
            </x-tooltip>
        </div>

        @if ($expense->created_at->toDateTimeString() !== $expense->updated_at->toDateTimeString())
            <div class="text-shy">
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->updated_date . __(' at ') . $expense->updated_time }}">
                    {{ __('Updated ') }}
                    <span class="width-content">{{ $expense->formatted_updated_date }}</span>
                    {{ __(' by ') }}<span class="bold-username">{{ $expense->updator_user->username }}</span>
                </x-tooltip>
            </div>
        @endif
    </div>

    <!-- Modals -->

    <x-image-modal idPrefix="expense">
        <x-slot name="main_image">
            <div class="modal-img-lg-container">
                <div id="expense-img-lg-container" class="expense-img-lg-container">
                    <img id="expense-image-lg" class="expense-img-lg" src="" alt="Expense image (large view)">

                    <x-blur-background-button id="expense-image-modal-left" class="image-modal-left" icon="fa-solid fa-chevron-left" onclick="prevImage()" />
                    <x-blur-background-button id="expense-image-modal-right" class="image-modal-right" icon="fa-solid fa-chevron-right" onclick="nextImage()" />
                </div>
            </div>
        </x-slot>

        <x-slot name="gallery_images">
            <div class="expense-img-gallery">
                @foreach ($expense_images as $index => $image)
                    <div class="expense-img-preview-container expense-img-trigger">
                        <img class="expense-img-preview modal-img-preview" src="{{ $image->expense_image_url }}" alt="{{ __('Expense image') }}" onclick="setModalImage(this, {{ $index }})" tabindex="0">
                    </div>
                @endforeach
            </div>
        </x-slot>
    </x-image-modal>

    <x-modal name="upload-expense-images" id="upload-expense-images" :show="false" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Upload images') }}</h3>
                <p class="text-shy">
                    {{ __('Supports JPEG, JPG, and PNG file types. Up to 5 images can be added. Maximum file size is 5MB.') }}
                </p>
            </div>

            <x-dropzone formAction="{{ route('images.upload-expense', $expense) }}" formId="expense-img-form" previewsId="expense-img-previews" />

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')" onclick="clearExpenseUploader()">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="submitExpenseImages()">{{ __('Upload') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    @include('expenses.partials.expense-delete-modal')
</x-app-layout>

<script>
    function showExpenseNoteForm() {
        const addNoteBtn = document.getElementById('expense-add-note-btn')
        if (addNoteBtn) {
            addNoteBtn.classList.add('hidden');
        }

        const updateNoteBtn = document.getElementById('expense-update-note-btn')
        if (updateNoteBtn) {
            updateNoteBtn.classList.add('hidden');
        }

        const expenseNote = document.getElementById('expense-note');
        if (expenseNote) {
            expenseNote.classList.add('hidden');
        }

        document.getElementById('expense-update-note-form').classList.remove('hidden');
        const textArea = document.getElementById('expense-note-textarea');
        resizeTextarea(textArea);
        textArea.focus();
    }

    function hideExpenseNoteForm() {
        document.getElementById('expense-update-note-form').classList.add('hidden');

        const expenseNote = document.getElementById('expense-note');
        if (expenseNote) {
            expenseNote.classList.remove('hidden');
        }

        const addNoteBtn = document.getElementById('expense-add-note-btn')
        if (addNoteBtn) {
            addNoteBtn.classList.remove('hidden');
        }

        const updateNoteBtn = document.getElementById('expense-update-note-btn')
        if (updateNoteBtn) {
            updateNoteBtn.classList.remove('hidden');
        }
    }

    function removeExpenseImage(event, removeBtn) {
        event.preventDefault();
        imgId = removeBtn.dataset.expenseImgId
        removeImageForm = document.getElementById('expense-remove-img-form');
        removeImageForm.action = `/images/delete-expense/${imgId}`;
        removeImageForm.submit();
    }

    const expenseImages = document.querySelectorAll('.expense-img-preview');

    expenseImages.forEach(image => {
        image.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                image.click();
            }
        });
    });
</script>
