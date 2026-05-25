<script>
(function () {
    const MAX_DAY = 30;
    const PANEL_PREFIXES = ['', 'mais_'];

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function getActivePeriodPrefix() {
        const modeField = document.getElementById('exchange_mode_field');
        if (modeField && modeField.value === 'mais') {
            return 'mais_';
        }

        return '';
    }

    function isTrocaJustaMaisMode() {
        const modeField = document.getElementById('exchange_mode_field');
        return modeField && modeField.value === 'mais';
    }

    function isComplementDiarias() {
        const complement = document.getElementById('complement_trade_type');
        return complement && complement.value === 'diarias' && !complement.disabled;
    }

  /**
   * @returns {{ signed: number, magnitude: number, intent: 'solicitar'|'ofertar'|null }|null}
   */
    function parseDaysDifference(raw) {
        if (raw === null || raw === undefined) {
            return null;
        }
        const normalized = String(raw).trim().replace(/\s+/g, '');
        if (normalized === '') {
            return null;
        }
        const match = normalized.match(/^([+-])(\d+)$/);
        if (!match) {
            return null;
        }
        const magnitude = parseInt(match[2], 10);
        if (!magnitude || magnitude <= 0) {
            return null;
        }
        const signed = match[1] === '-' ? -magnitude : magnitude;

        return {
            signed: signed,
            magnitude: magnitude,
            intent: signed > 0 ? 'solicitar' : 'ofertar',
        };
    }

    function getDaysDifferenceExtra() {
        if (!isTrocaJustaMaisMode() || !isComplementDiarias()) {
            return 0;
        }
        const input = document.getElementById('days_difference');
        if (!input || input.disabled) {
            return 0;
        }
        const parsed = parseDaysDifference(input.value);
        return parsed ? parsed.magnitude : 0;
    }

    function updateDaysDifferenceHint() {
        const hint = document.getElementById('days_difference_intent_hint');
        const input = document.getElementById('days_difference');
        if (!hint || !input) {
            return;
        }
        if (!isTrocaJustaMaisMode() || !isComplementDiarias()) {
            hint.classList.add('d-none');
            hint.textContent = '';
            return;
        }
        const parsed = parseDaysDifference(input.value);
        if (!parsed) {
            hint.classList.add('d-none');
            hint.textContent = '';
            return;
        }
        hint.classList.remove('d-none');
        if (parsed.intent === 'solicitar') {
            hint.textContent = 'Você solicita o período selecionado mais ' + parsed.magnitude + ' diária(s) — o período fim será ajustado automaticamente.';
        } else {
            hint.textContent = 'Você oferece o período selecionado mais ' + parsed.magnitude + ' diária(s) na troca — o período fim será ajustado automaticamente.';
        }
    }

    function getSelectedPernoites(quotaSelect) {
        if (!quotaSelect || !quotaSelect.value) {
            return 0;
        }
        const opt = quotaSelect.options[quotaSelect.selectedIndex];
        return parseInt(opt.getAttribute('data-pernoites') || '0', 10) || 0;
    }

    function isFieldInActivePanel(el) {
        return el && !el.disabled;
    }

    function bindExchangePeriodPanel(prefix) {
        const root = document.getElementById(
            prefix === '' ? 'exchange_source_and_criteria' : 'exchange_source_and_criteria_mais'
        );
        if (!root || root.dataset.periodBound === '1') {
            return;
        }
        root.dataset.periodBound = '1';

        const quotaSelect = document.getElementById(prefix + 'quota_id');
        const dayStart = document.getElementById(prefix + 'desired_period_day_start');
        const dayEnd = document.getElementById(prefix + 'desired_period_day_end');
        const monthSelect = document.getElementById(prefix + 'desired_period_month');
        const yearSelect = document.getElementById(prefix + 'desired_period_year');
        const autoHint = root.querySelector('.exchange-period-auto-hint');

        if (!dayStart || !dayEnd || !monthSelect || !yearSelect) {
            return;
        }

        function syncMonthYearRequired() {
            if (!isFieldInActivePanel(dayStart)) {
                return;
            }
            monthSelect.required = true;
            yearSelect.required = true;
            if (dayStart.value !== '') {
                monthSelect.setAttribute('aria-required', 'true');
                yearSelect.setAttribute('aria-required', 'true');
            }
        }

        function applyAutoEndFromPernoites() {
            if (!isFieldInActivePanel(dayStart)) {
                return;
            }

            const startVal = parseInt(dayStart.value, 10);
            if (!dayStart.value || Number.isNaN(startVal)) {
                dayEnd.disabled = false;
                dayEnd.classList.remove('bg-light');
                if (autoHint) {
                    autoHint.classList.add('d-none');
                }
                return;
            }

            const pernoites = getSelectedPernoites(quotaSelect);
            const extraDiarias = prefix === 'mais_' ? getDaysDifferenceExtra() : 0;
            const endDay = Math.min(MAX_DAY, startVal + pernoites + extraDiarias);
            dayEnd.value = pad2(endDay);
            dayEnd.disabled = true;
            dayEnd.classList.add('bg-light');

            if (autoHint) {
                if (extraDiarias > 0 && prefix === 'mais_') {
                    const parsed = parseDaysDifference(document.getElementById('days_difference')?.value);
                    const verb = parsed && parsed.intent === 'ofertar' ? 'oferta' : 'solicita';
                    autoHint.textContent = 'Período fim calculado: pernoites da cota + ' + extraDiarias + ' diária(s) que você ' + verb + ' na troca justa.';
                    autoHint.classList.remove('d-none');
                } else {
                    autoHint.textContent = 'Preenchido automaticamente conforme os pernoites da cota.';
                    autoHint.classList.toggle('d-none', pernoites <= 0);
                }
            }
        }

        function onDayStartChange() {
            if (!isFieldInActivePanel(dayStart)) {
                return;
            }
            syncMonthYearRequired();
            if (dayStart.value === '') {
                dayEnd.value = '';
                dayEnd.disabled = false;
                dayEnd.classList.remove('bg-light');
                if (autoHint) {
                    autoHint.classList.add('d-none');
                }
                return;
            }
            applyAutoEndFromPernoites();
        }

        dayStart.addEventListener('change', onDayStartChange);
        if (quotaSelect) {
            quotaSelect.addEventListener('change', function () {
                if (isFieldInActivePanel(dayStart) && dayStart.value !== '') {
                    applyAutoEndFromPernoites();
                }
            });
        }

        syncMonthYearRequired();
        if (dayStart.value !== '' && isFieldInActivePanel(dayStart)) {
            applyAutoEndFromPernoites();
        }
    }

    function bindTrocaJustaDaysDifference() {
        const daysInput = document.getElementById('days_difference');
        const complement = document.getElementById('complement_trade_type');
        if (!daysInput || daysInput.dataset.trocaJustaBound === '1') {
            return;
        }
        daysInput.dataset.trocaJustaBound = '1';

        function onDaysDifferenceChange() {
            updateDaysDifferenceHint();
            if (isTrocaJustaMaisMode()) {
                window.refreshActiveExchangePeriod();
            }
        }

        daysInput.addEventListener('input', onDaysDifferenceChange);
        daysInput.addEventListener('change', onDaysDifferenceChange);
        if (complement) {
            complement.addEventListener('change', function () {
                updateDaysDifferenceHint();
                if (isTrocaJustaMaisMode()) {
                    window.refreshActiveExchangePeriod();
                }
            });
        }
        updateDaysDifferenceHint();
    }

    window.refreshActiveExchangePeriod = function () {
        const prefix = getActivePeriodPrefix();
        const dayStart = document.getElementById(prefix + 'desired_period_day_start');
        if (dayStart && isFieldInActivePanel(dayStart)) {
            dayStart.dispatchEvent(new Event('change'));
        }
    };

    window.exchangePeriodBindAll = function () {
        PANEL_PREFIXES.forEach(bindExchangePeriodPanel);
        bindTrocaJustaDaysDifference();
        window.refreshActiveExchangePeriod();
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.exchangePeriodBindAll();

        const form = document.querySelector('.exchange-offer-form-card form');
        if (form) {
            form.addEventListener('submit', function () {
                PANEL_PREFIXES.forEach(function (prefix) {
                    const dayEnd = document.getElementById(prefix + 'desired_period_day_end');
                    if (dayEnd) {
                        dayEnd.disabled = false;
                    }
                });
            });
        }

        const tabList = document.getElementById('exchangeModeTabs');
        if (tabList) {
            tabList.addEventListener('shown.bs.tab', function () {
                updateDaysDifferenceHint();
                window.refreshActiveExchangePeriod();
            });
        }
    });
})();
</script>
