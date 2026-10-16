<?php

trait MediaActions {
    // MEDIA MANAGEMENT
    public function media() {
        $this->userModel->requireAuth();
        
        $images = $this->mediaModel->getImages();
        $documents = $this->mediaModel->getDocuments();
        
        $data = [
            'title' => 'Media Library - Admin',
            'page' => 'admin-media',
            'images' => $images,
            'documents' => $documents
        ];
        
        $this->render('admin/media', 'admin', $data);
    }
    
    public function uploadMedia() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
            return;
        }
        
        try {
            $currentUser = $this->userModel->getCurrentUser();
            $fileId = $this->mediaModel->uploadFile($_FILES['file'], $currentUser['id']);
            
            $file = $this->mediaModel->find($fileId);
            $this->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'file' => $file
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function updateMedia($id) {
        $this->userModel->requireAuth();

        $isAjax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $this->redirect('admin/media');
            return;
        }

        $file = $this->mediaModel->find($id);
        if (!$file) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'File not found.');
            $this->redirect('admin/media');
            return;
        }

        $updateData = [
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'title' => trim($_POST['title'] ?? '')
        ];

        $updated = $this->mediaModel->updateFile($id, $updateData);

        if ($updated) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'File updated successfully.',
                    'file' => $this->mediaModel->find($id)
                ]);
                return;
            }

            $this->setFlash('success', 'File updated successfully!');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update file.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update file.');
        }

        $this->redirect('admin/media');
    }

    public function deleteMedia($id) {
        $this->userModel->requireAuth();

        $isAjax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $this->redirect('admin/media');
            return;
        }

        $file = $this->mediaModel->find($id);
        if (!$file) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'File not found.');
            $this->redirect('admin/media');
            return;
        }

        if ($this->mediaModel->deleteFile($id)) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'File deleted successfully.'
                ]);
                return;
            }

            $this->setFlash('success', 'File deleted successfully!');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete file.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to delete file.');
        }

        $this->redirect('admin/media');
    }
}
