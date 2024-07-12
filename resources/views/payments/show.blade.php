<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Payment') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Payment') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('payments.edit', $payment)">{{ __('Edit') }}</x-primary-button>
        <x-primary-button icon="fa-solid fa-scale-balanced icon" :href="route('payments.create')">{{ __('New Payment') }}</x-primary-button>
    </x-slot>

    <x-slot name="overflow_options">
        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-payment-images')">
            <i class="fa-solid fa-images"></i>
            <div>{{ __('Add Images') }}</div>
        </div>
        <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-payment')">
            <i class="fa-solid fa-trash-can"></i>
            <div>{{ __('Delete') }}</div>
        </div>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <a class="dropdown-item" href="{{ route('payments.edit', $payment) }}">
            <i class="fa-solid fa-pen-to-square"></i>
            <div>{{ __('Edit') }}</div>
        </a>
        <a class="dropdown-item" href="{{ route('payments.create') }}">
            <i class="fa-solid fa-scale-balanced"></i>
            <div>{{ __('New Payment') }}</div>
        </a>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'payment-created')
        <x-session-status>{{ __('Payment created.') }}</x-session-status>
    @elseif (session('status') === 'payment-updated')
        <x-session-status>{{ __('Payment updated.') }}</x-session-status>
    @elseif (session('status') === 'payment-confirmed')
        <x-session-status>{{ __('Payment confirmed.') }}</x-session-status>
    @elseif (session('status') === 'payment-rejected')
        <x-session-status>{{ __('Payment rejected.') }}</x-session-status>
    @elseif (session('status') === 'expense-images-uploaded')
        <x-session-status>{{ __('Images uploaded.') }}</x-session-status>
    @elseif (session('status') === 'expense-image-deleted')
        <x-session-status>{{ __('Image deleted.') }}</x-session-status>
    @elseif (session('status') === 'max-images-reached')
        <x-session-status innerClass="text-warning">{{ __('Images uploaded. You can only add up to ') . $max_images_allowed . __(' images!') }}</x-session-status>
    @elseif (session('status') === 'payment-note-updated')
        <x-session-status>{{ __('Note updated.') }}</x-session-status>
    @endif

    <!-- Content -->

    <div>
        <h1>{{ __('$') . $payment->amount }}</h1>

        <div class="expense-info-date-group-category">
            <div class="text-shy text-thin-caps payment-info-date">{{ $payment->formatted_date }}</div>
            @if ($payment->is_settle_all_balances)
                <div class="info-chip info-chip-green">{{ __('Settle All Balances') }}</div>
            @else
                <a class="info-chip info-chip-link info-chip-grey" href="{{ route('groups.show', $payment->group->id) }}">{{ $payment->group->name }}</a>
            @endif
            <!--<a class="">{{ __('Category') }}</a>--> <!-- TODO: display payment category -->
        </div>

        @if (auth()->user()->id === $payment->payer && $payment->is_rejected)
            <!-- Payment rejected (payer): Show options -->
            <div class="info-container red-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                </div>
                <div class="space-top-xs">
                    <span class="bold-username">{{ $payment->payee->username }}</span>{{ __(' rejected your payment. Make sure you sent the money and the payment information you added is correct.') }}

                    <div class="btn-container-start">
                        <x-secondary-button href="{{ route('payments.edit', $payment) }}">{{ __('Edit Payment') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @elseif (auth()->user()->id === $payment->payer && !$payment->is_confirmed)
            <!-- Payment pending (payer) -->
            <div class="text-sm text-yellow margin-top-sm"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This payment is pending') }}</div>
        @elseif (auth()->user()->id === $payment->payee->id && !$payment->is_confirmed && !$payment->is_rejected)
            <!-- Payment pending (payee): Show options -->
            <div class="info-container yellow-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-yellow"></i>
                </div>
                <div class="space-top-xs">
                    {{ __('Did you receive this payment from ') }}<span class="bold-username">{{ $payment->payer_user->username }}</span>{{ __('? Your balances will not be adjusted until you confirm the payment.') }}

                    <div class="btn-container-start">
                        <x-secondary-button onclick="submitConfirmPayment()">{{ __('Confirm') }}</x-secondary-button>
                        <x-secondary-button onclick="submitRejectPayment()">{{ __('Reject') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @elseif (auth()->user()->id === $payment->payee->id && $payment->is_rejected)
            <!-- Payment rejected (payee): Show options -->
            <div class="info-container red-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                </div>
                <div class="space-top-xs">
                    {{ __('You rejected this payment from ') }}<span class="bold-username">{{ $payment->payer_user->username }}</span>{{ __('. If you change your mind, you can still confirm the payment.') }}

                    <div class="btn-container-start">
                        <x-secondary-button onclick="submitConfirmPayment()">{{ __('Confirm Payment') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @else
            <!-- Payment confirmed (both) -->
            <div class="text-sm text-success margin-top-sm"><i class="fa-solid fa-check fa-sm icon"></i>{{ __('This payment was confirmed') }}</div>
        @endif
    </div>

    <div class="margin-top-lg space-top-sm">
        <div class="text-primary payment-info-user-amounts">
            <div class="profile-img-sm-container">
                <img class="profile-img" src="{{ $payment->payer_user->profile_image_url }}" alt="{{ __('Profile image for ') . $payment->payer_user->username }}"/>
            </div>
            <div class="expense-info-breakdown-payer-container">
                <div class="expense-info-breakdown-payer">
                        @if ($payment->payer === auth()->user()->id)
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $payment->payer_user->username }}</span>
                        @endif
                    </span>
                    {{ __(' paid ') . __('$') . $payment->amount }}
                </div>
            </div>
        </div>
    
        <div class="text-primary payment-info-user-amounts">
            <div class="profile-img-sm-container">
                <img class="profile-img" src="{{ $payment->payee->profile_image_url }}" alt="{{ __('Profile image for ') . $payment->payee->username }}"/>
            </div>
            <div class="expense-info-breakdown-payer-container">
                <div class="expense-info-breakdown-payer">
                        @if ($payment->payee->id === auth()->user()->id)
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $payment->payee->username }}</span>
                        @endif
                    </span>
                    {{ __(' received ') . __('$') . $payment->amount }}
                </div>
            </div>
        </div>
    </div>

    <div class="expense-note-media-container margin-top-lg">
        <div class="container">
            <div class="btn-container-apart">
                <h4>{{ __('Note') }}</h4>

                @if ($payment->note)
                    <x-icon-button id="payment-update-note-btn" class="text-small" icon="fa-solid fa-pen-to-square icon" onclick="showPaymentNoteForm()">{{ __('Edit') }}</x-icon-button>
                @endif
            </div>
            
            <div class="margin-top-sm">
                @if ($payment->note)
                    <div class="text-small" id="payment-note">
                        <p class="p-no-margin">{!! nl2br(e($payment->note)) !!}</p>
                    </div>
                @else
                    <button id="payment-add-note-btn" class="expense-empty-note" onclick="showPaymentNoteForm()">
                        {{ __('Click to add') }}
                    </button>
                @endif

                <form id="payment-update-note-form" method="post" action="{{ route('payments.update-note', $payment) }}" class="hidden">
                    @csrf
                    @method('patch')

                    <x-input-label for="payment-note-textarea" class="screen-reader-only" :value="__('Note')" />
                    <x-text-area class="p-no-margin" id="payment-note-textarea" name="payment-note" maxlength="65535" :value="$payment->note ?? ''" />
                    <x-input-error :messages="$errors->get('payment-note')" />

                    <div class="btn-container-start">
                        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                        <x-primary-button onclick="hidePaymentNoteForm()">{{ __('Cancel') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
            <h4>{{ __('Images') }}</h4>

            <div class="expense-image-previews-container margin-top-sm">
                @foreach ($payment_images as $index => $image)
                    <div class="expense-img-preview-container expense-img-trigger">
                        <img class="expense-img-preview" src="{{ $image->expense_image_url }}" alt="{{ __('Payment image') }}" x-data="" x-on:click.prevent="$dispatch('open-modal', 'view-image-gallery')" onclick="setModalImage(this, {{ $index }})" tabindex="0">
                        <x-blur-background-button class="expense-remove-img-btn" icon="fa-solid fa-sm fa-xmark" data-expense-img-id="{{ $image->id }}" onclick="removePaymentImage(event, this)" />

                        <form id="payment-remove-img-form" action="" method="post">
                            @csrf
                            @method('delete')
                        </form>
                    </div>
                @endforeach

                @unless ($payment->images()->count() >= $max_images_allowed)
                    <button class="expense-add-image-btn" icon="fa-solid fa" x-data="" x-on:click.prevent="$dispatch('open-modal', 'upload-payment-images')">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                @endunless
            </div>
        </div>
    </div>

    <div class="horizontal-center margin-top-lg">
        <div class="text-shy">
            <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->created_date . __(' at ') . $payment->created_time }}">
                {{ __('Added ') }}
                <span class="width-content">{{ $payment->formatted_created_date }}</span>
                {{ __(' by ') }}<span class="bold-username">{{ $payment->creator_user->username }}</span>
            </x-tooltip>
        </div>

        @if ($payment->created_at->toDateTimeString() !== $payment->updated_at->toDateTimeString())
            <div class="text-shy">
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->updated_date . __(' at ') . $payment->updated_time }}">
                    {{ __('Updated ') }}
                    <span class="width-content">{{ $payment->formatted_updated_date }}</span>
                    {{ __(' by ') }}<span class="bold-username">{{ $payment->updator_user->username }}</span>
                </x-tooltip>
            </div>
        @endif
    </div>

    <form id="confirm-payment-form" action="{{ route('payments.confirm', $payment) }}" method="POST">
        @csrf

        <input type="hidden" name="payment_id" value="{{ $payment->id }}"/>
    </form>

    <form id="reject-payment-form" action="{{ route('payments.reject', $payment) }}" method="POST">
        @csrf

        <input type="hidden" name="payment_id" value="{{ $payment->id }}"/>
    </form>

    <!-- Modals -->

    <x-image-modal idPrefix="payment">
        <x-slot name="main_image">
            <div class="modal-img-lg-container">
                <div id="payment-img-lg-container" class="expense-img-lg-container">
                    <img id="payment-image-lg" class="expense-img-lg" src="" alt="Expense image (large view)">

                    <x-blur-background-button id="payment-image-modal-left" class="image-modal-left" icon="fa-solid fa-chevron-left" onclick="prevImage()" />
                    <x-blur-background-button id="payment-image-modal-right" class="image-modal-right" icon="fa-solid fa-chevron-right" onclick="nextImage()" />
                </div>
            </div>
        </x-slot>

        <x-slot name="gallery_images">
            <div class="expense-img-gallery">
                @foreach ($payment_images as $index => $image)
                    <div class="expense-img-preview-container expense-img-trigger">
                        <img class="expense-img-preview modal-img-preview" src="{{ $image->expense_image_url }}" alt="{{ __('Payment image') }}" onclick="setModalImage(this, {{ $index }})" tabindex="0">
                    </div>
                @endforeach
            </div>
        </x-slot>
    </x-image-modal>

    <x-modal name="upload-payment-images" id="upload-expense-images" :show="false" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Upload images') }}</h3>
                <p class="text-shy">
                    {{ __('Supports JPEG, JPG, and PNG file types. Up to 5 images can be added. Maximum file size is 5MB.') }}
                </p>
            </div>

            <x-dropzone formAction="{{ route('images.upload-expense', $payment) }}" formId="expense-img-form" previewsId="expense-img-previews" />

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')" onclick="clearExpenseUploader()">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="submitExpenseImages()">{{ __('Upload') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    @include('payments.partials.payment-delete-modal')
</x-app-layout>

