@props(['product', 'saved' => false, 'label' => false])
@auth
    @unless(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('wishlist.toggle', $product) }}" {{ $attributes->class(['wishlist-form m-0']) }} data-ajax-wishlist>
            @csrf
            <button type="submit" @class(['wishlist-toggle', 'is-saved' => $saved, 'with-label' => $label])
                    aria-pressed="{{ $saved ? 'true' : 'false' }}"
                    aria-label="{{ $saved ? 'Bỏ' : 'Lưu' }} {{ $product->name }} {{ $saved ? 'khỏi' : 'vào' }} yêu thích"
                    title="{{ $saved ? 'Bỏ khỏi yêu thích' : 'Lưu vào yêu thích' }}">
                <x-ui-icon name="heart" :size="19" :stroke="1.9"/>
                @if($label)<span data-wishlist-label>{{ $saved ? 'Đã yêu thích' : 'Yêu thích' }}</span>@endif
            </button>
        </form>
    @endunless
@endauth
