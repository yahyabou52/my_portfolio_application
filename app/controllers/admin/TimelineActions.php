<?php

trait TimelineActions {
    // TIMELINE MANAGEMENT
    public function timeline() {
        $this->userModel->requireAuth();

        $records = $this->timelineModel->all('sort_order ASC, created_at DESC');
        $timelineItems = array_map(function ($record) {
            return $this->transformTimelineItem($record);
        }, $records);

        $orderValue = implode(',', array_map(function ($item) {
            return (string)$item['id'];
        }, $timelineItems));

        $publishedItems = array_values(array_filter($timelineItems, function ($item) {
            return $item['status'] === 'published';
        }));
        $draftItems = array_values(array_filter($timelineItems, function ($item) {
            return $item['status'] === 'draft';
        }));

        $previewSource = !empty($publishedItems) ? $publishedItems : $timelineItems;
        $previewItems = array_slice($previewSource, 0, 4);

        $groupedItems = $this->groupTimelineByType($timelineItems);
        $groupedPreview = $this->groupTimelineByType($previewSource);

        $itemsJson = json_encode($timelineItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($itemsJson)) {
            $itemsJson = '[]';
        }

        $data = [
            'title' => 'Timeline - Admin',
            'page' => 'admin-timeline',
            'timeline_items' => $timelineItems,
            'timeline_order_value' => $orderValue,
            'timeline_counts' => [
                'total' => count($timelineItems),
                'published' => count($publishedItems),
                'draft' => count($draftItems)
            ],
            'timeline_preview_items' => $previewItems,
            'timeline_statuses' => self::TIMELINE_STATUSES,
            'timeline_groups' => $groupedItems,
            'timeline_preview_groups' => $groupedPreview,
            'timeline_items_json' => $itemsJson,
            'timeline_group_counts' => [
                'experience' => count($groupedItems['experience'] ?? []),
                'education' => count($groupedItems['education'] ?? [])
            ],
            'timeline_reorder_url' => url('admin/timeline/reorder')
        ];

        $this->render('admin/timeline', 'admin', $data);
    }

    public function createTimelineItem() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->collectTimelineFormInput();
            $errors = $this->validateTimelineInput($input);

            if (!empty($errors)) {
                $this->handleTimelineFormFailure($input, $errors, 'admin/timeline/create');
                return;
            }

            if ($input['sort_order'] === null) {
                $input['sort_order'] = $this->getNextTimelineSortOrder();
            }

            $created = $this->persistTimelineItem(0, $input);

            if ($created) {
                $this->setFlash('success', 'Timeline entry created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create timeline entry.');
            }

            $this->redirect('admin/timeline');
            return;
        }

        $data = [
            'title' => 'Create Timeline Entry - Admin',
            'page' => 'admin-timeline',
            'timeline_defaults' => $this->buildTimelineFormDefaults(),
            'timeline_statuses' => self::TIMELINE_STATUSES
        ];

        $this->render('admin/timeline-form', 'admin', $data);
    }

    public function editTimelineItem($id) {
        $this->userModel->requireAuth();

        $record = $this->timelineModel->find($id);
        if (!$record) {
            $this->setFlash('error', 'Timeline entry not found.');
            $this->redirect('admin/timeline');
            return;
        }

        $timelineItem = $this->transformTimelineItem($record);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->collectTimelineFormInput();
            if ($input['sort_order'] === null) {
                $input['sort_order'] = $timelineItem['sort_order'];
            }

            $errors = $this->validateTimelineInput($input);
            if (!empty($errors)) {
                $this->handleTimelineFormFailure($input, $errors, 'admin/timeline/' . $timelineItem['id'] . '/edit');
                return;
            }

            $updated = $this->persistTimelineItem($timelineItem['id'], $input);

            if ($updated) {
                $this->setFlash('success', 'Timeline entry updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update timeline entry.');
            }

            $this->redirect('admin/timeline');
            return;
        }

        $formItem = $timelineItem;
        $formItem['tags'] = implode(', ', $timelineItem['tags']);

        $data = [
            'title' => 'Edit Timeline Entry - Admin',
            'page' => 'admin-timeline',
            'timeline_item' => $formItem,
            'timeline_defaults' => $this->buildTimelineFormDefaults([
                'sort_order' => $timelineItem['sort_order']
            ]),
            'timeline_statuses' => self::TIMELINE_STATUSES
        ];

        $this->render('admin/timeline-form', 'admin', $data);
    }

    public function deleteTimelineItem($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/timeline');
            return;
        }

        $record = $this->timelineModel->find($id);
        if (!$record) {
            $this->setFlash('error', 'Timeline entry not found.');
            $this->redirect('admin/timeline');
            return;
        }

        if ($this->timelineModel->delete($record['id'])) {
            $this->setFlash('success', 'Timeline entry deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete timeline entry.');
        }

        $this->redirect('admin/timeline');
    }

    public function reorderTimeline() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $this->redirect('admin/timeline');
            return;
        }

        $orderInput = trim((string)($_POST['order'] ?? ''));
        if ($orderInput === '') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'No order changes were detected.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'No order changes were detected.');
            $this->redirect('admin/timeline');
            return;
        }

        $ids = array_values(array_filter(array_map(function ($value) {
            return (int)trim($value);
        }, explode(',', $orderInput)), function ($value) {
            return $value > 0;
        }));

        if (empty($ids)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid order data supplied.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'Invalid order data supplied.');
            $this->redirect('admin/timeline');
            return;
        }

        $position = 1;
        foreach ($ids as $itemId) {
            $item = $this->timelineModel->find($itemId);
            if ($item) {
                $this->timelineModel->update($itemId, ['sort_order' => $position++]);
            }
        }

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Timeline order updated successfully.',
                'order' => $ids
            ]);
            return;
        }

        $this->setFlash('success', 'Timeline order updated successfully.');
        $this->redirect('admin/timeline');
    }
}
