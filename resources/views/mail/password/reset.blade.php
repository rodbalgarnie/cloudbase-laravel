@component('mail::layout')
{{-- Header --}}
@slot ('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ $details['logo'] }}" style="height:80px" alt="Logo">    
@endcomponent
@endslot

{{ $details['salutation'] }}<br/><br/>
{{ $details['text1'] }}<br/><br/>

@component('mail::button', ['url' => $details['url']])
Reset password
@endcomponent

{{ $details['text2'] }}<br/><br/>
{{ $details['url'] }}


{{-- Subcopy --}}
@slot('subcopy')
@component('mail::subcopy')

Regards,
{{ $details['companyuser'] }}<br/>
{{ $details['company'] }}<br/>
{{ $details['companyemail'] }}<br/>
{{ $details['companytel'] }}
@endcomponent
@endslot

{{-- Footer --}}
@slot ('footer')
@component('mail::footer')
{{ $details['address'] }}
@endcomponent
@endslot
@endcomponent
