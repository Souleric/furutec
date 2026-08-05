<?php
// Furutec Editor — content values (the "draft" state).
// - The editor writes to this file when Amy saves.
// - publish.php reads this + the template + writes public_html/index.html.
// - Never edited by hand once the editor is deployed — do it through /editor.

return array(

    'hero' => array(
        'bg_video'   => 'banner-video-2.mp4',
        'headline_1' => 'Path to Power',
        'headline_2' => 'Efficiency & Reliability',
        'lede'       => 'Engineered for industrial, commercial, and infrastructure projects. Furutec delivers safe, efficient, and scalable busduct solutions trusted by engineers and contractors.',
    ),

    'origin' => array(
        'eyebrow' => 'Inside Furutec',
        'heading' => 'Origin of Furutec.',
        'body'    => 'Driven by engineering expertise and a commitment to quality, Furutec delivers reliable busduct solutions trusted by engineers and developers. We continue to innovate our products to meet the evolving demands of modern power distribution systems.',
        'video'   => 'Furutec promo video 1.mp4',
        'poster'  => 'promo video thumbnail.png',
        'caption' => 'Inside Furutec — Manufacturing Facility',
    ),

    'products' => array(
        'section_eyebrow'   => 'OUR PRODUCTS',
        'section_heading_1' => 'Complete busduct solutions,',
        'section_heading_2' => 'for every environment.',
        'section_subtitle'  => 'From standard indoor installations to intelligent data centre distribution — Furutec has the right system for your project.',

        'card_1_title'       => 'Indoor Solution',
        'card_1_description' => 'Compact sandwich busducts ranging from **500 A to 6,300 A**, offering **IP65 & IP66 protection** with excellent heat dissipation, engineered for reliable indoor power distribution.',

        'card_2_title'       => 'Outdoor Solution',
        'card_2_description' => 'High-performance busducts with **IP68 protection**, engineered to withstand harsh environments and certified for **Seismic Zone 4** resilience.',

        'card_3_title'       => 'Data Centre Solution',
        'card_3_description' => 'Designed for hyperscale data centres with **customisable tap-off units** and **space-efficient power distribution**, ensuring maximum uptime and operational flexibility.',

        'card_4_title'       => 'Power Monitoring Solution',
        'card_4_description' => 'Advanced monitoring system providing **real-time energy insights**, predictive maintenance capabilities, and live load balancing for enhanced operational reliability.',

        'card_5_title'       => 'Lighting Solution',
        'card_5_description' => 'Lighting and power busduct systems featuring **detachable, reusable components** for simplified maintenance, reduced installation time, and lower lifecycle costs.',
    ),

);
