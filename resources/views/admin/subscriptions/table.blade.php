@foreach($subscriptionAllData as $subscription)
<tr>
    <td class="sno-cell">{{ $loop->iteration }}</td>

    <td class="month-cell">
        {{ optional($subscription->start_date)->format('d M Y') }} -
        {{ optional($subscription->end_date)->format('d M Y') }}
    </td>

    <td class="number-highlight">
        {{ $subscription->active_count }}
    </td>

    <td class="inactive-number">
        {{ $subscription->inactive_count }}
    </td>

    <td>
        {{ $subscription->active_count + $subscription->inactive_count }}
    </td>
</tr>
@endforeach

<tr>
    <td colspan="5" class="text-center">
        {{ $subscriptionAllData->links() }}
    </td>
</tr>
