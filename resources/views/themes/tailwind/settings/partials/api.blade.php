<div class="flex flex-col px-10 py-8">
	<form id="language_form" action="{{ route('wave.settings.api.post') }}" method="POST">
		<div>
			<label for="key_name" class="block text-sm font-medium leading-5 text-gray-700">Languages</label>
			<div class="mt-1 rounded-md shadow-sm">
				<div class="form-group" style="margin: 10px;">
					<select class="form-control form-select disable_delivery_info required" required="" name="language" id="language">
						<option value="en" {{ session()->get('locale') == 'en' ? 'selected' : '' }}> English </option>
						<option value="es" {{ session()->get('locale') == 'es' ? 'selected' : '' }}> Spanish </option>
						<option value="fr" {{ session()->get('locale') == 'fr' ? 'selected' : '' }}> French </option>
					</select>
				</div>
			</div>
		</div>
		{{ csrf_field() }}
	</form>
</div>