import Event from '../../../../../backend-module/resources/assets/js/utils/consoleevent';
import Document from '../../../../../backend-module/resources/assets/js/resources/Document';
import InvoiceLine from './InvoiceLine';
import PaymentLine from './PaymentLine';

export default class Receipment extends Document {

    constructor() {
        super();
        this.invoicesAmount = document.querySelector('[name="invoices_amount"]');
        this.paymentsAmount = document.querySelector('[name="payments_amount"]');
    }

    _getContainerInstance(container) {
        switch (true) {
            case container.classList.contains('invoice-container'):
                return new InvoiceLine(this, container);
            case container.classList.contains('payment-container'):
                return new PaymentLine(this, container);
        }
        return null;
    }

}
