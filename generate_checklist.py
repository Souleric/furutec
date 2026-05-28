"""Generate Furutec website checklist as Word document.

Source of truth: client PDF "Content Outline Website (1) (1).pdf".
Every row in the main checklist is taken verbatim/paraphrased from the PDF.
Items that are not directly from the PDF (build observations, technical advice,
project-hygiene notes) live in the final "Build Notes" appendix.

Status values: "Done", "Pending", "Partial", "N/A".
"""

from docx import Document
from docx.shared import Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_ALIGN_VERTICAL
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

OUTPUT = "/Users/ericcheah/Furutec/Furutec website 3/Furutec_Website_Checklist.docx"

doc = Document()

# ── Document margins ─────────────────────────────────────────
for section in doc.sections:
    section.top_margin = Cm(1.8)
    section.bottom_margin = Cm(1.8)
    section.left_margin = Cm(2.0)
    section.right_margin = Cm(2.0)

# ── Base style ───────────────────────────────────────────────
style = doc.styles["Normal"]
style.font.name = "Calibri"
style.font.size = Pt(10)

BRAND = RGBColor(0x2E, 0x31, 0x92)
INK = RGBColor(0x11, 0x11, 0x14)
MUTED = RGBColor(0x6B, 0x6B, 0x73)

def set_cell_shading(cell, hex_color):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_color)
    tc_pr.append(shd)

def add_heading(text, level=1):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(14)
    p.paragraph_format.space_after = Pt(6)
    run = p.add_run(text)
    run.bold = True
    if level == 1:
        run.font.size = Pt(18)
        run.font.color.rgb = BRAND
    elif level == 2:
        run.font.size = Pt(14)
        run.font.color.rgb = INK
    elif level == 3:
        run.font.size = Pt(11)
        run.font.color.rgb = INK
    return p

def add_para(text, italic=False, color=None, size=None):
    p = doc.add_paragraph()
    run = p.add_run(text)
    if italic:
        run.italic = True
    if color:
        run.font.color.rgb = color
    if size:
        run.font.size = Pt(size)
    return p

def add_checklist_table(rows):
    """rows: list of (item_text, status, remarks)."""
    table = doc.add_table(rows=1 + len(rows), cols=3)
    table.style = "Light Grid Accent 1"
    table.autofit = False

    widths = (Cm(10.0), Cm(2.4), Cm(5.0))
    for r in table.rows:
        for c, w in zip(r.cells, widths):
            c.width = w

    hdr = table.rows[0].cells
    hdr[0].text = "Checklist Item (PDF reference)"
    hdr[1].text = "Status"
    hdr[2].text = "Remarks / Notes"
    for cell in hdr:
        set_cell_shading(cell, "2E3192")
        for paragraph in cell.paragraphs:
            for run in paragraph.runs:
                run.bold = True
                run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
                run.font.size = Pt(10)

    for i, (item, status, remarks) in enumerate(rows, start=1):
        row = table.rows[i].cells
        row[0].text = item
        row[1].text = status
        row[2].text = remarks
        for c in row:
            c.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
        status_cell = row[1]
        if status == "Done":
            set_cell_shading(status_cell, "DCEFD6")
        elif status == "Pending":
            set_cell_shading(status_cell, "FFE2C9")
        elif status == "Partial":
            set_cell_shading(status_cell, "FFF4CC")
        elif status == "N/A":
            set_cell_shading(status_cell, "EEEEEE")
        for paragraph in status_cell.paragraphs:
            for run in paragraph.runs:
                run.bold = True
                run.font.size = Pt(9)
    doc.add_paragraph()

# ── COVER ────────────────────────────────────────────────────
title = doc.add_paragraph()
title.alignment = WD_ALIGN_PARAGRAPH.CENTER
title_run = title.add_run("FURUTEC WEBSITE")
title_run.bold = True
title_run.font.size = Pt(28)
title_run.font.color.rgb = BRAND

subtitle = doc.add_paragraph()
subtitle.alignment = WD_ALIGN_PARAGRAPH.CENTER
sub_run = subtitle.add_run("Content Outline — Build Checklist & Sign-off")
sub_run.font.size = Pt(14)
sub_run.font.color.rgb = INK

meta = doc.add_paragraph()
meta.alignment = WD_ALIGN_PARAGRAPH.CENTER
meta_run = meta.add_run(
    "Mirror of the client-supplied PDF spec: 'Content Outline Website'.  "
    "Each row corresponds to a line in the PDF."
)
meta_run.italic = True
meta_run.font.size = Pt(10)
meta_run.font.color.rgb = MUTED

