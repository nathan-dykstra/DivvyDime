<div class="dropzone-preview-template" id="dropzone-preview-template">
    <div class="dz-details">
        <div class="vertical-center">
            <div class="dz-thumbnail-container">
                <img class="dz-thumbnail" data-dz-thumbnail />
                <div class="dz-filetype-icon hidden">
                    <i class="fa-solid fa-xl fa-file"></i> <!-- Default file icon -->
                </div>
            </div>
        </div>

        <div class="dz-info">
            <p class="text-primary p-no-margin" data-dz-name></p>
            <p class="text-shy" data-dz-size></p>
            <div class="dz-progress">
                <span class="dz-upload" data-dz-uploadprogress></span>
            </div>
            <p class="dz-file-error text-warning" data-dz-errormessage></p>
        </div>
    </div>

    <x-no-background-button class="dz-remove-btn" icon="fa-solid fa-xmark" data-dz-remove />
</div>

<style>
    .dropzone-preview-template {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 32px;
        width: 100%;
    }

    .dz-details {
        display: flex;
        gap: 16px;
        width: 100%;
    }

    .dz-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }

    .dz-thumbnail-container {
        width: 80px;
        height: 80px;
        border-radius: var(--border-radius);
        transition: transform 0.3s ease;
    }

    .dz-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: var(--border-radius);
    }

    .dz-filetype-icon {
        width: 80px;
        height: 80px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 3em;
        color: var(--icon-grey);
    }

    .dz-progress {
        width: 100%;
        background: transparent;
        border-radius: 4px;
        height: 8px;
        margin-top: 8px;
        overflow: hidden;
        position: relative;
    }

    .dz-upload {
        display: block;
        height: 100%;
        background: linear-gradient(to right, rgba(5,112,213,1) 0%, rgba(48,216,246,1) 100%);
        width: 0;
        transition: width 0.3s ease;
    }

    .dz-remove-btn {
        height: 32px;
        width: 32px;
    }

    @keyframes expandFadeIn {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.1);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes collapseFadeOut {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.1);
        }
        100% {
            opacity: 0;
            transform: scale(0.5);
        }
    }

    .dz-animating-expand {
        animation: expandFadeIn 0.5s forwards;
    }

    .dz-animating-collapse {
        animation: collapseFadeOut 0.5s forwards;
    }
</style>
