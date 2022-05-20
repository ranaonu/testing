@extends('theme::layouts.app')

@section('content')

<main class="Shipping-layout">
    <div class="container max-w-7xl">
        <form role="form" method="POST" action="{{ route('wave.FileClaimSave2') }}" enctype="multipart/form-data">
        @csrf
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
                                        Authorization Code
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <input id="auth_code" type="text" name="auth_code" required class="w-full form-control" value="{{$claim->auth_code}}" readonly >
                                    </div>
                                   
                                </div>
                            </div>
                            
                        </div> 
                        <div class="row">
                        
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="shipment_number" class="block text-sm font-medium leading-5 text-gray-700">
                                        Shipment Number <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <input id="shipment_number" type="text" name="shipment_number" required class="w-full form-control" value="{{$claim->shipment_number}}" >
                                    </div>
                                    @if ($errors->has('shipment_number'))
                                        <div class="mt-1 text-red-500">
                                            {{ $errors->first('shipment_number') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="claim_issue" class="block text-sm font-medium leading-5 text-gray-700">
                                        Claim Issue <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <select id="claim_issue" type="text" name="claim_issue" required class="w-full form-control" >
                                            <option value="">Select Issue</option>
                                            <option value="Shipment Lost">Shipment Lost</option>
                                            <option value="Shipment Damage">Shipment Damage</option>
                                            <option value="Shipment deliver late">Shipment deliver late</option>
                                            <option value="Wrong shipment deliver">Wrong shipment deliver</option>
                                            <option value="Missing item in shipment">Missing item in shipment</option>
                                        </select>
                                    </div>
                                   
                                </div>
                            </div>
                            
                        </div> 
                         
                        
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="description" class="block text-sm font-medium leading-5 text-gray-700">
                                        Details <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <textarea maxlength="150" id="description" type="text" name="description" required class="w-full form-control" style="height: 150px;" ></textarea>
                                    </div>
                                    <div class="mt-1">
                                        <span style="font-size:12px;color:#999;">Max words allowed - 150</span>
                                    </div>
                                </div>
                            </div>
                            
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
