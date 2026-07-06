@extends('superadmin.layout.master')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Feedback List</h2>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SNo.</th>
                            <th>Business Name</th>
                            <th>Employee Name</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Attachment</th>
                            <th>Date Posted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $feedback)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $feedback->business->b_name ?? 'N/A' }}</td>
                            <td>{{ $feedback->employee->emp_full_name ?? 'N/A' }}</td>
                            <td>{{ $feedback->f_title }}</td>
                            <td>
                                <div style="max-width: 200px;">
                                    {{ Str::limit($feedback->f_description, 100) }}
                                    @if(strlen($feedback->f_description) > 100)
                                        <br><button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $feedback->id }}">
                                            Read More
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($feedback->f_attachment)
                                    <a href="{{ asset('uploads/' . $feedback->f_attachment) }}" target="_blank">View</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $feedback->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No feedback found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Detail Modals -->
@foreach($feedbacks as $feedback)
    @if(strlen($feedback->f_description) > 100)
    <div class="modal fade" id="feedbackModal{{ $feedback->id }}" tabindex="-1" aria-labelledby="feedbackModalLabel{{ $feedback->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel{{ $feedback->id }}">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Business:</strong> {{ $feedback->business->b_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Employee:</strong> {{ $feedback->employee->emp_full_name ?? 'N/A' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Title:</strong> {{ $feedback->f_title }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {{ $feedback->f_description }}
                            </div>
                        </div>
                    </div>
                    @if($feedback->f_attachment)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Attachment:</strong>
                            <div class="mt-2">
                                <a href="{{ asset('uploads/' . $feedback->f_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download Attachment
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Date Posted:</strong> {{ $feedback->created_at->format('d M Y, h:i A') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection 