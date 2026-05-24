<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <th>{{ __('app.id') }}</th>
            <td>{{ $payment->id }}</td>
        </tr>
        <tr>
            <th>{{ __('app.student name') }}</th>
            <td>{{ $payment->student?->name }}</td>
        </tr>
        <tr>
            <th>{{ __('app.email') }}</th>
            <td>{{ $payment->student?->email }}</td>
        </tr>
        <tr>
            <th>{{ __('app.course') }}</th>
            <td>{{ $payment->items->first()?->course?->getLocalizationTitle() }}</td>
        </tr>
        <tr>
            <th>{{ __('app.order_id') }}</th>
            <td>{{ $payment->order_id }}</td>
        </tr>
        <tr>
            <th>{{ __('app.status') }}</th>
            <td>{{ ucfirst($payment->status) }}</td>
        </tr>
        <tr>
            <th>{{ __('app.amount') }}</th>
            <td>{{ "$".(new \App\Helpers\Helper)->formatNumber($payment->amount) }}</td>
        </tr>
        <tr>
            <th>{{ __('app.created at') }}</th>
            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>
</div>
