@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                About Page
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('app.pages.about.update') }}">
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('about') ? 'has-error' : '' }}">
                                    <label for="about" class="col-md-4 control-label is-required">About us</label>

                                    <div class="col-md-8">
                                        <textarea id="about" name="about" rows="10" class="form-control" required>{{ old('about') ? old('about') : (isset($data['about']) ? $data['about']->about : null) }}</textarea>

                                        @if ($errors->has('about'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('about') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('terms') ? 'has-error' : '' }}">
                                    <label for="terms" class="col-md-4 control-label is-required">Terms &amp; Conditions</label>

                                    <div class="col-md-8">
                                        <textarea id="terms" name="terms" rows="10" class="form-control" required>{{ old('terms') ? old('terms') : (isset($data['about']) ? $data['about']->terms : null) }}</textarea>

                                        @if ($errors->has('terms'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('terms') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('privacy') ? 'has-error' : '' }}">
                                    <label for="privacy" class="col-md-4 control-label is-required">Privacy Policy</label>

                                    <div class="col-md-8">
                                        <textarea id="privacy" name="privacy" rows="10" class="form-control" required>{{ old('privacy') ? old('privacy') : (isset($data['about']) ? $data['about']->privacy : null) }}</textarea>

                                        @if ($errors->has('privacy'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('privacy') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('ach') ? 'has-error' : '' }}">
                                    <label for="ach" class="col-md-4 control-label is-required">Subscription Agreement</label>

                                    <div class="col-md-8">
                                        <textarea id="ach" name="ach" rows="10" class="form-control" required>{{ old('ach') ? old('ach') : (isset($data['about']) ? $data['about']->ach : null) }}</textarea>

                                        @if ($errors->has('ach'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('ach') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('community_standards') ? 'has-error' : '' }}">
                                    <label for="community_standards" class="col-md-4 control-label is-required">Community Standards</label>

                                    <div class="col-md-8">
                                        <textarea id="community_standards" name="community_standards" rows="10" class="form-control" required>{{ old('community_standards') ? old('community_standards') : (isset($data['about']) ? $data['about']->community_standards : null) }}</textarea>

                                        @if ($errors->has('community_standards'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('community_standards') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('become_followee') ? 'has-error' : '' }}">
                                    <label for="become_followee" class="col-md-4 control-label is-required">How to become a Followee</label>

                                    <div class="col-md-8">
                                        <textarea id="become_followee" name="become_followee" rows="10" class="form-control" required>{{ old('become_followee') ? old('become_followee') : (isset($data['about']) ? $data['about']->become_followee : null) }}</textarea>

                                        @if ($errors->has('become_followee'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('become_followee') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('become_followhost') ? 'has-error' : '' }}">
                                    <label for="become_followhost" class="col-md-4 control-label is-required">How to become a Followhost</label>

                                    <div class="col-md-8">
                                        <textarea id="become_followhost" name="become_followhost" rows="10" class="form-control" required>{{ old('become_followhost') ? old('become_followhost') : (isset($data['about']) ? $data['about']->become_followhost : null) }}</textarea>

                                        @if ($errors->has('become_followhost'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('become_followhost') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Sales Representative Agreement
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('app.pages.sales-rep-agreement.update') }}">
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('agreement') ? 'has-error' : '' }}">
                                    <label for="agreement" class="col-md-4 control-label is-required">Agreement</label>

                                    <div class="col-md-8">
                                        <textarea id="agreement" name="agreement" rows="10" class="form-control" required>{{ old('agreement') ? old('agreement') : (isset($data['sales_rep_agreement']) ? $data['sales_rep_agreement']->agreement : null) }}</textarea>

                                        @if ($errors->has('agreement'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('agreement') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Landing Page
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('app.pages.landing.update') }}">
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('gallery_picture_1_url') ? 'has-error' : '' }}">
                                    <label for="gallery_picture_1_url" class="col-md-4 control-label">Gallery Picture #1</label>

                                    <div class="col-md-8">
                                        <input id="gallery_picture_1_url" type="text" class="form-control" name="gallery_picture_1_url" value="{{ old('gallery_picture_1_url') ? old('gallery_picture_1_url') : (isset($data['landing_hero']) ? $data['landing_hero']->gallery_picture_1_url : null)  }}" placeholder="{{ url('/img/landing-hero-gallery-1.jpg') }}">

                                        @if ($errors->has('gallery_picture_1_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('gallery_picture_1_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('gallery_picture_2_url') ? 'has-error' : '' }}">
                                    <label for="gallery_picture_2_url" class="col-md-4 control-label">Gallery Picture #2</label>

                                    <div class="col-md-8">
                                        <input id="gallery_picture_2_url" type="text" class="form-control" name="gallery_picture_2_url" value="{{ old('gallery_picture_2_url') ? old('gallery_picture_2_url') : (isset($data['landing_hero']) ? $data['landing_hero']->gallery_picture_2_url : null)  }}" placeholder="{{ url('/img/landing-hero-gallery-2.jpg') }}">

                                        @if ($errors->has('gallery_picture_2_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('gallery_picture_2_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('gallery_picture_3_url') ? 'has-error' : '' }}">
                                    <label for="gallery_picture_3_url" class="col-md-4 control-label">Gallery Picture #3</label>

                                    <div class="col-md-8">
                                        <input id="gallery_picture_3_url" type="text" class="form-control" name="gallery_picture_3_url" value="{{ old('gallery_picture_3_url') ? old('gallery_picture_3_url') : (isset($data['landing_hero']) ? $data['landing_hero']->gallery_picture_3_url : null)  }}" placeholder="{{ url('/img/landing-hero-gallery-3.jpg') }}">

                                        @if ($errors->has('gallery_picture_3_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('gallery_picture_3_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('gallery_video_url') ? 'has-error' : '' }}">
                                    <label for="gallery_video_url" class="col-md-4 control-label">Gallery Video URL (center)</label>

                                    <div class="col-md-8">
                                        <input id="gallery_video_url" type="text" class="form-control" name="gallery_video_url" value="{{ old('gallery_video_url') ? old('gallery_video_url') : (isset($data['landing_hero']) ? $data['landing_hero']->gallery_video_url : null)  }}" placeholder="{{ Storage::url('landing-marketing-video.mp4') }}">

                                        @if ($errors->has('gallery_video_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('gallery_video_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('screenshot_1_video_url') ? 'has-error' : '' }}">
                                    <label for="screenshot_1_video_url" class="col-md-4 control-label">App Screenshot Video URL (left)</label>

                                    <div class="col-md-8">
                                        <input id="screenshot_1_video_url" type="text" class="form-control" name="screenshot_1_video_url" value="{{ old('screenshot_1_video_url') ? old('screenshot_1_video_url') : (isset($data['landing_hero']) ? $data['landing_hero']->screenshot_1_video_url : null)  }}" placeholder="{{ Storage::url('iphone-marketing-video-1.mp4') }}">

                                        @if ($errors->has('screenshot_1_video_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('screenshot_1_video_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('screenshot_2_video_url') ? 'has-error' : '' }}">
                                    <label for="screenshot_2_video_url" class="col-md-4 control-label">App Screenshot Video URL (right)</label>

                                    <div class="col-md-8">
                                        <input id="screenshot_2_video_url" type="text" class="form-control" name="screenshot_2_video_url" value="{{ old('screenshot_2_video_url') ? old('screenshot_2_video_url') : (isset($data['landing_hero']) ? $data['landing_hero']->screenshot_2_video_url : null)  }}" placeholder="{{ Storage::url('iphone-marketing-video-2.mp4') }}">

                                        @if ($errors->has('screenshot_2_video_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('screenshot_2_video_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                University Page
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('app.pages.university.update') }}">
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('marketing_video_title') ? 'has-error' : '' }}">
                                    <label for="marketing_video_title" class="col-md-4 control-label">Marketing video section title</label>

                                    <div class="col-md-8">
                                        <input id="marketing_video_title" type="text" class="form-control" name="marketing_video_title" value="{{ old('marketing_video_title') ? old('marketing_video_title') : (isset($data['university']) ? $data['university']->marketing_video_title : null)  }}" placeholder="View Our Marketing Video!">

                                        @if ($errors->has('marketing_video_title'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('marketing_video_title') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('marketing_video_url') ? 'has-error' : '' }}">
                                    <label for="marketing_video_url" class="col-md-4 control-label">Marketing video URL</label>

                                    <div class="col-md-8">
                                        <input id="marketing_video_url" type="text" class="form-control" name="marketing_video_url" value="{{ old('marketing_video_url') ? old('marketing_video_url') : (isset($data['university']) ? $data['university']->marketing_video_url : null)  }}" placeholder="{{ Storage::url('admin/marketing-video.mp4') }}">

                                        @if ($errors->has('marketing_video_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('marketing_video_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('marketing_video_thumb_url') ? 'has-error' : '' }}">
                                    <label for="marketing_video_thumb_url" class="col-md-4 control-label">Marketing video thumbnail URL</label>

                                    <div class="col-md-8">
                                        <input id="marketing_video_thumb_url" type="text" class="form-control" name="marketing_video_thumb_url" value="{{ old('marketing_video_thumb_url') ? old('marketing_video_thumb_url') : (isset($data['university']) ? $data['university']->marketing_video_thumb_url : null)  }}" placeholder="{{ Storage::url('admin/marketing-video-thumb.jpg') }}">

                                        @if ($errors->has('marketing_video_thumb_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('marketing_video_thumb_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Users
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('app.pages.users.update') }}">
                                {{ method_field('PUT') }}
                                @csrf

                                <div class="form-group {{ $errors->has('anonymous_user_avatar_url') ? 'has-error' : '' }}">
                                    <label for="anonymous_user_avatar_url" class="col-md-4 control-label">Anonymous user avatar URL</label>

                                    <div class="col-md-8">
                                        <input id="anonymous_user_avatar_url" type="text" class="form-control" name="anonymous_user_avatar_url" value="{{ old('anonymous_user_avatar_url') ? old('anonymous_user_avatar_url') : (isset($data['users']) ? $data['users']->anonymous_user_avatar_url : null)  }}" placeholder="{{ Storage::url('users/anonymous-user-avatar.jpg') }}">

                                        @if ($errors->has('anonymous_user_avatar_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('anonymous_user_avatar_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
