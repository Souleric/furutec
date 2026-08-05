<?php
// Furutec Editor — field definitions.
// The editor UI is generated from this file.  Adding a new field here
// requires (a) an entry in data/content.php and (b) a matching
// {{PLACEHOLDER}} in editor/templates/index.template.html.

return array(
    'sections' => array(

        'hero' => array(
            'label' => 'Hero (top of homepage)',
            'summary' => 'Big headline over the background video',
            'fields' => array(
                'headline_1' => array(
                    'type'  => 'text',
                    'label' => 'Headline · Line 1',
                    'help'  => 'Big white text at the top of the hero.',
                    'max'   => 60,
                ),
                'headline_2' => array(
                    'type'  => 'text',
                    'label' => 'Headline · Line 2 (accent color)',
                    'help'  => 'The colored accent line under the headline.',
                    'max'   => 60,
                ),
                'lede' => array(
                    'type'  => 'textarea',
                    'label' => 'Subtitle paragraph',
                    'help'  => 'Descriptive text under the headline.',
                    'rows'  => 3,
                    'max'   => 400,
                ),
                'bg_video' => array(
                    'type'  => 'video',
                    'label' => 'Background video',
                    'help'  => 'Auto-plays behind the hero. Muted, looped. MP4 only.',
                    'max_mb'=> 30,
                ),
            ),
        ),

        'origin' => array(
            'label' => 'Inside Furutec (Origin section)',
            'summary' => 'Origin of Furutec headline, body, and factory video',
            'fields' => array(
                'eyebrow' => array(
                    'type'  => 'text',
                    'label' => 'Eyebrow label',
                    'help'  => 'Small uppercase label above the heading.',
                    'max'   => 40,
                ),
                'heading' => array(
                    'type'  => 'text',
                    'label' => 'Section heading',
                    'max'   => 60,
                ),
                'body' => array(
                    'type'  => 'textarea',
                    'label' => 'Body paragraph',
                    'rows'  => 5,
                    'max'   => 800,
                ),
                'video' => array(
                    'type'  => 'video',
                    'label' => 'Video',
                    'help'  => 'MP4 only. Click-to-play, muted, does not loop.',
                    'max_mb'=> 30,
                ),
                'poster' => array(
                    'type'  => 'image',
                    'label' => 'Video thumbnail',
                    'help'  => 'Shown before the video plays. JPG, PNG, or WEBP.',
                    'max_mb'=> 5,
                ),
                'caption' => array(
                    'type'  => 'text',
                    'label' => 'Video caption',
                    'help'  => 'Small white text overlaid on the video.',
                    'max'   => 80,
                ),
            ),
        ),

        'products' => array(
            'label' => 'Our Products (5 cards)',
            'summary' => 'The 5 product-solution cards on the homepage',
            'fields' => array(
                'section_eyebrow' => array(
                    'type' => 'text', 'label' => 'Section eyebrow', 'max' => 40,
                ),
                'section_heading_1' => array(
                    'type' => 'text', 'label' => 'Heading · Line 1', 'max' => 80,
                ),
                'section_heading_2' => array(
                    'type' => 'text', 'label' => 'Heading · Line 2 (accent)', 'max' => 60,
                ),
                'section_subtitle' => array(
                    'type' => 'textarea', 'label' => 'Section subtitle', 'rows' => 3, 'max' => 400,
                ),

                // 5 fixed product cards.  Titles + descriptions only.
                // Icons and links stay hard-coded for MVP.
                'card_1_title'       => array('type' => 'text',     'group' => 'Card 1 · Indoor',   'label' => 'Card title', 'max' => 40),
                'card_1_description' => array('type' => 'markdown', 'group' => 'Card 1 · Indoor',   'label' => 'Description', 'help' => 'Wrap key numbers in **bold** — e.g. **500 A to 6,300 A**.', 'rows' => 4, 'max' => 500),

                'card_2_title'       => array('type' => 'text',     'group' => 'Card 2 · Outdoor',  'label' => 'Card title', 'max' => 40),
                'card_2_description' => array('type' => 'markdown', 'group' => 'Card 2 · Outdoor',  'label' => 'Description', 'rows' => 4, 'max' => 500),

                'card_3_title'       => array('type' => 'text',     'group' => 'Card 3 · Data Centre', 'label' => 'Card title', 'max' => 40),
                'card_3_description' => array('type' => 'markdown', 'group' => 'Card 3 · Data Centre', 'label' => 'Description', 'rows' => 4, 'max' => 500),

                'card_4_title'       => array('type' => 'text',     'group' => 'Card 4 · Power Monitoring', 'label' => 'Card title', 'max' => 40),
                'card_4_description' => array('type' => 'markdown', 'group' => 'Card 4 · Power Monitoring', 'label' => 'Description', 'rows' => 4, 'max' => 500),

                'card_5_title'       => array('type' => 'text',     'group' => 'Card 5 · Lighting', 'label' => 'Card title', 'max' => 40),
                'card_5_description' => array('type' => 'markdown', 'group' => 'Card 5 · Lighting', 'label' => 'Description', 'rows' => 4, 'max' => 500),
            ),
        ),

    ),
);
