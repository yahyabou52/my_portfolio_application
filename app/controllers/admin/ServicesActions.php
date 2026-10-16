<?php

trait ServicesActions {
    // SERVICES MANAGEMENT
    public function services() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleServicesStructureSave();
            return;
        }

        $services = $this->serviceModel->getAllWithFeatures(true);
        $normalized = array_map([$this, 'formatServiceForManager'], $services);

        $data = [
            'title' => 'Services Management - Admin',
            'page' => 'admin-services',
            'services' => $normalized
        ];

        $this->render('admin/services', 'admin', $data);
    }
}
