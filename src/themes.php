<?php
declare(strict_types=1);

function theme_settings_file(): string { return data_root().'/public-domain/themes/theme-settings.json'; }

function theme_registry(): array {
    return [
        'atelier-natural'=>[
            'label'=>'Atelier Natural',
            'description'=>'The core warm-ivory Atelier Wood identity for general collections.',
            'mode'=>'natural',
            'tokens'=>[
                'bg'=>'#f4efe6','surface'=>'#fbf8f2','surface_2'=>'#eee5d8','ink'=>'#241b15','muted'=>'#75685d',
                'accent'=>'#293222','accent_2'=>'#ad9161','border'=>'#d9cdbf','hero_from'=>'#efe2d2','hero_to'=>'#f7f2e9',
                'story_bg'=>'#30261f','story_ink'=>'#f5eee5','glow'=>'#d7bc8b','shadow'=>'#3b281c'
            ],
        ],
        'sacred-luxury'=>[
            'label'=>'Sacred Luxury',
            'description'=>'Luminous ivory, deep temple walnut and antique-gold detailing with a ceremonial editorial finish.',
            'mode'=>'luxury',
            'tokens'=>[
                'bg'=>'#f7f1e6','surface'=>'#fffaf1','surface_2'=>'#eadfce','ink'=>'#24150f','muted'=>'#776657',
                'accent'=>'#6f3d20','accent_2'=>'#b89455','border'=>'#dcc7a5','hero_from'=>'#f5e6cf','hero_to'=>'#fbf6ed',
                'story_bg'=>'#20130e','story_ink'=>'#fff6e8','glow'=>'#e7c575','shadow'=>'#4a2717'
            ],
        ],
        'sacred-ivory'=>[
            'label'=>'Sacred Ivory',
            'description'=>'Airy, luminous and contemporary with pearl ivory surfaces, champagne brass and restrained ornament.',
            'mode'=>'ivory',
            'tokens'=>[
                'bg'=>'#fbf8f1','surface'=>'#ffffff','surface_2'=>'#f1eadf','ink'=>'#2a211c','muted'=>'#75695f',
                'accent'=>'#7f6038','accent_2'=>'#c8aa73','border'=>'#e7dac6','hero_from'=>'#f8efe2','hero_to'=>'#ffffff',
                'story_bg'=>'#5c4939','story_ink'=>'#fffaf4','glow'=>'#f2dca8','shadow'=>'#755e49'
            ],
        ],
        'heritage-walnut'=>[
            'label'=>'Heritage Walnut',
            'description'=>'A richer traditional presentation with carved-walnut depth, parchment surfaces and aged brass.',
            'mode'=>'heritage',
            'tokens'=>[
                'bg'=>'#eee2d2','surface'=>'#f8efe3','surface_2'=>'#dcc8ad','ink'=>'#26140d','muted'=>'#70594b',
                'accent'=>'#4a2416','accent_2'=>'#9e753e','border'=>'#c8a77f','hero_from'=>'#d7b993','hero_to'=>'#f0dfc7',
                'story_bg'=>'#2a160e','story_ink'=>'#f8e9d6','glow'=>'#c4954b','shadow'=>'#2b160f'
            ],
        ],

        'material-atelier'=>[
            'label'=>'Material Atelier','description'=>'Warm stone, timber and restrained bronze for furniture and core living collections.','mode'=>'material',
            'tokens'=>['bg'=>'#f2ece2','surface'=>'#fbf7f0','surface_2'=>'#e5d9ca','ink'=>'#211913','muted'=>'#6f6258','accent'=>'#5b3826','accent_2'=>'#aa845c','border'=>'#d7c6b3','hero_from'=>'#e9dccb','hero_to'=>'#f8f2e9','story_bg'=>'#292019','story_ink'=>'#f7eee4','glow'=>'#c99d6a','shadow'=>'#2a1c14'],
        ],
        'luminous-atelier'=>[
            'label'=>'Luminous Atelier','description'=>'Soft alabaster, charcoal and aged brass for lighting-led collections.','mode'=>'luminous',
            'tokens'=>['bg'=>'#f6f2ea','surface'=>'#fffdf8','surface_2'=>'#e8e0d2','ink'=>'#181817','muted'=>'#66615a','accent'=>'#292824','accent_2'=>'#b69458','border'=>'#ddd2c0','hero_from'=>'#eee6d7','hero_to'=>'#fffaf0','story_bg'=>'#171715','story_ink'=>'#fff8ea','glow'=>'#e1ba6b','shadow'=>'#18130e'],
        ],
        'gift-atelier'=>[
            'label'=>'Gift Atelier','description'=>'Warm blush, kraft neutrals and dark olive for gifting and personalised objects.','mode'=>'gift',
            'tokens'=>['bg'=>'#f5eee7','surface'=>'#fffaf5','surface_2'=>'#eadbd0','ink'=>'#2a1d18','muted'=>'#76655e','accent'=>'#5b4d37','accent_2'=>'#b78368','border'=>'#decabd','hero_from'=>'#efdcd1','hero_to'=>'#faf2e9','story_bg'=>'#2e2d21','story_ink'=>'#fff6ea','glow'=>'#d9a485','shadow'=>'#34221a'],
        ],
        'earth-atelier'=>[
            'label'=>'Earth Atelier','description'=>'Natural clay, forest olive and weathered timber for outdoor collections.','mode'=>'earth',
            'tokens'=>['bg'=>'#eee9dc','surface'=>'#faf7ef','surface_2'=>'#dcd8c7','ink'=>'#1f241b','muted'=>'#69705f','accent'=>'#35452f','accent_2'=>'#a97952','border'=>'#cfd0bc','hero_from'=>'#dde0cb','hero_to'=>'#f4eddf','story_bg'=>'#263024','story_ink'=>'#f4f0e6','glow'=>'#c29b67','shadow'=>'#1e261c'],
        ],
        'soft-living'=>[
            'label'=>'Soft Living','description'=>'Quiet oat, pale clay and cocoa for calm family and kids spaces.','mode'=>'soft',
            'tokens'=>['bg'=>'#f7f1e9','surface'=>'#fffdf9','surface_2'=>'#eadfd3','ink'=>'#2b231f','muted'=>'#756b64','accent'=>'#74594b','accent_2'=>'#c39b7a','border'=>'#dfd0c3','hero_from'=>'#f0e3d8','hero_to'=>'#fbf6ef','story_bg'=>'#493b34','story_ink'=>'#fff8f2','glow'=>'#dab596','shadow'=>'#3a2c25'],
        ],
        'architectural'=>[
            'label'=>'Architectural','description'=>'Stone, graphite and bronze for TradeOS, specifications and project workspaces.','mode'=>'architectural',
            'tokens'=>['bg'=>'#ecebe7','surface'=>'#f8f7f3','surface_2'=>'#deddd7','ink'=>'#171816','muted'=>'#64655f','accent'=>'#333631','accent_2'=>'#98795a','border'=>'#cfcfc8','hero_from'=>'#dfdfda','hero_to'=>'#f3f0e9','story_bg'=>'#20211f','story_ink'=>'#f3f2ed','glow'=>'#b59877','shadow'=>'#161715'],
        ],
        'sacred-noir'=>[
            'label'=>'Sacred Noir',
            'description'=>'Dramatic charcoal-walnut luxury with restrained gold illumination for campaign or evening launches.',
            'mode'=>'noir',
            'tokens'=>[
                'bg'=>'#17120f','surface'=>'#211915','surface_2'=>'#30231d','ink'=>'#f7efe4','muted'=>'#c0afa0',
                'accent'=>'#d5aa61','accent_2'=>'#f0ca7c','border'=>'#594234','hero_from'=>'#1c1511','hero_to'=>'#352218',
                'story_bg'=>'#0e0b09','story_ink'=>'#fff8ed','glow'=>'#d5a54f','shadow'=>'#000000'
            ],
        ],
    ];
}

