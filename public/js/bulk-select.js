function initBulkSelect({ formId, selectAllId, countId, buttonId, confirmMessage }) {
    const form = document.getElementById(formId);
    const selectAll = document.getElementById(selectAllId);
    const countEl = document.getElementById(countId);
    const button = document.getElementById(buttonId);

    if (!form || !button || !countEl) {
        return;
    }

    const boxes = () => Array.from(document.querySelectorAll('.row-select[form="' + formId + '"]'));

    function sync() {
        const all = boxes();
        const selected = all.filter((box) => box.checked);

        countEl.textContent = selected.length === 0 ? 'None selected' : selected.length + ' selected';
        button.disabled = selected.length === 0;

        if (selectAll) {
            selectAll.disabled = all.length === 0;
            selectAll.checked = all.length > 0 && selected.length === all.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < all.length;
        }
    }

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (target.classList && target.classList.contains('row-select') && target.getAttribute('form') === formId) {
            sync();
        }
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            boxes().forEach((box) => {
                box.checked = selectAll.checked;
            });
            sync();
        });
    }

    form.addEventListener('submit', (event) => {
        const count = boxes().filter((box) => box.checked).length;
        if (count === 0 || !window.confirm(confirmMessage(count))) {
            event.preventDefault();
        }
    });

    sync();
}
