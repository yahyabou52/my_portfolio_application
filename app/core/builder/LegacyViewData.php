<?php
declare(strict_types=1);
require_once ROOT_PATH.'/app/core/ContentRepository.php';
require_once ROOT_PATH.'/app/core/WebsiteContent.php';
final class LegacyViewData {
    public static function capture(PDO $db): array {
        try { return Database::using($db,function(){
            $repo=new ContentRepository();$global=$repo->getGlobalData();
            unset($global['activeServices'],$global['socialLinks'],$global['contactSettings']);
            foreach(['site_title','site_tagline'] as $key)unset($global['siteSettings'][$key]);
            $hero=$repo->getHeroData();unset($hero['hero_description'],$hero['hero_intro_name_first'],$hero['hero_intro_name_rest'],$hero['hero_primary_cta'],$hero['hero_secondary_cta']);
            $about=$repo->getAboutData();foreach(['bio','about_content','image_url','about_image_url','timeline'] as $key)unset($about[$key]);
            $pages=[];foreach((new Page())->all() as $page){
                $sections=json_decode($page['sections']??'{}',true)?:[];
                foreach(['about_bio','about_content','about_image_path'] as $key)unset($sections[$key]);
                $pages[$page['page_key']]=['title'=>$page['title'],'meta_description'=>$page['meta_description']??'','sections'=>$sections];
            }
            $values=[];foreach(Database::getInstance()->getConnection()->query('SELECT setting_key,setting_value FROM site_settings')->fetchAll() as $row)$values[$row['setting_key']]=$row['setting_value'];
            return ['global'=>$global,'hero'=>$hero,'about'=>$about,'pages'=>$pages,'values'=>$values];
        }); } catch(PDOException $error) {
            if(!in_array($error->getCode(),['42S02','42S22'],true))throw $error;
            // Sparse legacy sites remain blocked from activation; preserve their raw tables in the export.
            return ['global'=>['siteSettings'=>[],'footerSettings'=>[],'navigationMenu'=>[]],'hero'=>[],'about'=>[],'pages'=>[],'values'=>[],'incomplete'=>true];
        }
    }
    public static function data(PDO $db,array $doc,string $pageKey): array {
        return Database::using($db,function()use($doc,$pageKey){
            $repo=new ContentRepository();$p=$doc['profile'];$presentation=$doc['presentation']??self::capture(Database::getInstance()->getConnection());
            $global=$presentation['global'];
            $global['siteSettings']=array_merge($global['siteSettings'],['site_title'=>$p['name'],'site_tagline'=>$p['profession'],'site_description'=>!empty($doc['compatibility'])?($global['siteSettings']['site_description']??$p['intro']):$p['intro']]);
            $global['contactSettings']=['contact_email'=>$p['email'],'contact_phone'=>$p['phone'],'contact_address'=>$p['location']];
            $global['socialLinks']=$p['social'];$global['activeServices']=$repo->getActiveServices();
            $hero=$presentation['hero'];$parts=explode(' ',trim($p['name']),2);$hero['hero_intro_name_first']=$parts[0];$hero['hero_intro_name_rest']=$parts[1]??'';$hero['hero_description']=$p['intro'];
            $hero['hero_primary_cta']=['text'=>$hero['hero_primary_cta_text']??'','url'=>navbar_build_nav_url($hero['hero_primary_cta_url']??'')];
            $hero['hero_secondary_cta']=['text'=>$hero['hero_secondary_cta_text']??'','url'=>navbar_build_nav_url($hero['hero_secondary_cta_url']??'')];
            $about=$presentation['about'];$about['bio']=$p['bio'];$about['about_content']=$p['bio'];$about['image_url']=$p['photo']?media_url($p['photo']):asset('images/placeholder.svg');$about['about_image_url']=$about['image_url'];$about['timeline']=$repo->getTimeline();
            $meta=$presentation['pages'][$pageKey]??[];
            $projects=(new Project())->getPublished();foreach($projects as &$project)$project['technologies_list']=(new Project())->getTechnologies($project['id']);unset($project);
            return array_merge($global,[
                'title'=>$doc['pages'][$pageKey]['title']??$meta['title']??$p['name'],'meta_description'=>$meta['meta_description']??'','page'=>$pageKey,
                'page_content'=>$pageKey==='about'?$p['bio']:'','page_sections'=>$meta['sections']??[],
                'hero'=>$hero,'hero_stats'=>$repo->getHeroStats(),'about'=>$about,
                'featured_projects'=>$repo->getFeaturedProjects(6),'projects'=>$projects,'categories'=>(new Project())->getCategories(),'current_category'=>'','search_query'=>'',
                'services'=>$pageKey==='home'?$repo->getHomepageServices():$repo->getActiveServices(),
                'skills_by_category'=>$repo->getSkillsByCategory(),'featured_testimonials'=>$repo->getFeaturedTestimonials($pageKey==='about'?6:3),
                'process_steps'=>$repo->getServiceProcessSteps(),'pricing_plans'=>$repo->getPricingPlans(),'faqs'=>$repo->getFaq($pageKey)
            ]);
        });
    }
}
