import Event from '../../../../../backend-module/resources/assets/js/utils/consoleevent';

export default class Order {
    constructor() {
        this.total = document.querySelector('[name="total"]');
        this.lines = [];
    }

    register(lineContainer, totalFieldName = 'lines[total][]') {
        // register lineContainer
        this.lines.push(lineContainer);
        // capture total change on line
        lineContainer.querySelector('[name="'+totalFieldName+'"]')
            .addEventListener('change', e => this._update(totalFieldName));
        // check orderline form
        if (lineContainer.classList.contains('line-container'))
            // register orderline events
            this._orderLine(lineContainer);
    }

    unregister(lineContainer, totalFieldName = 'lines[total][]') {
        // remove container from list
        this.lines.splice(this.lines.indexOf(lineContainer), 1);
        // update total price
        this._update(totalFieldName);
    }

    _update(totalFieldName) {
        // total acumulator
        let total = 0;
        // foreach lines
        this.lines.forEach(line => {
            // parse total
            let lineTotal = line.querySelector('[name="'+totalFieldName+'"]').value.replace(/\,*/g, '') * 1;
            // ignore if is empty
            if (lineTotal == 0) return;
            // add to acumulator
            total += lineTotal;
        });
        // set total
        this.total.value = total > 0 ? total : '';
        // fire format
        if (total > 0) (new Event('blur')).fire( this.total );
    }

    _orderLine(orderLineContainer) {
        // TODO: move here all JS that is on backend-module/app.js
    }

}
