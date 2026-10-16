<?php

trait TestimonialsActions {
    // TESTIMONIALS MANAGEMENT
    public function testimonials() {
        $this->userModel->requireAuth();

        $records = $this->testimonialModel->getAllOrdered();
        $fallbackImage = $this->getTestimonialPlaceholderImage();

        $payload = array_map(function (array $testimonial) use ($fallbackImage) {
            return $this->formatTestimonialForManager($testimonial, $fallbackImage);
        }, $records);

        $data = [
            'title' => 'Testimonials Management - Admin',
            'page' => 'admin-testimonials',
            'testimonials' => $records,
            'testimonials_json' => htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'testimonial_placeholder' => $fallbackImage,
            'testimonial_routes' => [
                'create' => url('admin/testimonials'),
                'update_base' => url('admin/testimonials'),
                'delete_base' => url('admin/testimonials'),
                'reorder' => url('admin/testimonials/order')
            ]
        ];

        $this->render('admin/testimonials', 'admin', $data);
    }

    public function storeTestimonial() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $input = $this->sanitizeTestimonialInput($_POST);
        $errors = $this->validateTestimonialInput($input);

        [$input, $uploadErrors] = $this->handleTestimonialImageUpload($input);
        $errors = array_merge($errors, $uploadErrors);

        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted errors.',
                    'errors' => $errors
                ], 422);
            }

            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('admin/testimonials');
            return;
        }

        $input['sort_order'] = $this->testimonialModel->getNextSortOrder();

        try {
            $id = $this->testimonialModel->create($input);
        } catch (Exception $exception) {
            error_log('Failed to create testimonial: ' . $exception->getMessage());

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to create testimonial. Please try again.'
                ], 500);
            }

            $this->setFlash('error', 'Failed to create testimonial.');
            $this->redirect('admin/testimonials');
            return;
        }

        $record = $this->testimonialModel->find($id);
        $formatted = $this->formatTestimonialForManager($record ?? $input + ['id' => $id], $this->getTestimonialPlaceholderImage());

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Testimonial added successfully.',
                'testimonial' => $formatted
            ]);
            return;
        }

        $this->setFlash('success', 'Testimonial created successfully.');
        $this->redirect('admin/testimonials');
    }

    public function updateTestimonial($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $existing = $this->testimonialModel->find($id);
        if (!$existing) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Testimonial not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'Testimonial not found.');
            $this->redirect('admin/testimonials');
            return;
        }

        $input = $this->sanitizeTestimonialInput($_POST, $existing);
        $errors = $this->validateTestimonialInput($input);

        [$input, $uploadErrors] = $this->handleTestimonialImageUpload($input, $existing);
        $errors = array_merge($errors, $uploadErrors);

        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted errors.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('admin/testimonials');
            return;
        }

        // Preserve existing sort order unless explicitly provided
        if (!isset($input['sort_order'])) {
            $input['sort_order'] = (int)($existing['sort_order'] ?? 0);
        }

        try {
            $this->testimonialModel->update($id, $input);
        } catch (Exception $exception) {
            error_log('Failed to update testimonial: ' . $exception->getMessage());

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update testimonial. Please try again.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update testimonial.');
            $this->redirect('admin/testimonials');
            return;
        }

        $record = $this->testimonialModel->find($id);
        $formatted = $this->formatTestimonialForManager($record ?? $existing, $this->getTestimonialPlaceholderImage());

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Testimonial updated successfully.',
                'testimonial' => $formatted
            ]);
            return;
        }

        $this->setFlash('success', 'Testimonial updated successfully.');
        $this->redirect('admin/testimonials');
    }
    
    public function deleteTestimonial($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }
        
        $deleted = $this->testimonialModel->delete($id);

        if ($this->isAjaxRequest()) {
            if ($deleted) {
                $this->json([
                    'success' => true,
                    'message' => 'Testimonial removed.'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete testimonial.'
                ], 500);
            }
            return;
        }

        if ($deleted) {
            $this->setFlash('success', 'Testimonial deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete testimonial.');
        }
        
        $this->redirect('admin/testimonials');
    }

    public function reorderTestimonials() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $payload = $this->parseJsonInput();
        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];

        $normalized = [];
        foreach ($items as $index => $item) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'sort_order' => isset($item['sort_order']) ? (int)$item['sort_order'] : ($index + 1),
                'is_visible' => !empty($item['is_visible']) ? 1 : 0
            ];
        }

        if (empty($normalized)) {
            $this->json([
                'success' => false,
                'message' => 'No testimonial order changes detected.'
            ], 422);
            return;
        }

        try {
            $this->testimonialModel->reorder($normalized);
        } catch (Exception $exception) {
            error_log('Failed to reorder testimonials: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to save testimonial ordering. Please try again.'
            ], 500);
            return;
        }

        $records = $this->testimonialModel->getAllOrdered();
        $fallbackImage = $this->getTestimonialPlaceholderImage();
        $payload = array_map(function (array $testimonial) use ($fallbackImage) {
            return $this->formatTestimonialForManager($testimonial, $fallbackImage);
        }, $records);

        $this->json([
            'success' => true,
            'message' => 'Testimonials updated successfully.',
            'testimonials' => $payload
        ]);
    }
}
