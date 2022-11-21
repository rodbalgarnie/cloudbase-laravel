@component('mail::layout')
{{-- Header --}}
@slot ('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ $details['logo'] }}" style="height:80px" alt="Logo">    
@endcomponent
@endslot

{!! $details['text1'] !!}<br/><br/>


{{-- Footer --}}
@slot ('footer')
@component('mail::footer')
{{ $details['footer'] }}
@endcomponent
@endslot
@endcomponent