<script>
    function submitConfirmPayment() {
        document.getElementById('confirm-payment-form').submit();
    }

    function submitRejectPayment() {
        document.getElementById('reject-payment-form').submit();
    }

    function showPaymentNoteForm() {
        const addNoteBtn = document.getElementById('payment-add-note-btn')
        if (addNoteBtn) {
            addNoteBtn.classList.add('hidden');
        }

        const updateNoteBtn = document.getElementById('payment-update-note-btn')
        if (updateNoteBtn) {
            updateNoteBtn.classList.add('hidden');
        }

        const expenseNote = document.getElementById('payment-note');
        if (expenseNote) {
            expenseNote.classList.add('hidden');
        }

        document.getElementById('payment-update-note-form').classList.remove('hidden');
        const textArea = document.getElementById('payment-note-textarea');
        resizeTextarea(textArea);
        textArea.focus();
    }

    function hidePaymentNoteForm() {
        document.getElementById('payment-update-note-form').classList.add('hidden');

        const expenseNote = document.getElementById('payment-note');
        if (expenseNote) {
            expenseNote.classList.remove('hidden');
        }

        const addNoteBtn = document.getElementById('payment-add-note-btn')
        if (addNoteBtn) {
            addNoteBtn.classList.remove('hidden');
        }

        const updateNoteBtn = document.getElementById('payment-update-note-btn')
        if (updateNoteBtn) {
            updateNoteBtn.classList.remove('hidden');
        }
    }

    function removePaymentImage(event, removeBtn) {
        event.preventDefault();
        imgId = removeBtn.dataset.expenseImgId
        removeImageForm = document.getElementById('payment-remove-img-form');
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