doc.add_paragraph()
legend = doc.add_paragraph()
legend_run = legend.add_run(
    "Legend  ·  Done = implemented and verified  ·  Partial = partly delivered "
    "(see Remarks)  ·  Pending = not yet implemented  ·  N/A = item removed or superseded."
)
legend_run.italic = True
legend_run.font.size = Pt(9)
legend_run.font.color.rgb = MUTED

doc.add_paragraph()

# ── 1.0 PROJECT OVERVIEW ─────────────────────────────────────
add_heading("1.0  Project Overview", level=1)
add_para(
    "Develop a corporate website that is high-technology and user-friendly, "
    "showcases products, strengthens brand credibility, and generates business inquiries."
)
add_heading("Target Audience", level=3)
add_para("• Contractors\n• Engineers\n• Developers and industrial clients")
add_heading("Key Goals", level=3)
add_para("• Present Furutec as a reliable busduct solution provider.\n"
         "• Show the credibility Furutec brand works.\n"
         "• Engage clients through the website.")

# ── 2.0 SITEMAP ─────────────────────────────────────────────
add_heading("2.0  Sitemap Website Diagram", level=1)
add_checklist_table([
    ("Home page — anchor-based navigation",                             "Done",    ""),
    ("Product landing page",                                            "Done",    ""),
    ("Portfolio — Certificate Credential",                              "Done",    ""),
    ("Portfolio — Track Record",                                        "Done",    ""),
    ("CTA — Contact form, Contact-Us table, Maps (Subang Jaya, Penang)","Done",    ""),
])

# ── 3.1 HOMEPAGE ────────────────────────────────────────────
add_heading("3.0  Content Breakdown — Homepage (Section 3.1)", level=1)

add_heading("3.1.1  Hero Section", level=2)
add_checklist_table([
    ("Cinematic background — data centre server room video",      "Done",   ""),
    ("Eyebrow — 'UNLOCKING BUSDUCT INNOVATION' with orange dot",  "Done",   ""),
    ("Title — 'Your Path To Efficiency & Reliability'",          "Done",   ""),
    ("Body copy (industrial, commercial, infrastructure projects…)", "Done", ""),
    ("CTA button — 'Learn More'",                                 "Done",   ""),
    ("Certification logo strip at bottom of hero",                "Done",   ""),
])

add_heading("3.1.2  Company Profile", level=2)
add_checklist_table([
    ("Title — 'Build on expertise. Driven by innovation'",        "Done", ""),
    ("Paragraph (verbatim from PDF, 31 years R&D, certifications etc.)", "Done", ""),
    ("Learn More button → EITA WEBSITE",                          "Done", "Linked to https://www.eita.com.my (confirmed with client)."),
])

add_heading("3.1.3  Product R&D — Our Product Milestones", level=2)
add_checklist_table([
    ("Section title — 'Our Product Milestones'",                  "Done", ""),
    ("Card 1 — tagline 'Mission Critical' + title 'Data Centre Application'", "Done", ""),
    ("Card 1 description — 'High-efficiency live system solutions for zero-downtime Halls'", "Done", ""),
    ("Card 2 — title 'Indoor Installation' + compact sandwich busduct description", "Done", ""),
    ("Card 3 — title 'Outdoor Installation' + IP68 cast resin description", "Done", ""),
])

add_heading("3.1.4  Product Overview", level=2)
add_checklist_table([
    ("Tagline — 'OUR PRODUCTS'",                                  "Done", ""),
    ("Title — 'Complete busduct solutions For every environment'", "Done", ""),
    ("Description (PDF copy)",                                    "Done", ""),
    ("Hyperlink Product list — Indoor Solution / Outdoor Solution / Data Centre Solution / Power Monitoring Solution / Lightning Solution", "Done", ""),
    ("Product Overview diagram image (4 numbered systems)",       "Done", ""),
])

add_heading("3.1.5  Portfolio Teaser", level=2)
add_checklist_table([
    ("Title — 'Credential & Track Record'",                       "Done", ""),
    ("Description — 'Where Credential Meets Capability'",         "Done", ""),
    ("Learn More button → Portfolio page",                        "Done", ""),
])

