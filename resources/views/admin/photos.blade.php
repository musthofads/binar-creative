@extends('layouts.app')

@section('title', 'Manage Photos - Admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">
                <i class="bi bi-images"></i> Manage Photos
            </h2>

            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#single">Single Photos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#strips">Strips</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="single" class="tab-pane fade show active">
                    <div id="singlePhotosTable">
                        <div class="text-center">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>

                <div id="strips" class="tab-pane fade">
                    <div id="stripPhotosTable">
                        <div class="text-center">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadPhotos() {
        try {
            const response = await axios.get('/api/admin/photos-admin');

            if (response.data.success) {
                displaySinglePhotos(response.data.single_photos.data);
                displayStripPhotos(response.data.strip_photos.data);
            }
        } catch (error) {
            console.error('Error loading photos:', error);
        }
    }

    function displaySinglePhotos(photos) {
        const container = document.getElementById('singlePhotosTable');

        if (photos.length === 0) {
            container.innerHTML = '<p class="text-center">No photos found</p>';
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Session ID</th>
                            <th>User</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${photos.map(photo => `
                            <tr>
                                <td><img src="${photo.url}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;"></td>
                                <td><small>${photo.session_id}</small></td>
                                <td>${photo.user ? photo.user.name : 'Guest'}</td>
                                <td>${new Date(photo.created_at).toLocaleString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deletePhoto(${photo.id}, 'single')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    function displayStripPhotos(photos) {
        const container = document.getElementById('stripPhotosTable');

        if (photos.length === 0) {
            container.innerHTML = '<p class="text-center">No strips found</p>';
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Session ID</th>
                            <th>User</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${photos.map(photo => `
                            <tr>
                                <td><img src="${photo.url}" style="width: 120px; height: 60px; object-fit: cover; border-radius: 5px;"></td>
                                <td><small>${photo.session_id}</small></td>
                                <td>${photo.user ? photo.user.name : 'Guest'}</td>
                                <td>${new Date(photo.created_at).toLocaleString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deletePhoto(${photo.id}, 'strip')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    async function deletePhoto(id, type) {
        if (!confirm('Are you sure you want to delete this photo?')) return;

        try {
            await axios.delete(`/api/admin/photos/${id}?type=${type}`);
            alert('Photo deleted successfully');
            loadPhotos();
        } catch (error) {
            console.error('Error deleting photo:', error);
            alert('Failed to delete photo');
        }
    }

    loadPhotos();
</script>
@endsection
