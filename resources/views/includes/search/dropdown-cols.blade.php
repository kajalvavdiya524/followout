@php
    $chunkSize = ceil($followoutCategories->count() / 2);
@endphp

@foreach ($followoutCategories->chunk($chunkSize) as $followoutCategoriesChunk)
    <div class="pull-left" style="width: 50%;">
        <ul class="multi-column-dropdown">
            @foreach ($followoutCategoriesChunk as $followoutCategory)
                <li>
                    @if ($for === 'followouts')
                        <a href="{{ route('followouts.index', ['category' => $followoutCategory->id]) }}">
                            {{ $followoutCategory->name }}
                        </a>
                    @elseif ($for === 'users')
                        <a href="{{ route('search.users', ['experience' => $followoutCategory->id]) }}">
                            {{ $followoutCategory->name }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
