import DocumentLine from '../../../../../backend-module/resources/assets/js/resources/DocumentLine';

export default class InvoiceLine extends DocumentLine {

    #thousands;
    #fields = [];

    constructor(document, container) {
        super(document, container);
        this.#thousands = this.container.querySelectorAll('[name^="invoices"][thousand]');
        this.#fields.push(...this.container.querySelectorAll('select'));
        this.#fields.push(...this.container.querySelectorAll('[name="invoices[imputed_amount][]"]'));
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
            if (field.localName.match(/^select/)) this.#loadInvoice(field);

            // update total
            this.#updateTotal(e);

            // redirect event to listener
            this.updated(e);
        }));
    }

    #loadInvoice(field) {
        // get selected invoice
        let invoice = field.selectedOptions[0];
        // set pending amount on fields
        this.container.querySelector('[name="invoices[pending_amount][]"]').value =
        this.container.querySelector('[name="invoices[imputed_amount][]"]').value =
            invoice.dataset.pendingAmount;

        // fire thousands plugin formatter
        this.#thousands.forEach(thousand => this.fire('blur', thousand));
    }

    #updateTotal(event) {
        // total acumulator
        let total = 0;
        // foreach lines
        this.document.lines.forEach(line => {
            // ignore if not invoice line
            if (!(line instanceof InvoiceLine)) return;

            // parse total
            let lineTotal = line.container.querySelector('[name="invoices[imputed_amount][]"]').value.replace(/\,*/g, '') * 1;
            // ignore if is empty
            if (lineTotal == 0) return;
            // add to acumulator
            total += lineTotal;
        });

        // set totals
        this.document.invoicesAmount.value = total > 0 ? total : '';
        // fire format
        if (total > 0) this.fire('blur', this.document.invoicesAmount);
    }

}
