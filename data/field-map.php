<?php
// Furutec Editor — field definitions (multi-page).
// - `pages` lists each editable page: its label, template file, live output file,
//   and which sections (from the `sections` map) appear on that page.
// - `shared_sections` are section slugs that appear on EVERY page — publish
//   syncs them across all templates. They also show at the top of every page's
//   accordion in the editor UI.
// - `sections` is the master map of section definitions (fields).

return array(

    'pages' => array(
        'home' => array(
            'label'     => 'Homepage',
            'template'  => 'home.template.html',
            'live_file' => 'index.html',
            'sections'  => array('hero', 'origin', 'facilities', 'company', 'product_overview', 'products', 'certs_cta', 'quote_cta'),
        ),
        'certificates' => array(
            'label'     => 'Certificates page',
            'template'  => 'certificates.template.html',
            'live_file' => 'portfolio.html',
            'sections'  => array('cert_hero', 'cert_grid', 'cert_stand'),
        ),
    ),

    'shared_sections' => array('nav', 'footer'),

    'sections' => array(

        // ============================================================
        // SHARED: NAV (appears on every page)
        // ============================================================
        'nav' => array(
            'label'   => 'Header / Navigation (all pages)',
            'summary' => 'Logo, phone, and Contact button. Appears on every page.',
            'shared'  => true,
            'fields'  => array(
                'logo' => array('type'=>'image','label'=>'Header logo','help'=>'PNG or JPG. Currently 54 px tall on desktop.','max_mb'=>5),
                'phone' => array('type'=>'text','label'=>'Phone number','help'=>'Shown in the top-right of every page.','max'=>40),
                'contact_button_label' => array('type'=>'text','label'=>'Contact button label','max'=>30),
            ),
        ),

        // ============================================================
        // SHARED: FOOTER (appears on every page)
        // ============================================================
        'footer' => array(
            'label'   => 'Footer (all pages)',
            'summary' => 'Logo, contact info, column headings, copyright, social URLs.',
            'shared'  => true,
            'fields'  => array(
                'logo'    => array('type'=>'image','label'=>'Footer logo (white)','max_mb'=>5),
                'company_name' => array('type'=>'text','label'=>'Company name','max'=>80),
                'company_reg'  => array('type'=>'text','label'=>'Company registration number','max'=>80),
                'email'   => array('type'=>'text','label'=>'Email address','max'=>80),
                'phone'   => array('type'=>'text','label'=>'Phone number','max'=>40),
                'location_1' => array('type'=>'text','label'=>'Location line 1','max'=>80),
                'location_2' => array('type'=>'text','label'=>'Location line 2','max'=>80),
                'col_1_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 1 heading','max'=>30),
                'col_2_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 2 heading','max'=>30),
                'col_3_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 3 heading','max'=>30),
                'col_4_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 4 heading','max'=>30),
                'copyright' => array('type'=>'text','group'=>'Bottom bar','label'=>'Copyright line','max'=>200),
                'social_facebook' => array('type'=>'url','group'=>'Social media URLs','label'=>'Facebook URL','max'=>200),
                'social_youtube'  => array('type'=>'url','group'=>'Social media URLs','label'=>'YouTube URL','max'=>200),
                'social_linkedin' => array('type'=>'url','group'=>'Social media URLs','label'=>'LinkedIn URL','max'=>200),
                'social_whatsapp' => array('type'=>'url','group'=>'Social media URLs','label'=>'WhatsApp URL','help'=>'Format: https://wa.me/60128874517','max'=>200),
            ),
        ),

        // ============================================================
        // HOMEPAGE SECTIONS
        // ============================================================
        'hero' => array(
            'label'   => 'Hero (top of homepage)',
            'summary' => 'Big headline over the background video.',
            'fields'  => array(
                'headline_1' => array('type'=>'text','label'=>'Headline · Line 1','help'=>'Big white text at the top of the hero.','max'=>60),
                'headline_2' => array('type'=>'text','label'=>'Headline · Line 2 (accent color)','max'=>60),
                'lede'       => array('type'=>'textarea','label'=>'Subtitle paragraph','rows'=>3,'max'=>400),
                'bg_video'   => array('type'=>'video','label'=>'Background video','help'=>'Auto-plays behind the hero. Muted, looped. MP4 only.','max_mb'=>30),
            ),
        ),

        'origin' => array(
            'label'   => 'Inside Furutec (Origin section)',
            'summary' => 'Origin of Furutec headline, body, and factory video.',
            'fields'  => array(
                'eyebrow' => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading' => array('type'=>'text','label'=>'Section heading','max'=>60),
                'body'    => array('type'=>'textarea','label'=>'Body paragraph','rows'=>5,'max'=>800),
                'video'   => array('type'=>'video','label'=>'Video','max_mb'=>30),
                'poster'  => array('type'=>'image','label'=>'Video thumbnail','max_mb'=>5),
                'caption' => array('type'=>'text','label'=>'Video caption','max'=>80),
            ),
        ),

        'facilities' => array(
            'label'   => 'Bukit Minyak Facilities',
            'summary' => 'Plant name, description, 4 badges, 6 photos.',
            'fields'  => array(
                'tag'     => array('type'=>'text','label'=>'Section tag','max'=>60),
                'name'    => array('type'=>'text','label'=>'Plant name','max'=>60),
                'desc_1'  => array('type'=>'markdown','label'=>'Description paragraph 1','rows'=>4,'max'=>800),
                'desc_2'  => array('type'=>'markdown','label'=>'Description paragraph 2','rows'=>4,'max'=>800),
                'badge_1_label' => array('type'=>'text','group'=>'Badge 1','label'=>'Label','help'=>'Use / for line breaks.','max'=>60),
                'badge_1_sub'   => array('type'=>'text','group'=>'Badge 1','label'=>'Sublabel','max'=>60),
                'badge_2_label' => array('type'=>'text','group'=>'Badge 2','label'=>'Label','max'=>60),
                'badge_2_sub'   => array('type'=>'text','group'=>'Badge 2','label'=>'Sublabel','max'=>60),
                'badge_3_label' => array('type'=>'text','group'=>'Badge 3','label'=>'Label','max'=>60),
                'badge_3_sub'   => array('type'=>'text','group'=>'Badge 3','label'=>'Sublabel','max'=>60),
                'badge_4_label' => array('type'=>'text','group'=>'Badge 4','label'=>'Label','max'=>60),
                'badge_4_sub'   => array('type'=>'text','group'=>'Badge 4','label'=>'Sublabel','max'=>60),
                'photo_aerial' => array('type'=>'image','group'=>'Main photos','label'=>'Aerial plant photo','max_mb'=>5),
                'photo_front'  => array('type'=>'image','group'=>'Main photos','label'=>'Plant entrance photo','max_mb'=>5),
                'process_1' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 1','max_mb'=>5),
                'process_2' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 2','max_mb'=>5),
                'process_3' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 3','max_mb'=>5),
                'process_4' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 4','max_mb'=>5),
            ),
        ),

        'company' => array(
            'label'   => 'Company Profile',
            'summary' => 'Zoom-reveal section with logo video, 3-line heading, body copy, and CTA.',
            'fields'  => array(
                'bg_video'  => array('type'=>'video','label'=>'Background logo video','max_mb'=>30),
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'heading_3' => array('type'=>'text','label'=>'Heading · Line 3','max'=>60),
                'body_1'    => array('type'=>'textarea','label'=>'Body paragraph 1','rows'=>4,'max'=>800),
                'body_2'    => array('type'=>'textarea','label'=>'Body paragraph 2','rows'=>4,'max'=>800),
                'button_label' => array('type'=>'text','group'=>'CTA button','label'=>'Button label','max'=>30),
                'button_url'   => array('type'=>'url','group'=>'CTA button','label'=>'Button URL','max'=>200),
            ),
        ),

        'product_overview' => array(
            'label'   => 'Product Overview (diagram)',
            'summary' => 'Interactive diagram with 5 hotspots. Card titles editable; positions fixed.',
            'fields'  => array(
                'eyebrow'  => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1'=> array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_accent'=> array('type'=>'text','label'=>'Heading · Accent phrase','max'=>60),
                'heading_2'=> array('type'=>'text','label'=>'Heading · Line 2','max'=>60),
                'subtitle' => array('type'=>'textarea','label'=>'Subtitle','rows'=>2,'max'=>300),
                'diagram'  => array('type'=>'image','label'=>'Diagram image','max_mb'=>5),
                'card_1_title' => array('type'=>'text','group'=>'Card 1','label'=>'Card title','max'=>40),
                'card_2_title' => array('type'=>'text','group'=>'Card 2','label'=>'Card title','max'=>40),
                'card_3_title' => array('type'=>'text','group'=>'Card 3','label'=>'Card title','max'=>40),
                'card_4_title' => array('type'=>'text','group'=>'Card 4','label'=>'Card title','max'=>40),
                'card_5_title' => array('type'=>'text','group'=>'Card 5','label'=>'Card title','max'=>40),
            ),
        ),

        'products' => array(
            'label'   => 'Our Products (5 cards)',
            'summary' => 'The 5 product-solution cards on the homepage.',
            'fields'  => array(
                'section_eyebrow'   => array('type'=>'text','label'=>'Section eyebrow','max'=>40),
                'section_heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>80),
                'section_heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'section_subtitle'  => array('type'=>'textarea','label'=>'Section subtitle','rows'=>3,'max'=>400),
                'card_1_title'       => array('type'=>'text','group'=>'Card 1 · Indoor','label'=>'Card title','max'=>40),
                'card_1_description' => array('type'=>'markdown','group'=>'Card 1 · Indoor','label'=>'Description','rows'=>4,'max'=>500),
                'card_2_title'       => array('type'=>'text','group'=>'Card 2 · Outdoor','label'=>'Card title','max'=>40),
                'card_2_description' => array('type'=>'markdown','group'=>'Card 2 · Outdoor','label'=>'Description','rows'=>4,'max'=>500),
                'card_3_title'       => array('type'=>'text','group'=>'Card 3 · Data Centre','label'=>'Card title','max'=>40),
                'card_3_description' => array('type'=>'markdown','group'=>'Card 3 · Data Centre','label'=>'Description','rows'=>4,'max'=>500),
                'card_4_title'       => array('type'=>'text','group'=>'Card 4 · Power Monitoring','label'=>'Card title','max'=>40),
                'card_4_description' => array('type'=>'markdown','group'=>'Card 4 · Power Monitoring','label'=>'Description','rows'=>4,'max'=>500),
                'card_5_title'       => array('type'=>'text','group'=>'Card 5 · Lighting','label'=>'Card title','max'=>40),
                'card_5_description' => array('type'=>'markdown','group'=>'Card 5 · Lighting','label'=>'Description','rows'=>4,'max'=>500),
            ),
        ),

        'certs_cta' => array(
            'label'   => 'Certificates & Portfolio CTA',
            'summary' => 'Dark full-width band with background image, headline, and two buttons.',
            'fields'  => array(
                'bg_image'  => array('type'=>'image','label'=>'Background image','max_mb'=>5),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'subtitle'  => array('type'=>'text','label'=>'Subtitle','max'=>200),
                'button_1_label' => array('type'=>'text','group'=>'Button 1','label'=>'Button label','max'=>30),
                'button_2_label' => array('type'=>'text','group'=>'Button 2','label'=>'Button label','max'=>30),
            ),
        ),

        'quote_cta' => array(
            'label'   => 'Get In Touch CTA (bottom)',
            'summary' => 'Bottom-page CTA that links to the quote form.',
            'fields'  => array(
                'bg_image'  => array('type'=>'image','label'=>'Background image','max_mb'=>5),
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'lede'      => array('type'=>'textarea','label'=>'Body text','rows'=>3,'max'=>400),
                'button_label' => array('type'=>'text','label'=>'Button label','max'=>30),
            ),
        ),

        // ============================================================
        // CERTIFICATES PAGE SECTIONS (portfolio.html)
        // ============================================================
        'cert_hero' => array(
            'label'   => 'Certificates hero',
            'summary' => 'Top of the Certificates page — eyebrow, 2-line heading, subtitle.',
            'fields'  => array(
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'subtitle'  => array('type'=>'textarea','label'=>'Subtitle','rows'=>4,'max'=>600),
            ),
        ),

        'cert_grid' => array(
            'label'   => 'Certificate grid (9 cards)',
            'summary' => 'Each of the 9 certificate cards has a logo image, alt text, and caption.',
            'fields'  => array(
                'card_1_image' => array('type'=>'image','group'=>'Card 1','label'=>'Logo image','max_mb'=>2),
                'card_1_alt'   => array('type'=>'text','group'=>'Card 1','label'=>'Alt text (screen readers)','max'=>60),
                'card_1_sub'   => array('type'=>'text','group'=>'Card 1','label'=>'Caption','max'=>120),

                'card_2_image' => array('type'=>'image','group'=>'Card 2','label'=>'Logo image','max_mb'=>2),
                'card_2_alt'   => array('type'=>'text','group'=>'Card 2','label'=>'Alt text','max'=>60),
                'card_2_sub'   => array('type'=>'text','group'=>'Card 2','label'=>'Caption','max'=>120),

                'card_3_image' => array('type'=>'image','group'=>'Card 3','label'=>'Logo image','max_mb'=>2),
                'card_3_alt'   => array('type'=>'text','group'=>'Card 3','label'=>'Alt text','max'=>60),
                'card_3_sub'   => array('type'=>'text','group'=>'Card 3','label'=>'Caption','max'=>120),

                'card_4_image' => array('type'=>'image','group'=>'Card 4','label'=>'Logo image','max_mb'=>2),
                'card_4_alt'   => array('type'=>'text','group'=>'Card 4','label'=>'Alt text','max'=>60),
                'card_4_sub'   => array('type'=>'text','group'=>'Card 4','label'=>'Caption','max'=>120),

                'card_5_image' => array('type'=>'image','group'=>'Card 5','label'=>'Logo image','max_mb'=>2),
                'card_5_alt'   => array('type'=>'text','group'=>'Card 5','label'=>'Alt text','max'=>60),
                'card_5_sub'   => array('type'=>'text','group'=>'Card 5','label'=>'Caption','max'=>120),

                'card_6_image' => array('type'=>'image','group'=>'Card 6','label'=>'Logo image','max_mb'=>2),
                'card_6_alt'   => array('type'=>'text','group'=>'Card 6','label'=>'Alt text','max'=>60),
                'card_6_sub'   => array('type'=>'text','group'=>'Card 6','label'=>'Caption','max'=>120),

                'card_7_image' => array('type'=>'image','group'=>'Card 7','label'=>'Logo image','max_mb'=>2),
                'card_7_alt'   => array('type'=>'text','group'=>'Card 7','label'=>'Alt text','max'=>60),
                'card_7_sub'   => array('type'=>'text','group'=>'Card 7','label'=>'Caption','max'=>120),

                'card_8_image' => array('type'=>'image','group'=>'Card 8','label'=>'Logo image','max_mb'=>2),
                'card_8_alt'   => array('type'=>'text','group'=>'Card 8','label'=>'Alt text','max'=>60),
                'card_8_sub'   => array('type'=>'text','group'=>'Card 8','label'=>'Caption','max'=>120),

                'card_9_image' => array('type'=>'image','group'=>'Card 9','label'=>'Logo image','max_mb'=>2),
                'card_9_alt'   => array('type'=>'text','group'=>'Card 9','label'=>'Alt text','max'=>60),
                'card_9_sub'   => array('type'=>'text','group'=>'Card 9','label'=>'Caption','max'=>120),
            ),
        ),

        'cert_stand' => array(
            'label'   => 'Cert stand (bottom CTA band)',
            'summary' => '"Where credential meets capability" block at the bottom.',
            'fields'  => array(
                'heading'   => array('type'=>'text','label'=>'Heading','max'=>80),
                'body'      => array('type'=>'textarea','label'=>'Body paragraph','rows'=>4,'max'=>600),
                'cta_label' => array('type'=>'text','label'=>'CTA link label','help'=>'The link URL (to portfolio-track-record.html) stays fixed.','max'=>40),
            ),
        ),

    ),
);
