@extends('layouts.app')

@section('title', 'Sessions - Admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">
                <i class="bi bi-collection"></i> Photo Sessions
            </h2>

            <div id="sessionsTable">
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadSessions() {
        try {
            const response = await axios.get('/api/admin/sessions');

            if (response.data.success) {
                displaySessions(response.data.sessions);
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            document.getElementById('sessionsTable').innerHTML =
                '<p class="text-danger text-center">Failed to load sessions</p>';
        }
    }

    function displaySessions(sessions) {
        const container = document.getElementById('sessionsTable');

        if (sessions.length === 0) {
            container.innerHTML = '<p class="text-center">No sessions found</p>';
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>Photo Count</th>
                            <th>Last Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${sessions.map(session => `
                            <tr>
                                <td><code>${session.session_id}</code></td>
                                <td><span class="badge bg-primary">${session.photo_count}</span></td>
                                <td>${new Date(session.last_activity).toLocaleString()}</td>
                                <td>
                                    <a href="/preview?session=${session.session_id}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    loadSessions();
</script>
@endsection
