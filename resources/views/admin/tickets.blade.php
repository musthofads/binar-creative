@extends('layouts.app')

@section('title', 'Support Tickets - Admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">
                <i class="bi bi-ticket"></i> Support Tickets
            </h2>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->email }}</td>
                                <td>{{ $ticket->subject }}</td>
                                <td>
                                    <span class="badge bg-{{
                                        $ticket->status === 'PENDING' ? 'warning' :
                                        ($ticket->status === 'IN_PROGRESS' ? 'info' :
                                        ($ticket->status === 'RESOLVED' ? 'success' : 'secondary'))
                                    }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-info" data-bs-toggle="modal"
                                                data-bs-target="#ticketModal{{ $ticket->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-warning" onclick="updateStatus({{ $ticket->id }}, 'IN_PROGRESS')">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <button class="btn btn-success" onclick="updateStatus({{ $ticket->id }}, 'RESOLVED')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal for ticket details -->
                            <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Ticket #{{ $ticket->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>From:</strong> {{ $ticket->email }}</p>
                                            <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
                                            <p><strong>Message:</strong></p>
                                            <p>{{ $ticket->message }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No tickets found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function updateStatus(ticketId, status) {
        try {
            await axios.patch(`/api/admin/tickets/${ticketId}`, { status });
            alert('Ticket status updated');
            location.reload();
        } catch (error) {
            console.error('Error updating ticket:', error);
            alert('Failed to update ticket');
        }
    }
</script>
@endsection
