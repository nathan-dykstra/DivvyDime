@props(['number' => 4])

@for ($i = 0; $i < $number; $i++)
    <div class="expense">
        <div>
            <div class="loading-line-1"></div>
            <div class="loading-line-2"></div>
            <div class="loading-line-3"></div>
        </div>
    </div>
@endfor
