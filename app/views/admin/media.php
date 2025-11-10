<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Media Library</h1>
                    <p class="text-muted">Manage your website images and files</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Upload Files
                </button>
            </div>
        </div>
    </div>
    
    <!-- Images Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>
                        Images (<?= count($images) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($images)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-images display-1 text-muted"></i>
                            <h5 class="mt-3">No images uploaded</h5>
                            <p class="text-muted">Upload your first image to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-3">
                                    <div class="card media-item">
                                        <div class="position-relative">
                                            <img src="<?= url($image['file_path']) ?>" 
                                                 class="card-img-top" 
                                                 style="height: 200px; object-fit: cover;"
                                                 alt="<?= htmlspecialchars($image['alt_text'] ?: $image['original_name']) ?>">
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm"
                                                            onclick="editMedia(<?= $image['id'] ?>, '<?= htmlspecialchars($image['title']) ?>', '<?= htmlspecialchars($image['alt_text']) ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm"
                                                            onclick="deleteMedia(<?= $image['id'] ?>, '<?= htmlspecialchars($image['original_name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2">
                                            <h6 class="card-title text-truncate" title="<?= htmlspecialchars($image['original_name']) ?>">
                                                <?= htmlspecialchars($image['title'] ?: $image['original_name']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?= number_format($image['file_size'] / 1024, 1) ?> KB
                                            </small>
                                            <div class="mt-1">
                                                <input type="text" class="form-control form-control-sm" 
                                                       value="<?= url($image['file_path']) ?>" 
                                                       readonly onclick="this.select()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Documents Section -->
    <?php if (!empty($documents)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-earmark me-2"></i>
                            Documents (<?= count($documents) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Type</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark me-2"></i>
                                                <?= htmlspecialchars($doc['title'] ?: $doc['original_name']) ?>
                                            </td>
                                            <td><?= number_format($doc['file_size'] / 1024, 1) ?> KB</td>
                                            <td>
                                                <span class="badge bg-secondary"><?= strtoupper(pathinfo($doc['original_name'], PATHINFO_EXTENSION)) ?></span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($doc['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= url($doc['file_path']) ?>" 
                                                       class="btn btn-outline-primary" 
                                                       target="_blank">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-primary"
                                                            onclick="editMedia(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['title']) ?>', '<?= htmlspecialchars($doc['alt_text']) ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger"
                                                            onclick="deleteMedia(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['original_name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="fileInput" class="form-label">Select Files</label>
                    <input type="file" class="form-control" id="fileInput" multiple 
                           accept="image/*,.pdf,.doc,.docx">
                    <div class="form-text">
                        Supported formats: Images (JPG, PNG, GIF, WebP, SVG), Documents (PDF, DOC, DOCX)
                        <br>Maximum file size: 5MB
                    </div>
                </div>
                <div id="uploadProgress" class="d-none">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadFiles()">Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Media Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTitle" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="editAltText" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="editAltText" name="alt_text">
                        <div class="form-text">Describe the image for accessibility</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditId = null;

function uploadFiles() {
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    
    if (files.length === 0) {
        alert('Please select files to upload');
        return;
    }
    
    const progressContainer = document.getElementById('uploadProgress');
    const progressBar = progressContainer.querySelector('.progress-bar');
    
    progressContainer.classList.remove('d-none');
    
    let uploadedCount = 0;
    const totalFiles = files.length;
    
    Array.from(files).forEach((file, index) => {
        const formData = new FormData();
        formData.append('file', file);
        
        fetch('<?= url('admin/media/upload') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            uploadedCount++;
            const progress = (uploadedCount / totalFiles) * 100;
            progressBar.style.width = progress + '%';
            
            if (uploadedCount === totalFiles) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            uploadedCount++;
            const progress = (uploadedCount / totalFiles) * 100;
            progressBar.style.width = progress + '%';
        });
    });
}

function editMedia(id, title, altText) {
    currentEditId = id;
    document.getElementById('editTitle').value = title || '';
    document.getElementById('editAltText').value = altText || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

function deleteMedia(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"?`)) {
        fetch(`<?= url('admin/media/') ?>${id}/delete`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete file: ' + data.message);
            }
        });
    }
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`<?= url('admin/media/') ?>${currentEditId}/update`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update file: ' + data.message);
        }
    });
});
</script>