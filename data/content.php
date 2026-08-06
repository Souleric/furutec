<?php
// Furutec Editor content values.
// - The editor writes to this file when Amy saves.
// - publish.php reads this + the template + writes public_html/index.html.
// - Never edit by hand once the editor is deployed — do it through /editor.

return array(

    // -------- NAV ----------
    'nav' => array(
        'logo'                 => 'Logo.jpeg',
        'phone'                => '+6012-887 4517',
        'contact_button_label' => 'Contact Us',
    ),

    // -------- HERO ----------
    'hero' => array(
        'bg_video'   => 'banner-video-2.mp4',
        'headline_1' => 'Path to Power',
        'headline_2' => 'Efficiency & Reliability',
        'lede'       => 'Engineered for industrial, commercial, and infrastructure projects. Furutec delivers safe, efficient, and scalable busduct solutions trusted by engineers and contractors.',
    ),

    // -------- INSIDE FURUTEC ----------
    'origin' => array(
        'eyebrow' => 'Inside Furutec',
        'heading' => 'Origin of Furutec.',
        'body'    => 'Driven by engineering expertise and a commitment to quality, Furutec delivers reliable busduct solutions trusted by engineers and developers. We continue to innovate our products to meet the evolving demands of modern power distribution systems.',
        'video'   => 'Furutec promo video 1.mp4',
        'poster'  => 'promo video thumbnail.png',
        'caption' => 'Inside Furutec — Manufacturing Facility',
    ),

    // -------- FACILITIES ----------
    'facilities' => array(
        'tag'    => 'Our Manufacturing Plant',
        'name'   => 'Bukit Minyak, Penang',
        'desc_1' => 'Our manufacturing site in Bukit Minyak Industrial Park operates from two adjacent buildings — **Plant 1** and **Plant 2** — alongside the FURUTEC administrative block.',
        'desc_2' => 'Every busduct system comes together here, from precision-machined components and copper-bar fabrication through full assembly and in-house type-testing to IEC 61439-6. Every unit is verified on the floor before it leaves the site.',

        'badge_1_label' => 'Two Integrated / Plants',
        'badge_1_sub'   => 'Plant 1 & Plant 2',
        'badge_2_label' => 'IEC 61439-6 / Type-Tested',
        'badge_2_sub'   => 'In-house verification',
        'badge_3_label' => 'Internationally / Certified',
        'badge_3_sub'   => 'ASTA · KEMA · SIRIM',
        'badge_4_label' => '5 Product / Solutions',
        'badge_4_sub'   => 'Indoor → Power Monitoring',

        'photo_aerial' => 'penang-plant-aerial.jpg',
        'photo_front'  => 'penang-plant-front.jpg',
        'process_1'    => 'penang-plant-process-1.png',
        'process_2'    => 'penang-plant-process-2.png',
        'process_3'    => 'penang-plant-process-3.png',
        'process_4'    => 'penang-plant-process-4.png',
    ),

    // -------- COMPANY PROFILE ----------
    'company' => array(
        'bg_video'     => 'Furutec logo video  2.mp4',
        'eyebrow'      => 'Company Profile',
        'heading_1'    => 'Build on expertise.',
        'heading_2'    => 'Driven by innovation.',
        'heading_3'    => 'Committed by passion.',
        'body_1'       => 'Furutec Electrical Sdn Bhd is a manufacturer of busduct systems specializing in indoor, outdoor, data center, lighting, and power monitoring solutions. With over three decades of R&D and manufacturing experience, supported by two production facilities in Malaysia and China, we deliver certified and reliable systems trusted by engineers, developers, and project owners worldwide.',
        'body_2'       => 'Our products are type-tested and certified by reputable certification bodies to ensure full compliance with international standards. Our comprehensive range of busduct systems is also thoughtfully tailored to meet diverse applications and market segments.',
        'button_label' => 'Discover More',
        'button_url'   => 'https://www.eita.com.my',
    ),

    // -------- PRODUCT OVERVIEW (interactive diagram) ----------
    'product_overview' => array(
        'eyebrow'        => 'PRODUCT OVERVIEW',
        'heading_1'      => 'How A',
        'heading_accent' => 'Furutec Busduct System',
        'heading_2'      => 'Comes Together.',
        'subtitle'       => 'Click any number on the diagram, or use the arrows below the card, to see how each component fits into the system.',
        'diagram'        => 'PRODUCT OVERVIEW 2.png',

        'card_1_title' => 'Outdoor Busduct',
        'card_2_title' => 'Indoor Busduct',
        'card_3_title' => 'Data Centre Solution',
        'card_4_title' => 'Power Monitoring',
        'card_5_title' => 'Lighting Busduct',
    ),

    // -------- OUR PRODUCTS (5 cards) ----------
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

    // -------- CERTIFICATES & PORTFOLIO CTA ----------
    'certs_cta' => array(
        // Full URL supported — starts with http:// or https:// → used as-is.
        // On upload, this becomes a local filename like "uploads/2026-xx-xx-xxxx.jpg".
        'bg_image'       => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=2000&q=80&auto=format&fit=crop',
        'heading_1'      => 'Certificates &',
        'heading_2'      => 'Portfolio',
        'subtitle'       => 'Where Credential Meets Capability.',
        'button_1_label' => 'Certificates',
        'button_2_label' => 'Portfolio',
    ),

    // -------- GET IN TOUCH CTA ----------
    'quote_cta' => array(
        'bg_image'     => '1.png',
        'eyebrow'      => 'Get In Touch',
        'heading_1'    => 'Ready to specify your',
        'heading_2'    => 'next busduct project?',
        'lede'         => 'Tell us about your load, location and timeline. Our engineering team will scope a busduct configuration tailored to your project.',
        'button_label' => 'Get In Touch',
    ),

    // -------- FOOTER ----------
    'footer' => array(
        'logo'         => 'Logo-white.png',
        'company_name' => 'Furutec Electrical Sdn Bhd',
        'company_reg'  => 'Company No. 198001003423 (57207-W)',
        'email'        => 'info@furutec.com.my',
        'phone'        => '+6012-887 4517',
        'location_1'   => 'Subang Jaya, Selangor',
        'location_2'   => 'Penang, Malaysia',

        'col_1_heading' => 'Product',
        'col_2_heading' => 'Industries',
        'col_3_heading' => 'Support',
        'col_4_heading' => 'Company',

        'copyright' => '© 2026 Furutec Electrical Sdn Bhd. All Rights Reserved.',

        'social_facebook' => '#',
        'social_youtube'  => '#',
        'social_linkedin' => '#',
        'social_whatsapp' => '#',
    ),

);
