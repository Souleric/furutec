<?php
// Furutec Editor — field definitions (multi-page).
// - `pages` lists each editable page: its label, template file, live output file,
//   and which sections (from the `sections` map) appear on that page.
// - `shared_sections` are section slugs that appear on EVERY page — publish
//   syncs them across all templates. They also show at the top of every page's
//   accordion in the editor UI.
// - `sections` is the master map of section definitions (fields).

// ------------------------------------------------------------------
// Helpers used inside the section definitions below.  Guarded so a
// re-include (fx_load_fieldmap() re-includes on every call) does not
// redeclare.  MUST be declared before the `return array(...)` below.
// ------------------------------------------------------------------
if (!function_exists('fx_gallery_fields')) {
    function fx_gallery_fields($count) {
        $out = array();
        for ($i = 1; $i <= $count; $i++) {
            $out["slide_{$i}_image"] = array(
                'type'=>'image','group'=>"Gallery · Slide $i",'label'=>"Image",'max_mb'=>5,
            );
            $out["slide_{$i}_alt"] = array(
                'type'=>'text','group'=>"Gallery · Slide $i",'label'=>"Alt text (screen readers)",'max'=>120,
            );
        }
        return $out;
    }
    function fx_feature_fields($count) {
        $out = array();
        for ($i = 1; $i <= $count; $i++) {
            $out["feature_{$i}"] = array(
                'type'=>'lines','group'=>'Key features / capabilities','label'=>"Item $i",'help'=>'Press Enter where you want a new line.','max'=>200,
            );
        }
        return $out;
    }
    function fx_spec_fields($count) {
        $out = array();
        for ($i = 1; $i <= $count; $i++) {
            $out["spec_{$i}_label"] = array(
                'type'=>'text','group'=>"Spec row $i",'label'=>'Label','max'=>60,
            );
            $out["spec_{$i}_value"] = array(
                'type'=>'lines','group'=>"Spec row $i",'label'=>'Value','help'=>'Press Enter where you want a new line.','max'=>200,
            );
        }
        return $out;
    }
}

