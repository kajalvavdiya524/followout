<div class="StatsGrid">
    @if ($followout->isReposted())
        <div class="StatsGridItem">
            <div class="StatsGridItem__count">
                {{ number_format($followout->getFolloweesCount(true), 0) }}
            </div>
            <div class="StatsGridItem__description">
                {{ Str::plural('followee', $followout->getFolloweesCount(true)) }} invited
            </div>
        </div>
    @else
        <div class="StatsGridItem">
            <div class="StatsGridItem__count">
                {{ number_format($followout->getFolloweesCount(), 0) }}
            </div>
            <div class="StatsGridItem__description">
                {{ Str::plural('followee', $followout->getFolloweesCount()) }}
            </div>
        </div>
    @endif
    @unless ($followout->isReposted())
        <div class="StatsGridItem">
            <div class="StatsGridItem__count">
                {{ number_format($followout->getRepostedFollowoutsFolloweesCount(), 0) }}
            </div>
            <div class="StatsGridItem__description">
                {{ Str::plural('followee', $followout->getRepostedFollowoutsFolloweesCount()) }} from reposts
            </div>
        </div>
        <div class="StatsGridItem">
            <div class="StatsGridItem__count">
                {{ number_format($followout->getTotalFolloweesCount(), 0) }}
            </div>
            <div class="StatsGridItem__description">
                {{ Str::plural('followee', $followout->getTotalFolloweesCount()) }} total
            </div>
        </div>
    @endunless
</div>

<div class="StatsGrid">
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->getAttendeesCount(), 0) }}
        </div>
        <div class="StatsGridItem__description">
            followed out
        </div>
    </div>
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->getRepostedFollowoutsAttendeesCount(), 0) }}
        </div>
        <div class="StatsGridItem__description">
            reposts followed out
        </div>
    </div>
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->getTotalAttendeesCount(), 0) }}
        </div>
        <div class="StatsGridItem__description">
            total followed out
        </div>
    </div>
</div>

<div class="StatsGrid">
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->views_count, 0) }}
        </div>
        <div class="StatsGridItem__description">
            {{ Str::plural('view', $followout->views_count) }}
        </div>
    </div>
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->getRepostedFollowoutsViewsCount(), 0) }}
        </div>
        <div class="StatsGridItem__description">
            {{ Str::plural('view', $followout->getRepostedFollowoutsViewsCount()) }} for reposts
        </div>
    </div>
    <div class="StatsGridItem">
        <div class="StatsGridItem__count">
            {{ number_format($followout->total_views_count) }}
        </div>
        <div class="StatsGridItem__description">
            total {{ Str::plural('view', $followout->total_views_count) }}
        </div>
    </div>
</div>
