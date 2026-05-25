<!-- Filtros de Destinatários -->
<div class="card border-0 bg-light mb-3">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>Filtros de Destinatários</h6>
        <p class="text-muted small mb-3">Selecione os filtros para escolher quem receberá esta comunicação. Deixe em branco para enviar a todos.</p>
        
        <div class="row g-3">
            <div class="col-md-4">
                <label for="filter_city" class="form-label small fw-semibold">Cidade</label>
                <input type="text" class="form-control form-control-sm" id="filter_city" name="filters[city]" value="{{ old('filters.city') }}" placeholder="Ex: Florianópolis">
            </div>
            
            <div class="col-md-4">
                <label for="filter_state" class="form-label small fw-semibold">Estado</label>
                <select class="form-select form-select-sm" id="filter_state" name="filters[state]">
                    <option value="">Todos</option>
                    <option value="AC" {{ old('filters.state') == 'AC' ? 'selected' : '' }}>Acre</option>
                    <option value="AL" {{ old('filters.state') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                    <option value="AP" {{ old('filters.state') == 'AP' ? 'selected' : '' }}>Amapá</option>
                    <option value="AM" {{ old('filters.state') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                    <option value="BA" {{ old('filters.state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                    <option value="CE" {{ old('filters.state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                    <option value="DF" {{ old('filters.state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                    <option value="ES" {{ old('filters.state') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                    <option value="GO" {{ old('filters.state') == 'GO' ? 'selected' : '' }}>Goiás</option>
                    <option value="MA" {{ old('filters.state') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                    <option value="MT" {{ old('filters.state') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                    <option value="MS" {{ old('filters.state') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                    <option value="MG" {{ old('filters.state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                    <option value="PA" {{ old('filters.state') == 'PA' ? 'selected' : '' }}>Pará</option>
                    <option value="PB" {{ old('filters.state') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                    <option value="PR" {{ old('filters.state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                    <option value="PE" {{ old('filters.state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                    <option value="PI" {{ old('filters.state') == 'PI' ? 'selected' : '' }}>Piauí</option>
                    <option value="RJ" {{ old('filters.state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                    <option value="RN" {{ old('filters.state') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                    <option value="RS" {{ old('filters.state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                    <option value="RO" {{ old('filters.state') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                    <option value="RR" {{ old('filters.state') == 'RR' ? 'selected' : '' }}>Roraima</option>
                    <option value="SC" {{ old('filters.state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                    <option value="SP" {{ old('filters.state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                    <option value="SE" {{ old('filters.state') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                    <option value="TO" {{ old('filters.state') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="filter_gender" class="form-label small fw-semibold">Gênero</label>
                <select class="form-select form-select-sm" id="filter_gender" name="filters[gender]">
                    <option value="">Todos</option>
                    <option value="M" {{ old('filters.gender') == 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('filters.gender') == 'F' ? 'selected' : '' }}>Feminino</option>
                    <option value="O" {{ old('filters.gender') == 'O' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="filter_quota_type" class="form-label small fw-semibold">Tipo de Cota</label>
                <select class="form-select form-select-sm" id="filter_quota_type" name="filters[quota_type]">
                    <option value="">Todos</option>
                    <option value="fixa" {{ old('filters.quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                    <option value="flexivel" {{ old('filters.quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                    <option value="fixa_flexivel" {{ old('filters.quota_type') == 'fixa_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="filter_profile_type" class="form-label small fw-semibold">Tipo de Perfil</label>
                <select class="form-select form-select-sm" id="filter_profile_type" name="filters[profile_type]">
                    <option value="">Todos</option>
                    <option value="curioso" {{ old('filters.profile_type') == 'curioso' ? 'selected' : '' }}>Curioso</option>
                    <option value="inteligente" {{ old('filters.profile_type') == 'inteligente' ? 'selected' : '' }}>Inteligente</option>
                    <option value="sabio" {{ old('filters.profile_type') == 'sabio' ? 'selected' : '' }}>Sábio</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Opções de Envio -->
<div class="card border-0 bg-light mb-3">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-paper-plane me-2"></i>Opções de Envio</h6>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1" {{ old('send_email', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="send_email">
                        <i class="fas fa-envelope me-2 text-primary"></i>Enviar por E-mail
                    </label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="send_whatsapp" name="send_whatsapp" value="1" {{ old('send_whatsapp') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="send_whatsapp">
                        <i class="fab fa-whatsapp me-2 text-success"></i>Enviar por WhatsApp
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>









