<?php

trait AboutSectionActions {
    // ABOUT SECTION MANAGEMENT
    public function about() {
        $this->userModel->requireAuth();
        $page = $this->ensureAboutPage();
        $sections = $this->decodePageSections($page['sections'] ?? []);

        $timelineMeta = $this->normalizeTimelineMeta($sections['timeline'] ?? []);

        $aboutFormDefaults = [
            'about_greeting' => trim((string)($sections['about_greeting'] ?? $sections['about_subtitle'] ?? '')),
            'about_headline' => trim((string)($sections['about_headline'] ?? $sections['about_title'] ?? ($page['title'] ?? 'About Me'))),
            'about_bio' => trim((string)($sections['about_bio'] ?? $sections['about_content'] ?? ($page['content'] ?? ''))),
            'about_philosophy' => trim((string)($sections['about_philosophy'] ?? '')),
            'about_image_path' => trim((string)($sections['about_image_path'] ?? '')),
            'about_image_alt' => trim((string)($sections['about_image_alt'] ?? 'Portrait')),
            'timeline_title' => $timelineMeta['title'] ?? 'Experience & Education',
            'timeline_subtitle' => $timelineMeta['subtitle'] ?? ''
        ];

        $timelineItems = $this->contentRepository ? $this->contentRepository->getTimeline() : [];
        $aboutPreview = $this->buildAboutPreviewFallback($aboutFormDefaults, $timelineItems);

        $data = [
            'title' => 'About Section - Admin',
            'page' => 'admin-about',
            'about_form' => $aboutFormDefaults,
            'about_preview' => $aboutPreview,
            'timeline_items' => $timelineItems
        ];

        $this->render('admin/about', 'admin', $data);
    }
    
