import Application from '../../../../backend-module/resources/assets/js/resources/Application';
import Order from './resources/Order';
// import Invoice from './resources/Invoice';

Application.register('order', Order);
Application.register('invoice', Order);
