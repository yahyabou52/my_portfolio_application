<?php
trait ThemeActions {
    public function theme() {
        $this->userModel->requireAuth();
        $values=WebsiteContent::values();
        $values['theme_default']=(new SiteSetting())->getSettings()['theme_default']??'light';
        $draft=$_SESSION['theme_draft']??[];unset($_SESSION['theme_draft']);
        $this->render('admin/theme','admin',['title'=>'Appearance','page'=>'admin-theme','theme_data'=>array_merge($values,$draft)]);
    }
    public function updateTheme() {
        $this->userModel->requireAuth();
        $data=[];
        try {
            foreach(['primary_color','secondary_color','accent_color'] as $key){
                if(!isset($_POST[$key]))continue;
                if(!preg_match('/^#[0-9a-fA-F]{6}$/',(string)$_POST[$key]))throw new InvalidArgumentException('Choose a valid color.');
                $data[$key]=$_POST[$key];
            }
            if(isset($_POST['theme_default'])){
                if(!in_array($_POST['theme_default'],['light','dark'],true))throw new InvalidArgumentException('Choose light or dark appearance.');
                $data['theme_default']=$_POST['theme_default'];
            }
            $data['dark_mode_enabled']=isset($_POST['dark_mode_enabled'])?'1':'0';
            if(isset($_POST['custom_css'])){
                if(strlen($_POST['custom_css'])>50000)throw new InvalidArgumentException('Custom CSS must be under 50,000 characters.');
                $data['custom_css']=$_POST['custom_css'];
            }
            if(!$this->settingModel->updateSettings($data))throw new RuntimeException('Save failed');
            $this->setFlash('success','Appearance saved. Your website is up to date.');
        }catch(Throwable $error){
            $_SESSION['theme_draft']=array_intersect_key($_POST,array_flip(['primary_color','secondary_color','accent_color','theme_default','custom_css']));
            $_SESSION['theme_draft']['dark_mode_enabled']=isset($_POST['dark_mode_enabled'])?'1':'0';
            $this->setFlash('error',$error instanceof InvalidArgumentException?$error->getMessage():'Appearance could not be saved. Your edits are still here.');
        }
        $this->redirect('admin/theme');
    }
}