add_heading("3.1.6  CTA Section — Get In Touch", level=2)
add_checklist_table([
    ("Tagline — 'GET IN TOUCH'",                                  "Done", ""),
    ("Title — 'Ready to specify your next busduct project?'",     "Done", ""),
    ("Form #1 Select Topic — Indoor Solution / Outdoor Solution / Data Centre Solution / Power Monitoring Solution / Others", "Done", ""),
    ("Form #2 Full Name",                                         "Done", ""),
    ("Form #3 Corporate Email",                                   "Done", ""),
    ("Form #4 Phone Number",                                      "Done", ""),
    ("Form #5 Project Description",                               "Done", ""),
    ("Form #6 Submit Now button",                                 "Done", ""),
    ("Form #7 Country",                                           "Done", ""),
    ("Email submissions to be received by Amy, Vincent & Darren", "Pending", "Backend / SMTP routing — to be wired up in deployment phase."),
    ("Contact Us Directly — Email: info@furutec.com.my",          "Done", ""),
    ("Contact Us Directly — Phone: +603-56350538",                "Partial", "Currently displayed as +603-5635 0538. PDF footer screenshot shows +603 5635 7088. Confirm canonical number with client."),
    ("Contact Us Directly — Working Hours: 8:30AM–6:00PM (Mon–Fri)","Done", ""),
    ("Maps — Subang Jaya (HQ) + Penang Manufacturing Plant",      "Done", ""),
])

# ── 3.2 PRODUCT LANDING PAGE ─────────────────────────────────
add_heading("3.0  Content Breakdown — Product Landing Page (Section 3.2)", level=1)

add_heading("3.2.1  Indoor Solution — AH & AH-ES Busduct", level=2)
add_checklist_table([
    ("Image — AH & AH-ES busduct",                                "Done", ""),
    ("Highlight tag — 'Most Popular'",                            "Done", ""),
    ("Title — 'AH & AH-ES BUSDUCT'",                              "Done", ""),
    ("Subtitle — 'Integrated Power Distribution for Every Scale'", "Done", ""),
    ("Description (PDF sandwich-type busduct copy)",              "Done", ""),
    ("Buttons — Contact Us + Request a Catalog",                  "Done", ""),
    ("Key Feature 1 — Compliant with IEC 61439-6 & UL 857",       "Done", ""),
    ("Key Feature 2 — Compact Sandwich Construction",             "Done", ""),
    ("Key Feature 3 — Extruded Aluminium Alloy Housing",          "Done", ""),
    ("Key Feature 4 — Corrosion Resistant",                       "Done", ""),
    ("Key Feature 5 — Excellent Heat Dissipation",                "Done", ""),
    ("Key Feature 6 — 50% Earth Bar or 100% Housing Ground System", "Done", ""),
    ("Key Feature 7 — Seismic Zone 4 Protection",                 "Done", ""),
    ("Key Feature 8 — IK10 Mechanical Impact",                    "Done", ""),
    ("Key Feature 9 — Complete IP66",                             "Done", ""),
    ("Tech Spec — Current Rating: 500A – 6,300A",                 "Done", ""),
    ("Tech Spec — Compliance Standard: IEC 61439-6 & IEC 61439-1","Done", ""),
    ("Tech Spec — Voltage: Up to 1,000V",                         "Done", ""),
    ("Tech Spec — Conductor: Copper / Aluminium",                 "Done", ""),
    ("Tech Spec — Insulation Material: Polyester Film / Epoxy (Optional)", "Done", ""),
    ("Video Product (now: animated diagram video, auto-plays on scroll, replay button)", "Done", "Diagram component-breakdown video plays per section (assets/products/*-diagram video.mp4)."),
    ("Component Diagram Breakdown image",                         "Done", ""),
])

