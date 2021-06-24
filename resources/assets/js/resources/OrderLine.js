import DocumentLine from '../../../../../backend-module/resources/assets/js/resources/DocumentLine';

export default class OrderLine extends DocumentLine {

    #thousands;
    #fields = [];

    constructor(document, container) {
        super(document, container);
        this.#thousands = this.container.querySelectorAll('[name^="lines"][thousand]');
        this.#fields.push(...this.container.querySelectorAll('select'));
        this.#fields.push(...this.container.querySelectorAll('[name="lines[price][]"],[name="lines[quantity][]"]'));
        this._init();
    }

    destructor() {
        // update total
        this.#updateTotal(null);
    }

    _init() {
        super._init();
        // capture change on fields
        this.#fields.forEach(field => field.addEventListener('change', e => {
            // ignore if field doesn't have form (deleted line)
            if (field.form === null) return;

            // if field is <select> fire product/variant change
            if (field.localName.match(/^select/)) this.#loadProduct(field);
            // if field is <input> fire product/variant change
            if (field.localName.match(/^input/)) this.#updatePrice(field);

            // update total
            this.#updateTotal(e);

            // redirect event to listener
            this.updated(e);
        }));
    }

    #loadProduct(field) {
        // build request data
        let data = { _token: this.document.token },
            option;
        // load product,variant,currency selected options
        if ((option = this.container.querySelector('[name="lines[product_id][]"]').selectedOptions[0]).value) data.product = option.value;
        if ((option = this.container.querySelector('[name="lines[variant_id][]"]').selectedOptions[0]).value) data.variant = option.value;
        if ((option = field.form.querySelector('[name="currency_id"]').selectedOptions[0]).value) data.currency = option.value;
        // ignore if no product
        if (!data.product) return;
        // request current price quantity
        $.ajax({
            method: 'POST',
            url: '/orders/price',
            data: data,
            // update current price for product+variant on locator
            success: data => {
                // set price
                this.container.querySelector('[name="lines[price][]"]').value = data.price ?? null;
                // get line quantity
                let quantity = this.container.querySelector('[name="lines[quantity][]"]');
                // parse or set to 1 as default
                quantity.value = !data.price || quantity.value.length > 0 ? quantity.value : 1;
                // execute change event on quantity field
                this.fire('change', quantity);
            },
        });
    }

    #updatePrice(field) {
        // get fields
        let price = this.container.querySelector('[name="lines[price][]"]'),
            quantity = this.container.querySelector('[name="lines[quantity][]"]'),
            total = this.container.querySelector('[name="lines[total][]"]');

        // update total value
        total.value = (
            // convert price to integer without decimals
            parseInt(price.value.replace(/[^0-9\.]/g,'') * Math.pow(10, price.dataset.decimals))
            // multiply for quantity
            * parseFloat(quantity.value)
        // divide total for currency decimals
        ) / Math.pow(10, price.dataset.decimals);

        // fire thousands plugin formatter
        this.#thousands.forEach(thousand => this.fire('blur', thousand));
        // fire total change
        this.fire('change', total);
    }

    #updateTotal(event) {
        // total acumulator
        let total = 0;
        // foreach lines
        this.document.lines.forEach(line => {
            // parse total
            let lineTotal = line.container.querySelector('[name="lines[total][]"]').value.replace(/\,*/g, '') * 1;
            // ignore if is empty
            if (lineTotal == 0) return;
            // add to acumulator
            total += lineTotal;
        });
        // set total
        this.document.total.value = total > 0 ? total : '';
        // fire format
        if (total > 0) this.fire('blur', this.document.total);
    }

}
