<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $expense->name }}</h2>

            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('expenses.edit', $expense)">{{ __('Edit') }}</x-primary-button>

                <x-dropdown>
                    <x-slot name="trigger">
                        <x-primary-button icon="fa-solid fa-ellipsis-vertical" />
                    </x-slot>

                    <x-slot name="content">
                        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-expense-images')">
                            <i class="fa-solid fa-images"></i>
                            <div>{{ __('Add Images') }}</div>
                        </div>
                        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')">
                            <i class="fa-solid fa-trash-can"></i>
                            <div>{{ __('Delete') }}</div>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'expense-created')
        <x-session-status>{{ __('Expense created.') }}</x-session-status>
    @elseif (session('status') === 'expense-updated')
        <x-session-status>{{ __('Expense updated.') }}</x-session-status>
    @elseif (session('status') === 'expense-images-uploaded')
        <x-session-status>{{ __('Images uploaded.') }}</x-session-status>
    @elseif (session('status') === 'expense-image-deleted')
        <x-session-status>{{ __('Image deleted.') }}</x-session-status>
    @elseif (session('status') === 'max-images-reached')
        <x-session-status innerClass="text-warning">{{ __('You can only upload up to ') . $max_images_allowed . __(' images!') }}</x-session-status>
    @endif

    <h1>{{ __('$') . $expense->amount }}</h1>
    <div class="expense-info-date-group-category">
        <div class="text-shy text-thin-caps expense-info-date">{{ $expense->formatted_date }}</div>
        <a class="metric-group metric-group-hover" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
        <a class="metric-group">{{ __('Category') }}</a>
    </div>

    <div class="expense-info-container margin-top-lg">
        <div>
            <div class="expense-info-breakdown">
                <div class="expense-info-breakdown-left">
                    <div class="profile-img-sm-container">
                        <img class="profile-img-sm" src="{{ $expense->payer_user->profile_image_url }}" alt="{{ __('Profile image for ') . $expense->payer_user->username }}"/>
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
                    <!-- TODO: edit button -->
                @endif
            </div>
            

            <div class="margin-top-sm">
                @if ($expense->note)
                    <div class="text-small">
                        <p class="p-no-margin">{!! nl2br(e($expense->note)) !!}</p>
                    </div>
                @else
                    <button id="expense-add-note-button" class="expense-empty-note" onclick="showExpenseNoteForm()">
                        {{ __('Click to add') }}
                    </button>
                @endif

                <form id="expense-update-note-form" method="post" action="" class="hidden">
                    @csrf
                    @method('put')

                    <x-input-label for="expense-note" class="screen-reader-only" :value="__('Note')" />
                    <x-text-area class="p-no-margin" id="expense-note" name="expense-note" maxlength="65535" :value="$expense->note ?? ''" />

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
                @foreach ($expense->images as $image)
                    <div class="expense-img-preview-container expense-img-trigger">
                        <img class="expense-img-preview" src="{{ $image->expense_image_url }}" alt="{{ __('Expense image') }}">
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

    <x-modal name="upload-expense-images" :show="false" focusable>
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


<style>
    .expense-note-media-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
        width: 100%;
    }

    @media screen and (max-width: 768px) {
        .expense-note-media-container {
            grid-template-columns: 1fr;
        }
    }

    .expense-empty-note, .expense-add-image-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 75px;
        color: var(--text-shy);
        border: 2px solid var(--border-grey);
        border-radius: var(--border-radius);
        transition: background-color 0.3s ease-in-out, border 0.3s ease-in-out, color 0.3s ease-in-out;
    }

    .expense-empty-note:hover, .expense-add-image-btn:hover {
        background-color: var(--blue-background);
        color: var(--blue-text);
        border: 2px solid var(--blue-text);
    }

    .expense-empty-note:focus-visible, .expense-add-image-btn:focus-visible {
        outline: 3px solid var(--blue-hover); /* TODO: Change this to --primary-color-hover */
        outline-offset: 1px;
    }

    .expense-image-previews-container {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }

    .expense-img-trigger {
        transition: transform 0.3s ease;
    }

    .expense-img-trigger:hover {
        cursor: pointer;
        transform: scale(1.1);
    }

    .expense-add-image-btn {
        font-size: 2em;
        width: 75px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        resizeTextarea(document.getElementById('expense-note'));
    });

    function showExpenseNoteForm() {
        const addNoteBtn = document.getElementById('expense-add-note-button')
        if (addNoteBtn) {
            addNoteBtn.classList.add('hidden');
        }
        document.getElementById('expense-update-note-form').classList.remove('hidden');
        document.getElementById('expense-note').focus();
    }

    function hideExpenseNoteForm() {
        document.getElementById('expense-update-note-form').classList.add('hidden');

        const addNoteBtn = document.getElementById('expense-add-note-button')
        if (addNoteBtn) {
            addNoteBtn.classList.remove('hidden');
        }
    }
</script>
