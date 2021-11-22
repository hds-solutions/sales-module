<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $resource->document_number }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset(mix('backend-module/assets/css/printables.css')) }}">
    <style type="text/css" media="all">
        @page {
            margin: 0;
            padding: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

    <header>
        <h2>{{ $resource->company->name }}</h2>
        <h3 class="mb-1">{{ config('app.name') }}</h3>
        <p class="mt-0">{{ $resource->branch->name.', '.$resource->branch->address }}</p>

        <div class="row">
            <div class="col text-right font-weight-bold">RUC:</div>
            <div class="col text-left">{{ $resource->company->ftid }}</div>
        </div>
        <div class="row">
            <div class="col text-right font-weight-bold">Teléfono:</div>
            <div class="col text-left">{{ $resource->branch->phone ?? '--' }}</div>
        </div>
        <div class="row">
            <div class="col text-right font-weight-bold">Timbrado:</div>
            <div class="col text-left">{{ $resource->stamping->document_number }}</div>
        </div>
        <div class="row">
            <div class="col text-right font-weight-bold">Válido desde:</div>
            <div class="col text-left">{{ $resource->stamping->valid_from_pretty }}</div>
        </div>
        <div class="row">
            <div class="col text-right font-weight-bold">Válido hasta:</div>
            <div class="col text-left">{{ $resource->stamping->valid_until_pretty }}</div>
        </div>
    </header>

    <hr>

    {{-- <div class="row testing my-3">
        @for($i=1;$i<=12;$i++)
        <div class="col"><small>{{ $i }}</small></div>
        @endfor
    </div> --}}

    <div class="row justify-content-center text-left">
        <div class="col">
            <div class="row">
                <div class="col-4 font-weight-bold">Factura Nro:</div>
                <div class="col">{{ $resource->document_number }}</div>
            </div>

            <div class="row">
                <div class="col-4 font-weight-bold">Fecha:</div>
                <div class="col">{{ $resource->transacted_at_pretty }}</div>
                {{-- <div class="col">{{ pretty_date(now(), true) }}</div> --}}
            </div>

            <div class="row">
                <div class="col-4 font-weight-bold">Cajero:</div>
                <div class="col">{{ $resource->employee->full_name }}</div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center text-left mt-2">
        <div class="col">
            <div class="row">
                <div class="col-4 font-weight-bold">Cliente:</div>
                <div class="col">{{ $resource->partnerable->full_name }}</div>
            </div>

            <div class="row">
                <div class="col-4 font-weight-bold">RUC:</div>
                <div class="col">{{ $resource->partnerable->ftid }}</div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row font-weight-bold fs-1rem">
        <div class="col-4">Código</div>
        <div class="col-5 text-left">Description</div>
        <div class="col-3">Importe</div>
    </div>

    <hr>

    @foreach($resource->lines as $line)
        <?php $currency = currency($line->currency_id); ?>

        <div class="row fs-1rem">
            <div class="col-4"><small>{{ $line->variant->sku ?? $line->product->code ?? '--' }}</small></div>
            <div class="col text-left"><small>{{ $line->variant->name ?? $line->product->name }}</small></div>
            {{-- <div class="col-2">{{ $line->quantity_invoiced }}</div> --}}
        </div>

        <div class="row fs-1rem mb-2">
            <div class="col-2"><small>{{ rtrim($line->product->taxRaw, 'i') }}%</small></div>
            <div class="col-10">
                <div class="row text-right">
                    <div class="col">
                        <div class="row">
                            <div class="col text-left"><small class="mx-1">{{ $line->quantity_invoiced }}x</small></div>
                            <div class="col text-right"><small>{{ number($line->price_invoiced, $currency->decimals) }}</small></div>
                        </div>
                    </div>
                    <div class="col-2"><small>{{ $currency->code }}</small></div>
                    <div class="col-4 font-weight-bold"><small>{{ number($line->total, $currency->decimals) }}</small></div>
                </div>
            </div>
        </div>
    @endforeach

    <hr>

    <div class="row font-weight-bold fs-1rem">
        <div class="col-3 text-left">TOTAL</div>
        <div class="col text-right fs-2x"><small class="font-weight-normal mr-2">{{ currency($resource->currency_id)->code }}</small>{{ number($resource->total, 0) }}</div>
    </div>

    <hr>

    <div class="row fs-1rem">
        <div class="col text-left">Detalle de pagos</div>
        <div class="col-5 text-right">
            <div class="row">
                <div class="col-4">{{ currency($resource->currency_id)->code }}</div>
                <div class="col">{{ number($resource->receipments->sum('payments_net_amount'), currency($resource->currency_id)->decimals) }}</div>
            </div>
        </div>
    </div>

    <?php $return = 0; ?>
    @foreach($resource->receipments as $receipment)
        @foreach($receipment->payments as $payment)
            @if ($payment->receipmentPayment->payment_type == Payment::PAYMENT_TYPE_Cash &&
                $payment->receipmentPayment->payment_amount > $payment->amount)
                <?php $return += $payment->receipmentPayment->payment_amount - $payment->amount; ?>
            @endif
            <div class="row fs-1rem">
                <div class="col text-left ml-3"><small>{!! match($payment->receipmentPayment->payment_type) {
                    Payment::PAYMENT_TYPE_Cash          => 'Efectivo',
                    Payment::PAYMENT_TYPE_Card          => 'Tarjeta <small>'.$payment->document_number.'</small>',
                    //Payment::PAYMENT_TYPE_Card          => $payment->card_holder.' <small>** '.$payment->card_number.'</small>',
                    Payment::PAYMENT_TYPE_Credit        => trans_choice('sales::receipment.payments.dues.0', $payment->dues, [ 'dues' => $payment->dues ]).' <small>'.$payment->interest.'%</small>',
                    Payment::PAYMENT_TYPE_Check         => $payment->document_number.'<small class="ml-2">'.$payment->bank_name.'</small>',
                    Payment::PAYMENT_TYPE_CreditNote    => $payment->document_number.'<small class="ml-2">'.$payment->payment_amount.'</small>',
                    default => null,
                } !!}</small></div>
                <div class="col-5 text-right">
                    <div class="row">
                        <div class="col-4"><small>{{ currency($payment->receipmentPayment->currency_id)->code }}</small></div>
                        <div class="col"><small>{{ number($payment->receipmentPayment->payment_amount, currency($payment->receipmentPayment->currency_id)->decimals) }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    @if ($return)
    <div class="row fs-1rem mt-1">
        <div class="col text-left">Vuelto</div>
        <div class="col-5 text-right">
            <div class="row">
                <div class="col-4">{{ currency($resource->currency_id)->code }}</div>
                <div class="col">{{ number($return, currency($resource->currency_id)->decimals) }}</div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-3 fs-1rem">
        <div class="col-12">Factura de venta</div>
        <div class="col-12">
            <div class="row">
                <div class="col-5 text-right">Original:</div>
                <div class="col text-left">Cliente - Comprador</div>
            </div>
            <div class="row">
                <div class="col-5 text-right">Duplicado:</div>
                <div class="col text-left">Archivo Tributario</div>
            </div>
        </div>
    </div>

    {{-- <img src="{{ asset('backend-module/assets/images/qr.png') }}"> --}}

</body>
</html>