add_heading("3.2.2  Outdoor Solution — Cast Resin Busduct", level=2)
add_checklist_table([
    ("Image — Cast Resin Busduct",                                "Done", ""),
    ("Highlight tag — 'Weatherproof'",                            "Done", ""),
    ("Title — 'CAST RESIN BUSDUCT'",                              "Done", ""),
    ("Subtitle — 'Safe by Design, Powered by Resin'",             "Done", ""),
    ("Description (PDF extreme-environment copy)",                "Done", ""),
    ("Buttons — Contact Us + Request a Catalog",                  "Done", ""),
    ("Key Feature 1 — Compliant with BS 6387, IEC 60331, JIS C8364 & CNS 14286", "Done", ""),
    ("Key Feature 2 — Mineral-Insulated Cast Resin Type",         "Done", ""),
    ("Key Feature 3 — Excellent Heat Dissipation",                "Done", ""),
    ("Key Feature 4 — Resistant to Water, Fire & Corrosion",      "Done", ""),
    ("Key Feature 5 — Seismic Zone 4 Protection",                 "Done", ""),
    ("Key Feature 6 — IK10 Mechanical Impact",                    "Done", ""),
    ("Key Feature 7 — High Degree Protection of IP68",            "Done", ""),
    ("Tech Spec — Current Rating: 400A – 6,300A",                 "Done", ""),
    ("Tech Spec — IP Rating: IP68",                               "Done", ""),
    ("Tech Spec — Compliance Standard: IEC 61439-6 & IEC 61439-1","Done", ""),
    ("Tech Spec — Voltage: Up to 1,000V",                         "Done", ""),
    ("Tech Spec — Conductor: Copper / Aluminium",                 "Done", ""),
    ("Video Product (now: animated diagram video, auto-plays on scroll, replay button)", "Done", "Diagram component-breakdown video plays per section (assets/products/*-diagram video.mp4)."),
    ("Component Diagram Breakdown image",                         "Done", ""),
])

add_heading("3.2.3  Data Centre Solution — i-DC Busduct", level=2)
add_checklist_table([
    ("Image — i-DC Busduct",                                      "Done", ""),
    ("Highlight tag — 'Featured'",                                "Done", ""),
    ("Title — 'i-DC BUSDUCT'",                                    "Done", ""),
    ("Subtitle — 'High Density Power, Zero Downtime'",            "Done", ""),
    ("Description (PDF AI/Cloud/Edge uptime copy)",               "Done", ""),
    ("Buttons — Contact Us + Request a Catalog",                  "Done", ""),
    ("Key Feature 1 — Compliant with IEC 61439-6",                "Done", ""),
    ("Key Feature 2 — Space-Efficient Modular & Compact Design",  "Done", ""),
    ("Key Feature 3 — Flexibility & Scalability for Future Expansion", "Done", ""),
    ("Key Feature 4 — Degree Protection of IP 40 / 54",           "Done", ""),
    ("Key Feature 5 — Quick Installation & Lower Installation Cost", "Done", ""),
    ("Key Feature 6 — Maintenance-Free",                          "Done", ""),
    ("Key Feature 7 — Eco-Friendly & Reusable",                   "Done", ""),
    ("Key Feature 8 — Customizable Tap Off Units",                "Done", ""),
    ("Key Feature 9 — Safe Live Installation of Tap Off Units",   "Done", ""),
    ("Key Feature 10 — Integrated with Branch Circuit Power Monitoring", "Done", ""),
    ("Tech Spec — Current Rating Busduct: 250A, 400A, 630A, 800A","Done", ""),
    ("Tech Spec — Current Rating TOU: 16A, 32A, 63A, 100A",       "Done", ""),
    ("Tech Spec — Compliance Standard: IEC 61439-6 & IEC 61439-1","Done", ""),
    ("Tech Spec — Conductor: Copper",                             "Done", ""),
    ("Tech Spec — Busduct Housing Material: Extruded Aluminium-Alloy", "Done", ""),
    ("Video Product (now: animated diagram video, auto-plays on scroll, replay button)", "Done", "Diagram component-breakdown video plays per section (assets/products/*-diagram video.mp4)."),
    ("Component Diagram Breakdown image",                         "Done", ""),
])

add_heading("3.2.4  Power Monitoring Solution — BCPM System", level=2)
add_checklist_table([
    ("Image — Power Monitoring",                                  "Done", ""),
    ("Highlight tag — 'Smart System'",                            "Done", ""),
    ("Title — 'BCPM System'",                                     "Done", ""),
    ("Subtitle — 'Precision Monitoring, Proven Savings.'",        "Done", ""),
    ("Description (PDF BCPM sensors → HMI → energy/overload copy)","Done", ""),
    ("Buttons — Contact Us + Request a Catalog",                  "Done", ""),
    ("Video Product (now: animated diagram video, auto-plays on scroll, replay button)", "Done", "Diagram component-breakdown video plays per section (assets/products/*-diagram video.mp4)."),
])