function theme_default_settings(): array {
    return [
        'version'=>1,
        'surfaces'=>[
            'default'=>['preset'=>'atelier-natural','overrides'=>[]],
            'sacred-spaces'=>['preset'=>'sacred-luxury','overrides'=>[]],
            'furniture'=>['preset'=>'material-atelier','overrides'=>[]],
            'lighting'=>['preset'=>'luminous-atelier','overrides'=>[]],
            'gifting'=>['preset'=>'gift-atelier','overrides'=>[]],
            'outdoor'=>['preset'=>'earth-atelier','overrides'=>[]],
            'kids'=>['preset'=>'soft-living','overrides'=>[]],
            'trade'=>['preset'=>'architectural','overrides'=>[]],
        ],
        'updated_at'=>null,
    ];
}

function theme_settings(): array {
    $data=foundation_json_read(theme_settings_file(), theme_default_settings());
    if(!isset($data['surfaces']) || !is_array($data['surfaces'])) return theme_default_settings();
    return array_replace_recursive(theme_default_settings(), $data);
}

function theme_allowed_surfaces(): array {
    return [
        'default'=>'Default storefront',
        'sacred-spaces'=>'Sacred Spaces collection',
        'furniture'=>'Furniture & living',
        'lighting'=>'Lighting',
        'gifting'=>'Gifting & personalised',
        'outdoor'=>'Outdoor accents',
        'kids'=>'Kids & family',
        'trade'=>'Trade / architect workspace',
    ];
}

