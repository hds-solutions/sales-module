import Application from '../../../../backend-module/resources/assets/js/resources/Application';
import Event from '../../../../backend-module/resources/assets/js/utils/consoleevent';

import Order from './resources/Order';
import Receipment from './resources/Receipment';

Application.register('order', Order);
Application.register('invoice', Order);
Application.register('receipment', Receipment);

// Stamping
document.querySelectorAll('[data-stamping]').forEach(field => {
    // get stamping field
    let stamping = field.form.querySelector( field.getAttribute('data-stamping') );
    let value = field.value,
        selected = stamping.selectedOptions[0],
        current = selected.value;
    // capture change
    stamping.addEventListener('change', e => {
        // lock field by default
        field.setAttribute('readonly', true);
        field.value = null;
        // check if stamping is selected
        if ((selected = stamping.selectedOptions[0]).value) {
            // set next document number
            field.value = (selected.dataset.next !== '' && current === selected.value ? value : null) ?? selected.dataset.next ?? null;
            // unlock field
            field.removeAttribute('readonly');
        }
    });
    // fire change on parent
    (new Event('change')).fire( stamping );
});
