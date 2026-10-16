<?php
declare(strict_types=1);
final class SectionRegistry {
    public static function all(): array {
        return [
            'hero'=>['component'=>'hero','variants'=>['split','centered'],'fields'=>['heading'=>'text','body'=>'text','button_label'=>'text','button_url'=>'url'],'profile'=>['name','profession','intro','photo']],
            'about'=>['component'=>'about','variants'=>['split','stacked'],'fields'=>['heading'=>'text'],'profile'=>['bio','photo']],
            'projects'=>['component'=>'projects','variants'=>['grid','list'],'fields'=>['heading'=>'text'],'collection'=>'projects'],
            'skills'=>['component'=>'skills','variants'=>['chips','list'],'fields'=>['heading'=>'text'],'collection'=>'skills'],
            'experience'=>['component'=>'experience','variants'=>['timeline','list'],'fields'=>['heading'=>'text'],'collection'=>'timeline_items'],
            'contact'=>['component'=>'contact','variants'=>['card','minimal'],'fields'=>['heading'=>'text','button_label'=>'text'],'profile'=>['email','phone','location','social']],
        ];
    }
    public static function get(string $type): array {
        return self::all()[$type] ?? throw new InvalidArgumentException('Unknown section type.');
    }
}
