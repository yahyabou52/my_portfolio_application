<?php

trait HeroSectionActions {
    // HERO SECTION MANAGEMENT
    public function hero() {
        $this->userModel->requireAuth();

        $heroRecord = $this->ensureHeroSection();
        $heroFormDefaults = [
            'hero_intro_prefix' => "Hi, I'm",
            'hero_intro_name_first' => '',
            'hero_intro_name_rest' => '',
            'hero_intro_suffix' => '',
            'hero_title' => 'Creative UI/UX Designer',
            'hero_subtitle' => '',
            'hero_description' => '',
            'hero_primary_cta_text' => 'View My Work',
            'hero_primary_cta_url' => '/portfolio',
            'hero_secondary_cta_text' => "Let's Talk",
            'hero_secondary_cta_url' => '/contact',
            'hero_background_image_path' => '',
            'hero_background_image_alt' => 'Portrait',
            'scroll_indicator_text' => 'Scroll to explore'
        ];

        $heroForm = array_merge($heroFormDefaults, $heroRecord);
        $heroId = (int)($heroRecord['id'] ?? 1);

        $heroStats = $this->heroStatModel->getByHero($heroId, false);

        $heroPreview = $this->contentRepository
            ? $this->contentRepository->getHeroData()
            : $this->buildHeroPreviewFallback($heroForm, $heroStats);

        $previewStats = array_map(function ($stat) {
            return [
                'id' => $stat['id'] ?? null,
                'label' => $stat['label'] ?? '',
                'value' => $stat['value'] ?? '',
                'is_active' => (int)($stat['is_active'] ?? 0)
            ];
        }, $heroStats);

        $data = [
            'title' => 'Hero Section - Admin',
            'page' => 'admin-hero',
            'hero_form' => $heroForm,
            'hero_preview' => $heroPreview,
            'hero_stats' => $heroStats,
            'hero_preview_stats' => $previewStats
        ];

        $this->render('admin/hero', 'admin', $data);
    }
    
    public function updateHero() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/hero');
            return;
        }

        $heroRecord = $this->ensureHeroSection();
        $currentImagePath = $heroRecord['hero_background_image_path'] ?? '';

        $input = [
            'hero_intro_prefix' => trim($_POST['hero_intro_prefix'] ?? ''),
            'hero_intro_name_first' => trim($_POST['hero_intro_name_first'] ?? ''),
            'hero_intro_name_rest' => trim($_POST['hero_intro_name_rest'] ?? ''),
            'hero_intro_suffix' => trim($_POST['hero_intro_suffix'] ?? ''),
            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),
            'hero_description' => trim($_POST['hero_description'] ?? ''),
            'hero_primary_cta_text' => trim($_POST['hero_primary_cta_text'] ?? ''),
            'hero_primary_cta_url' => trim($_POST['hero_primary_cta_url'] ?? ''),
            'hero_secondary_cta_text' => trim($_POST['hero_secondary_cta_text'] ?? ''),
            'hero_secondary_cta_url' => trim($_POST['hero_secondary_cta_url'] ?? ''),
            'hero_background_image_path' => trim($_POST['hero_background_image_path'] ?? ''),
            'hero_background_image_alt' => trim($_POST['hero_background_image_alt'] ?? ''),
            'scroll_indicator_text' => trim($_POST['scroll_indicator_text'] ?? ''),
        ];

        try {
            $uploadedImagePath = $this->processHeroImageUpload();
        } catch (Exception $exception) {
            $this->setFlash('error', $exception->getMessage());
            $this->redirect('admin/hero');
            return;
        }
        if ($uploadedImagePath !== null) {
            $input['hero_background_image_path'] = $uploadedImagePath;
        } elseif ($input['hero_background_image_path'] === '' && $currentImagePath !== '') {
            $input['hero_background_image_path'] = $currentImagePath;
        }

        if ($this->heroSectionModel->updateActive($input)) {
            $this->setFlash('success', 'Hero section updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update hero section.');
        }

        $this->redirect('admin/hero');
    }
}