    public function updateAbout() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/about');
            return;
        }

        $page = $this->ensureAboutPage();
        $sections = $this->decodePageSections($page['sections'] ?? []);

        $input = [
            'about_greeting' => trim($_POST['about_greeting'] ?? ''),
            'about_headline' => trim($_POST['about_headline'] ?? ''),
            'about_bio' => trim($_POST['about_bio'] ?? ''),
            'about_philosophy' => trim($_POST['about_philosophy'] ?? ''),
            'about_image_path' => trim($_POST['about_image_path'] ?? ''),
            'about_image_alt' => trim($_POST['about_image_alt'] ?? ''),
            'timeline_title' => trim($_POST['timeline_title'] ?? ''),
            'timeline_subtitle' => trim($_POST['timeline_subtitle'] ?? '')
        ];

        try {
            $uploadedImagePath = $this->processAboutImageUpload();
        } catch (Exception $exception) {
            $this->setFlash('error', $exception->getMessage());
            $this->redirect('admin/about');
            return;
        }

        if ($uploadedImagePath !== null) {
            $input['about_image_path'] = $uploadedImagePath;
        } elseif ($input['about_image_path'] === '' && !empty($sections['about_image_path'])) {
            $input['about_image_path'] = $sections['about_image_path'];
        }

        $sections['about_greeting'] = $input['about_greeting'];
        $sections['about_headline'] = $input['about_headline'];
        $sections['about_bio'] = $input['about_bio'];
        $sections['about_philosophy'] = $input['about_philosophy'];
        $sections['about_image_path'] = $input['about_image_path'];
        $sections['about_image_alt'] = $input['about_image_alt'];
        if (isset($sections['about_highlights'])) {
            unset($sections['about_highlights']);
        }

        // Maintain legacy keys for compatibility with existing views until fully migrated
        $sections['about_title'] = $input['about_headline'];
        $sections['about_subtitle'] = $input['about_greeting'];
        $sections['about_content'] = $input['about_bio'];

        $existingTimeline = $sections['timeline'] ?? [];
        if (!is_array($existingTimeline)) {
            $existingTimeline = [];
        }

        $existingTimeline['title'] = $input['timeline_title'];
        $existingTimeline['subtitle'] = $input['timeline_subtitle'];
        $sections['timeline'] = $existingTimeline;

        $updatePayload = [
            'content' => $input['about_bio'],
            'sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        $updated = $this->pageModel->update($page['id'], $updatePayload);

        if ($updated) {
            $this->setFlash('success', 'About section updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update about section.');
        }

        $this->redirect('admin/about');
    }

    public function featuredProjects() {
        $this->userModel->requireAuth();

        $allProjects = $this->featuredProjectModel->getAllProjects();
        $featuredProjects = $this->featuredProjectModel->getFeaturedProjects();

        $featuredIds = array_map(function ($project) {
            return (int)$project['id'];
        }, $featuredProjects);

        $serializedFeatured = array_map(function ($project) {
            return [
                'id' => (int)$project['id'],
                'title' => $project['title'] ?? '',
                'slug' => $project['slug'] ?? '',
                'short_description' => $project['short_description'] ?? '',
                'main_image_url' => !empty($project['main_image_path'])
                    ? media_url($project['main_image_path'])
                    : asset('images/placeholder.svg'),
                'sort_order' => (int)($project['featured_sort_order'] ?? 0)
            ];
        }, $featuredProjects);

        $data = [
            'title' => 'Featured Work - Admin',
            'page' => 'admin-featured-projects',
            'projects' => $allProjects,
            'featured_projects' => $serializedFeatured,
            'featured_ids' => $featuredIds
        ];

        $this->render('admin/featured-projects', 'admin', $data);
    }

    public function updateFeaturedProjects() {
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

            $this->redirect('admin/featured-projects');
            return;
        }

        $featuredRaw = $_POST['featured'] ?? '';
        $projectIds = array_values(array_filter(array_map(function ($value) {
            return (int)trim($value);
        }, explode(',', (string)$featuredRaw)), function ($value) {
            return $value > 0;
        }));

        if (count($projectIds) < 3 || count($projectIds) > 6) {
            $message = 'Featured list must include between 3 and 6 projects.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/featured-projects');
            return;
        }

        try {
            $this->featuredProjectModel->updateFeaturedList($projectIds);
        } catch (Exception $exception) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update featured projects.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update featured projects.');
            $this->redirect('admin/featured-projects');
            return;
        }

        if ($isAjax) {
            $projects = $this->featuredProjectModel->getFeaturedProjects();
            $serialized = array_map(function ($project) {
                return [
                    'id' => (int)$project['id'],
                    'title' => $project['title'] ?? '',
                    'slug' => $project['slug'] ?? '',
                    'short_description' => $project['short_description'] ?? '',
                    'main_image_url' => !empty($project['main_image_path'])
                        ? media_url($project['main_image_path'])
                        : asset('images/placeholder.svg'),
                    'sort_order' => (int)($project['featured_sort_order'] ?? 0)
                ];
            }, $projects);

            $this->json([
                'success' => true,
                'message' => 'Featured projects updated successfully.',
                'projects' => $serialized
            ]);
            return;
        }

        $this->setFlash('success', 'Featured projects updated successfully.');
        $this->redirect('admin/featured-projects');
    }

    public function servicesPreview() {
        $this->userModel->requireAuth();

        $allServices = $this->serviceModel->all('sort_order ASC, title ASC');
        $selectedServices = $this->serviceModel->getHomepageFeatured();

        $normalizedAll = array_map([$this, 'normalizeServiceForPreview'], $allServices);
        $normalizedSelected = array_map([$this, 'normalizeServiceForPreview'], $selectedServices);

        usort($normalizedSelected, function ($a, $b) {
            return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
        });

        $selectedIds = array_map(function ($service) {
            return (int)$service['id'];
        }, $normalizedSelected);

        $available = array_values(array_filter($normalizedAll, function ($service) use ($selectedIds) {
            return !in_array((int)$service['id'], $selectedIds, true);
        }));

        $data = [
            'title' => 'Services Preview - Admin',
            'page' => 'admin-services-preview',
            'services' => $available,
            'selected_services' => $normalizedSelected,
            'all_services' => $normalizedAll,
            'services_min' => 3,
            'services_max' => 6
        ];

        $this->render('admin/services-preview', 'admin', $data);
    }

    public function updateServicesPreview() {
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

            $this->redirect('admin/services/preview');
            return;
        }

        $rawPayload = trim($_POST['services'] ?? '');
        if ($rawPayload === '') {
            $message = 'No services were provided.';
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $decoded = json_decode($rawPayload, true);
        if (!is_array($decoded)) {
            $message = 'Invalid services payload.';
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $services = [];
        foreach ($decoded as $item) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $visible = isset($item['visible']) ? (int)$item['visible'] : 1;
            $services[] = [
                'id' => $id,
                'visible' => $visible ? 1 : 0
            ];
        }

        if (count($services) < 3 || count($services) > 6) {
            $message = 'Please select between 3 and 6 services for the homepage.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $visibleCount = 0;
        $existingIds = [];
        foreach ($services as $service) {
            $record = $this->serviceModel->find($service['id']);
            if (!$record) {
                $message = 'One or more selected services could not be found.';

                if ($isAjax) {
                    $this->json([
                        'success' => false,
                        'message' => $message
                    ], 404);
                    return;
                }

                $this->setFlash('error', $message);
                $this->redirect('admin/services/preview');
                return;
            }

            $existingIds[] = $service['id'];
            if ((int)$service['visible'] === 1) {
                $visibleCount += 1;
            }
        }

        if ($visibleCount < 3) {
            $message = 'At least 3 services must remain visible.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        try {
            $this->serviceModel->updateHomepageSelection($services);
        } catch (Exception $exception) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update homepage services.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update homepage services.');
            $this->redirect('admin/services/preview');
            return;
        }

        $freshServices = $this->serviceModel->getHomepageFeatured();
        $normalized = array_map([$this, 'normalizeServiceForPreview'], $freshServices);

        if ($isAjax) {
            $this->json([
                'success' => true,
                'message' => 'Homepage services updated successfully.',
                'services' => $normalized
            ]);
            return;
        }

        $this->setFlash('success', 'Homepage services updated successfully.');
        $this->redirect('admin/services/preview');
    }

    private function normalizeServiceForPreview($service) {
        if (!$service) {
            return [];
        }

        $description = strip_tags((string)($service['description'] ?? ''));
        $description = trim(preg_replace('/\s+/', ' ', $description));

        if ($description === '') {
            $summary = 'No description provided.';
        } else {
            $summary = str_limit($description, 140, '…');
        }

        $icon = trim((string)($service['icon'] ?? ''));

        return [
            'id' => (int)($service['id'] ?? 0),
            'title' => $service['title'] ?? 'Untitled Service',
            'summary' => $summary,
            'icon' => $icon,
            'status' => $service['status'] ?? 'draft',
            'visible' => (int)($service['homepage_is_visible'] ?? 1) === 1,
            'sort_order' => (int)($service['homepage_sort_order'] ?? 0),
            'featured' => (int)($service['homepage_featured'] ?? 0) === 1
        ];
    }
}
