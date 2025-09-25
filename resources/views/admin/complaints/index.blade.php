@extends('admin.layouts.admin-layout')

@section('title', 'Complaints')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Complaints</h2>
        @can('Create Complaint')
            <button class="btn btn-primary" id="addComplaintBtn">
                <i class="fas fa-plus"></i> Add Complaint
            </button>
        @endcan
    </div>

    <table class="table table-striped" id="complaintsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Title</th>
                <th>Status</th>
                <th>Created At</th>
                @canany(['Edit Complaint', 'Delete Complaint'])
                    <th>Actions</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($complaints as $complaint)
                <tr id="complaint-{{ $complaint->id }}">
                    <td>{{ $complaint->id }}</td>
                    <td>{{ $complaint->user->name ?? 'N/A' }}</td>
                    <td class="complaint-title">{{ $complaint->title }}</td>
                    <td>{{ ucfirst($complaint->status) }}</td>
                    <td>{{ $complaint->created_at->format('Y-m-d') }}</td>
                    @canany(['Edit Complaint', 'Delete Complaint'])
                        <td>
                            @can('Edit Complaint')
                                <button class="btn btn-sm btn-info editBtn" data-id="{{ $complaint->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('Delete Complaint')
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $complaint->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="complaintForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="complaintModalLabel">Add Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="complaintId">

                    <div class="mb-3">
                        <label for="complaintTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="complaintTitle" name="title" required>
                        <div class="invalid-feedback" id="titleError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="complaintDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="complaintDescription" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="complaintStatus" class="form-label">Status</label>
                        <select class="form-control" id="complaintStatus" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <div class="invalid-feedback" id="statusError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveComplaintBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('complaintModal'));
    const form = document.getElementById('complaintForm');
    const complaintId = document.getElementById('complaintId');
    const title = document.getElementById('complaintTitle');
    const description = document.getElementById('complaintDescription');
    const status = document.getElementById('complaintStatus');

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Add
    document.getElementById('addComplaintBtn')?.addEventListener('click', () => {
        form.reset();
        complaintId.value = '';
        document.getElementById('complaintModalLabel').textContent = 'Add Complaint';
        modal.show();
    });

    // Edit
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            fetch(`/admin/complaints/${btn.dataset.id}/edit`)
                .then(res => res.json())
                .then(data => {
                    complaintId.value = data.id;
                    title.value = data.title;
                    description.value = data.description;
                    status.value = data.status;
                    document.getElementById('complaintModalLabel').textContent = 'Edit Complaint';
                    modal.show();
                });
        });
    });

    // Save
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = complaintId.value;
        const url = id ? `/admin/complaints/${id}` : '/admin/complaints';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                title: title.value,
                description: description.value,
                status: status.value
            })
        })
        .then(async res => {
            if (res.status === 422) {
                const data = await res.json();
                // simple error display
                if (data.errors.title) title.classList.add('is-invalid');
                if (data.errors.description) description.classList.add('is-invalid');
                if (data.errors.status) status.classList.add('is-invalid');
            } else {
                return res.json();
            }
        })
        .then(data => {
            if (data) {
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
                modal.hide();
            }
        });
    });

    // Delete
    document.addEventListener('click', e => {
        if (e.target.closest('.deleteBtn')) {
            const id = e.target.closest('.deleteBtn').dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the complaint!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`/admin/complaints/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            document.getElementById(`complaint-${id}`).remove();
                        });
                    });
                }
            });
        }
    });
});
</script>
@endpush
