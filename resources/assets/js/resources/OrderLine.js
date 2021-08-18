import Application from '../../../../../backend-module/resources/assets/js/resources/Application';
import DocumentLine from '../../../../../backend-module/resources/assets/js/resources/DocumentLine';

export default class OrderLine extends DocumentLine {

    #thousands;
    #fields = [];
    #finder;
    #loading = false;

    constructor(document, container) {
        super(document, container);
        this.#thousands = this.container.querySelectorAll('[name^="lines"][thousand]');
        this.#fields.push(...this.container.querySelectorAll('select'));
        this.#fields.push(...this.container.querySelectorAll('[name="lines[price][]"],[name="lines[quantity][]"]'));
        this.#finder = this.container.querySelector('[name="product-finder"]');
        this._init();
    }

    destructor() {
        // update total
        this.#updateTotal(null);
    }

    _init() {
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

        // capture product finder event
        this.#finder.addEventListener('keydown', e => {
            // ignore if key isn't <enter>
            if (e.keyCode !== 13) return false;
            // disable default event
            else e.preventDefault();

            // parse quantity
            let match, qty = (match = this.#finder.value.match(/^(\d*\.?\d*)\*/)) ? (match[1] ?? 1) : 1;
            // remove quantity from code
            if (qty !== null) {
                // remove qty from code
                this.#finder.value = this.#finder.value.replace(qty+'*', '');
                // set qty on field
                this.container.querySelector('[name="lines[quantity][]"]').value = qty;
            }

            // disable field while working
            this.#finder.setAttribute('disabled', true);

            // find product
            $.ajax({
                method: 'POST',
                url: '/sales/product',
                data: {
                    _token: this.document.token,
                    product: this.#finder.value,
                },
                success: data => {
                    // active flag to prevent multiple ajax requests
                    this.#loading = true;

                    // select product
                    Application.$(this.container.querySelector('[name="lines[product_id][]"]'))
                        .selectpicker('val', data.variant !== null ? data.variant.product_id : (data.product !== null ? data.product.id : null));
                    // fire change to enable variants selector
                    this.fire('change', this.container.querySelector('[name="lines[product_id][]"]'));

                    // select variant
                    Application.$(this.container.querySelector('[name="lines[variant_id][]"]'))
                        .selectpicker('val', data.variant !== null ? data.variant.id : null);
                    // disable flag, next change event fires ajax requests
                    this.#loading = false;
                    // fire change to update price
                    this.fire('change', this.container.querySelector('[name="lines[variant_id][]"]'));

                    // re-enable field
                    this.#finder.removeAttribute('disabled');

                    // check if a product/variant was found
                    if (data.variant === null && data.product === null) return this.#finder.select();

                    // remove product finder
                    this.#finder.remove();

                    // set focus on next line
                    this.document.lines.last().container.querySelector('[name="'+this.#finder.name+'"]').focus();
                },
            });
        });
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
        if (this.#loading || !data.product) return;
        // request current price quantity
        $.ajax({
            method: 'POST',
            url: '/sales/price',
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
