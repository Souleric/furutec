<?php
// Furutec Editor — content values.
// Nested by page.  `shared` sections (nav, footer) appear on every page and
// are propagated to every template on publish.  Per-page content only affects
// that single page.
//
// The editor writes to this file when Amy saves.  Never edit by hand once
// deployed — do it through /editor.

return array(

    // ============================================================
    // SHARED — appears on every page (nav + footer).
    // Publish syncs these to all page templates.
    // ============================================================
    'shared' => array(

        'nav' => array(
            'logo'                 => 'Logo.jpeg',
            'phone'                => '+6012-887 4517',
            'contact_button_label' => 'Contact Us',
        ),

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
            'social_whatsapp' => 'https://wa.me/60128874517',
        ),

    ),

    // ============================================================
    // HOMEPAGE (index.html)
    // ============================================================
    'home' => array(

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

        'certs_cta' => array(
            'bg_image'       => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=2000&q=80&auto=format&fit=crop',
            'heading_1'      => 'Certificates &',
            'heading_2'      => 'Portfolio',
            'subtitle'       => 'Where Credential Meets Capability.',
            'button_1_label' => 'Certificates',
            'button_2_label' => 'Portfolio',
        ),

        'quote_cta' => array(
            'bg_image'     => '1.png',
            'eyebrow'      => 'Get In Touch',
            'heading_1'    => 'Ready to specify your',
            'heading_2'    => 'next busduct project?',
            'lede'         => 'Tell us about your load, location and timeline. Our engineering team will scope a busduct configuration tailored to your project.',
            'button_label' => 'Get In Touch',
        ),

    ),

    // ============================================================
    // CERTIFICATES PAGE (portfolio.html)
    // ============================================================
    'certificates' => array(

        'cert_hero' => array(
            'eyebrow'   => 'CERTIFICATE CREDENTIAL',
            'heading_1' => 'Type-tested.',
            'heading_2' => 'Globally certified.',
            'subtitle'  => 'With the innovation of our R&D team, Furutec Busduct System is successfully type-tested in compliance with international standards and certified by reputable certification bodies across Asia, Europe, and North America.',
        ),

        'cert_grid' => array(
            'card_1_image' => 'CERTS LOGOS/UL_Mark (1).svg',
            'card_1_alt'   => 'UL',
            'card_1_sub'   => 'Underwriters Laboratories',

            'card_2_image' => 'CERTS LOGOS/Dekra.png',
            'card_2_alt'   => 'DEKRA',
            'card_2_sub'   => 'Type-test laboratory',

            'card_3_image' => 'certs/intertek.svg',
            'card_3_alt'   => 'Intertek',
            'card_3_sub'   => 'Intertek · Independent test & verification',

            'card_4_image' => 'CERTS LOGOS/SGBP.jpg',
            'card_4_alt'   => 'SGBC',
            'card_4_sub'   => 'Singapore Green Building Product (SGBC)',

            'card_5_image' => 'CERTS LOGOS/TUV.png',
            'card_5_alt'   => 'TÜV SÜD',
            'card_5_sub'   => 'TÜV SÜD · PSB Singapore',

            'card_6_image' => 'CERTS LOGOS/ISO 9001 Certified_col.png',
            'card_6_alt'   => 'ISO 9001 Certified',
            'card_6_sub'   => 'ISO 9001 Certified Quality Management',

            'card_7_image' => 'CERTS LOGOS/Malaysian Brand.png',
            'card_7_alt'   => 'Malaysian Brand',
            'card_7_sub'   => 'Tier-1 manufacturer recognition',

            'card_8_image' => 'CERTS LOGOS/Sirim Quality Award.jpg',
            'card_8_alt'   => 'SIRIM QAS International',
            'card_8_sub'   => 'SIRIM QAS · Malaysian quality recognition',

            'card_9_image' => 'CERTS LOGOS/SIRIM ECO.png',
            'card_9_alt'   => 'SIRIM Eco-Label',
            'card_9_sub'   => 'SIRIM Eco-Label · Malaysia environmental mark',
        ),

        'cert_stand' => array(
            'heading' => 'Where Credential Meets Capability.',
            'body'    => 'Our certifications aren\'t shelfware — they reflect three decades of validated engineering, rigorous manufacturing control, and a track record of safe deployments across Asia, the Middle East, and beyond.',
            'cta_label' => 'See our Track Record',
        ),

    ),

    // ============================================================
    // PORTFOLIO PAGE (portfolio-track-record.html)
    // ============================================================
    'portfolio' => array(

        'port_hero' => array(
            'eyebrow'   => 'TRACK RECORD',
            'heading_1' => 'Trusted across',
            'heading_2' => 'every industry.',
            'subtitle'  => 'Furutec busduct systems power facilities where reliability is not optional — from heavy industry to mission-critical infrastructure.',
        ),

        'port_grid' => array(
            'card_1_image' => 'Portfolio/data centre.png',
            'card_1_tag'   => 'High Reliability',
            'card_1_title' => 'Data Centre',
            'card_1_desc'  => 'High-capacity busduct systems designed for data centres, ensuring reliable power distribution, scalability, and zero downtime with plug-and-play expansion.',

            'card_2_image' => 'Portfolio/infrastructure.png',
            'card_2_tag'   => 'Heavy Duty',
            'card_2_title' => 'Infrastructure (MRT & Train Station)',
            'card_2_desc'  => 'Robust and safe power distribution solutions for MRT and railway infrastructure, supporting continuous operation in high-traffic environments.',

            'card_3_image' => 'Portfolio/medical.png',
            'card_3_tag'   => 'Mission Critical',
            'card_3_title' => 'Medical & Healthcare',
            'card_3_desc'  => 'Ensures uninterrupted and safe power supply for hospitals and healthcare facilities with redundant and low-maintenance systems.',

            'card_4_image' => 'Portfolio/industrial.png',
            'card_4_tag'   => 'Up to 6,500 A',
            'card_4_title' => 'Industrial',
            'card_4_desc'  => 'Reliable high-capacity busduct systems for manufacturing, automotive, and heavy industries with efficient energy distribution.',

            'card_5_image' => 'Portfolio/oil and gas.png',
            'card_5_tag'   => 'Harsh Environment',
            'card_5_title' => 'Oil & Gas',
            'card_5_desc'  => 'Durable and corrosion-resistant systems designed for demanding oil and gas environments, ensuring safety and operational reliability.',

            'card_6_image' => 'Portfolio/commercial.png',
            'card_6_tag'   => 'Space Efficient',
            'card_6_title' => 'Commercial',
            'card_6_desc'  => 'Compact and flexible power solutions for offices, malls, and commercial buildings, optimizing space and reducing installation complexity.',

            'card_7_image' => 'Portfolio/residential.png',
            'card_7_tag'   => 'Safe & Compact',
            'card_7_title' => 'Residential',
            'card_7_desc'  => 'Efficient and safe power distribution for residential developments, supporting modern living with compact design and reliability.',

            'card_8_image' => 'Portfolio/education.png',
            'card_8_tag'   => 'Energy Efficient',
            'card_8_title' => 'Education',
            'card_8_desc'  => 'Cost-effective and efficient systems for schools and universities, ensuring stable power for daily operations and future expansion.',

            'card_9_image' => 'Portfolio/government.png',
            'card_9_tag'   => 'High Compliance',
            'card_9_title' => 'Government',
            'card_9_desc'  => 'Trusted power distribution systems for government facilities, meeting strict regulatory standards and ensuring long-term reliability.',
        ),

    ),

    // ============================================================
    // CONTACT PAGE (quote.html)
    // ============================================================
    'contact' => array(

        'contact_hero' => array(
            'eyebrow'   => 'GET IN TOUCH',
            'heading_1' => 'Ready to specify your',
            'heading_2' => 'next busduct project?',
        ),

        'contact_form' => array(
            'title'    => 'Furutec Busduct System',
            'reassure' => 'Response within 24 hours · No Spam · Dedicated Engineering Support',
        ),

        'contact_locations' => array(
            'company_name'    => 'Furutec Electrical Sdn Bhd',
            'loc_1_label'     => 'Headquarters',
            'loc_1_addr'      => "Lot Block A, 4, Jalan SS 13/7,\nSubang Jaya Industrial Estate,\n47500 Subang Jaya, Selangor.",
            'loc_1_maps_url'  => 'https://www.google.com/maps/dir/?api=1&destination=Lot+Block+A%2C+4%2C+Jalan+SS+13%2F7%2C+Subang+Jaya+Industrial+Estate%2C+47500+Subang+Jaya%2C+Selangor',
            'loc_2_label'     => 'Manufacturing Plant',
            'loc_2_addr'      => "MK13, Plot 89, Lorong Perindustrian Bukit Minyak 11,\nKawasan Perindustrian Bukit Minyak,\n14100 Simpang Ampat, Penang.",
            'loc_2_maps_url'  => 'https://www.google.com/maps/dir/?api=1&destination=MK13%2C+Plot+89%2C+Lorong+Perindustrian+Bukit+Minyak+11%2C+Kawasan+Perindustrian+Bukit+Minyak%2C+14100+Simpang+Ampat%2C+Penang',
            'map_tab_1_label' => 'Subang Jaya HQ',
            'map_tab_2_label' => 'Penang Plant',
        ),

        'contact_direct' => array(
            'tile_1_label'        => 'Email',
            'tile_1_value'        => 'info@furutec.com.my',
            'tile_2_label'        => 'Phone',
            'tile_2_value'        => '+6012-887 4517',
            'tile_3_label'        => 'Working Hours',
            'tile_3_value_line_1' => '8:30 AM – 6:00 PM',
            'tile_3_value_line_2' => 'Monday to Friday',
        ),

    ),

    // ============================================================
    // PRODUCT PAGE (product.html)
    // ============================================================
    'product' => array(

        'product_intro' => array(
            'eyebrow'        => 'OUR PRODUCTS',
            'heading_1'      => 'Complete busduct solutions',
            'heading_accent' => 'for every environment.',
            'subtitle'       => 'From standard indoor installations to intelligent data center distribution. Furutec has the right system for your project.',
        ),

        'product_nav' => array(
            'indoor_label'     => '01 · Indoor Busduct',
            'outdoor_label'    => '02 · Outdoor Busduct',
            'datacenter_label' => '03 · Data Centre Solution',
            'power_label'      => '04 · Power Monitoring',
            'lighting_label'   => '05 · Lighting Busduct',
        ),

        'product_buttons' => array(
            'contact_label'   => 'Contact Us',
            'catalogue_label' => 'Request a Catalogue',
            'catalogue_url'   => 'quote.html',
        ),

        // ---- 01 · Indoor ----
        'product_indoor' => array(
            'section_title' => 'INDOOR BUSDUCT SOLUTION',
            'tag_label'     => 'Most Popular',
            'title'         => 'AH & AH-ES BUSDUCT',
            'subtitle'      => 'Integrated Power Distribution for Every Scale',
            'desc'          => 'The sandwich-type busduct designed for high-capacity power transmission with minimal voltage drop. Its compact architecture makes it ideal for tight riser shafts in modern architecture.',

            'slide_1_image' => 'Indoor Busduct Solution/picture 4.png',
            'slide_1_alt'   => 'AH & AH-ES Busduct — insulation and bolt detail',
            'slide_2_image' => 'Indoor Busduct Solution/Picture 1.png',
            'slide_2_alt'   => 'AH & AH-ES Busduct — ceiling installation',
            'slide_3_image' => 'Indoor Busduct Solution/picture 2.png',
            'slide_3_alt'   => 'AH & AH-ES Busduct — power distribution panels overhead',
            'slide_4_image' => 'Indoor Busduct Solution/picture 3.png',
            'slide_4_alt'   => 'AH & AH-ES Busduct — L1 L2 L3 N connection close-up',
            'slide_5_image' => 'Indoor Busduct Solution/picture 5.jpg',
            'slide_5_alt'   => 'AH & AH-ES Busduct — ceiling-mounted installation',

            'feature_1' => 'Compact Sandwich Construction',
            'feature_2' => 'Extruded Aluminium Alloy Housing',
            'feature_3' => 'Corrosion Resistant',
            'feature_4' => 'Excellent Heat Dissipation',
            'feature_5' => '50% Earth Bar or 100% Housing Ground System',
            'feature_6' => 'Seismic Zone 4 Protection',
            'feature_7' => 'IK10 Mechanical Impact',
            'feature_8' => 'Complete IP66',

            'spec_1_label' => 'Current Rating',      'spec_1_value' => '500 A – 6,300 A',
            'spec_2_label' => 'Voltage',             'spec_2_value' => 'Up to 1,000 V',
            'spec_3_label' => 'Compliance Standard', 'spec_3_value' => 'IEC 61439-6 & UL 857',
            'spec_4_label' => 'Insulation Material', 'spec_4_value' => 'Polyester Film / Epoxy (Optional)',
            'spec_5_label' => 'Conductor',           'spec_5_value' => 'Copper / Aluminium',

            'diagram_title'  => 'AH & AH-ES — Component Diagram Breakdown',
            'diagram_video'  => 'products/indoor-diagram video.mp4',
            'diagram_poster' => 'products/indoor-diagram-poster.jpg',
        ),

        // ---- 02 · Outdoor ----
        'product_outdoor' => array(
            'section_title' => 'OUTDOOR BUSDUCT SOLUTION',
            'tag_label'     => 'Weatherproof',
            'title'         => 'CAST RESIN BUSDUCT',
            'subtitle'      => 'Safe by Design, Powered by Resin',
            'desc'          => 'Built for the most extreme environments. The CR busduct is encapsulated in a specialized cast resin that provides total protection against water, dust, and chemical corrosion.',

            'slide_1_image' => 'products/CR (OUTDOOR)/cr 1.png',
            'slide_1_alt'   => 'Cast Resin Busduct — view 1',
            'slide_2_image' => 'products/CR (OUTDOOR)/cr 2.png',
            'slide_2_alt'   => 'Cast Resin Busduct — view 2',
            'slide_3_image' => 'products/CR (OUTDOOR)/cr 3.png',
            'slide_3_alt'   => 'Cast Resin Busduct — view 3',
            'slide_4_image' => 'products/CR (OUTDOOR)/cr 4.png',
            'slide_4_alt'   => 'Cast Resin Busduct — view 4',
            'slide_5_image' => 'products/CR (OUTDOOR)/cr 5.png',
            'slide_5_alt'   => 'Cast Resin Busduct — view 5',

            'feature_1' => 'Compliant with BS 6387, IEC 60331, JIS C8364 & CNS 14286',
            'feature_2' => 'Mineral-Insulated Cast Resin Type',
            'feature_3' => 'Excellent Heat Dissipation',
            'feature_4' => 'Resistant to Water, Fire & Corrosion',
            'feature_5' => 'Seismic Zone 4 Protection',
            'feature_6' => 'IK10 Mechanical Impact',
            'feature_7' => 'High Degree Protection of IP68',

            'spec_1_label' => 'Current Rating',      'spec_1_value' => '400 A – 6,300 A',
            'spec_2_label' => 'IP Rating',           'spec_2_value' => 'IP68',
            'spec_3_label' => 'Compliance Standard', 'spec_3_value' => 'IEC 61439-6 & IEC 61439-1',
            'spec_4_label' => 'Voltage',             'spec_4_value' => 'Up to 1,000 V',
            'spec_5_label' => 'Conductor',           'spec_5_value' => 'Copper / Aluminium',

            'diagram_title'  => 'Cast Resin — Component Diagram Breakdown',
            // Note: original live file has `?v=2` cachebust on these; dropped
            // because URL-encoding the `?` would break the query. The video
            // still loads correctly without the cachebust.
            'diagram_video'  => 'products/CR (OUTDOOR)/Furutec_Busduct Assembly.mp4',
            'diagram_poster' => 'products/CR (OUTDOOR)/Furutec_Busduct Assembly-poster.jpg',
        ),

        // ---- 03 · Data Centre ----
        'product_datacenter' => array(
            'section_title' => 'DATA CENTRE BUSDUCT SOLUTION',
            'tag_label'     => 'Featured',
            'title'         => 'i-DC BUSDUCT',
            'subtitle'      => 'High Density Power, Zero Downtime',
            'desc'          => 'In an era where AI, Cloud, and Edge computing define global progress, uptime is the only currency that matters. Furutec i-DC is not just a busduct — it is a high-performance infrastructure solution engineered to eliminate risk and empower scalability.',

            'slide_1_image' => 'products/I-DC(DATA CENTRE)/i-dc 1.png',
            'slide_1_alt'   => 'i-DC Busduct — view 1',
            'slide_2_image' => 'products/I-DC(DATA CENTRE)/i-dc 2.png',
            'slide_2_alt'   => 'i-DC Busduct — view 2',
            'slide_3_image' => 'products/I-DC(DATA CENTRE)/i dc 3.png',
            'slide_3_alt'   => 'i-DC Busduct — view 3',
            'slide_4_image' => 'products/I-DC(DATA CENTRE)/i-dc 4.png',
            'slide_4_alt'   => 'i-DC Busduct — view 4',
            'slide_5_image' => 'products/I-DC(DATA CENTRE)/i dc 5.png',
            'slide_5_alt'   => 'i-DC Busduct — view 5',
            'slide_6_image' => 'products/I-DC(DATA CENTRE)/idc 6.png',
            'slide_6_alt'   => 'i-DC Busduct — view 6',

            'feature_1'  => 'Compliant with IEC 61439-6',
            'feature_2'  => 'Space-Efficient Modular & Compact Design',
            'feature_3'  => 'Flexibility & Scalability for Future Expansion',
            'feature_4'  => 'Degree Protection of IP40 / 54',
            'feature_5'  => 'Quick Installation & Lower Installation Cost',
            'feature_6'  => 'Maintenance-Free',
            'feature_7'  => 'Eco-Friendly & Reusable',
            'feature_8'  => 'Customizable Tap Off Units',
            'feature_9'  => 'Safe Live Installation of Tap Off Units',
            'feature_10' => 'Integrated with Branch Circuit Power Monitoring',

            'spec_1_label' => 'Busduct Current Rating', 'spec_1_value' => '250 A · 400 A · 630 A · 800 A',
            'spec_2_label' => 'TOU Current Rating',     'spec_2_value' => '16 A · 32 A · 63 A · 100 A',
            'spec_3_label' => 'Compliance Standard',    'spec_3_value' => 'IEC 61439-6 & IEC 61439-1',
            'spec_4_label' => 'Conductor',              'spec_4_value' => 'Copper',
            'spec_5_label' => 'Housing Material',       'spec_5_value' => 'Extruded Aluminium-Alloy',

            'diagram_title'  => 'i-DC Busduct — Component Diagram Breakdown',
            'diagram_video'  => 'products/I-DC(DATA CENTRE)/DATA CENTRE BUSDUCT SOLUTION video.mp4',
            'diagram_poster' => 'products/I-DC(DATA CENTRE)/DATA CENTRE BUSDUCT SOLUTION-poster.jpg',
        ),

        // ---- 04 · Power Monitoring (no specs, no diagram) ----
        'product_power' => array(
            'section_title' => 'POWER MONITORING SOLUTION',
            'tag_label'     => 'Smart System',
            'title'         => 'POWER MONITORING SYSTEM',
            'subtitle'      => 'Precision Monitoring, Proven Savings',
            'desc'          => 'Furutec\'s Power Monitoring system uses sensors to collect real-time data on electricity usage and potential issues, then displays everything on a user-friendly HMI screen. This allows you to see exactly where energy is being wasted, prevent power overloads before they cause a blackout, and ensure electricity is distributed evenly and safely across the entire system.',

            'slide_1_image' => 'products/POWER MONITORING SYSTEM/pms 1.png',
            'slide_1_alt'   => 'Power Monitoring System — view 1',
            'slide_2_image' => 'products/POWER MONITORING SYSTEM/pms 2.jpg',
            'slide_2_alt'   => 'Power Monitoring System — view 2',
            'slide_3_image' => 'products/POWER MONITORING SYSTEM/pms 3.png',
            'slide_3_alt'   => 'Power Monitoring System — view 3',

            'feature_1' => 'Real-Time Energy Telemetry',
            'feature_2' => 'Per-Circuit Current & Voltage Sensing',
            'feature_3' => 'User-Friendly HMI Display',
            'feature_4' => 'Overload & Anomaly Alerts',
            'feature_5' => 'Identify Energy Wastage',
            'feature_6' => 'Even Load Distribution Across Network',
        ),

        // ---- 05 · Lighting (paired diagram: LB + PB) ----
        'product_lighting' => array(
            'section_title' => 'LIGHTING BUSDUCT SOLUTION',
            'tag_label'     => 'Reliability',
            'title'         => 'LB&PB BUSDUCT',
            'subtitle'      => 'Where Light Meets Performance',
            'desc'          => 'The Lighting Busduct system enables efficient installation and easy maintenance with detachable, reusable components that reduce costs, while the Power Busduct system ensures safe, reliable, and efficient power distribution through its wide phase spacing design, enhancing insulation performance for diverse project needs.',

            'slide_1_image' => 'products/LB&PB (LIGHTING)/lb 1.png',
            'slide_1_alt'   => 'LB & PB Busduct — view 1',
            'slide_2_image' => 'products/LB&PB (LIGHTING)/lb 2.png',
            'slide_2_alt'   => 'LB & PB Busduct — view 2',
            'slide_3_image' => 'products/LB&PB (LIGHTING)/lb 3.png',
            'slide_3_alt'   => 'LB & PB Busduct — view 3',
            'slide_4_image' => 'products/LB&PB (LIGHTING)/lb 4.png',
            'slide_4_alt'   => 'LB & PB Busduct — view 4',
            'slide_5_image' => 'products/LB&PB (LIGHTING)/pb 1.png',
            'slide_5_alt'   => 'LB & PB Busduct — view 5',
            'slide_6_image' => 'products/LB&PB (LIGHTING)/pb 2.png',
            'slide_6_alt'   => 'LB & PB Busduct — view 6',
            'slide_7_image' => 'products/LB&PB (LIGHTING)/pb 3.png',
            'slide_7_alt'   => 'LB & PB Busduct — view 7',

            'feature_1' => 'Detachable, Reusable Components',
            'feature_2' => 'Efficient, Low-Cost Installation',
            'feature_3' => 'Wide Phase-Spacing Design',
            'feature_4' => 'Enhanced Insulation Performance',
            'feature_5' => 'Compact Ceiling / Perimeter Profile',
            'feature_6' => 'Plug-In Tap-Off Flexibility',

            'spec_1_label' => 'LB Current Rating',        'spec_1_value' => 'Copper · 16A ~ 40A',
            'spec_2_label' => 'PB Current Rating',        'spec_2_value' => 'Copper 63A ~ 400A · Aluminium 100A ~ 630A',
            'spec_3_label' => 'IP Rating',                'spec_3_value' => 'IP40 / IP54',
            'spec_4_label' => 'Compliance Standard',      'spec_4_value' => 'IEC 61439-6 & IEC 61439-1',
            'spec_5_label' => 'Conductor Material',       'spec_5_value' => 'LB Copper · PB Copper & Aluminium',
            'spec_6_label' => 'System Frequency',         'spec_6_value' => '50 / 60 Hz',
            'spec_7_label' => 'Operational Voltage (AC)', 'spec_7_value' => '230 / 400 V',

            'diagram_title'    => 'LB & PB — Component Diagram Breakdown',
            'diagram_1_label'  => 'LB Busduct',
            'diagram_1_video'  => 'products/LB&PB (LIGHTING)/LB Busduct.mp4',
            'diagram_1_poster' => 'products/LB&PB (LIGHTING)/LB Busduct-poster.jpg',
            'diagram_2_label'  => 'PB Busduct',
            'diagram_2_video'  => 'products/LB&PB (LIGHTING)/PB Busduct.mp4',
            'diagram_2_poster' => 'products/LB&PB (LIGHTING)/PB Busduct-poster.jpg',
        ),

    ),

);