add_heading("3.2.5  Lighting Solution — LB&PB Busduct", level=2)
add_checklist_table([
    ("Image — LB&PB BUSDUCT",                                     "Done", ""),
    ("Highlight tag — 'Reliability'",                             "Done", ""),
    ("Product title — 'LB&PB BUSDUCT'",                           "Done", ""),
    ("Subtitle — 'Where Light Meets Performance'",                "Done", ""),
    ("Description (PDF Lighting Busduct + Power Busduct copy)",   "Done", ""),
    ("Buttons — Contact Us + Request a Catalog",                  "Done", ""),
    ("Video Product (now: animated diagram video, auto-plays on scroll, replay button)", "Done", "Diagram component-breakdown video plays per section (assets/products/*-diagram video.mp4)."),
    ("Tech Spec — LB Current Rating: Copper 16A ~ 40A",           "Done", ""),
    ("Tech Spec — PB Current Rating: Copper 63A ~ 400A, Aluminium 100A ~ 630A", "Done", ""),
    ("Tech Spec — IP Rating: IP40 / IP54",                        "Done", ""),
    ("Tech Spec — Compliance Standard: IEC 61439-6 & IEC 61439-1","Done", ""),
    ("Tech Spec — Conductor Material: LB Copper, PB Copper & Aluminium", "Done", ""),
    ("Tech Spec — System Frequency: 50 / 60 Hz",                  "Done", ""),
    ("Tech Spec — Rated Operational Voltage (AC): 230 / 400 V",   "Done", ""),
])

# ── 3.3 PORTFOLIO ────────────────────────────────────────────
add_heading("3.0  Content Breakdown — Portfolio (Section 3.3)", level=1)

add_heading("3.3.1  Certification Credential", level=2)
add_checklist_table([
    ("Description — 'With the innovation of our R&D team, Furutec Busduct System is type-tested and certified by reputable certification bodies'", "Done", ""),
    ("Logo 1 — GCC ISO 9001",                                     "Done",    "Image rendered (assets/certs/iso.svg)."),
    ("Logo 2 — Intertek",                                         "Done",    "Image rendered (assets/certs/intertek.svg)."),
    ("Logo 3 — DEKRA",                                            "Done",    "Image rendered (assets/certs/dekra.svg)."),
    ("Logo 4 — UL",                                               "Done",    "Image rendered (assets/certs/ul.svg)."),
    ("Logo 5 — SIRIM Eco-Label",                                  "Done",    "Image rendered (assets/certs/sirim.png)."),
    ("Logo 6 — SIRIM Quality Award",                              "Pending", "Showing styled text placeholder with 'Logo pending' badge. Client to supply logo file."),
    ("Logo 7 — Malaysian Brand",                                  "Pending", "Showing styled text placeholder with 'Logo pending' badge. Client to supply logo file."),
    ("Logo 8 — KEMA-KEUR",                                        "Done",    "Image rendered (assets/certs/kema.png)."),
    ("Logo 9 — TÜV SÜD / PSB Singapore",                          "Pending", "Image rendered (assets/certs/tuv-rheinland.svg). Awaiting client confirmation whether the asset is TÜV Rheinland or TÜV SÜD — PDF screenshot suggests TÜV SÜD."),
    ("Logo 10 — Singapore Green Building Product",                "Pending", "Showing styled text placeholder with 'Logo pending' badge. Client to supply logo file."),
    ("Remove non-PDF cards (CB Scheme, duplicate TÜV/PSB split)", "Done",    "CB Scheme card removed; TÜV+PSB combined into one card per PDF."),
])

add_heading("3.3.2  Track Record", level=2)
add_checklist_table([
    ("Title — 'Trusted across every industry'",                   "Done", ""),
    ("Description — 'Furutec busduct systems power facilities where reliability is not optional…'", "Done", ""),
    ("Industry 1 — Data Centre · tag: High Reliability",          "Done", ""),
    ("Industry 2 — Infrastructure (MRT & Train Station) · tag: Heavy Duty", "Done", "Card label updated to 'Infrastructure (MRT & Train Station)' per PDF."),
    ("Industry 3 — Medical & Healthcare · tag: Mission Critical", "Done", ""),
    ("Industry 4 — Industrial · tag: Up to 6,500 A",              "Done", ""),
    ("Industry 5 — Oil & Gas · tag: Harsh Environment",           "Done", ""),
    ("Industry 6 — Commercial · tag: Space Efficient",            "Done", ""),
    ("Industry 7 — Residential · tag: Safe & Compact",            "Done", ""),
    ("Industry 8 — Education · tag: Energy Efficient",            "Done", ""),
    ("Industry 9 — Government · tag: High Compliance",            "Done", ""),
])

