<?php
declare(strict_types=1);
final class SectionRegistry {
    public static function all(): array {
        return [
            'hero'=>['template'=>'home/index','section_key'=>'home.section.hero-section','component'=>'hero','variants'=>['split','centered'],'fields'=>['heading'=>'text','body'=>'text','button_label'=>'text','button_url'=>'url'],'profile'=>['name','profession','intro','photo']],
            'about'=>['template'=>'home/about','section_key'=>'about.section.about-hero','component'=>'about','variants'=>['split','stacked'],'fields'=>['heading'=>'text'],'profile'=>['bio','photo']],
            'projects'=>['template'=>'home/index','section_key'=>'home.section.featured-work-section','component'=>'projects','variants'=>['grid','list'],'fields'=>['heading'=>'text'],'collection'=>'projects'],
            'skills'=>['template'=>'home/index','section_key'=>'home.section.skills-section','component'=>'skills','variants'=>['chips','list'],'fields'=>['heading'=>'text'],'collection'=>'skills'],
            'experience'=>['template'=>'home/about','section_key'=>'about.section.experience-section','component'=>'experience','variants'=>['timeline','list'],'fields'=>['heading'=>'text'],'collection'=>'timeline_items'],
            'services'=>['template'=>'home/index','section_key'=>'home.section.services-section','component'=>'services','variants'=>['cards'],'fields'=>['heading'=>'text'],'collection'=>'services'],
            'testimonials'=>['template'=>'home/index','section_key'=>'home.section.testimonials-section','component'=>'testimonials','variants'=>['cards'],'fields'=>['heading'=>'text'],'collection'=>'testimonials'],
            'contact'=>['template'=>'contact/index','section_key'=>'contact.section.contact-content','component'=>'contact','variants'=>['card','minimal'],'fields'=>['heading'=>'text','button_label'=>'text'],'profile'=>['email','phone','location','social']],
        ];
    }
    public static function editableFields(array $document): array { require_once __DIR__.'/EditableFields.php';return EditableFields::schema($document); }
    public static function availableVariants(string $type): array { return $type==='projects'?['grid','list']:[self::get($type)['variants'][0]]; }
    public static function get(string $type): array {
        return self::all()[$type] ?? throw new InvalidArgumentException('Unknown section type.');
    }
}
