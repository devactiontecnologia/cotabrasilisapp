@extends('layouts.app')

@section('title', 'Completar dados da cota')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5">
                <h4 class="fw-bold mb-4">Completar dados da sua cota</h4>
                <form method="POST" action="{{ route('owner.onboarding.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Hotel</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="hotel_autocomplete" placeholder="Digite para buscar" autocomplete="off">
                            <input type="hidden" name="quota_details[hotel_id]" id="hotel_id" value="{{ $profile->quota_details['hotel_id'] ?? '' }}">
                            <div id="hotel_suggestions" class="list-group position-absolute w-100" style="z-index: 2000; display:none;"></div>
                        </div>
                        <small class="text-muted">Você pode selecionar um hotel oficial cadastrado.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Número de Quartos</label>
                            <input type="number" min="1" name="quota_details[number_of_rooms]" class="form-control" value="{{ $profile->quota_details['number_of_rooms'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tamanho (m²)</label>
                            <input type="text" name="quota_details[size]" class="form-control" value="{{ $profile->quota_details['size'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sazonalidade</label>
                            <input type="text" name="quota_details[seasonality]" class="form-control" value="{{ $profile->quota_details['seasonality'] ?? '' }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Observações</label>
                        <input type="text" name="quota_details[notes]" class="form-control" value="{{ $profile->quota_details['notes'] ?? '' }}">
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Fotos da Cota (até 5 imagens)</label>
                        <input type="file" name="quota_photos[]" id="quota_photos" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Formatos: JPG/PNG. Tamanho máximo por arquivo: 5MB.</small>
                    </div>

                    @php($photos = $profile->quota_details['photos'] ?? [])
                    @if(!empty($photos))
                    <div class="mt-3">
                        <label class="form-label">Suas fotos</label>
                        <div class="row g-3">
                            @foreach($photos as $p)
                            <div class="col-6 col-md-3">
                                <div class="card h-100 position-relative">
                                    <img src="{{ Storage::disk('public')->url($p) }}" class="card-img-top" alt="Foto da cota">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deletePhoto('{{ $p }}', this)"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                 

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-success">Salvar e continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const hotelInput = document.getElementById('hotel_autocomplete');
const hotelIdInput = document.getElementById('hotel_id');
const suggestionsBox = document.getElementById('hotel_suggestions');
let hotelSearchTimeout = null;
if (hotelInput) {
  hotelInput.addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(hotelSearchTimeout);
    if (query.length < 2) {
      suggestionsBox.style.display = 'none';
      suggestionsBox.innerHTML = '';
      return;
    }
    hotelSearchTimeout = setTimeout(async () => {
      try {
        const res = await fetch(`/api/hotels/search?query=${encodeURIComponent(query)}`);
        const json = await res.json();
        const items = json.data || [];
        if (items.length === 0) { suggestionsBox.style.display = 'none'; suggestionsBox.innerHTML = ''; return; }
        suggestionsBox.innerHTML = '';
        items.forEach(item => {
          const a = document.createElement('a');
          a.href = '#';
          a.className = 'list-group-item list-group-item-action';
          a.textContent = item.label;
          a.addEventListener('click', (e) => {
            e.preventDefault();
            hotelInput.value = item.label;
            hotelIdInput.value = item.id;
            suggestionsBox.style.display = 'none';
            // Load official info
            loadHotelInfo(item.id);
          });
          suggestionsBox.appendChild(a);
        });
        suggestionsBox.style.display = 'block';
      } catch (err) {
        suggestionsBox.style.display = 'none';
      }
    }, 250);
  });
}

async function loadHotelInfo(hotelId) {
  try {
    const res = await fetch(`/api/hotels/${hotelId}`);
    const json = await res.json();
    const d = json.data;
    const box = document.getElementById('hotel_official_info');
    box.innerHTML = '';
    if (!d) { box.textContent = 'Informações indisponíveis.'; return; }
    const desc = document.createElement('p');
    desc.textContent = d.description || 'Sem descrição disponível.';
    const website = document.createElement('a');
    if (d.website) { website.href = d.website; website.textContent = d.website; website.target = '_blank'; }
    box.appendChild(desc);
    if (d.website) { box.appendChild(website); }
  } catch (e) {
    document.getElementById('hotel_official_info').textContent = 'Erro ao carregar informações do hotel.';
  }
}
</script>
<script>
// Preview before upload
document.getElementById('quota_photos')?.addEventListener('change', function(e) {
  const containerId = 'quota_photos_preview_container';
  let container = document.getElementById(containerId);
  if (!container) {
    container = document.createElement('div');
    container.id = containerId;
    container.className = 'row g-3 mt-2';
    this.closest('div').appendChild(container);
  }
  container.innerHTML = '';
  Array.from(e.target.files).slice(0,5).forEach(file => {
    const reader = new FileReader();
    reader.onload = function(ev) {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-3';
      col.innerHTML = `<div class="card h-100"><img src="${ev.target.result}" class="card-img-top" alt="preview"></div>`;
      container.appendChild(col);
    };
    reader.readAsDataURL(file);
  });
});

async function deletePhoto(path, btn) {
  if (!confirm('Remover esta foto?')) return;
  try {
    const res = await fetch(`{{ route('owner.onboarding.photo.delete') }}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ path })
    });
    if (res.ok) {
      const col = btn.closest('.col-6');
      if (col) col.remove();
    } else {
      alert('Não foi possível remover a foto.');
    }
  } catch (e) {
    alert('Erro de rede ao remover a foto.');
  }
}
</script>
@endpush
@endsection

