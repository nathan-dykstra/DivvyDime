@props(['formAction' => null, 'formId' => null, 'previewsId' => null, 'icon' => 'fa-solid fa-2xl fa-images', 'class' => ''])

<div class="dropzone-container">
    <form id="{{ $formId }}" action="{{ $formAction }}" method="post" class="dropzone space-top-xs" enctype="multipart/form-data">
        @csrf

        <div id="dropzone-images-icon">
            <i class="{{ $icon }}"></i>
        </div>
    </form>
</div>

<div id="{{ $previewsId }}" class="image-upload-preview-container">
    <x-dropzone-preview-template />
</div>

<style>
    .dropzone-container {
        display: flex;
        justify-content: center;
    }

    .dropzone {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: var(--text-shy);
        background-color: transparent;
        border: 2px dashed var(--border-grey);
        border-radius: var(--border-radius);
        cursor: pointer;
        width: 400px;
        height: 200px;
        transition: border-color 0.2s ease-in-out, background-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }

    .dropzone:hover {
        background-color: var(--blue-background);
        border: 2px dashed var(--blue-text);
        color: var(--blue-text)
    }

    .dragover {
        background-color: var(--blue-background);
        border: 2px dashed var(--blue-text);
        color: var(--blue-text)
    }

    @media screen and (max-width: 768px) {
        .dropzone {
            width: 100%;
            height: 100px;
        }
    }

    .image-upload-preview-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    @media screen and (max-width: 768px) {
        .image-upload-preview-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .dz-button:focus {
        outline: none;
    }
</style>
