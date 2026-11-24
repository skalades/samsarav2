/**
 * SAMSARA Currency Input & Live Indonesian Rupiah Formatter
 * Modular, scalable, and lightweight auto-formatter for all currency fields across SAMSARA.
 */
(function (root) {
  'use strict';

  /**
   * Format any number or numeric string to Indonesian Rupiah representation:
   * e.g. 25000 -> "Rp 25.000" (or "25.000" if withPrefix === false)
   */
  function formatRupiah(value, withPrefix = true) {
    if (value === null || value === undefined || value === '') {
      return withPrefix ? 'Rp 0' : '0';
    }

    const isNegative = String(value).includes('-');
    const cleanDigits = String(value).replace(/\D/g, '');

    if (!cleanDigits) {
      return withPrefix ? 'Rp 0' : '0';
    }

    const num = parseInt(cleanDigits, 10);
    const formatted = num.toLocaleString('id-ID');

    if (withPrefix) {
      return (isNegative ? '-Rp ' : 'Rp ') + formatted;
    }

    return (isNegative ? '-' : '') + formatted;
  }

  /**
   * Parse an Indonesian-formatted currency string or raw number to integer/float:
   * e.g. "Rp 25.000" -> 25000, "1.500.000" -> 1500000
   */
  function parseRupiah(value) {
    if (value === null || value === undefined || value === '') {
      return 0;
    }

    if (typeof value === 'number') {
      return isNaN(value) ? 0 : value;
    }

    const str = String(value).trim();
    const isNegative = str.startsWith('-') || str.includes('-Rp');
    const clean = str.replace(/[^\d]/g, '');

    if (!clean) return 0;

    const num = parseInt(clean, 10);
    return isNegative ? -num : num;
  }

  /**
   * Core function to format a currency input field on the fly,
   * accurately preserving cursor position.
   */
  function formatInputElement(input) {
    if (!input) return;

    const withPrefix = input.dataset.currencyPrefix === 'true';
    const allowEmpty = input.dataset.allowEmpty === 'true';
    const oldValue = input.value || '';
    const oldSelStart = input.selectionStart || 0;

    // Count how many digits appear before the cursor in the old value
    const digitsBeforeCursor = (oldValue.slice(0, oldSelStart).match(/\d/g) || []).length;

    // Strip everything except digits
    const cleanDigits = oldValue.replace(/\D/g, '');

    if (cleanDigits === '') {
      input.value = allowEmpty ? '' : (withPrefix ? 'Rp 0' : '0');
      input.dataset.rawValue = '0';
      return;
    }

    const num = parseInt(cleanDigits, 10);
    let formatted = num.toLocaleString('id-ID');
    if (withPrefix) {
      formatted = 'Rp ' + formatted;
    }

    input.value = formatted;
    input.dataset.rawValue = num.toString();

    // Calculate new cursor position based on digit count
    let newSelStart = 0;
    let digitsSeen = 0;
    for (let i = 0; i < formatted.length; i++) {
      if (/\d/.test(formatted[i])) {
        digitsSeen++;
      }
      if (digitsSeen === digitsBeforeCursor) {
        newSelStart = i + 1;
        break;
      }
    }

    if (digitsBeforeCursor === 0) {
      newSelStart = withPrefix ? (formatted.startsWith('Rp ') ? 3 : 0) : 0;
    } else if (digitsSeen < digitsBeforeCursor) {
      newSelStart = formatted.length;
    }

    try {
      input.setSelectionRange(newSelStart, newSelStart);
    } catch (e) {
      // Ignored for non-textual input types if any
    }
  }

  /**
   * Bind events to a single currency input element.
   */
  function bindCurrencyInput(input) {
    if (input.__samsaraCurrencyBound) return;
    input.__samsaraCurrencyBound = true;

    // Change type to text with numeric inputmode for mobile keypad friendliness
    // (type="number" forbids thousand separator dots in HTML5 standard)
    if (input.type === 'number') {
      input.type = 'text';
      input.setAttribute('inputmode', 'numeric');
      input.removeAttribute('step');
    }

    // Format initial value if already present
    if (input.value !== '') {
      formatInputElement(input);
    }

    // Live formatting on typing / input
    input.addEventListener('input', function () {
      formatInputElement(input);
    });

    // Handle backspace when cursor is directly after a dot
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace') {
        const val = input.value;
        const selStart = input.selectionStart;
        const selEnd = input.selectionEnd;

        // If no range selected and character to the left is dot or space
        if (selStart === selEnd && selStart > 0) {
          const charLeft = val.charAt(selStart - 1);
          if (charLeft === '.' || charLeft === ' ') {
            e.preventDefault();
            // Delete the digit before the dot
            const before = val.slice(0, Math.max(0, selStart - 2));
            const after = val.slice(selStart);
            input.value = before + after;
            input.setSelectionRange(selStart - 2, selStart - 2);
            formatInputElement(input);
            input.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }
      }
    });

    // Ensure form submission unmasks to raw digits
    const form = input.form;
    if (form && !form.__samsaraCurrencySubmitBound) {
      form.__samsaraCurrencySubmitBound = true;
      form.addEventListener('submit', function () {
        const currencyInputs = form.querySelectorAll('[data-currency-input], .currency-input');
        currencyInputs.forEach(function (inp) {
          const raw = (inp.value || '').replace(/\D/g, '');
          inp.dataset.preSubmitValue = inp.value;
          inp.value = raw !== '' ? raw : (inp.dataset.allowEmpty === 'true' ? '' : '0');
        });

        // Restore formatted values shortly after in case of client-side validation abort or back button
        setTimeout(function () {
          currencyInputs.forEach(function (inp) {
            if (inp.dataset.preSubmitValue !== undefined) {
              inp.value = inp.dataset.preSubmitValue;
            }
          });
        }, 80);
      });
    }
  }

  /**
   * Automatically scan a container (or document) and bind all currency inputs.
   */
  function initCurrencyInputs(container = document) {
    if (!container || !container.querySelectorAll) return;
    const inputs = container.querySelectorAll('[data-currency-input], .currency-input');
    inputs.forEach(bindCurrencyInput);
  }

  /**
   * Programmatically set an input's value and immediately apply Indonesian formatting.
   */
  function setCurrencyInputValue(inputOrId, value) {
    const input = typeof inputOrId === 'string' ? document.getElementById(inputOrId) : inputOrId;
    if (!input) return;

    bindCurrencyInput(input);
    input.value = value !== null && value !== undefined ? String(value) : '';
    formatInputElement(input);
    input.dispatchEvent(new Event('input', { bubbles: true }));
  }

  // Export to window
  root.formatRupiah = formatRupiah;
  root.parseRupiah = parseRupiah;
  root.initCurrencyInputs = initCurrencyInputs;
  root.setCurrencyInputValue = setCurrencyInputValue;

  // Auto-init on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initCurrencyInputs(document);
    });
  } else {
    initCurrencyInputs(document);
  }

})(typeof window !== 'undefined' ? window : this);
