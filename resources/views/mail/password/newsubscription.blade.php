@component('mail::layout')
{{-- Header --}}
@slot ('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ $details['logo'] }}" style="height:80px" alt="Logo">    
@endcomponent
@endslot

{{ $details['text1'] }}<br/><br/>

{{-- Subcopy --}}
@slot('subcopy')
@component('mail::subcopy')

Regards,
{{ $details['dealercontact'] }}<br/>
{{ $details['dealertitle'] }}<br/>
{{ $details[dealercontactemail'] }}<br/>
{{ $details['dealercontacttel'] }}
@endcomponent
@endslot

{{-- Footer --}}
@slot ('footer')
@component('mail::footer')
{{ $details['address'] }}
@endcomponent
@endslot
@endcomponent