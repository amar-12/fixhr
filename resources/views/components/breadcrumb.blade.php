<ol class="breadcrumb breadcrumb-arrow mt-3" style="background: none;">
    @foreach ($breadcrumbs as $breadcrumb)
        <li>
            @if ($loop->last)
                <span><strong>{{ $breadcrumb['title'] }}</strong></span>
            @else
                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
            @endif
        </li>
    @endforeach
</ol>
