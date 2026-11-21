@props(['rating' => 0, 'count' => null, 'size' => 15])
@php
    $value = round((float) $rating, 1);
    $filled = (int) round($value);
@endphp
<span {{ $attributes->class(['star-rating']) }} title="{{ number_format($value, 1, ',', '.') }}/5 sao" aria-label="Đánh giá {{ number_format($value, 1, ',', '.') }} trên 5 sao">
    <span class="star-rating-icons" aria-hidden="true">
        @for($i = 1; $i <= 5; $i++)
            <x-ui-icon name="star" :size="$size" :stroke="1.6" :class="$i <= $filled ? 'is-filled' : ''" />
        @endfor
    </span>
    @if($count !== null)
        <span class="star-rating-text">{{ number_format($value, 1, ',', '.') }} ({{ $count }})</span>
    @endif
</span>
