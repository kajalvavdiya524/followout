@extends('layouts.app')

@section('content')
    @if (is_null($content))
        <div id="about" class="Section Section--padding-md" style="margin-top: -15px;">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">About</div>
                        <p class="AboutSection__text">
                            Nothing here yet...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div id="about" class="Section Section--padding-md" style="margin-top: -15px;">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">About Us</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->about)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="terms" class="Section Section--padding-md Section--bg-gray">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Terms &amp; Conditions</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->terms)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="privacy" class="Section Section--padding-md">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Privacy Policy</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->privacy)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="subscription" class="Section Section--padding-md Section--bg-gray">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Subscription Agreement</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->ach)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="community_standards" class="Section Section--padding-md">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Community Standards</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->community_standards)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="become_followee" class="Section Section--padding-md Section--bg-gray">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">How to Become a Followee</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->become_followee)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="become_followhost" class="Section Section--padding-md">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">How to Become a Followhost</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->become_followhost)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
