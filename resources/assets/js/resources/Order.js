import Document from '../../../../../backend-module/resources/assets/js/resources/Document';
import OrderLine from './OrderLine';

export default class Order extends Document {

    constructor() {
        super();
        this.total = document.querySelector('[name="total"]');
        this.price_list = document.querySelector('[name="price_list_id"]');
        this._init();
    }

    _getContainerInstance(container) {
        return new OrderLine(this, container);
    }

    _init() {
        // capture price_list change and redirect change to every line
        this.price_list.addEventListener('change', e =>
            // foreach lines and fire change
            this.lines.forEach(line =>
                // fire change on first <select> (product selector)
                Order.fire('change', [...line.container.querySelectorAll('select')].pop())
            )
        );
    }

}
