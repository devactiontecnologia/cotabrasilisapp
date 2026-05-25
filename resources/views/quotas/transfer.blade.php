@extends('layouts.app')

@section('title', 'Transferir Titularidade')

@section('content')
<div class="row justify-content-center py-5">
  <div class="col-lg-8">
    <div class="card border-0 shadow-lg">
      <div class="card-body p-5">
        <h4 class="fw-bold mb-3">Transferir Titularidade da Cota</h4>
        
        <!-- Quota Info -->
        <div class="alert alert-info">
          <h6 class="fw-bold">Cota a ser transferida:</h6>
          <p class="mb-1"><strong>Hotel:</strong> {{ $quota->hotel_name }}</p>
          <p class="mb-1"><strong>Localização:</strong> {{ $quota->location }}</p>
          <p class="mb-1"><strong>Período:</strong> {{ \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') }}</p>
          <p class="mb-0"><strong>Hóspedes:</strong> {{ $quota->number_of_guests }}</p>
        </div>

        <!-- Transfer Fee Info -->
        @php
          $hotel = \App\Models\Hotel::where('name', $quota->hotel_name)->first();
          $category = $hotel ? $hotel->category : 'B';
          $fees = [
            'B' => 1000.00,    // Bom
            'MB' => 1500.00,   // Muito Bom
            'OT' => 2000.00,   // Ótimo
            'IN' => 3000.00,   // Incrível
            'UN' => 5000.00,   // Único
          ];
          $transferFee = $fees[$category] ?? 1000.00;
        @endphp
        
        <div class="alert alert-warning">
          <h6 class="fw-bold">Taxa de Transferência</h6>
          <p class="mb-1">Categoria do hotel: <strong>{{ $category }}</strong></p>
          <p class="mb-0">Valor: <strong>R$ {{ number_format($transferFee, 2, ',', '.') }}</strong></p>
        </div>

        <form method="POST" action="{{ route('quotas.transfer.submit', $quota) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">E-mail do novo titular</label>
            <input type="email" class="form-control @error('new_owner_email') is-invalid @enderror" name="new_owner_email" value="{{ old('new_owner_email') }}" placeholder="novo@exemplo.com" required>
            @error('new_owner_email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">O usuário deve estar cadastrado na plataforma.</div>
          </div>
          
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="confirm_transfer" required>
              <label class="form-check-label" for="confirm_transfer">
                Confirmo que desejo transferir a titularidade desta cota e estou ciente da taxa de <strong>R$ {{ number_format($transferFee, 2, ',', '.') }}</strong>
              </label>
            </div>
          </div>
          
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('quotas.show', $quota) }}" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-primary" type="submit">Transferir Titularidade</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

