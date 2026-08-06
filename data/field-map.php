<?php
// Furutec Editor — field definitions.
// The editor UI is generated from this file.  Adding a new field here
// requires (a) an entry in data/content.php and (b) a matching
// {{PLACEHOLDER}} in editor/templates/index.template.html.
//
// Fields common to multiple pages (nav phone, footer contact) currently
// only update index.html on publish.  Cross-page sync is on the roadmap
// but not part of this MVP+1.  The help text on those fields flags it.

return array(
    'sections' => array(

        // ============================================================
        // 1) TOP NAVIGATION (site header)
        // ============================================================
        'nav' => array(
            'label'   => '01 · Header / Navigation',
            'summary' => 'Logo, phone number, and Contact Us button in the sticky top bar.',
            'fields'  => array(
                'logo' => array(
                    'type'   => 'image',
                    'label'  => 'Header logo',
                    'help'   => 'PNG or JPG. Currently 54 px tall on desktop.  ⚠ Only updates the homepage — tell your web team to sync to product/portfolio/quote pages.',
                    'max_mb' => 5,
                ),
                'phone' => array(
                    'type'  => 'text',
                    'label' => 'Phone number',
                    'help'  => 'Shown next to the Contact Us button.  ⚠ Only updates the homepage — the same phone appears in the footer and on other pages.',
                    'max'   => 40,
                ),
                'contact_button_label' => array(
                    'type'  => 'text',
                    'label' => 'Contact button label',
                    'help'  => 'Dark button on the top-right. Defaults to "Contact Us".',
                    'max'   => 30,
                ),
            ),
        ),

        // ============================================================
        // 2) HERO (top of homepage) — already covered in MVP
        // ============================================================
        'hero' => array(
            'label'   => '02 · Hero (top of homepage)',
            'summary' => 'Big headline over the background video.',
            'fields'  => array(
                'headline_1' => array('type'=>'text','label'=>'Headline · Line 1','help'=>'Big white text at the top of the hero.','max'=>60),
                'headline_2' => array('type'=>'text','label'=>'Headline · Line 2 (accent color)','help'=>'The colored accent line under the headline.','max'=>60),
                'lede'       => array('type'=>'textarea','label'=>'Subtitle paragraph','help'=>'Descriptive text under the headline.','rows'=>3,'max'=>400),
                'bg_video'   => array('type'=>'video','label'=>'Background video','help'=>'Auto-plays behind the hero. Muted, looped. MP4 only.','max_mb'=>30),
            ),
        ),

        // ============================================================
        // 3) INSIDE FURUTEC / ORIGIN — already covered in MVP
        // ============================================================
        'origin' => array(
            'label'   => '03 · Inside Furutec (Origin section)',
            'summary' => 'Origin of Furutec headline, body, and factory video.',
            'fields'  => array(
                'eyebrow' => array('type'=>'text','label'=>'Eyebrow label','help'=>'Small uppercase label above the heading.','max'=>40),
                'heading' => array('type'=>'text','label'=>'Section heading','max'=>60),
                'body'    => array('type'=>'textarea','label'=>'Body paragraph','rows'=>5,'max'=>800),
                'video'   => array('type'=>'video','label'=>'Video','help'=>'MP4 only. Click-to-play, muted, does not loop.','max_mb'=>30),
                'poster'  => array('type'=>'image','label'=>'Video thumbnail','help'=>'Shown before the video plays. JPG, PNG, or WEBP.','max_mb'=>5),
                'caption' => array('type'=>'text','label'=>'Video caption','help'=>'Small white text overlaid on the video.','max'=>80),
            ),
        ),

        // ============================================================
        // 4) BUKIT MINYAK FACILITIES (fv2-section)
        // ============================================================
        'facilities' => array(
            'label'   => '04 · Bukit Minyak Facilities',
            'summary' => 'Plant name, description, 4 capability badges, aerial + front photo, 4 process photos.',
            'fields'  => array(
                'tag'     => array('type'=>'text','label'=>'Section tag','help'=>'Small pill label above the plant name.','max'=>60),
                'name'    => array('type'=>'text','label'=>'Plant name','help'=>'Currently "Bukit Minyak, Penang".','max'=>60),
                'desc_1'  => array('type'=>'markdown','label'=>'Description paragraph 1','help'=>'Wrap key terms in **bold** to emphasize.','rows'=>4,'max'=>800),
                'desc_2'  => array('type'=>'markdown','label'=>'Description paragraph 2','help'=>'Second body paragraph.','rows'=>4,'max'=>800),

                // 4 capability badges
                'badge_1_label' => array('type'=>'text','group'=>'Badge 1','label'=>'Label','help'=>'Use / for line breaks, e.g. "Two Integrated / Plants".','max'=>60),
                'badge_1_sub'   => array('type'=>'text','group'=>'Badge 1','label'=>'Sublabel','max'=>60),

                'badge_2_label' => array('type'=>'text','group'=>'Badge 2','label'=>'Label','max'=>60),
                'badge_2_sub'   => array('type'=>'text','group'=>'Badge 2','label'=>'Sublabel','max'=>60),

                'badge_3_label' => array('type'=>'text','group'=>'Badge 3','label'=>'Label','max'=>60),
                'badge_3_sub'   => array('type'=>'text','group'=>'Badge 3','label'=>'Sublabel','max'=>60),

                'badge_4_label' => array('type'=>'text','group'=>'Badge 4','label'=>'Label','max'=>60),
                'badge_4_sub'   => array('type'=>'text','group'=>'Badge 4','label'=>'Sublabel','max'=>60),

                // Main photos
                'photo_aerial' => array('type'=>'image','group'=>'Main photos','label'=>'Aerial plant photo','help'=>'Left half of the photo split. Approx 16:10 landscape works best.','max_mb'=>5),
                'photo_front'  => array('type'=>'image','group'=>'Main photos','label'=>'Plant entrance photo','help'=>'Right half of the photo split.','max_mb'=>5),

                // Process photos (4)
                'process_1' => array('type'=>'image','group'=>'Process photos (overlay strip)','label'=>'Process photo 1','help'=>'PNG with transparent background works best.','max_mb'=>5),
                'process_2' => array('type'=>'image','group'=>'Process photos (overlay strip)','label'=>'Process photo 2','max_mb'=>5),
                'process_3' => array('type'=>'image','group'=>'Process photos (overlay strip)','label'=>'Process photo 3','max_mb'=>5),
                'process_4' => array('type'=>'image','group'=>'Process photos (overlay strip)','label'=>'Process photo 4','max_mb'=>5),
            ),
        ),

        // ============================================================
        // 5) COMPANY PROFILE / ENGINEERED SECTION
        // ============================================================
        'company' => array(
            'label'   => '05 · Company Profile',
            'summary' => 'Zoom-reveal section with logo video, 3-line heading, body copy, and CTA.',
            'fields'  => array(
                'bg_video'  => array('type'=>'video','label'=>'Background logo video','help'=>'Plays once when the section enters view. MP4 only.','max_mb'=>30),
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent color)','max'=>60),
                'heading_3' => array('type'=>'text','label'=>'Heading · Line 3','max'=>60),
                'body_1'    => array('type'=>'textarea','label'=>'Body paragraph 1','rows'=>4,'max'=>800),
                'body_2'    => array('type'=>'textarea','label'=>'Body paragraph 2','rows'=>4,'max'=>800),
                'button_label' => array('type'=>'text','group'=>'CTA button','label'=>'Button label','max'=>30),
                'button_url'   => array('type'=>'url','group'=>'CTA button','label'=>'Button URL','help'=>'Full URL including https://. Currently points to https://www.eita.com.my','max'=>200),
            ),
        ),

        // ============================================================
        // 6) PRODUCT OVERVIEW (interactive diagram)
        // ============================================================
        'product_overview' => array(
            'label'   => '06 · Product Overview (diagram)',
            'summary' => 'Interactive diagram with 5 hotspots. Diagram image + card titles editable; hotspot positions are fixed.',
            'fields'  => array(
                'eyebrow'  => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1'=> array('type'=>'text','label'=>'Heading · Line 1','help'=>'Currently "How A".','max'=>60),
                'heading_accent'=> array('type'=>'text','label'=>'Heading · Accent phrase','help'=>'Blue inline text, currently "Furutec Busduct System".','max'=>60),
                'heading_2'=> array('type'=>'text','label'=>'Heading · Line 2','help'=>'Currently "Comes Together.".','max'=>60),
                'subtitle' => array('type'=>'textarea','label'=>'Subtitle','rows'=>2,'max'=>300),
                'diagram'  => array('type'=>'image','label'=>'Diagram image','help'=>'Product overview illustration. Hotspot positions are set in the code so keep the composition consistent.','max_mb'=>5),

                'card_1_title' => array('type'=>'text','group'=>'Card 1 (top-left hotspot)','label'=>'Card title','max'=>40),
                'card_2_title' => array('type'=>'text','group'=>'Card 2 (top-middle hotspot)','label'=>'Card title','max'=>40),
                'card_3_title' => array('type'=>'text','group'=>'Card 3 (top-right hotspot)','label'=>'Card title','max'=>40),
                'card_4_title' => array('type'=>'text','group'=>'Card 4 (bottom-middle hotspot)','label'=>'Card title','max'=>40),
                'card_5_title' => array('type'=>'text','group'=>'Card 5 (bottom-right hotspot)','label'=>'Card title','max'=>40),
            ),
        ),

        // ============================================================
        // 7) OUR PRODUCTS (5 cards) — already covered in MVP
        // ============================================================
        'products' => array(
            'label'   => '07 · Our Products (5 cards)',
            'summary' => 'The 5 product-solution cards on the homepage.',
            'fields'  => array(
                'section_eyebrow'   => array('type'=>'text','label'=>'Section eyebrow','max'=>40),
                'section_heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>80),
                'section_heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'section_subtitle'  => array('type'=>'textarea','label'=>'Section subtitle','rows'=>3,'max'=>400),

                'card_1_title'       => array('type'=>'text',     'group'=>'Card 1 · Indoor',   'label'=>'Card title','max'=>40),
                'card_1_description' => array('type'=>'markdown', 'group'=>'Card 1 · Indoor',   'label'=>'Description','help'=>'Wrap key numbers in **bold**.','rows'=>4,'max'=>500),

                'card_2_title'       => array('type'=>'text',     'group'=>'Card 2 · Outdoor',  'label'=>'Card title','max'=>40),
                'card_2_description' => array('type'=>'markdown', 'group'=>'Card 2 · Outdoor',  'label'=>'Description','rows'=>4,'max'=>500),

                'card_3_title'       => array('type'=>'text',     'group'=>'Card 3 · Data Centre','label'=>'Card title','max'=>40),
                'card_3_description' => array('type'=>'markdown', 'group'=>'Card 3 · Data Centre','label'=>'Description','rows'=>4,'max'=>500),

                'card_4_title'       => array('type'=>'text',     'group'=>'Card 4 · Power Monitoring','label'=>'Card title','max'=>40),
                'card_4_description' => array('type'=>'markdown', 'group'=>'Card 4 · Power Monitoring','label'=>'Description','rows'=>4,'max'=>500),

                'card_5_title'       => array('type'=>'text',     'group'=>'Card 5 · Lighting', 'label'=>'Card title','max'=>40),
                'card_5_description' => array('type'=>'markdown', 'group'=>'Card 5 · Lighting', 'label'=>'Description','rows'=>4,'max'=>500),
            ),
        ),

        // ============================================================
        // 8) CERTIFICATES & PORTFOLIO CTA
        // ============================================================
        'certs_cta' => array(
            'label'   => '08 · Certificates & Portfolio CTA',
            'summary' => 'Dark full-width band with background image, headline, and two buttons.',
            'fields'  => array(
                'bg_image'  => array('type'=>'image','label'=>'Background image','help'=>'Large full-width photo. Darkened automatically for text readability.','max_mb'=>5),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'subtitle'  => array('type'=>'text','label'=>'Subtitle','max'=>200),
                'button_1_label' => array('type'=>'text','group'=>'Button 1','label'=>'Button label','max'=>30),
                'button_2_label' => array('type'=>'text','group'=>'Button 2','label'=>'Button label','max'=>30),
            ),
        ),

        // ============================================================
        // 9) GET IN TOUCH CTA
        // ============================================================
        'quote_cta' => array(
            'label'   => '09 · Get In Touch CTA (bottom)',
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
        // 10) FOOTER
        // ============================================================
        'footer' => array(
            'label'   => '10 · Footer',
            'summary' => 'Logo, contact info, column headings, copyright, and social media URLs. ⚠ Same footer on all 5 pages — edits only apply to homepage until we ship cross-page sync.',
            'fields'  => array(
                'logo'    => array('type'=>'image','label'=>'Footer logo (white)','help'=>'White or light logo. Currently 54 px tall.','max_mb'=>5),
                'company_name' => array('type'=>'text','label'=>'Company name','max'=>80),
                'company_reg'  => array('type'=>'text','label'=>'Company registration number','help'=>'Italic small print under the company name.','max'=>80),
                'email'   => array('type'=>'text','label'=>'Email address','max'=>80),
                'phone'   => array('type'=>'text','label'=>'Phone number','max'=>40),
                'location_1' => array('type'=>'text','label'=>'Location line 1','help'=>'e.g. "Subang Jaya, Selangor"','max'=>80),
                'location_2' => array('type'=>'text','label'=>'Location line 2','help'=>'e.g. "Penang, Malaysia"','max'=>80),

                'col_1_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 1 heading','help'=>'Currently "Product".','max'=>30),
                'col_2_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 2 heading','help'=>'Currently "Industries".','max'=>30),
                'col_3_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 3 heading','help'=>'Currently "Support".','max'=>30),
                'col_4_heading' => array('type'=>'text','group'=>'Column headings','label'=>'Column 4 heading','help'=>'Currently "Company".','max'=>30),

                'copyright' => array('type'=>'text','group'=>'Bottom bar','label'=>'Copyright line','help'=>'Update the year annually.','max'=>200),

                'social_facebook' => array('type'=>'url','group'=>'Social media URLs','label'=>'Facebook URL','help'=>'Full URL. Leave # if not yet published.','max'=>200),
                'social_youtube'  => array('type'=>'url','group'=>'Social media URLs','label'=>'YouTube URL','max'=>200),
                'social_linkedin' => array('type'=>'url','group'=>'Social media URLs','label'=>'LinkedIn URL','max'=>200),
                'social_whatsapp' => array('type'=>'url','group'=>'Social media URLs','label'=>'WhatsApp URL','help'=>'Format: https://wa.me/60128874517','max'=>200),
            ),
        ),

    ),
);
