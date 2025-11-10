# Website Content Structure

home
  hero
    hero_intro_prefix / hero_intro_name_first / hero_intro_name_rest / hero_intro_suffix
    hero_title / hero_subtitle / hero_description
    primary_cta { text, url }
    secondary_cta { text, url('contact') }
    hero_stats[] { value, label } (max 3)
    hero_background_image_url (fallback images/hero-portrait.jpg)
    scroll_indicator { text }
  featured_work
    section_title / section_subtitle
    projects[] {
      image_url, slug, title, category, short_description,
      client (shown when client_visibility!='no'),
      project_url(optional), featured flag,
      technologies[] (preview top 3 + remainder count)
    }
    view_all_projects_cta { text, url('portfolio') }
  services_preview
    section_title / section_subtitle
    services[] {
      id, icon, title, description,
      features[] (max 5)
    }
  skills_tools
    section_title / section_subtitle
    categories[] {
      name, icon(fallback bi-palette),
      skills[] { name, proficiency_level% }
    }
  testimonials
    section_title / section_subtitle
    testimonials[] {
      rating, testimonial_text,
      client_name, client_position, client_company,
      client_image
    }
  page_cta
    title / subtitle
    primary_cta { text="Get In Touch", url('contact') }
    secondary_cta { text="View Services", url('services') }

about
  about_hero
    about_image_url (fallback images/about-photo.jpg)
    about_title / about_subtitle / about_content
    about_skills[] (highlight list, max 6)
  timeline (optional page_sections['timeline'])
    title / subtitle
    items[] {
      title, company, date,
      description, tags[],
      is_education flag
    }
  extra_content (optional page_content HTML)
  skills_detail (shared skills_by_category)
    section_title / section_subtitle
    categories[] {
      name,
      skills[] { name, proficiency_level%, progress bar }
    }
  testimonials (same schema as home)
  page_cta
    title / subtitle
    primary_cta { text="Get In Touch", url('contact') }
    secondary_cta { text="View Portfolio", url('portfolio') }

services
  services_hero
    title / subtitle
  main_services
    cards[] {
      id, icon, title, description,
      features[], price_label, price_amount
    }
  design_process
    section_title / section_subtitle
    steps[] { order, icon, title, description }
  packages
    section_title / section_subtitle
    plans[] {
      name, price_amount, description,
      features[],
      cta { text, url('contact') },
      featured_badge(optional)
    }
  faq
    section_title / section_subtitle
    faqs[] { question, answer, default_open flag }
  page_cta
    title / subtitle
    primary_cta { text="Start Your Project", url('contact') }
    secondary_cta { text="View My Work", url('portfolio') }

portfolio_index
  header
    title / subtitle
    filters { all_link, categories[] }
    search_form { placeholder, query_param 'search' }
  portfolio_grid
    projects[] {
      image_url, slug, title, category,
      short_description,
      client (honors client_visibility),
      featured flag,
      project_url(optional),
      technologies[] (chips + overflow badge)
    }
    empty_state { icon, message variants, reset_cta }
  page_cta
    title / subtitle
    primary_cta { text="Start a Project", url('contact') }
    secondary_cta { text="View Services", url('services') }

portfolio_show
  project_header
    breadcrumb { text, url('portfolio') }
    category_badge / title / short_description
    meta {
      client(optional),
      created_at year,
      project_url button(optional)
    }
  project_hero_image { image_url, alt }
  project_details
    overview { description rich text }
    challenge { static storytelling block }
    solution { static storytelling block }
    process_steps[] { order, title, description }
  sidebar
    technologies[] (chips)
    project_info {
      client(optional), category, year,
      project_url(optional)
    }
    share_buttons { linkedin, twitter, copy_link }
  gallery (optional)
    images[] { filename, alt }
  results
    cards[] { metric_value, title, description }
  project_navigation
    previous_project { title, slug } optional
    next_project { title, slug } optional
  page_cta
    title / subtitle
    primary_cta { text="Start a Project", url('contact') }
    secondary_cta { text="View More Work", url('portfolio') }

contact
  header
    title / subtitle
  contact_content
    form {
      fields: name, email, subject, message;
      flash_errors;
      submit_cta { text="Send Message" }
    }
    contact_methods[] {
      type(email/phone/location),
      value,
      helper_text
    }
    social_links[] { platform, icon }
    response_info[] { label, value }
  faq
    section_title / section_subtitle
    faqs[] { question, answer, default_open flag }
  page_cta
    title / subtitle
    primary_cta { text="Send Message", anchor '#contactForm' }
    secondary_cta { text="View My Work", url('portfolio') }

error_pages
  404
    hero { code, title, description, illustration }
    actions { home_cta, back_cta, contact_link }
  500
    hero { code (glitch animation), title, description, illustration }
    optional_debug { last error log lines via ?debug=1 }
    actions { home_cta, retry_cta }
    guidance_list { refresh, check back, contact }
    optional_retry_indicator
