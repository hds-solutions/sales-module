import Application from '../../../../backend-module/resources/assets/js/resources/Application';
import Order from './resources/Order';
import Receipment from './resources/Receipment';

Application.register('order', Order);
Application.register('invoice', Order);
Application.register('receipment', Receipment);