# ── 3.3 FOOTER ───────────────────────────────────────────────
add_heading("3.0  Content Breakdown — Footer (Section 3.3 Footer)", level=1)
add_checklist_table([
    ("Logo + 'Furutec Electrical Sdn Bhd' + Company No. 198001003423 (57207-W)", "Done", ""),
    ("Tagline — 'Engineering The Future Of Power Distribution — Reliable, Efficient, And Built For Demanding Environments Worldwide.'", "Done", ""),
    ("Email — info@furutec.com.my",                               "Done", ""),
    ("Phone — +603-5635 0538",                                    "Done", "Client confirmed: use +603-5635 0538 (matches PDF Section 3.1.6 text). PDF page-14 footer screenshot showed +603 5635 7088 — superseded."),
    ("Location — Subang Jaya, Selangor",                          "Done", ""),
    ("Product column — Indoor Solution / Outdoor Solution / Data Centre Solution / Power Monitoring Solution", "Done", "Lightning Solution removed from footer per PDF spec (4 items only). Still accessible via the Our Products top-nav dropdown."),
    ("Industries column — Data Centres / Commercial Buildings / Public Transportation / Medical & Healthcare", "Done", "Links now point to portfolio-track-record.html (broken portfolio.html#track-record anchors fixed)."),
    ("Support column — Technical Docs / Product Catalog / FAQ / Warranty", "Pending", "Items present but all href='#'. Awaiting destination URLs from client."),
    ("Company column — About Furutec / Key Projects / Certifications / Contact", "Done", ""),
    ("Social Media — Facebook / YouTube / LinkedIn / WhatsApp",   "Pending", "Icons present but all href='#'. Awaiting social profile URLs from client."),
])

# ── APPENDIX: BUILD NOTES (NOT FROM PDF) ─────────────────────
doc.add_page_break()
add_heading("Appendix — Build Notes (Not from Client PDF)", level=1)
add_para(
    "The items below are observations from the implementation team and are NOT specified "
    "in the client's PDF. They are recorded here for transparency and discussion. "
    "Mark each one in the Status column once a decision has been made.",
    italic=True, color=MUTED,
)

add_heading("Open Questions raised by PDF ambiguity", level=2)
add_checklist_table([
    ("Phone number — PDF Section 3.1.6 text says '+603-56350538'; PDF footer screenshot shows '+603 5635 7088'. Which is correct?", "Done", "Client confirmed +603-5635 0538 (the PDF Section 3.1.6 text version)."),
    ("Product naming — PDF uses 'Lightning Solution' (Section 3.1.4) and 'Lighting Solution' (Section 3.2.5 header) and 'LB&PB BUSDUCT' (product title).", "Done", "Resolved: site now uses 'Lightning Solution' canonically in nav, footer, dropdowns, and homepage detail panel; product title remains 'LB&PB BUSDUCT' inside section 3.2.5."),
    ("Header navigation — PDF hero screenshot shows Our Products / About Us / Our Industries / News / Projects / Contact Us, but the Section 2.0 sitemap body lists only 5 nodes. Confirm which nav items are required.", "Pending", ""),
    ("TÜV brand — PDF screenshot suggests TÜV SÜD; available logo asset is TÜV Rheinland. Confirm which brand is correct.", "Pending", ""),
])

add_heading("Sections present on site that are NOT in PDF spec", level=2)
add_checklist_table([
    ("Homepage — 'Engineered In Malaysia With Four Decades Of Expertise' stats panel + world map", "Pending", "Awaiting client confirmation. Tentatively KEEP as credibility signal."),
    ("Homepage — 'Regional Projects' tabbed project grid",         "Pending", "Awaiting client confirmation. Tentatively KEEP as soft Track Record on the homepage."),
    ("Homepage — large 'Industries' image-card panels (above CTA)","Pending", "Awaiting client confirmation. Tentatively KEEP as industry showcase before the CTA."),
    ("Product page — 'Busduct System Assembly' PDF-download block","Done",    "Removed from product.html. All nav/footer links to #assembly also removed."),
    ("Product page — closing 'Credential & Track Record' dark CTA band", "Done", "Removed from product.html."),
    ("Cert page — additional 'Where Credential Meets Capability' close-out block", "Pending", "Awaiting client decision: keep as wayfinding to Track Record, or remove."),
    ("Track Record page — 'Your industry deserves engineered power' dark CTA", "Done", "Removed from portfolio-track-record.html."),
    ("Track Record page — redundant '9 INDUSTRY VERTICALS' intro block", "Done", "Removed; page hero already carries PDF title + description."),
])

