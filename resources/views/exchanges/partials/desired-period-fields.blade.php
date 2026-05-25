@php
    $domIdPrefix = isset($domIdPrefix) ? (string) $domIdPrefix : '';
    $currentYear = (int) date('Y');
    $nextYear = $currentYear + 1;

    $oldDayStart = old('desired_period_day_start');
    $oldDayEnd = old('desired_period_day_end');
    $oldMonth = old('desired_period_month');
    $oldYear = old('desired_period_year');

    if ($oldDayStart === null && old('desired_period_start')) {
        try {
            $parsedStart = \Carbon\Carbon::parse(old('desired_period_start'));
            $oldDayStart = str_pad((string) $parsedStart->day, 2, '0', STR_PAD_LEFT);
            $oldMonth = $oldMonth ?? $parsedStart->month;
            $oldYear = $oldYear ?? $parsedStart->year;
        } catch (\Throwable $e) {
            // ignore
        }
    }

    if ($oldDayEnd === null && old('desired_period_end')) {
        try {
            $parsedEnd = \Carbon\Carbon::parse(old('desired_period_end'));
            $oldDayEnd = str_pad((string) $parsedEnd->day, 2, '0', STR_PAD_LEFT);
            $oldMonth = $oldMonth ?? $parsedEnd->month;
            $oldYear = $oldYear ?? $parsedEnd->year;
        } catch (\Throwable $e) {
            // ignore
        }
    }

    if (isset($exchangeOffer) && $exchangeOffer) {
        if ($oldMonth === null && $exchangeOffer->desired_period_month) {
            $oldMonth = $exchangeOffer->desired_period_month;
        }
        if ($oldYear === null && $exchangeOffer->desired_period_year) {
            $oldYear = $exchangeOffer->desired_period_year;
        }
        if ($oldDayStart === null && $exchangeOffer->desired_period_start) {
            $oldDayStart = $exchangeOffer->desired_period_start->format('d');
        }
        if ($oldDayEnd === null && $exchangeOffer->desired_period_end) {
            $oldDayEnd = $exchangeOffer->desired_period_end->format('d');
        }
    }

    $padDay = static function ($value) {
        if ($value === null || $value === '') {
            return '';
        }

        return str_pad((string) (int) $value, 2, '0', STR_PAD_LEFT);
    };
    $oldDayStart = $padDay($oldDayStart);
    $oldDayEnd = $padDay($oldDayEnd);
    $oldMonth = $oldMonth !== null && $oldMonth !== '' ? str_pad((string) (int) $oldMonth, 2, '0', STR_PAD_LEFT) : '';
@endphp

<div class="col-6 col-sm-3 col-md-3 exchange-period-day-col">
    <label for="{{ $domIdPrefix }}desired_period_day_start" class="form-label fw-semibold">Período Início</label>
    <select class="form-select exchange-period-day @error('desired_period_day_start') is-invalid @enderror"
            id="{{ $domIdPrefix }}desired_period_day_start" name="desired_period_day_start"
            data-exchange-period-field="day_start">
        <option value="">—</option>
        @for ($d = 1; $d <= 30; $d++)
            @php $dayVal = str_pad((string) $d, 2, '0', STR_PAD_LEFT); @endphp
            <option value="{{ $dayVal }}" {{ $oldDayStart === $dayVal ? 'selected' : '' }}>{{ $dayVal }}</option>
        @endfor
    </select>
    @error('desired_period_day_start')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-6 col-sm-3 col-md-3 exchange-period-day-col">
    <label for="{{ $domIdPrefix }}desired_period_day_end" class="form-label fw-semibold">Período Fim</label>
    <select class="form-select exchange-period-day @error('desired_period_day_end') is-invalid @enderror"
            id="{{ $domIdPrefix }}desired_period_day_end" name="desired_period_day_end"
            data-exchange-period-field="day_end">
        <option value="">—</option>
        @for ($d = 1; $d <= 30; $d++)
            @php $dayVal = str_pad((string) $d, 2, '0', STR_PAD_LEFT); @endphp
            <option value="{{ $dayVal }}" {{ $oldDayEnd === $dayVal ? 'selected' : '' }}>{{ $dayVal }}</option>
        @endfor
    </select>
    @error('desired_period_day_end')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-hint text-secondary exchange-period-auto-hint d-none">Preenchido automaticamente conforme os pernoites da cota.</small>
</div>

<div class="col-6 col-sm-3 col-md-3">
    <label for="{{ $domIdPrefix }}desired_period_month" class="form-label fw-semibold">Mês <span class="text-danger">*</span></label>
    <select class="form-select @error('desired_period_month') is-invalid @enderror"
            id="{{ $domIdPrefix }}desired_period_month" name="desired_period_month" required
            data-exchange-period-field="month">
        <option value="">—</option>
        @for ($m = 1; $m <= 12; $m++)
            @php $monthVal = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
            <option value="{{ $monthVal }}" {{ $oldMonth === $monthVal ? 'selected' : '' }}>{{ $monthVal }}</option>
        @endfor
    </select>
    @error('desired_period_month')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-6 col-sm-3 col-md-3">
    <label for="{{ $domIdPrefix }}desired_period_year" class="form-label fw-semibold">Ano <span class="text-danger">*</span></label>
    <select class="form-select @error('desired_period_year') is-invalid @enderror"
            id="{{ $domIdPrefix }}desired_period_year" name="desired_period_year" required
            data-exchange-period-field="year">
        <option value="">—</option>
        <option value="{{ $currentYear }}" {{ (string) $oldYear === (string) $currentYear ? 'selected' : '' }}>{{ $currentYear }}</option>
        <option value="{{ $nextYear }}" {{ (string) $oldYear === (string) $nextYear ? 'selected' : '' }}>{{ $nextYear }}</option>
    </select>
    @error('desired_period_year')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
