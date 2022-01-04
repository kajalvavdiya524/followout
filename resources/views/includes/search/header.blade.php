@if (auth()->guest() || (auth()->check() && !auth()->user()->isUnregistered() && auth()->user()->isActivated()))
    <div class="search-nav">
        <div id="search-dropdown" class="dropdown">
            <a href="javascript:void(0);" class="Header__nav-item dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-fw fa-search"></i>
            </a>
            <ul class="Header__dropdown Header__dropdown--search dropdown-menu multi-column columns-2">
                <div class="SearchTabs">
                    <div id="search-tab-link-followouts" class="SearchTabs__link SearchTabs__link--active" data-tab-for="followouts">Followouts</div>
                    <div id="search-tab-link-users" class="SearchTabs__link" data-tab-for="users">Followout Community</div>
                </div>
                <div id="search-for-followouts-dropdown" class="SearchTabs__tab">
                    <form action="{{ route('search.followouts') }}" method="GET" style="margin-bottom: 15px;">
                        <div class="input-group">
                            <input type="text" class="form-control" name="title" placeholder="Followout name">
                            <span class="input-group-btn">
                                <button class="Button Button--input-group Button--primary" type="submit">Search</button>
                            </span>
                        </div>
                    </form>

                    @include('includes.search.dropdown-cols', ['for' => 'followouts'])
                </div>
                <div id="search-for-users-dropdown" class="SearchTabs__tab" style="display: none;">
                    @if (auth()->guest())
                        <a href="{{ route('register') }}">Sign up</a> or <a href="{{ route('login') }}">log in</a> to join the Followout community.
                    @else
                        <form action="{{ route('search.users') }}" method="GET" style="margin-bottom: 15px;">
                            <div class="input-group">
                                <input type="text" class="form-control" name="name" placeholder="User name">
                                <span class="input-group-btn">
                                    <button class="Button Button--input-group Button--primary" type="submit">Search</button>
                                </span>
                            </div>
                        </form>

                        @include('includes.search.dropdown-cols', ['for' => 'users'])
                    @endif
                </div>
            </ul>
        </div>
    </div>
@endif

@push('scripts-footer')
    <script>
        var activeSearchTabName = 'followouts';

        function showSearchTab(tabName) {
            $('#search-dropdown .dropdown-menu').addClass('keepopen');

            if (activeSearchTabName === tabName) {
                return false;
            }

            if (tabName === 'users') {
                activeSearchTabName = 'users';
                $('#search-tab-link-users').addClass('SearchTabs__link--active');
                $('#search-tab-link-followouts').removeClass('SearchTabs__link--active');
                $('#search-for-followouts-dropdown').hide();
                $('#search-for-users-dropdown').show();
            } else if (tabName === 'followouts') {
                activeSearchTabName = 'followouts';
                $('#search-tab-link-followouts').addClass('SearchTabs__link--active');
                $('#search-tab-link-users').removeClass('SearchTabs__link--active');
                $('#search-for-followouts-dropdown').show();
                $('#search-for-users-dropdown').hide();
            }
        }

        $(document).on('click', '.dropdown-menu', function(e) {
            if ($(this).hasClass('keepopen')) { e.stopPropagation(); }
        });

        $(document).on('click', '.SearchTabs__link', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var tabName = $(e.target).data('tab-for');

            showSearchTab(tabName);
        });
    </script>
@endpush