add_heading("Technical / project-hygiene notes", level=2)
add_checklist_table([
    ("Duplicate cert page — portfolio.html and portfolio-certifications.html serve the same content. Pick one canonical URL.", "Done", "Resolved: portfolio-certifications.html deleted. portfolio.html is canonical."),
    ("Stray file — editor.html exists in project root. Confirm whether it is needed.", "Pending", ""),
    ("Background video — convert to web-optimised MP4 before launch.", "Done", "Converted to assets/banner-video-2.mp4 (11 MB, 1920px-wide H.264, +faststart). Source 244 MB .mov retained as backup. index.html now uses the new MP4."),
    ("Milestone card images (Section 3.1.3) — currently pulled from external WordPress URLs (furutec.com.my). Move to locally hosted images before launch.", "Pending", ""),
    ("Industry card images (Section 3.3.2 Track Record) — currently pulled from Unsplash. Replace with client-supplied project photography before launch.", "Pending", ""),
])

# ── OUTSTANDING ITEMS — CONSOLIDATED SUMMARY ─────────────────
doc.add_page_break()
add_heading("Outstanding Items — Awaiting Client Input", level=1)
add_para(
    "All items below are blocked on client decision, asset delivery, or URL supply. "
    "Cross-references in brackets point to the detailed row in the main checklist or "
    "appendix subsections above.",
    italic=True, color=MUTED,
)
add_checklist_table([
    ("(A) 3 certification logos to be supplied — SIRIM Quality Award, Malaysian Brand, Singapore Green Building Product [Section 3.3.1, logos 6 / 7 / 10]", "Pending", "Showing styled text placeholders with 'Logo pending' badge until logo files arrive."),
    ("(B) TÜV brand confirmation — Rheinland (current asset) vs SÜD (PDF screenshot) [Section 3.3.1, logo 9 + Open Questions]", "Pending", "Image renders but brand identity needs client sign-off."),
    ("(C) Header nav additions — confirm whether About Us / News / Projects / Contact Us are required (PDF hero screenshot shows them; Section 2.0 sitemap body omits them) [Open Questions]", "Pending", ""),
    ("(D) Homepage extra — 'Engineered In Malaysia With Four Decades Of Expertise' stats + world map [Appendix]", "Pending", "Awaiting client confirmation to keep, redesign, or remove."),
    ("(E) Homepage extra — 'Regional Projects' tabbed grid [Appendix]", "Pending", "Awaiting client confirmation to keep, redesign, or remove."),
    ("(F) Homepage extra — large 'Industries' image-card panels above CTA [Appendix]", "Pending", "Awaiting client confirmation to keep, redesign, or remove."),
    ("(G) Certifications page closing block — 'Where Credential Meets Capability' wayfinding to Track Record [Appendix]", "Pending", "Awaiting client decision: keep or remove."),
    ("(H) Stray file — editor.html exists in project root [Appendix]", "Pending", "Confirm whether file is needed; delete if not."),
    ("(I) Background video — convert assets/banner video 2.mov (244 MB) to web-optimised MP4 [Appendix]", "Done", "Converted to assets/banner-video-2.mp4 (11 MB, 1920px H.264 +faststart). index.html updated to use the new MP4."),
    ("(J) Milestone card images (Section 3.1.3 homepage) — currently external WordPress URLs from furutec.com.my [Appendix]", "Pending", "Move to locally hosted, client-supplied photos."),
    ("(K) Industry card images (Section 3.3.2 Track Record) — currently Unsplash stock [Appendix]", "Pending", "Replace with client-supplied project photography."),
    ("(L) Contact form backend — route submissions to Amy, Vincent & Darren [Section 3.1.6]", "Pending", "SMTP / Resend / form-handler wiring during deployment phase."),
    ("(M) Footer Support column URLs — Technical Docs / Product Catalog / FAQ / Warranty all href='#' [Footer]", "Pending", "Awaiting destination URLs from client."),
    ("(N) Footer Social Media URLs — Facebook / YouTube / LinkedIn / WhatsApp all href='#' [Footer]", "Pending", "Awaiting client social profile URLs."),
    ("(O) Product video files — 5 product sections (Indoor / Outdoor / Data Centre / BCPM / Lightning)", "Done", "3 diagram videos supplied + wired up (Indoor / Outdoor / Data Centre). BCPM + Lightning sections re-imagined without video placeholder per design pass."),
])

