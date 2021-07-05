export default class Payment {}

Object.defineProperties(Payment, {
    PAYMENT_TYPE_Cash: {
        value: 'CA',
        writable : false,
        enumerable : true,
        configurable : false,
    },
    PAYMENT_TYPE_Card: {
        value: 'CD',
        writable : false,
        enumerable : true,
        configurable : false,
    },
    PAYMENT_TYPE_Credit: {
        value: 'CR',
        writable : false,
        enumerable : true,
        configurable : false,
    },
    PAYMENT_TYPE_Check: {
        value: 'CH',
        writable : false,
        enumerable : true,
        configurable : false,
    },
    PAYMENT_TYPE_CreditNote: {
        value: 'CN',
        writable : false,
        enumerable : true,
        configurable : false,
    },
    PAYMENT_TYPE_Promissory: {
        value: 'PP',
        writable : false,
        enumerable : true,
        configurable : false,
    },
});
Object.defineProperty(Payment, 'PAYMENT_TYPES', {
    value: [
        Payment.PAYMENT_TYPE_Cash,
        Payment.PAYMENT_TYPE_Card,
        Payment.PAYMENT_TYPE_Credit,
        Payment.PAYMENT_TYPE_Check,
        Payment.PAYMENT_TYPE_CreditNote,
        Payment.PAYMENT_TYPE_Promissory,
    ],
    writable : false,
    enumerable : true,
    configurable : false,
});
