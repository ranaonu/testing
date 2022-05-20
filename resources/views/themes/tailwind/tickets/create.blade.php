@extends('theme::layouts.app')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet" />
<main class="Shipping-layout">
    <div class="container max-w-7xl">
        <form id="ticket_form" role="form" method="POST" action="{{ route('tickets.store') }}">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-card">
                        <div class="form-head">
                            <h3>Add ticket</h3>
                        </div>

                        <div class="form-body">
                            <div class="row">
                        
                                <div class="col-lg-6">

                                    <div class="form-group">
                                        <label for="author_name" class="block text-sm font-medium leading-5 text-gray-700">Name <span class="required">*</span></label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="author_name" type="text" class="form-control @error('author_name') is-invalid @enderror" name="author_name" required autocomplete="name" autofocus value="{{auth()->user()->name}}">

                                            @error('author_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="author_email" class="block text-sm font-medium leading-5 text-gray-700">Email <span class="required">*</span></label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="author_email" type="email" class="form-control @error('author_email') is-invalid @enderror" name="author_email" required autocomplete="email" value="{{auth()->user()->email}}">

                                            @error('author_email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="issue" class="block text-sm font-medium leading-5 text-gray-700">Issue <span class="required">*</span></label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <select id="issue" type="text" name="issue" required class="w-full form-control" >
                                                <option value="">Select Issue</option>
                                                <option value="Technical Problem">Technical Problem</option>
                                                <option value="Billing Issue">Billing Issue</option>
                                                <option value="Shipment Issue">Shipment Issue</option>
                                            </select>

                                            @error('issue')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="title" class="block text-sm font-medium leading-5 text-gray-700">Title <span class="required">*</span></label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autocomplete="title">

                                            @error('title')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="content" class="block text-sm font-medium leading-5 text-gray-700">Details <span class="required">*</span></label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3" required style="height: 150px;">{{ old('content') }}</textarea>
                                            @error('content')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="attachments" class="block text-sm font-medium leading-5 text-gray-700">Attachments</label>

                                        <div class="mt-1 rounded-md shadow-sm">
                                            <div class="needsclick dropzone @error('attachments') is-invalid @enderror" id="attachments-dropzone">
                            
                                            </div>
                                        </div>
                                        @error('attachments')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <div class="g-recaptcha" data-sitekey="{{env("recaptcha_site_key")}}"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-wrap text-center">
                        
                                <button type="submit" class="cstm-btn disable_delivery_info">
                                    Submit
                                </button>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@section('javascript')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
<script>
    var uploadedAttachmentsMap = {}
Dropzone.options.attachmentsDropzone = {
    url: '{{ route('tickets.storeMedia') }}',
    maxFilesize: 2, // MB
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2
    },
    success: function (file, response) {
      $('form').append('<input type="hidden" name="attachments[]" value="' + response.name + '">')
      uploadedAttachmentsMap[file.name] = response.name
    },
    removedfile: function (file) {
      file.previewElement.remove()
      var name = ''
      if (typeof file.file_name !== 'undefined') {
        name = file.file_name
      } else {
        name = uploadedAttachmentsMap[file.name]
      }
      $('form').find('input[name="attachments[]"][value="' + name + '"]').remove()
    },
    init: function () {
@if(isset($ticket) && $ticket->attachments)
          var files =
            {!! json_encode($ticket->attachments) !!}
              for (var i in files) {
              var file = files[i]
              this.options.addedfile.call(this, file)
              file.previewElement.classList.add('dz-complete')
              $('form').append('<input type="hidden" name="attachments[]" value="' + file.file_name + '">')
            }
@endif
    },
     error: function (file, response) {
         if ($.type(response) === 'string') {
             var message = response //dropzone sends it's own error messages in string
         } else {
             var message = response.errors.file
         }
         file.previewElement.classList.add('dz-error')
         _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
         _results = []
         for (_i = 0, _len = _ref.length; _i < _len; _i++) {
             node = _ref[_i]
             _results.push(node.textContent = message)
         }

         return _results
     }
}

$(document).ready(function(){
    $('#ticket_form').submit(function(){
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
@stop