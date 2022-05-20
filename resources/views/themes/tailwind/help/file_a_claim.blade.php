@extends('theme::layouts.app')

@section('content')

<main class="Shipping-layout">
    <div class="container max-w-7xl">
        <form id="claim_form" role="form" method="GET" action="{{ route('wave.FileClaimSave') }}">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-card">
                    
                    <div class="form-head">
                        <h3>File Claim</h3>
                    </div>
                    <div class="form-body">
                        <div class="row">
                        
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="auth_code" class="block text-sm font-medium leading-5 text-gray-700">
                                        Authorization Code <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <input id="auth_code" type="text" name="auth_code" required class="w-full form-control" value="" >
                                    </div>
                                    @if ($errors->has('auth_code'))
                                        <div class="mt-1 text-red-500">
                                            {{ $errors->first('auth_code') }}
                                        </div>
                                    @endif
                                    <div class="mt-1">
                                        <b>Note:</b> You need to contact the claim department at claims@zionshipping.com to provide you your authorization code.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="g-recaptcha" data-sitekey="{{env("recaptcha_site_key")}}"></div>
                                </div>
                            </div>
                            
                        </div> 
                         
                        
                        <div class="row">
                        
                            
                        </div>
                        <div class="btn-wrap text-center">
                            <button type="submit" class="cstm-btn disable_delivery_info">Submit</button>
                        </div>
                        
                    </div>
                </div>
            </div>
            
        </div>
        </form>
        <div id="loader_image">
            <img id="loading-image" src="{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}" style="display:none; height: 500px!important;     margin-left: auto; margin-right: auto; width: 50%;"/>
        </div>
        <div class="row delivery_information">
        </div>
    </div>
</main>
@endsection
@section('javascript')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    $(document).ready(function(){
        $('#claim_form').submit(function(){
            var response = grecaptcha.getResponse(0);
            if(response.length == 0)
            {
                //reCaptcha not verified
                alert("Please verify you are human!");
                return false;
            }
            
        });
    })
</script>
@endsection