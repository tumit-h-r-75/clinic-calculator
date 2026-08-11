(function () {
    'use strict';

    function formatDKK(value) {
        return new Intl.NumberFormat('da-DK').format(Math.round(value));
    }

    function readNumber(wrapper, key, fallback) {
        const input = wrapper.querySelector('[data-input="' + key + '"]');
        const value = input ? parseFloat(input.value) : fallback;
        return Number.isFinite(value) ? value : fallback;
    }

    function readLeaseLabel(wrapper) {
        const select = wrapper.querySelector('[data-input="lease"]');
        if (!select || !select.selectedOptions.length) {
            return '';
        }

        const option = select.selectedOptions[0];
        return option.getAttribute('data-label') || option.textContent.trim();
    }

    function writeText(wrapper, selector, text) {
        const element = wrapper.querySelector(selector);
        if (element) {
            element.textContent = text;
        }
    }

    function calculate(wrapper) {
        const treatments = readNumber(wrapper, 'treatments', 0);
        const price = readNumber(wrapper, 'price', 0);
        const material = readNumber(wrapper, 'material', 0);
        const days = readNumber(wrapper, 'days', 20);
        const lease = readNumber(wrapper, 'lease', 0);

        writeText(wrapper, '[data-value="treatments"]', formatDKK(treatments));
        writeText(wrapper, '[data-value="price"]', formatDKK(price));
        writeText(wrapper, '[data-value="material"]', formatDKK(material));

        const totalTreatments = treatments * days;
        const grossRevenue = totalTreatments * price;
        const totalMaterial = totalTreatments * material;
        const netProfit = grossRevenue - totalMaterial - lease;
        const netPricePerSession = price - material;
        const breakEven = netPricePerSession > 0 ? Math.ceil(lease / netPricePerSession) : null;
        const roi = lease > 0 ? (netProfit / lease).toFixed(1) : '0.0';
        const coverage = price > 0 ? (((price - material) / price) * 100).toFixed(1) : '0.0';
        const leaseLabel = readLeaseLabel(wrapper);

        writeText(wrapper, '[data-result="gross_revenue_inline"]', formatDKK(grossRevenue) + ' DKK');
        writeText(wrapper, '[data-result="total_treatments_inline"]', '(' + formatDKK(totalTreatments) + ' behandlinger)');
        writeText(wrapper, '[data-result="net_profit"]', formatDKK(netProfit) + ' kr.');
        writeText(wrapper, '[data-result="material_total"]', '-' + formatDKK(totalMaterial) + ' DKK');
        writeText(wrapper, '[data-result="break_even"]', breakEven === null ? 'Aldrig' : breakEven === 0 ? 'Fuld profit fra start' : formatDKK(breakEven) + ' behandlinger');
        writeText(wrapper, '[data-result="roi"]', roi + 'x dækkende dækning');
        writeText(wrapper, '[data-result="coverage"]', coverage + '% dækningsgrad');
        writeText(wrapper, '[data-result="lease_label"]', leaseLabel);
    }

    function bind(wrapper) {
        wrapper.querySelectorAll('[data-input]').forEach(function (input) {
            input.addEventListener('input', function () {
                calculate(wrapper);
            });
            input.addEventListener('change', function () {
                calculate(wrapper);
            });
        });

        calculate(wrapper);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.derma-roi-calculator-wrapper').forEach(bind);
    });
}());
