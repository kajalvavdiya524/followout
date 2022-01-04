@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Sales representatives
                            </div>
                        </div>
                        <div class="Block__body">
                        @if ($salesReps->count() > 0)
                            <div class="Form form-horizontal">
                        @endif
                            @forelse ($salesReps as $salesRep)
                                <form id="{{ $salesRep->id }}" class="Form form-horizontal" method="POST" action="{{ route('sales-reps.destroy', ['id' => $salesRep->id]) }}">
                                    {{ method_field('DELETE') }}
                                    {{ csrf_field() }}

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Name</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $salesRep->full_name }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Email</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $salesRep->email }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Phone</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $salesRep->phone }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Code</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $salesRep->code }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Code with Promo</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $salesRep->promo_code }}" disabled>
                                        </div>
                                    </div>

                                    @unless ($salesRep->accepted === true)
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Agreement</label>

                                            <div class="col-md-8">
                                                <div class="text-muted" style="line-height: 40px;">
                                                    This Sales Representative needs to accept the <a href="{{ route('sales-rep-agreement', ['hash' => $salesRep->hash]) }}" target="_blank">Sales Representative Agreement</a>.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Agreement URL</label>

                                            <div class="col-md-8">
                                                <input class="form-control" value="{{ route('sales-rep-agreement', ['hash' => $salesRep->hash]) }}" readonly>
                                            </div>
                                        </div>
                                    @endunless

                                    @if ($salesRep->hasInvitedUsers())
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Referred users</label>

                                            <div class="col-md-8">
                                                @foreach ($salesRep->users as $user)
                                                    <div>
                                                        <a target="_blank" href="{{ route('users.show', ['user' => $user->id]) }}">{{ $user->name }}</a>
                                                        <br>
                                                        <div class="text-muted">
                                                            {{ $user->subscribed() ? 'subscribed' : 'on a free plan' }} / total spent: ${{ $user->totalSpent(true) }}
                                                        </div>
                                                    </div>

                                                    @if (!$loop->last)
                                                        <hr>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <a href="{{ route('sales-reps.destroy', ['id' => $salesRep->id]) }}" class="Button Button--danger" data-method="DELETE" data-token="{{ csrf_token() }}" data-confirm="Are you sure you want to delete {{ $salesRep->name }}?">
                                                Delete sales representative
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <hr>
                            @empty
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4">
                                        <div class="text-muted" style="padding: 30px 0;">Nothing here yet.</div>
                                    </div>
                                </div>
                            @endforelse
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <a href="{{ route('sales-reps.create') }}" class="Button Button--primary">
                                        New sales representative
                                    </a>
                                </div>
                            </div>
                            @if ($salesReps->count() > 0)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
