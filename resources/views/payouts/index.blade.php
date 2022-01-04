@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Payouts
                            </div>
                        </div>
                        <div class="Block__body">
                            @foreach ($payouts as $payout)
                                <form class="Form form-horizontal" method="GET" action="#">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Recipient</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $payout->getFormattedRecipient() }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Status</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $payout->item_status ? ucfirst(mb_strtolower($payout->item_status)) : ($payout->batch_status ? ucfirst(mb_strtolower($payout->batch_status)) : 'Ready to send') }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Amount</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $payout->getFormattedAmountWithFees() }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Reason</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $payout->getFormattedItemType() }}" disabled>
                                        </div>
                                    </div>

                                    @if ($payout->notes)
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Notes</label>

                                            <div class="col-md-8">
                                                <input class="form-control" value="{{ $payout->notes }}" disabled>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Created At</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $payout->created_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }} ({{ $payout->created_at->diffForHumans() }})" disabled>
                                        </div>
                                    </div>

                                    @if ($payout->isSent())
                                        <div class="form-group">
                                            <div class="col-md-6 col-md-offset-4">
                                                <a href="{{ route('payouts.show', ['payout' => $payout->id]) }}" class="Button Button--primary">
                                                    View details
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <div class="col-md-6 col-md-offset-4">
                                                <a href="{{ route('payouts.approve', ['payout' => $payout->id]) }}" class="Button Button--primary">
                                                    Send
                                                </a>
                                                <a href="{{ route('payouts.cancel', ['payout' => $payout->id]) }}" class="Button Button--danger">
                                                    Cancel
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($loop->last)
                                        <hr>
                                    @else
                                        <hr>
                                        <br>
                                    @endif
                                </form>
                            @endforeach
                            <form class="Form form-horizontal" method="GET" action="#">
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <a href="{{ route('payouts.create') }}" class="Button Button--danger">
                                            New payout
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @unless ($payouts->currentPage() === 1 && ($payouts->currentPage() === $payouts->lastPage() || $payouts->lastPage() === 0))
                            <div class="Pagination Pagination--inside-block">
                                {{ $payouts->links() }}
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
