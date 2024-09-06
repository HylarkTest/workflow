@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @isset($header)
            {{ $header }}
        @else
            @component('mail::header')
                {{ config('app.name') }}
            @endcomponent
        @endif
    @endslot

    @slot('title')
        @component('mail::'.($title->attributes['component'] ?? 'title'))
            {{ $title }}
        @endcomponent
    @endslot

    @isset($subtitle)
    @slot('subtitle')
        {{ $subtitle }}
    @endslot
    @endif

    @slot('greeting')
        @component('mail::greeting')
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    @slot('closing')
        @component('mail::closing')
            {{ $signOff ?? '' }}
        @endcomponent
    @endslot

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
        @endcomponent
    @endslot
@endcomponent