return array(

    'pages' => array(
        'home' => array(
            'label'     => 'Homepage',
            'template'  => 'home.template.html',
            'live_file' => 'index.html',
            'sections'  => array('hero', 'hero_certs', 'origin', 'facilities', 'company', 'product_overview', 'products', 'certs_cta', 'quote_cta'),
        ),
        'certificates' => array(
            'label'     => 'Certificates page',
            'template'  => 'certificates.template.html',
            'live_file' => 'portfolio.html',
            'sections'  => array('cert_hero', 'cert_grid', 'cert_stand'),
        ),
        'portfolio' => array(
            'label'     => 'Portfolio page',
            'template'  => 'portfolio.template.html',
            'live_file' => 'portfolio-track-record.html',
            'sections'  => array('port_hero', 'port_grid'),
        ),
        'contact' => array(
            'label'     => 'Contact page',
            'template'  => 'contact.template.html',
            'live_file' => 'quote.html',
            'sections'  => array('contact_hero', 'contact_form', 'contact_locations', 'contact_direct'),
        ),
        'product' => array(
            'label'     => 'Product page',
            'template'  => 'product.template.html',
            'live_file' => 'product.html',
            'sections'  => array(
                'product_intro', 'product_nav', 'product_buttons',
                'product_indoor', 'product_outdoor', 'product_datacenter',
                'product_power', 'product_lighting',
            ),
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
            'preview_target' => 'header',
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
            'preview_target' => 'footer',
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
            'summary' => 'Big headline over a background video or image slideshow.',
            'preview_target' => '.ft-hero',
            'fields'  => array(
                'headline_1' => array('type'=>'text','label'=>'Headline · Line 1','help'=>'Big white text at the top of the hero.','max'=>60),
                'headline_2' => array('type'=>'text','label'=>'Headline · Line 2 (accent color)','max'=>60),
                'lede'       => array('type'=>'textarea','label'=>'Subtitle paragraph','rows'=>3,'max'=>400),

                // ---- Background ----------------------------------------
                // bg_mode decides which of the two blocks below is used.
                // Switching back and forth is non-destructive: the video and
                // the image slots keep their values either way.
                'bg_mode' => array(
                    'type'    => 'select',
                    'group'   => 'Background',
                    'label'   => 'Background type',
                    'help'    => 'Choose a single looping video, or a slideshow of images.',
                    'options' => array(
                        'video'     => 'Video (single, looping)',
                        'slideshow' => 'Image slideshow',
                    ),
                    'max'     => 20,
                ),
                'bg_video' => array('type'=>'video','group'=>'Background','label'=>'Background video','help'=>'Auto-plays, muted, looped. MP4 only.','max_mb'=>30,'focus'=>true,
                    'show_when'=>array('field'=>'bg_mode','is'=>'video')),
                // Where the video stays anchored when it's cropped to fill the
                // hero.  Stored as an object-position pair; set via the editor's
                // Focal point picker, never typed by hand.
                'bg_video_focus' => array('type'=>'hidden','label'=>'Video focal point','max'=>16,
                    'show_when'=>array('field'=>'bg_mode','is'=>'video')),

                // ---- Slideshow -----------------------------------------
                // Up to 6 slots.  Blank slots are skipped, so the client can
                // use 2 or 6 without touching anything else.  With only one
                // image filled it renders as a still background (no dots,
                // no timer).
                'slide_interval' => array(
                    'type'      => 'number',
                    'group'     => 'Image slideshow',
                    'label'     => 'Seconds per slide',
                    'help'      => 'How long each image stays before the next one fades in. Between 2 and 30.',
                    'min'       => 2,
                    'max_value' => 30,
                    'step'      => 1,
                    'default'   => 5,
                    'max'       => 4,
                    'show_when' => array('field'=>'bg_mode','is'=>'slideshow'),
                ),
                'slide_1_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 1','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
                'slide_2_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 2','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
                'slide_3_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 3','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
                'slide_4_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 4','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
                'slide_5_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 5','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
                'slide_6_image' => array('type'=>'image','group'=>'Image slideshow','label'=>'Slide 6','max_mb'=>5,'crop'=>array('w'=>1920,'h'=>928),'show_when'=>array('field'=>'bg_mode','is'=>'slideshow')),
            ),
        ),

        'origin' => array(
            'label'   => 'Inside Furutec (Origin section)',
            'summary' => 'Origin of Furutec headline, body, and factory video.',
            'preview_target' => '#about',
            'fields'  => array(
                'eyebrow' => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading' => array('type'=>'text','label'=>'Section heading','max'=>60),
                'body'    => array('type'=>'textarea','label'=>'Body paragraph','rows'=>5,'max'=>800),
                'video'   => array('type'=>'video','label'=>'Video','max_mb'=>30),
                'poster'  => array('type'=>'image','label'=>'Video thumbnail','max_mb'=>5),
                'caption' => array('type'=>'text','label'=>'Video caption','max'=>80),
            ),
        ),

        'hero_certs' => array(
            'label'   => 'Homepage · Certification bar',
            'summary' => '7 certification-mark logos shown in the white strip below the hero button.',
            'preview_target' => '.ft-hero-cert-bar',
            'fields'  => array(
                'cert_1_image' => array('type'=>'image','group'=>'Cert 1','label'=>'Logo','max_mb'=>2),
                'cert_1_alt'   => array('type'=>'text', 'group'=>'Cert 1','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_2_image' => array('type'=>'image','group'=>'Cert 2','label'=>'Logo','max_mb'=>2),
                'cert_2_alt'   => array('type'=>'text', 'group'=>'Cert 2','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_3_image' => array('type'=>'image','group'=>'Cert 3','label'=>'Logo','max_mb'=>2),
                'cert_3_alt'   => array('type'=>'text', 'group'=>'Cert 3','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_4_image' => array('type'=>'image','group'=>'Cert 4','label'=>'Logo','max_mb'=>2),
                'cert_4_alt'   => array('type'=>'text', 'group'=>'Cert 4','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_5_image' => array('type'=>'image','group'=>'Cert 5','label'=>'Logo','max_mb'=>2),
                'cert_5_alt'   => array('type'=>'text', 'group'=>'Cert 5','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_6_image' => array('type'=>'image','group'=>'Cert 6','label'=>'Logo','max_mb'=>2),
                'cert_6_alt'   => array('type'=>'text', 'group'=>'Cert 6','label'=>'Alt text (for screen readers)','max'=>60),
                'cert_7_image' => array('type'=>'image','group'=>'Cert 7','label'=>'Logo','max_mb'=>2),
                'cert_7_alt'   => array('type'=>'text', 'group'=>'Cert 7','label'=>'Alt text (for screen readers)','max'=>60),
            ),
        ),

        'facilities' => array(
            'label'   => 'Bukit Minyak Facilities',
            'summary' => '6 photos of the plant and manufacturing process.',
            'preview_target' => '#fv2-section',
            'fields'  => array(
                // Note: the copy-side of this section (section tag, plant name,
                // 2 description paragraphs, 4 capability badges) is CSS-hidden
                // in home.template.html (`.fv2-left { display:none; }`), so its
                // fields were removed from the editor on 24 Aug 2026 to avoid
                // confusing the client (Furutec-Contact-Form-Email-Flow).
                // If the copy panel is ever brought back, re-add those fields
                // here and repopulate values in data/content.php.
                'photo_aerial' => array('type'=>'image','group'=>'Main photos','label'=>'Aerial plant photo','max_mb'=>5,'crop'=>array('w'=>1600,'h'=>900)),
                'photo_front'  => array('type'=>'image','group'=>'Main photos','label'=>'Plant entrance photo','max_mb'=>5,'crop'=>array('w'=>1600,'h'=>900)),
                'process_1' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 1','max_mb'=>5),
                'process_2' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 2','max_mb'=>5),
                'process_3' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 3','max_mb'=>5),
                'process_4' => array('type'=>'image','group'=>'Process photos','label'=>'Process photo 4','max_mb'=>5),
            ),
        ),

        'company' => array(
            'label'   => 'Company Profile',
            'summary' => 'Section with the logo video behind a 3-line heading, body text and a button.',
            'preview_target' => '#engineered-section',
            'fields'  => array(
                'bg_video'  => array('type'=>'video','label'=>'Background logo video','max_mb'=>30,'focus'=>true),
                // Anchor point for the cover-crop — set via the Focal point picker.
                'bg_video_focus' => array('type'=>'hidden','label'=>'Video focal point','max'=>16),
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'heading_3' => array('type'=>'text','label'=>'Heading · Line 3','max'=>60),
                'body_1'    => array('type'=>'textarea','label'=>'Body paragraph 1','rows'=>4,'max'=>800),
                'body_2'    => array('type'=>'textarea','label'=>'Body paragraph 2','rows'=>4,'max'=>800),
                'button_label' => array('type'=>'text','group'=>'Button','label'=>'Button label','max'=>30),
                'button_url'   => array('type'=>'url','group'=>'Button','label'=>'Button URL','max'=>200),
            ),
        ),

        'product_overview' => array(
            'label'   => 'Product Overview (diagram)',
            'summary' => 'Diagram with 5 clickable points. You can edit the titles; their positions are fixed.',
            'preview_target' => '#product-overview-section',
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
            'preview_target' => '#fx-products',
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
            'label'   => 'Certificates & Portfolio band',
            'summary' => 'Dark full-width strip with a background image, headline and two buttons.',
            'preview_target' => '#fx-certs-cta',
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
            'label'   => 'Get In Touch band (bottom of homepage)',
            'summary' => 'The band at the bottom of the homepage that links to the contact form.',
            'preview_target' => '#quote',
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
            'preview_target' => 'main > section:nth-of-type(1)',
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
            'preview_target' => '.cert-wall',
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
            'label'   => 'Certificates page — bottom banner',
            'summary' => '"Where credential meets capability" block at the bottom.',
            'preview_target' => '.cert-stand',
            'fields'  => array(
                'heading'   => array('type'=>'text','label'=>'Heading','max'=>80),
                'body'      => array('type'=>'textarea','label'=>'Body paragraph','rows'=>4,'max'=>600),
                'cta_label' => array('type'=>'text','label'=>'Button label','help'=>'The link URL (to portfolio-track-record.html) stays fixed.','max'=>40),
            ),
        ),

        // ============================================================
        // PORTFOLIO PAGE SECTIONS (portfolio-track-record.html)
        // ============================================================
        'port_hero' => array(
            'label'   => 'Portfolio hero',
            'summary' => 'Top of the Portfolio page — eyebrow, 2-line heading, subtitle.',
            'preview_target' => 'main > section:nth-of-type(1)',
            'fields'  => array(
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
                'subtitle'  => array('type'=>'textarea','label'=>'Subtitle','rows'=>3,'max'=>400),
            ),
        ),

        'port_grid' => array(
            'label'   => 'Sector grid (9 cards)',
            'summary' => 'Each sector tile has a background image, a tag pill, a title, and a description.',
            'preview_target' => '.ind-wall',
            'fields'  => array(
                'card_1_image' => array('type'=>'image','group'=>'Card 1 (top-left)','label'=>'Background image','help'=>'4:3 photo works best','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_1_tag'   => array('type'=>'text', 'group'=>'Card 1 (top-left)','label'=>'Tag pill','max'=>40),
                'card_1_title' => array('type'=>'text', 'group'=>'Card 1 (top-left)','label'=>'Title','max'=>60),
                'card_1_desc'  => array('type'=>'textarea','group'=>'Card 1 (top-left)','label'=>'Description','rows'=>3,'max'=>400),

                'card_2_image' => array('type'=>'image','group'=>'Card 2','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_2_tag'   => array('type'=>'text', 'group'=>'Card 2','label'=>'Tag pill','max'=>40),
                'card_2_title' => array('type'=>'text', 'group'=>'Card 2','label'=>'Title','max'=>60),
                'card_2_desc'  => array('type'=>'textarea','group'=>'Card 2','label'=>'Description','rows'=>3,'max'=>400),

                'card_3_image' => array('type'=>'image','group'=>'Card 3','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_3_tag'   => array('type'=>'text', 'group'=>'Card 3','label'=>'Tag pill','max'=>40),
                'card_3_title' => array('type'=>'text', 'group'=>'Card 3','label'=>'Title','max'=>60),
                'card_3_desc'  => array('type'=>'textarea','group'=>'Card 3','label'=>'Description','rows'=>3,'max'=>400),

                'card_4_image' => array('type'=>'image','group'=>'Card 4','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_4_tag'   => array('type'=>'text', 'group'=>'Card 4','label'=>'Tag pill','max'=>40),
                'card_4_title' => array('type'=>'text', 'group'=>'Card 4','label'=>'Title','max'=>60),
                'card_4_desc'  => array('type'=>'textarea','group'=>'Card 4','label'=>'Description','rows'=>3,'max'=>400),

                'card_5_image' => array('type'=>'image','group'=>'Card 5','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_5_tag'   => array('type'=>'text', 'group'=>'Card 5','label'=>'Tag pill','max'=>40),
                'card_5_title' => array('type'=>'text', 'group'=>'Card 5','label'=>'Title','max'=>60),
                'card_5_desc'  => array('type'=>'textarea','group'=>'Card 5','label'=>'Description','rows'=>3,'max'=>400),

                'card_6_image' => array('type'=>'image','group'=>'Card 6','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_6_tag'   => array('type'=>'text', 'group'=>'Card 6','label'=>'Tag pill','max'=>40),
                'card_6_title' => array('type'=>'text', 'group'=>'Card 6','label'=>'Title','max'=>60),
                'card_6_desc'  => array('type'=>'textarea','group'=>'Card 6','label'=>'Description','rows'=>3,'max'=>400),

                'card_7_image' => array('type'=>'image','group'=>'Card 7','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_7_tag'   => array('type'=>'text', 'group'=>'Card 7','label'=>'Tag pill','max'=>40),
                'card_7_title' => array('type'=>'text', 'group'=>'Card 7','label'=>'Title','max'=>60),
                'card_7_desc'  => array('type'=>'textarea','group'=>'Card 7','label'=>'Description','rows'=>3,'max'=>400),

                'card_8_image' => array('type'=>'image','group'=>'Card 8','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_8_tag'   => array('type'=>'text', 'group'=>'Card 8','label'=>'Tag pill','max'=>40),
                'card_8_title' => array('type'=>'text', 'group'=>'Card 8','label'=>'Title','max'=>60),
                'card_8_desc'  => array('type'=>'textarea','group'=>'Card 8','label'=>'Description','rows'=>3,'max'=>400),

                'card_9_image' => array('type'=>'image','group'=>'Card 9','label'=>'Background image','max_mb'=>5,'crop'=>array('w'=>1200,'h'=>900)),
                'card_9_tag'   => array('type'=>'text', 'group'=>'Card 9','label'=>'Tag pill','max'=>40),
                'card_9_title' => array('type'=>'text', 'group'=>'Card 9','label'=>'Title','max'=>60),
                'card_9_desc'  => array('type'=>'textarea','group'=>'Card 9','label'=>'Description','rows'=>3,'max'=>400),
            ),
        ),

        // ============================================================
        // CONTACT PAGE SECTIONS (quote.html)
        // ============================================================
        'contact_hero' => array(
            'label'   => 'Contact hero',
            'summary' => 'Top of the Contact page — eyebrow and 2-line heading.',
            'preview_target' => '#quote',
            'fields'  => array(
                'eyebrow'   => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1' => array('type'=>'text','label'=>'Heading · Line 1','max'=>60),
                'heading_2' => array('type'=>'text','label'=>'Heading · Line 2 (accent)','max'=>60),
            ),
        ),

        'contact_form' => array(
            'label'   => 'Contact form — heading and reassurance text',
            'summary' => 'Form title and reassurance line. The form fields themselves stay wired to contact.php.',
            'preview_target' => '#quote-form',
            'fields'  => array(
                'title'     => array('type'=>'text','label'=>'Form title','help'=>'Small heading above the form.','max'=>60),
                'reassure'  => array('type'=>'text','label'=>'Reassurance line','help'=>'Shown below the submit button. Use · for dot separator.','max'=>200),
            ),
        ),

        'contact_locations' => array(
            'label'   => 'Locations + map',
            'summary' => 'Company name, two addresses, map tabs.',
            'preview_target' => '#gi-map-tabs',
            'fields'  => array(
                'company_name'    => array('type'=>'text','label'=>'Company name','max'=>80),
                'loc_1_label'     => array('type'=>'text','group'=>'Location 1','label'=>'Small label','help'=>'Shown above the address (e.g. "Headquarters").','max'=>40),
                'loc_1_addr'      => array('type'=>'textarea','group'=>'Location 1','label'=>'Address','help'=>'One address line per line. Line breaks show as new lines on the site.','rows'=>3,'max'=>400),
                'loc_1_maps_url'  => array('type'=>'url','group'=>'Location 1','label'=>'Google Maps directions URL','help'=>'The URL the "Directions" pill links to.','max'=>500),
                'loc_2_label'     => array('type'=>'text','group'=>'Location 2','label'=>'Small label','max'=>40),
                'loc_2_addr'      => array('type'=>'textarea','group'=>'Location 2','label'=>'Address','rows'=>3,'max'=>400),
                'loc_2_maps_url'  => array('type'=>'url','group'=>'Location 2','label'=>'Google Maps directions URL','max'=>500),
                'map_tab_1_label' => array('type'=>'text','group'=>'Map tabs','label'=>'Tab 1 label','max'=>40),
                'map_tab_2_label' => array('type'=>'text','group'=>'Map tabs','label'=>'Tab 2 label','max'=>40),
            ),
        ),

        'contact_direct' => array(
            'label'   => 'Contact Us Directly tiles',
            'summary' => 'Three tiles at the bottom of the section: Email, Phone, Working Hours.',
            'preview_target' => '.gi-direct',
            'fields'  => array(
                'tile_1_label'        => array('type'=>'text','group'=>'Tile 1 · Email','label'=>'Label','max'=>40),
                'tile_1_value'        => array('type'=>'text','group'=>'Tile 1 · Email','label'=>'Value','max'=>120),
                'tile_2_label'        => array('type'=>'text','group'=>'Tile 2 · Phone','label'=>'Label','max'=>40),
                'tile_2_value'        => array('type'=>'text','group'=>'Tile 2 · Phone','label'=>'Value','max'=>120),
                'tile_3_label'        => array('type'=>'text','group'=>'Tile 3 · Working Hours','label'=>'Label','max'=>40),
                'tile_3_value_line_1' => array('type'=>'text','group'=>'Tile 3 · Working Hours','label'=>'Value · Line 1','max'=>120),
                'tile_3_value_line_2' => array('type'=>'text','group'=>'Tile 3 · Working Hours','label'=>'Value · Line 2','max'=>120),
            ),
        ),

        // ============================================================
        // PRODUCT PAGE SECTIONS (product.html)
        // The page has 5 product blocks (indoor, outdoor, datacenter,
        // power, lighting).  Each has its own section here so counts
        // (gallery slides, features, spec rows) can differ per product.
        // ============================================================
        'product_intro' => array(
            'label'   => 'Product page — intro banner',
            'summary' => 'Top strip: eyebrow, 2-line headline, subtitle paragraph.',
            'preview_target' => 'main > section:nth-of-type(1)',
            'fields'  => array(
                'eyebrow'        => array('type'=>'text','label'=>'Eyebrow label','max'=>40),
                'heading_1'      => array('type'=>'text','label'=>'Heading · Line 1','help'=>'Black text.','max'=>80),
                'heading_accent' => array('type'=>'text','label'=>'Heading · Accent (blue)','help'=>'Blue text.','max'=>80),
                'subtitle'       => array('type'=>'textarea','label'=>'Subtitle','rows'=>3,'max'=>400),
            ),
        ),

        'product_nav' => array(
            'label'   => 'Product page — section menu labels',
            'summary' => 'The five menu labels in the bar that stays pinned above the product sections.',
            'preview_target' => '#prod-nav',
            'fields'  => array(
                'indoor_label'     => array('type'=>'text','label'=>'01 · Indoor label','max'=>60),
                'outdoor_label'    => array('type'=>'text','label'=>'02 · Outdoor label','max'=>60),
                'datacenter_label' => array('type'=>'text','label'=>'03 · Data Centre label','max'=>60),
                'power_label'      => array('type'=>'text','label'=>'04 · Power Monitoring label','max'=>60),
                'lighting_label'   => array('type'=>'text','label'=>'05 · Lighting label','max'=>60),
            ),
        ),

        'product_buttons' => array(
            'label'   => 'Product page — action buttons',
            'summary' => 'The two buttons under every product block ("Contact Us" and "Request a Catalogue"). One setting applies to all five products.',
            'preview_target' => '#indoor .ps-cta',
            'fields'  => array(
                'contact_label'   => array('type'=>'text','preview_target'=>'.ps-btn-primary','default'=>'Contact Us','label'=>'Contact button label','help'=>'Shown on the main button. Each product\'s button already links to the contact form with that product pre-selected.','max'=>40),
                'catalogue_label' => array('type'=>'text','preview_target'=>'.ps-btn-secondary','default'=>'Request a Catalogue','label'=>'Catalogue button label','help'=>'Shown next to the download icon.','max'=>40),
                'catalogue_url'   => array('type'=>'text','preview_target'=>'.ps-btn-secondary','default'=>'quote.html','label'=>'Catalogue button link','help'=>'Where the catalogue button goes. Default is quote.html (the contact form). Can also be a PDF uploaded via the Media Library, e.g. assets/uploads/catalogue.pdf','max'=>300),
            ),
        ),

        'product_indoor' => array(
            'label'   => '01 · Indoor Busduct',
            'summary' => 'Section title, gallery (5 images), content panel, spec (8 rows), diagram video.',
            'preview_target' => '#indoor',
            'fields'  => array_merge(
                array(
                    'section_title' => array('type'=>'text','label'=>'Section title (uppercase)','max'=>80),
                    'tag_label'     => array('type'=>'text','group'=>'Content panel','label'=>'Tag pill label','max'=>30),
                    'title'         => array('type'=>'text','group'=>'Content panel','label'=>'Product title','max'=>80),
                    'subtitle'      => array('type'=>'text','group'=>'Content panel','label'=>'Subtitle (italic)','max'=>120),
                    'desc'          => array('type'=>'textarea','group'=>'Content panel','label'=>'Description','help'=>'Press Enter where you want a new line.','rows'=>4,'max'=>600),
                ),
                // Gallery — 5 slides
                fx_gallery_fields(10),
                // Features — 8 items
                fx_feature_fields(8),
                // Spec rows — 5
                fx_spec_fields(8),
                // Diagram — single video
                array(
                    'diagram_title'      => array('type'=>'text','group'=>'Diagram band','label'=>'Diagram title','max'=>120),
                    'diagram_video'      => array('type'=>'video','group'=>'Diagram band','label'=>'Diagram video (MP4)','max_mb'=>30),
                    'diagram_poster'     => array('type'=>'image','group'=>'Diagram band','label'=>'Diagram video poster','max_mb'=>3),
                )
            ),
        ),

        'product_outdoor' => array(
            'label'   => '02 · Outdoor Busduct',
            'summary' => 'Section title, gallery (5 images), content panel, spec (8 rows), diagram video.',
            'preview_target' => '#outdoor',
            'fields'  => array_merge(
                array(
                    'section_title' => array('type'=>'text','label'=>'Section title (uppercase)','max'=>80),
                    'tag_label'     => array('type'=>'text','group'=>'Content panel','label'=>'Tag pill label','max'=>30),
                    'title'         => array('type'=>'text','group'=>'Content panel','label'=>'Product title','max'=>80),
                    'subtitle'      => array('type'=>'text','group'=>'Content panel','label'=>'Subtitle (italic)','max'=>120),
                    'desc'          => array('type'=>'textarea','group'=>'Content panel','label'=>'Description','help'=>'Press Enter where you want a new line.','rows'=>4,'max'=>600),
                ),
                fx_gallery_fields(10),
                fx_feature_fields(7),
                fx_spec_fields(8),
                array(
                    'diagram_title'      => array('type'=>'text','group'=>'Diagram band','label'=>'Diagram title','max'=>120),
                    'diagram_video'      => array('type'=>'video','group'=>'Diagram band','label'=>'Diagram video (MP4)','max_mb'=>30),
                    'diagram_poster'     => array('type'=>'image','group'=>'Diagram band','label'=>'Diagram video poster','max_mb'=>3),
                )
            ),
        ),

        'product_datacenter' => array(
            'label'   => '03 · Data Centre Solution',
            'summary' => 'Section title, gallery (6 images), content panel, spec (8 rows), diagram video.',
            'preview_target' => '#datacenter',
            'fields'  => array_merge(
                array(
                    'section_title' => array('type'=>'text','label'=>'Section title (uppercase)','max'=>80),
                    'tag_label'     => array('type'=>'text','group'=>'Content panel','label'=>'Tag pill label','max'=>30),
                    'title'         => array('type'=>'text','group'=>'Content panel','label'=>'Product title','max'=>80),
                    'subtitle'      => array('type'=>'text','group'=>'Content panel','label'=>'Subtitle (italic)','max'=>120),
                    'desc'          => array('type'=>'textarea','group'=>'Content panel','label'=>'Description','help'=>'Press Enter where you want a new line.','rows'=>4,'max'=>800),
                ),
                fx_gallery_fields(10),
                fx_feature_fields(10),
                fx_spec_fields(8),
                array(
                    'diagram_title'      => array('type'=>'text','group'=>'Diagram band','label'=>'Diagram title','max'=>120),
                    'diagram_video'      => array('type'=>'video','group'=>'Diagram band','label'=>'Diagram video (MP4)','max_mb'=>30),
                    'diagram_poster'     => array('type'=>'image','group'=>'Diagram band','label'=>'Diagram video poster','max_mb'=>3),
                )
            ),
        ),

        'product_power' => array(
            'label'   => '04 · Power Monitoring',
            'summary' => 'Gallery, content panel, spec (8 rows, optional). No diagram band.',
            'preview_target' => '#power',
            'fields'  => array_merge(
                array(
                    'section_title' => array('type'=>'text','label'=>'Section title (uppercase)','max'=>80),
                    'tag_label'     => array('type'=>'text','group'=>'Content panel','label'=>'Tag pill label','max'=>30),
                    'title'         => array('type'=>'text','group'=>'Content panel','label'=>'Product title','max'=>80),
                    'subtitle'      => array('type'=>'text','group'=>'Content panel','label'=>'Subtitle (italic)','max'=>120),
                    'desc'          => array('type'=>'textarea','group'=>'Content panel','label'=>'Description','help'=>'Press Enter where you want a new line.','rows'=>5,'max'=>800),
                ),
                fx_gallery_fields(10),
                fx_feature_fields(6),  // Power section uses "Key Capabilities" label
                fx_spec_fields(8)
            ),
        ),

        'product_lighting' => array(
            'label'   => '05 · Lighting Busduct',
            'summary' => 'Gallery (7 images), content panel, spec (8 rows), PAIRED diagram (LB + PB).',
            'preview_target' => '#lighting',
            'fields'  => array_merge(
                array(
                    'section_title' => array('type'=>'text','label'=>'Section title (uppercase)','max'=>80),
                    'tag_label'     => array('type'=>'text','group'=>'Content panel','label'=>'Tag pill label','max'=>30),
                    'title'         => array('type'=>'text','group'=>'Content panel','label'=>'Product title','max'=>80),
                    'subtitle'      => array('type'=>'text','group'=>'Content panel','label'=>'Subtitle (italic)','max'=>120),
                    'desc'          => array('type'=>'textarea','group'=>'Content panel','label'=>'Description','help'=>'Press Enter where you want a new line.','rows'=>4,'max'=>800),
                ),
                fx_gallery_fields(10),
                fx_feature_fields(6),
                fx_spec_fields(8),
                // Paired diagram: title + two labelled videos
                array(
                    'diagram_title'         => array('type'=>'text','group'=>'Diagram band','label'=>'Diagram title','max'=>120),
                    'diagram_1_label'       => array('type'=>'text','group'=>'Diagram · Left video','label'=>'Label (e.g. LB Busduct)','max'=>40),
                    'diagram_1_video'       => array('type'=>'video','group'=>'Diagram · Left video','label'=>'Video (MP4)','max_mb'=>30),
                    'diagram_1_poster'      => array('type'=>'image','group'=>'Diagram · Left video','label'=>'Poster image','max_mb'=>3),
                    'diagram_2_label'       => array('type'=>'text','group'=>'Diagram · Right video','label'=>'Label (e.g. PB Busduct)','max'=>40),
                    'diagram_2_video'       => array('type'=>'video','group'=>'Diagram · Right video','label'=>'Video (MP4)','max_mb'=>30),
                    'diagram_2_poster'      => array('type'=>'image','group'=>'Diagram · Right video','label'=>'Poster image','max_mb'=>3),
                )
            ),
        ),

    ),
);
