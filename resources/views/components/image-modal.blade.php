@props(['idPrefix' => null])

<x-modal name="view-image-gallery" :show="false" focusable>
    <div class="view-image-container">
        {{ $main_image }}

        {{ $gallery_images }}
    </div>
</x-modal>

<script>
    let imageList = [];
    let currentIndex = 0;
    let isZoomed = false;

    const modalMainImgContainer = document.getElementById('{{ $idPrefix }}-img-lg-container');
    const modalMainImg = document.getElementById('{{ $idPrefix }}-image-lg');
    const modalImageLeft = document.getElementById('{{ $idPrefix }}-image-modal-left');
    const modalImageRight = document.getElementById('{{ $idPrefix }}-image-modal-right');

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize the image list from the gallery
        const previews = document.querySelectorAll('.modal-img-preview');
        previews.forEach(img => imageList.push(img.src));
    });

    function setModalImage(imagePreview, index) {
        currentIndex = index;
        modalMainImg.src = imagePreview.src;
        updateMainImageArrows();
    }

    function prevImage() {
        if (currentIndex > 0) {
            currentIndex--;
            modalMainImg.src = imageList[currentIndex];
            updateMainImageArrows();
        }
    }

    function nextImage() {
        if (currentIndex < imageList.length - 1) {
            currentIndex++;
            modalMainImg.src = imageList[currentIndex];
            updateMainImageArrows();
        }
    }

    function updateMainImageArrows() {
        if (currentIndex === 0) { // Currently viewing first image (hide left button)
            modalImageLeft.classList.add('hidden');
        } else if (!isZoomed) {
            modalImageLeft.classList.remove('hidden');
        }

        if (currentIndex === imageList.length - 1) { // Currently viewing last image (hide right button)
            modalImageRight.classList.add('hidden');
        } else if (!isZoomed) {
            modalImageRight.classList.remove('hidden');
        }
    }

    modalMainImg.addEventListener('click', (e) => {
        if (!isZoomed) {
            const rect = modalMainImgContainer.getBoundingClientRect();
            const offsetX = e.clientX - rect.left;
            const offsetY = e.clientY - rect.top;

            // Set image origin based on where click/tap occurred
            const moveX = (offsetX / rect.width) * 100;
            const moveY = (offsetY / rect.height) * 100;
            modalMainImg.style.transformOrigin = `${moveX}% ${moveY}%`;

            // Zoom in
            modalMainImg.style.transform = 'scale(2)';
            modalMainImg.classList.add('zoomed');
            isZoomed = true;

            // Hide left/right arrow buttons
            modalImageLeft.classList.add('hidden');
            modalImageRight.classList.add('hidden');
        } else {
            // Zoom out
            modalMainImg.style.transform = 'scale(1)';
            modalMainImg.classList.remove('zoomed');
            isZoomed = false;

            setTimeout(() => {
                // Reset image origin (after image has zoomed out to not interfere with zoom animation)
                modalMainImg.style.transformOrigin = 'center center';
            }, 300); // Duration of zoom animation

            // Show left/right arrow buttons
            modalImageLeft.classList.remove('hidden');
            modalImageRight.classList.remove('hidden');
        }
    });

    modalMainImg.addEventListener('mousemove', (e) => {
        if (isZoomed) {
            handlePanning(e.clientX, e.clientY, false);
        }
    });

    modalMainImg.addEventListener('touchmove', (e) => {
        if (isZoomed) {
            e.preventDefault();
            const touch = e.touches[0];
            handlePanning(touch.clientX, touch.clientY, false);
        }
    });

    function handlePanning(clientX, clientY, isMouse) {
        const rect = modalMainImgContainer.getBoundingClientRect();
        const offsetX = clientX - rect.left;
        const offsetY = clientY - rect.top;

        const moveX = Math.max(Math.min((offsetX / rect.width) * 100, 100), 0);
        const moveY = Math.max(Math.min((offsetY / rect.height) * 100, 100), 0);
        modalMainImg.style.transformOrigin = `${moveX}% ${moveY}%`;
    }
</script>