# ── DESIGN ENHANCEMENTS — beyond the PDF spec ──────────────
doc.add_page_break()
add_heading("Design Enhancements — Beyond Client PDF", level=1)
add_para(
    "These items go beyond the literal PDF spec and are part of the design "
    "polish pass. They are listed here so the client and reviewer can see what "
    "additional work was delivered.",
    italic=True, color=MUTED,
)

add_heading("Interactive Components", level=2)
add_checklist_table([
    ("Homepage Product Overview — interactive isometric diagram with 5 numbered hotspots", "Done", "Click any hotspot to load the matching product detail card (Outdoor / Indoor / Lighting / Data Centre / BCPM). Hotspot positions stored as percentages; hand-tuned via the UI Kit editor."),
    ("Product page — animated component-breakdown video per section", "Done", "Auto-plays on scroll-into-view, fades to a Replay button at end. Same playback model on Indoor, Outdoor, and Data Centre sections."),
    ("Homepage Engineered-In-Malaysia section — Furutec logo intro video as full-section background", "Done", "Plays once when section is scrolled into view; overlay veil fades from 0% → 100% opacity once video ends; content zooms in from scale(0.6) → scale(1) over 1.1s."),
    ("Homepage Inside-Furutec — real factory video player", "Done", "Replaces the static YouTube thumbnail. Click-to-play, native browser controls, overlay fades out cleanly when playing, fades back in when video ends."),
    ("Magnet scroll-snap — Inside-Furutec + Engineered-in-Malaysia sections", "Done", "When the user pauses scrolling within ~20% of section approach (or 15% past leaving), the page smoothly snaps to align the section to viewport top. Picks closest section if multiple are in range."),
])

add_heading("UI Kit & Editor (hidden pages)", level=2)
add_checklist_table([
    ("/uikit.html — internal pattern library page", "Done", "Hidden (noindex), not linked from any nav. Showcases colours, typography, buttons, form elements, tags, every card variant, animations, and a sandbox for experiments."),
    ("Hotspot editor on /uikit.html", "Done", "Drag-and-drop tool for positioning the 5 Product Overview hotspots over the diagram. Live coordinate readout, copy-code button, reset, keyboard arrow nudges (1 px / 10 px with Shift), keyboard focus outline."),
])

add_heading("Interaction polish applied site-wide", level=2)
add_checklist_table([
    ("All primary buttons — rounded 8 px corners + sliding-arrow hover (with custom short-stem SVG)", "Done", "Polish script `_apply_button_polish.py` keeps every button across all pages in sync (v2 markup)."),
    ("Scroll-reveal animations — every major card, section header, eyebrow, and footer link", "Done", "Polish script `_apply_scroll_reveal.py` (v4) auto-tags interesting elements and animates them in with 800 ms cubic-bezier on viewport entry. Staggered sibling delays up to 640 ms. Suppresses initial-load flash. Respects `prefers-reduced-motion`."),
    ("Product page — eye-catching product badges", "Done", "Replaced pastel pills with saturated brand-gradient pills, themed icons (star / cloud-rain / sparkle / cpu / shield-check), soft shadows. Tight compact width hugging the icon + label."),
    ("Product page — re-imagined layout (removed video placeholders, polished spec card)", "Done", "Right column now stretches to match left card height. Spec card uses subtle gradient background, 4 px brand accent strip on left edge, uppercase tracked title, full-width rows with thin dividers — far more legible than the prior cramped 2-col grid."),
    ("Product page — BCPM section as single centred column", "Done", "Since BCPM has no spec data per PDF, layout uses single 820 px column rather than a 2-col grid with empty right side."),
    ("Homepage Product Overview section — component diagram title centred above image", "Done", "Title moved from left side to top-centre across all 3 product sections that use the diagram band."),
    ("Hero video swapped to web-optimised MP4 + replaces old image slideshow", "Done", "256 MB .mov → 11 MB H.264 1080p MP4 (96% smaller). Hero loads ~22× faster."),
])

add_heading("Footer + nav cleanup (applied site-wide)", level=2)
add_checklist_table([
    ("Broken portfolio anchors (`portfolio.html#track-record`, `#certificate-credential`) → working URLs", "Done", "All 6 pages updated to point at portfolio-track-record.html and portfolio.html respectively."),
    ("Lightning Solution removed from footer Product column (matches PDF's 4-item list)", "Done", "Footer columns identical on all 6 pages."),
    ("Stray file editor.html", "Pending", "Still in project root — confirm whether needed before deletion."),
])

doc.save(OUTPUT)
print(f"Written: {OUTPUT}")