function theme_valid_hex(string $value): bool { return (bool)preg_match('/^#[0-9a-fA-F]{6}$/',$value); }

function theme_sanitize_overrides(array $input): array {
    $allowed=['bg','surface','surface_2','ink','muted','accent','accent_2','border','hero_from','hero_to','story_bg','story_ink','glow','shadow'];
    $out=[];
    foreach($allowed as $key){
        $value=trim((string)($input[$key]??''));
        if($value!=='' && theme_valid_hex($value)) $out[$key]=strtolower($value);
    }
    return $out;
}

function theme_save_surface(string $surface, string $preset, array $overrides=[]): void {
    if(!array_key_exists($surface,theme_allowed_surfaces())) throw new RuntimeException('Unknown theme surface.');
    if(!isset(theme_registry()[$preset])) throw new RuntimeException('Unknown theme preset.');
    $settings=theme_settings();
    $settings['surfaces'][$surface]=['preset'=>$preset,'overrides'=>theme_sanitize_overrides($overrides)];
    $settings['updated_at']=gmdate('c');
    atomic_json_write(theme_settings_file(),$settings,foundation_checksum(theme_settings_file()),'theme.update');
    audit_event('theme.update',['surface'=>$surface,'preset'=>$preset]);
}

function theme_resolve(string $surface='default'): array {
    $settings=theme_settings();
    $registry=theme_registry();
    $surfaceConfig=$settings['surfaces'][$surface]??$settings['surfaces']['default']??['preset'=>'atelier-natural','overrides'=>[]];
    $preset=(string)($surfaceConfig['preset']??'atelier-natural');
    if(!isset($registry[$preset])) $preset='atelier-natural';
    $theme=$registry[$preset];
    $theme['key']=$preset;
    $theme['surface']=$surface;
    $theme['tokens']=array_merge($theme['tokens'],theme_sanitize_overrides((array)($surfaceConfig['overrides']??[])));
    return $theme;
}

function theme_css_variables(array $theme): string {
    $map=[
        'bg'=>'--collection-bg','surface'=>'--collection-surface','surface_2'=>'--collection-surface-2','ink'=>'--collection-ink',
        'muted'=>'--collection-muted','accent'=>'--collection-accent','accent_2'=>'--collection-accent-2','border'=>'--collection-border',
        'hero_from'=>'--collection-hero-from','hero_to'=>'--collection-hero-to','story_bg'=>'--collection-story-bg',
        'story_ink'=>'--collection-story-ink','glow'=>'--collection-glow','shadow'=>'--collection-shadow'
    ];
    $pairs=[];
    foreach($map as $key=>$css){$v=(string)($theme['tokens'][$key]??'');if(theme_valid_hex($v))$pairs[]=$css.':'.$v;}
    return implode(';',$pairs);
}

function theme_admin_rows(): array {
    $settings=theme_settings();$rows=[];
    foreach(theme_allowed_surfaces() as $surface=>$label){$rows[]=['surface'=>$surface,'label'=>$label,'config'=>$settings['surfaces'][$surface]??[],'resolved'=>theme_resolve($surface)];}
    return $rows;
}

function theme_surface_for_product(array $product): string {
    $category=(string)($product['category']??'');
    $collection=text_lower((string)($product['collection']??''));
    if($category==='sacred-spaces') return 'sacred-spaces';
    if($category==='outdoor-accents') return 'outdoor';
    if($category==='utility-gifting' || str_contains($collection,'gift')) return 'gifting';
    if(str_contains($category,'light') || str_contains($collection,'light')) return 'lighting';
    if(str_contains($category,'kid') || str_contains($collection,'kid')) return 'kids';
    return 'furniture';
}

function theme_release_checks(): array {
    $settings=theme_settings();$registry=theme_registry();$ok=true;$bad=[];
    foreach(theme_allowed_surfaces() as $surface=>$label){
        $cfg=$settings['surfaces'][$surface]??null;
        if(!$cfg || !isset($registry[(string)($cfg['preset']??'')])){$ok=false;$bad[]=$surface;continue;}
        foreach((array)($cfg['overrides']??[]) as $key=>$value){if(!theme_valid_hex((string)$value)){$ok=false;$bad[]=$surface.':'.$key;}}
    }
    return [
        ['name'=>'Theme registry integrity','ok'=>$ok,'detail'=>$ok?'All configured surfaces use allowlisted presets and sanitized color tokens.':'Invalid theme configuration: '.implode(', ',$bad)],
        ['name'=>'Sacred Luxury assignment','ok'=>(theme_resolve('sacred-spaces')['key']??'')!=='','detail'=>'Sacred Spaces resolves to '.(theme_resolve('sacred-spaces')['label']??'a valid theme').'.'],
    ];
}
