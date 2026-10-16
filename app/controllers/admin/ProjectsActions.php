<?php

trait ProjectsActions {
    // PROJECTS MANAGEMENT
    public function projects() {
        $this->userModel->requireAuth();
        
        $projects = $this->projectModel->all('sort_order ASC, created_at DESC');
        
        $data = [
            'title' => 'Projects Management - Admin',
            'page' => 'admin-projects',
            'projects' => $projects
        ];
        
        $this->render('admin/projects', 'admin', $data);
    }
    
    public function createProject() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formInput = $this->sanitizeProjectFormInput($_POST);
            [$formInput, $uploadErrors] = $this->handleProjectImageInput($formInput);
            $errors = array_merge($this->projectModel->validate($formInput), $uploadErrors);
            
            if (!empty($errors)) {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $formInput);
                $this->redirect('admin/projects/create');
                return;
            }
            
            $projectData = $this->mapProjectFormToData($formInput);
            $slugSource = $formInput['slug'] ?: $formInput['title'];
            $projectData['slug'] = $this->projectModel->generateSlug($slugSource);
            
            if ($this->projectModel->create($projectData)) {
                $this->setFlash('success', 'Project created successfully.');
                $this->redirect('admin/projects');
                return;
            }
            
            $this->setFlash('error', 'Failed to create project.');
            $this->setFlash('form_data', $formInput);
            $this->redirect('admin/projects/create');
            return;
        }
        
        $data = [
            'title' => 'Create Project - Admin',
            'page' => 'admin-project-create'
        ];
        
        $this->render('admin/project-form', 'admin', $data);
    }
    
    public function editProject($id) {
        $this->userModel->requireAuth();
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            $this->setFlash('error', 'Project not found.');
            $this->redirect('admin/projects');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formInput = $this->sanitizeProjectFormInput($_POST, $project);
            [$formInput, $uploadErrors] = $this->handleProjectImageInput($formInput, $project);
            $errors = array_merge($this->projectModel->validate($formInput), $uploadErrors);
            
            if (!empty($errors)) {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $formInput);
                $this->redirect('admin/projects/' . $id . '/edit');
                return;
            }
            
            $projectData = $this->mapProjectFormToData($formInput, $project);
            $slugSource = $formInput['slug'] ?: $formInput['title'];
            $sanitizedSlug = $this->projectModel->generateSlug($slugSource, $project['id']);
            if ($sanitizedSlug !== $project['slug']) {
                $projectData['slug'] = $sanitizedSlug;
            }
            
            if ($this->projectModel->update($id, $projectData)) {
                $this->setFlash('success', 'Project updated successfully.');
                $this->redirect('admin/projects');
                return;
            }
            
            $this->setFlash('error', 'Failed to update project.');
            $this->setFlash('form_data', $formInput);
            $this->redirect('admin/projects/' . $id . '/edit');
            return;
        }
        
        $data = [
            'title' => 'Edit Project - Admin',
            'page' => 'admin-project-edit',
            'project' => $project
        ];
        
        $this->render('admin/project-form', 'admin', $data);
    }
    
    public function deleteProject($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/projects');
            return;
        }
        
        if ($this->projectModel->delete($id)) {
            $this->setFlash('success', 'Project deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete project.');
        }
        
        $this->redirect('admin/projects');
    }
}
