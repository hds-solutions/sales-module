import DocumentLine from '../../../../../backend-module/resources/assets/js/resources/DocumentLine';
import Payment from '../models/Payment';

export default class PaymentLine extends DocumentLine {

    #fields = new Map;

    constructor(document, container) {
        super(document, container);
        this._init();
    }

    destructor() {
        // update total
        this.#updateTotal(null);
    }

    _init() {
        super._init();
        // get payment type selector
        let paymentType = this.container.querySelector('[name="payments[payment_type][]"]'),
            paymentAmount = this.container.querySelector('[name="payments[payment_amount][]"]'),
            // Credit payment type fields
            interest = this.container.querySelector('[name="payments[interest][]"]'),
            dues = this.container.querySelector('[name="payments[dues][]"]'),
            // Check payment type fields
            bank_name = this.container.querySelector('[name="payments[bank_name][]"]'),
            bank_account = this.container.querySelector('[name="payments[bank_account][]"]'),
            account_holder = this.container.querySelector('[name="payments[account_holder][]"]'),
            check_number = this.container.querySelector('[name="payments[check_number][]"]'),
            due_date = this.container.querySelector('[name="payments[due_date][]"]'),
            // CreditNote payment type fields
            credit_note_id = this.container.querySelector('[name="payments[credit_note_id][]"]'),
            // Card payment type fields
            card_holder = this.container.querySelector('[name="payments[card_holder][]"]'),
            card_number = this.container.querySelector('[name="payments[card_number][]"]'),
            is_credit = this.container.querySelector('[name="payments[is_credit][]"]');
        // group fields by PaymentType
        this.#fields = new Map([
            [ Payment.PAYMENT_TYPE_Cash,        [] ],
            [ Payment.PAYMENT_TYPE_Credit,      [ interest, dues ] ],
            [ Payment.PAYMENT_TYPE_Check,       [ bank_name, bank_account, account_holder, check_number, due_date ] ],
            [ Payment.PAYMENT_TYPE_CreditNote,  [ credit_note_id ] ],
            [ Payment.PAYMENT_TYPE_Card,        [ card_holder, card_number, is_credit ] ],
        ]);
        // capture payment type change
        paymentType.addEventListener('change', e => {
            // set PaymentType and Amount fields as mandatory
            paymentType.setAttribute('required', true);
            paymentAmount.setAttribute('required', true);
            // reset fields state
            this.#fields.forEach(group => group.forEach(field => field.removeAttribute('required')));
            if (paymentType.value) {
                // enable selected paymentType fields only
                this.#fields.get(paymentType.value).forEach(field => field.setAttribute('required', true));
            } else {
                // remove PaymentType and Amount fields required
                paymentType.removeAttribute('required');
                paymentAmount.removeAttribute('required');
            }
        });
        // capture total change
        paymentAmount.addEventListener('change', e => {
            // ignore if field doesn't have form (deleted line)
            if (paymentAmount.form === null) return;

            // update total
            this.#updateTotal(e);

            // redirect event to listener
            this.updated(e);
        });
    }

    #updateTotal(event) {
        // total acumulator
        let total = 0;
        // foreach lines
        this.document.lines.forEach(line => {
            // ignore if not invoice line
            if (!(line instanceof PaymentLine)) return;

            // parse total
            let lineTotal = line.container.querySelector('[name="payments[payment_amount][]"]').value.replace(/\,*/g, '') * 1;
            // ignore if is empty
            if (lineTotal == 0) return;
            // add to acumulator
            total += lineTotal;
        });

        // set totals
        this.document.paymentsAmount.value = total > 0 ? total : '';
        // fire format
        if (total > 0) this.fire('blur', this.document.paymentsAmount);
    }

}
